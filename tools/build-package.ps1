<#
  build-package.ps1 — Crea il pacchetto zip di Globo MCP pronto per il deploy
  su www.mcprotection.it/globo-mcp.

  custom/src/config/www_mcprotection_it.php e config_smtp.php contengono i valori
  reali di produzione (URL, tema, moduli attivi), TRANNE i segreti (utente e
  password del DB, credenziali SMTP): nel repository sono segnaposti __PROD_*__, i
  valori veri stanno in tools/prod-secrets.local.ps1 (non versionato) e il passo 4d
  li rimette nella copia. I valori di sviluppo vivono SOLO in dev-overlay/, che
  questo script NON applica.

  Lavora SEMPRE su una copia temporanea in dist/: core/src e custom/src non
  vengono mai modificati.

  Composizione (stessa logica di docker/php8/compose-webroot.sh, senza il livello
  dev-overlay):
    1. core/src         -> base
    2. custom/src       -> sovrascrive/aggiunge (vince sul core)
  Poi rimuove config/globo-ks_kosmosol_it.php (config di KosmoSol, inutilizzato:
  il selettore in config/config.php sceglie il file in base a HTTP_HOST, per
  www.mcprotection.it usa sempre www_mcprotection_it.php) e le librerie di terze
  parti usate solo da moduli disattivati in MCP (MOD_ADHOC_POST=false), poi
  verifica che nella config risultante ci siano i valori di produzione attesi.

  Uso:
    powershell -ExecutionPolicy Bypass -File tools/build-package.ps1
    powershell ... -File tools/build-package.ps1 -Version "1.0.1"

  Cosa include: core/src + custom/src fusi, ESCLUSI upload/ e globo_log/ (dati
  runtime, gia' presenti sul server). Cosa esclude: dev-overlay/, docs/, docker/,
  db-init/, .git, ecc.
#>

param(
  [string]$Version = "1.0.0",
  # Dove mettere al riparo i pacchetti gia' presenti in dist/ prima di cancellarla.
  # Fuori dal repo: dist/ e' in .gitignore e i pacchetti pesano un paio di MB l'uno.
  [string]$ArchiveDir = "C:\Temp\Globo-mcp-pacchetti"
)

$ErrorActionPreference = "Stop"

# ── Segreti di produzione (file locale, non versionato) ─────────────────────
# Caricati subito: se mancano, ci si ferma prima di toccare dist/.
$secretsFile = Join-Path $PSScriptRoot "prod-secrets.local.ps1"
if (-not (Test-Path $secretsFile)) {
  throw "Segreti mancanti: $secretsFile`n  Copia tools/prod-secrets.example.ps1 in prod-secrets.local.ps1 e inserisci i valori veri."
}
. $secretsFile
if (-not $ProdSecrets -or $ProdSecrets.Count -eq 0) {
  throw "prod-secrets.local.ps1: manca `$ProdSecrets (vedi tools/prod-secrets.example.ps1)"
}

# ── Percorsi ────────────────────────────────────────────────────────────────
$repoRoot  = Split-Path -Parent $PSScriptRoot
$coreSrc   = Join-Path $repoRoot "core\src"
$customSrc = Join-Path $repoRoot "custom\src"
$distDir   = Join-Path $repoRoot "dist"
$stageDir  = Join-Path $distDir  "globo"           # root interna allo zip
$zipPath   = Join-Path $distDir  "globo-mcp-$Version.zip"

Write-Host "== Build pacchetto Globo MCP $Version ==" -ForegroundColor Cyan
Write-Host "Repo:   $repoRoot"
Write-Host "Core:   $coreSrc"
Write-Host "Custom: $customSrc"

# ── 1. Archivio dei pacchetti esistenti, poi pulizia di dist/ ─────────────
# Il build cancella dist/ per intero, quindi porta via anche il pacchetto che in
# quel momento e' LIVE in produzione. E' successo su 5stelle il 2026-09-17:
# generando il 1.1.9 e' sparito il 1.1.8, l'unica copia di cio' che era stato
# davvero spedito sul server. Serve per confrontare la webroot online con quello
# che le e' stato mandato, e per ricaricarlo al volo in un rollback.
# Non si sovrascrive mai un file gia' in archivio: quello e' il pacchetto vero
# spedito allora, una ricostruzione successiva potrebbe non essere identica.
if (Test-Path $distDir) {
  $daSalvare = @(Get-ChildItem -Path $distDir -Filter *.zip -File)
  if ($daSalvare.Count -gt 0) {
    if (-not (Test-Path $ArchiveDir)) { New-Item -ItemType Directory -Force -Path $ArchiveDir | Out-Null }
    foreach ($z in $daSalvare) {
      $dest = Join-Path $ArchiveDir $z.Name
      if (Test-Path $dest) {
        Write-Host "  [=] $($z.Name): gia' in archivio, non sovrascritto" -ForegroundColor DarkGray
      } else {
        Copy-Item -Path $z.FullName -Destination $dest
        Write-Host "  [ok] $($z.Name) messo al riparo in $ArchiveDir" -ForegroundColor Green
      }
    }
  }
  Remove-Item -Recurse -Force $distDir
}
New-Item -ItemType Directory -Force -Path $stageDir | Out-Null

# ── 2. Composizione: core -> staging, poi custom sopra (vince) ──────────────
# robocopy: /E ricorsivo (incl. vuote), /XD esclude cartelle. Exit code < 8 = ok.
# Lo staging parte vuoto ad ogni run, quindi non serve /MIR o --delete: la
# seconda passata (custom) sovrascrive semplicemente i path in comune.
robocopy $coreSrc   $stageDir /E /XD (Join-Path $coreSrc   "upload") (Join-Path $coreSrc   "globo_log") /NFL /NDL /NJH /NJS /NP | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy (core) fallito (exit $LASTEXITCODE)" }
$global:LASTEXITCODE = 0

robocopy $customSrc $stageDir /E /XD (Join-Path $customSrc "upload") (Join-Path $customSrc "globo_log") /NFL /NDL /NJH /NJS /NP | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy (custom) fallito (exit $LASTEXITCODE)" }
$global:LASTEXITCODE = 0

Write-Host "  [ok] core + custom composti in staging" -ForegroundColor Green

# Cartelle dati: escluse dalla copia, ricreate vuote con un placeholder cosi'
# esistono all'estrazione su un server nuovo. L'unzip su un deploy esistente
# aggiunge solo il placeholder, non tocca i file gia' caricati.
# NB: la cartella documenti del modulo adhoc_mcp_* (MCP_DIR_DOC in config) NON
# e' qui: punta a httpdocs/documenti/upload/, FUORI dalla webroot globo-mcp/
# (un livello sopra - vedi docs/deploy.md). Non va creata dentro lo zip,
# altrimenti finirebbe nel posto sbagliato una volta estratto.
foreach ($d in @("upload", "globo_log")) {
  $dir = Join-Path $stageDir $d
  New-Item -ItemType Directory -Force -Path $dir | Out-Null
  Set-Content -Path (Join-Path $dir "index.html") -Value "" -NoNewline -Encoding ascii
}

# ── 3. Rimozione file/cartelle del core inutilizzati su MCP ─────────────────
# config/globo-ks_kosmosol_it.php e' la config di KosmoSol: il selettore in
# config/config.php (HTTP_HOST-based) non lo referenzia mai per il dominio
# www.mcprotection.it. Non serve nello zip MCP.
$ksConfigPath = Join-Path $stageDir "config\globo-ks_kosmosol_it.php"
if (Test-Path $ksConfigPath) {
  Remove-Item -Force $ksConfigPath
  Write-Host "  [ok] rimosso config/globo-ks_kosmosol_it.php (non usato da MCP)" -ForegroundColor Green
} else {
  Write-Host "  [!] config/globo-ks_kosmosol_it.php non trovato in staging (gia' assente?)" -ForegroundColor Yellow
}

# Librerie di terze parti nel core usate SOLO da moduli disattivati su MCP
# (adhoc_post_*, dietro MOD_ADHOC_POST - mai attivato, verificato 2026-07-13:
# nessuna pagina raggiungibile in MCP referenzia ckeditor/elfinder/imageuploader/
# fpdf/mdbootstrap). shadowbox e phpmailer restano: shadowbox e' usata da
# adhoc_mcp_acq_doc.php (modulo attivo), phpmailer dal core per l'invio email
# (globo_invia_email in varie/funzioni.php, trasversale).
foreach ($lib in @("ckeditor", "elfinder", "imageuploader", "fpdf", "mdbootstrap")) {
  $libPath = Join-Path $stageDir $lib
  if (Test-Path $libPath) {
    Remove-Item -Recurse -Force $libPath
    Write-Host "  [ok] rimossa $lib/ (usata solo da moduli disattivati su MCP)" -ForegroundColor Green
  }
}

# Endpoint di invio massivo specifici di KosmoSol: leggono un CSV da
# upload/scadx/ e upload/promemoria/ con path assoluto sulla webroot di
# globo-ks e spediscono email. Nessuna pagina di Globo li referenzia (sono
# richiamati da fuori, protetti solo da una chiave giornaliera debole
# md5(Ymd."globok0s")). Su MCP sarebbero codice morto ma raggiungibile: li
# togliamo dal pacchetto invece di lasciare in giro un endpoint che invia
# mail. Vedi Globo8/docs/refactoring-notes.md, sezione CUSTOM-CANDIDATE.
foreach ($ks in @("ins_invio_scadx_2.php", "ins_invio_promemoria_2.php")) {
  $ksPath = Join-Path $stageDir $ks
  if (Test-Path $ksPath) {
    Remove-Item -Force $ksPath
    Write-Host "  [ok] rimosso $ks (endpoint specifico di KosmoSol)" -ForegroundColor Green
  }
}

# ── 4. Verifica valori di produzione nella config composta ──────────────────
# Non facciamo sostituzioni (i valori prod sono gia' in custom/src), ma
# verifichiamo che siano davvero quelli attesi: se custom/src venisse mai
# disallineato (es. un dev-overlay fuso per errore, o un valore dimenticato),
# lo script si ferma invece di spedire uno zip con valori sbagliati.
$mcpConfigPath = Join-Path $stageDir "config\www_mcprotection_it.php"
if (-not (Test-Path $mcpConfigPath)) { throw "Config MCP mancante in staging: $mcpConfigPath" }
$mcpConfig = [IO.File]::ReadAllText($mcpConfigPath)

$checks = @(
  'define("URL_GLOBO", "http://www.mcprotection.it/globo-mcp");',
  'define("GLOBO_THEME", "verde-querce");',
  'define("AUTH_TOKEN", false);',
  'define("MOD_ADHOC_MCP", true); define("LIV_MOD_ADHOC_MCP", 1);'
)
foreach ($c in $checks) {
  if (-not $mcpConfig.Contains($c)) {
    throw "VALORE DI PRODUZIONE NON TROVATO in config/www_mcprotection_it.php:`n  atteso: $c`nLo script si ferma per non spedire una config sospetta (dev-overlay fuso per errore?)."
  }
}
Write-Host "  [ok] config/www_mcprotection_it.php: valori di produzione verificati" -ForegroundColor Green

# ── 4b. Guardrail: nessun path di un'altra installazione nel pacchetto ─────
# Gli script in script/ del core NON sono neutri: sono i file di produzione di
# KosmoSol, con $dominio e i require a path assoluti su globo-ks.kosmosol.it.
# Se un clone non li sovrascrive in custom/src/script/, finiscono nello zip
# cosi' come sono e in produzione muoiono al primo require (file inesistente su
# un'altra macchina) - cron morti in silenzio, nessuna mail e nemmeno una riga
# di log. E' successo davvero su MCP: le mail delle scadenze si sono fermate
# dal giorno del deploy e il cliente se n'e' accorto due settimane dopo.
# Da qui in poi il pacchetto non si chiude nemmeno.
$sospetti = @()
foreach ($f in Get-ChildItem -Path $stageDir -Recurse -File -Filter *.php) {
  # Riga per riga, saltando i commenti: un commento che cita globo-ks e'
  # documentazione (es. authorization.php spiega il cookie di produzione),
  # non un path che verra' aperto a runtime.
  foreach ($riga in [IO.File]::ReadAllLines($f.FullName)) {
    $t = $riga.TrimStart()
    if ($t.StartsWith("//") -or $t.StartsWith("#") -or $t.StartsWith("*") -or $t.StartsWith("/*")) { continue }
    if ($riga -match 'globo-ks\.kosmosol\.it') {
      $sospetti += $f.FullName.Substring($stageDir.Length + 1)
      break
    }
  }
}
if ($sospetti.Count -gt 0) {
  throw ("PATH DI UN'ALTRA INSTALLAZIONE nel pacchetto (globo-ks.kosmosol.it):`n  " +
         ($sospetti -join "`n  ") +
         "`nOgni file elencato va sovrascritto in custom/src/ con i path di" +
         " mcprotection.it. Vedi CLAUDE.md, script da cron.")
}
Write-Host "  [ok] nessun path di altre installazioni nel pacchetto" -ForegroundColor Green

# ── 4c. Numero di versione (mostrato in fondo alla card di login) ──────────
# Il default "dev" vive in config/config.php del core. Senza questa sostituzione
# il pacchetto se lo porta in produzione tal quale: fino al 1.0.1 la card di
# login di MC Protection ha mostrato "v.dev" (accorto Alessandro il 2026-09-08).
# Stesso meccanismo del pacchetto core e di quello 5stelle.
function Set-PhpDefine {
  param([string]$Content, [string]$Const, [string]$Value, [string]$File)
  $rx = 'define\("' + [regex]::Escape($Const) + '",\s*"[^"]*"'
  if (-not [regex]::IsMatch($Content, $rx)) { throw "Costante $Const non trovata in $File" }
  $repl = ('define("' + $Const + '", "' + $Value + '"').Replace('$', '$$')
  return [regex]::Replace($Content, $rx, $repl)
}
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
$configPhpPath = Join-Path $stageDir "config\config.php"
if (-not (Test-Path $configPhpPath)) { throw "config/config.php mancante in staging: $configPhpPath" }
$configPhpContent = [IO.File]::ReadAllText($configPhpPath)
$configPhpContent = Set-PhpDefine $configPhpContent "GLOBO_VERSION" $Version $configPhpPath
[IO.File]::WriteAllText($configPhpPath, $configPhpContent, $utf8NoBom)
Write-Host "  [ok] GLOBO_VERSION: impostata a $Version" -ForegroundColor Green

# ── 4d. Segreti al posto dei segnaposto (stesso passo del build del core) ────
# Nei sorgenti i segreti sono segnaposti __PROD_<NOME>__, perche' il repository e'
# condiviso; i valori veri ($ProdSecrets, da prod-secrets.local.ps1) si rimettono
# qui, sulla copia in staging. Due controlli bloccanti:
#  - ogni segreto del file locale deve trovare il suo segnaposto: se no file locale
#    e sorgenti sono disallineati (un segnaposto rinominato, un file tolto);
#  - nessun segnaposto deve restare nel pacchetto: in produzione partirebbe una
#    password finta, e il guasto si vedrebbe solo quando qualcosa smette di inviare
#    (le mail delle scadenze di MC Protection sono gia' rimaste ferme due settimane
#    senza che nessuno se ne accorgesse, vedi CLAUDE.md).
# Viene DOPO il guardrail 4b, che quindi non vede mai i valori veri.
# Lettura e scrittura in latin-1 (byte per byte), per non toccare gli accenti dei
# file legacy che non sono UTF-8.
$latin1 = [Text.Encoding]::GetEncoding(28591)
$segretiUsati = @{}
foreach ($f in Get-ChildItem -Path $stageDir -Recurse -File) {
  $t = $latin1.GetString([IO.File]::ReadAllBytes($f.FullName))
  if (-not $t.Contains('__PROD_')) { continue }
  foreach ($k in $ProdSecrets.Keys) {
    if ($t.Contains($k)) { $t = $t.Replace($k, [string]$ProdSecrets[$k]); $segretiUsati[$k] = $true }
  }
  [IO.File]::WriteAllBytes($f.FullName, $latin1.GetBytes($t))
  $rimasti = [regex]::Matches($t, '__PROD_[A-Z0-9_]+__') | ForEach-Object { $_.Value } | Sort-Object -Unique
  if ($rimasti) {
    throw "SEGNAPOSTO SENZA VALORE in $($f.FullName.Substring($stageDir.Length + 1)): $($rimasti -join ', ')`nAggiungerlo a tools/prod-secrets.local.ps1."
  }
}
$nonUsati = @($ProdSecrets.Keys | Where-Object { -not $segretiUsati.ContainsKey($_) })
if ($nonUsati.Count -gt 0) {
  throw "Segreti senza segnaposto nel pacchetto: $($nonUsati -join ', ')`nSorgenti e prod-secrets.local.ps1 sono disallineati."
}
Write-Host "  [ok] segreti: $($segretiUsati.Count) segnaposto sostituiti" -ForegroundColor Green

# ── 5. Creazione zip (separatori "/" forzati per compatibilita' Linux/unzip) ─
# NB: Compress-Archive di PowerShell 5.1 scrive i percorsi con "\" (backslash),
# che su Linux non vengono interpretati come cartelle. Costruiamo lo zip a mano
# con System.IO.Compression imponendo "/" nei nomi delle entry.
if (Test-Path $zipPath) { Remove-Item -Force $zipPath }
Add-Type -AssemblyName System.IO.Compression | Out-Null
Add-Type -AssemblyName System.IO.Compression.FileSystem | Out-Null

$baseLen = $distDir.Length + 1   # i nomi entry partono da "globo/..."
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
  foreach ($f in Get-ChildItem -Path $stageDir -Recurse -File) {
    $entry = $f.FullName.Substring($baseLen).Replace('\','/')
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
      $zip, $f.FullName, $entry, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
  }
} finally {
  $zip.Dispose()
}

# ── 6. Pulizia staging (resta solo lo zip in dist/) ──────────────────────────
Remove-Item -Recurse -Force $stageDir

$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host ""
Write-Host "== Pacchetto creato ==" -ForegroundColor Cyan
Write-Host "  $zipPath  ($sizeMB MB)"
Write-Host "  Struttura interna: globo/...  (estrai nella web root del server)"
Write-Host "  core/src e custom/src NON sono stati toccati: ambiente dev intatto." -ForegroundColor Yellow

# ── PROMEMORIA DEPLOY: passi manuali NON inclusi nello zip (solo codice) ──────
Write-Host ""
Write-Host "== PROMEMORIA DEPLOY (passi manuali, NON nello zip) ==" -ForegroundColor Magenta
Write-Host "  Procedura e controlli in docs/deploy.md, rileggerla ad ogni" -ForegroundColor Magenta
Write-Host "  rebuild - include la verifica CRITICA di httpdocs/documenti/upload/" -ForegroundColor Magenta
Write-Host "  (MCP_DIR_DOC, fuori dalla webroot globo-mcp/, NON nello zip)." -ForegroundColor Magenta
