<#
  build-package.ps1 — Crea il pacchetto zip di Globo pronto per il deploy in produzione.

  Lavora SEMPRE su una copia temporanea: i file sorgente in src/ non vengono mai
  modificati, quindi l'ambiente di sviluppo locale resta intatto (nessun "ripristino"
  da ricordare). Applica i valori di produzione solo alla copia e verifica ogni
  sostituzione: se una stringa attesa non viene trovata, lo script si ferma con
  errore (così non si spedisce mai uno zip con valori di sviluppo per sbaglio).

  Uso:
    powershell -ExecutionPolicy Bypass -File tools/build-package.ps1
    powershell ... -File tools/build-package.ps1 -Version "1.1.0"

  Cosa include: tutto src/ (la web root), ESCLUSI upload/ e globo_log/ (dati runtime,
  già presenti sul server). Cosa esclude: docs/, docker/, database/, .git, ecc.
#>

param(
  [string]$Version = "1.1.0",
  # Dove mettere al riparo i pacchetti gia' presenti in dist/ prima di cancellarla.
  # Fuori dal repo: dist/ e' in .gitignore e i pacchetti pesano qualche MB l'uno.
  [string]$ArchiveDir = "C:\Temp\Globo-core-pacchetti"
)

$ErrorActionPreference = "Stop"

# ── Segreti di produzione (file locale, non versionato) ─────────────────────
$secretsFile = Join-Path $PSScriptRoot "prod-secrets.local.ps1"
if (-not (Test-Path $secretsFile)) {
  throw "Segreti mancanti: $secretsFile`n  Copia tools/prod-secrets.example.ps1 in prod-secrets.local.ps1 e inserisci i valori DB di produzione."
}
. $secretsFile
foreach ($k in @('Host','User','Password','DbGlobo','DbKs')) {
  if (-not $ProdDb -or [string]::IsNullOrEmpty([string]$ProdDb[$k])) {
    throw "prod-secrets.local.ps1: valore mancante per ProdDb.$k"
  }
}

# Sostituisce define("CONST", "...") in un contenuto PHP (robusto alla spaziatura).
# Verifica: se la costante non c'è, lancia eccezione (niente sostituzione silenziosa).
function Set-PhpDefine {
  param([string]$Content, [string]$Const, [string]$Value, [string]$File)
  $rx = 'define\("' + [regex]::Escape($Const) + '",\s*"[^"]*"'
  if (-not [regex]::IsMatch($Content, $rx)) { throw "Costante $Const non trovata in $File" }
  $repl = ('define("' + $Const + '", "' + $Value + '"').Replace('$', '$$')
  return [regex]::Replace($Content, $rx, $repl)
}

# ── Percorsi ────────────────────────────────────────────────────────────────
$repoRoot = Split-Path -Parent $PSScriptRoot
$srcDir   = Join-Path $repoRoot "src"
$distDir  = Join-Path $repoRoot "dist"
$stageDir = Join-Path $distDir  "globo"           # root interna allo zip
$zipPath  = Join-Path $distDir  "globo-$Version.zip"

Write-Host "== Build pacchetto Globo $Version ==" -ForegroundColor Cyan
Write-Host "Repo:    $repoRoot"
Write-Host "Sorgente:$srcDir"

# ── 1. Archivio dei pacchetti esistenti, poi pulizia di dist/ ─────────────
# Il build cancella dist/ per intero, quindi porta via anche il pacchetto che in
# quel momento e' LIVE in produzione. E' successo su 5stelle il 2026-09-17:
# generando il 1.1.9 e' sparito il 1.1.8, l'unica copia di cio' che era stato
# davvero spedito sul server. Serve per confrontare la webroot online con quello
# che le e' stato mandato, e per ricaricarlo al volo in un rollback.
# Non si sovrascrive mai un file gia' in archivio: quello e' il pacchetto vero
# spedito allora, una ricostruzione successiva potrebbe non essere identica
# (qui, per giunta, il pacchetto include i segreti di produzione sostituiti al
# volo da prod-secrets.local.ps1: la copia e' l'unica prova di cosa e' partito).
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

# ── 2. Copia src → staging, escludendo dati runtime ─────────────────────────
# robocopy: /E ricorsivo (incl. vuote), /XD esclude cartelle. Exit code < 8 = ok.
robocopy $srcDir $stageDir /E /XD (Join-Path $srcDir "upload") (Join-Path $srcDir "globo_log") /NFL /NDL /NJH /NJS /NP | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy fallito (exit $LASTEXITCODE)" }
$global:LASTEXITCODE = 0

# Cartelle dati: escluse dalla copia (per non portare i file di sviluppo), ma
# ricreate vuote con un placeholder così esistono all'estrazione su un server
# nuovo. L'unzip su un deploy esistente aggiunge solo il placeholder, non tocca
# i file già caricati.
foreach ($d in @("upload", "globo_log")) {
  $dir = Join-Path $stageDir $d
  New-Item -ItemType Directory -Force -Path $dir | Out-Null
  Set-Content -Path (Join-Path $dir "index.html") -Value "" -NoNewline -Encoding ascii
}

# ── 3. Trasformazioni dev → produzione (solo sulla copia, con verifica) ──────
# Ogni voce: file relativo a staging, stringa-dev attesa, stringa-prod.
$replacements = @(
  @{ File = "config/globo-ks_kosmosol_it.php"
     Old  = 'define("URL_GLOBO", "http://localhost:8080");'
     New  = 'define("URL_GLOBO", "https://globo-ks.kosmosol.it/globo");' },
  @{ File = "config/globo-ks_kosmosol_it.php"
     Old  = 'define("DIR_LOG", "/var/www/html/globo_log/");'
     New  = 'define("DIR_LOG", "/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/globo_log/");' },
  @{ File = "config/globo-ks_kosmosol_it.php"
     Old  = 'define("EMAIL_KOSMOSOL", "alessandro@zooga.vc");'
     New  = 'define("EMAIL_KOSMOSOL", "globo@kosmosol.it");' },
  @{ File = "authorization.php"
     Old  = 'setcookie("_authLogGloboKs", $tokenCookie, time() + (DATE_EXPIRE_TOKEN * 86400), "/", "", 0, 0);'
     New  = 'setcookie("_authLogGloboKs", $tokenCookie, time() + (DATE_EXPIRE_TOKEN * 86400), "/", "globo-ks.kosmosol.it", 1, 0);' }
)

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
foreach ($r in $replacements) {
  $path = Join-Path $stageDir $r.File
  if (-not (Test-Path $path)) { throw "File mancante nel pacchetto: $($r.File)" }
  $content = [IO.File]::ReadAllText($path)
  if (-not $content.Contains($r.Old)) {
    throw "VALORE DEV NON TROVATO in $($r.File):`n  atteso: $($r.Old)`nLo script si ferma per non spedire valori errati."
  }
  $content = $content.Replace($r.Old, $r.New)
  [IO.File]::WriteAllText($path, $content, $utf8NoBom)
  Write-Host "  [prod] $($r.File): sostituito" -ForegroundColor Green
}

# ── 3b. Credenziali DB di produzione (dai segreti locali, non versionati) ───
$cfgPath = Join-Path $stageDir "config/globo-ks_kosmosol_it.php"
$cfg = [IO.File]::ReadAllText($cfgPath)
$cfg = Set-PhpDefine $cfg "MYSQL_HOST_GLOBO"     $ProdDb.Host     $cfgPath
$cfg = Set-PhpDefine $cfg "MYSQL_USER_GLOBO"     $ProdDb.User     $cfgPath
$cfg = Set-PhpDefine $cfg "MYSQL_PASSWORD_GLOBO" $ProdDb.Password $cfgPath
$cfg = Set-PhpDefine $cfg "MYSQL_DB_GLOBO"       $ProdDb.DbGlobo  $cfgPath
$cfg = Set-PhpDefine $cfg "MYSQL_DB_GLOBO_KS"    $ProdDb.DbKs     $cfgPath
[IO.File]::WriteAllText($cfgPath, $cfg, $utf8NoBom)
Write-Host "  [prod] config DB: host/user/password/db sostituiti" -ForegroundColor Green

# ── 3c. Numero di versione (mostrato in fondo alla card di login) ───────────
# Il default "dev" vive in config/config.php (config/config.php!defined check),
# qui lo si sostituisce col valore reale del pacchetto - cosi' chi guarda la
# schermata di login sa sempre quale versione gira in produzione, senza un
# passo manuale da ricordare ad ogni deploy.
$configPhpPath = Join-Path $stageDir "config/config.php"
$configPhpContent = [IO.File]::ReadAllText($configPhpPath)
$configPhpContent = Set-PhpDefine $configPhpContent "GLOBO_VERSION" $Version $configPhpPath
[IO.File]::WriteAllText($configPhpPath, $configPhpContent, $utf8NoBom)
Write-Host "  [prod] GLOBO_VERSION: impostata a $Version" -ForegroundColor Green

# ── 3d. Segreti al posto dei segnaposto (dai segreti locali, non versionati) ──
# Nei sorgenti i segreti (chiavi API, password SMTP...) sono segnaposti
# __PROD_<NOME>__, perche' il repository e' condiviso; i valori veri stanno solo in
# prod-secrets.local.ps1 ($ProdSecrets) e si rimettono qui, sulla copia in staging.
# Due controlli bloccanti:
#  - ogni segreto del file locale deve trovare il suo segnaposto: se no file locale
#    e sorgenti sono disallineati (un segnaposto rinominato, un file tolto);
#  - nessun segnaposto deve restare nel pacchetto: in produzione partirebbe una
#    password finta, e il guasto si vedrebbe solo quando qualcosa smette di inviare.
# Lettura e scrittura in latin-1 (byte per byte): i file legacy non sono tutti UTF-8
# e una riscrittura con un'altra codifica ne rovinerebbe gli accenti.
if (-not $ProdSecrets -or $ProdSecrets.Count -eq 0) {
  throw "prod-secrets.local.ps1: manca `$ProdSecrets (vedi tools/prod-secrets.example.ps1)"
}
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
Write-Host "  [prod] segreti: $($segretiUsati.Count) segnaposto sostituiti" -ForegroundColor Green

# ── 4. Creazione zip (separatori "/" forzati per compatibilità Linux/unzip) ──
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

# ── 5. Pulizia staging (resta solo lo zip in dist/) ─────────────────────────
Remove-Item -Recurse -Force $stageDir

$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host ""
Write-Host "== Pacchetto creato ==" -ForegroundColor Cyan
Write-Host "  $zipPath  ($sizeMB MB)"
Write-Host "  Struttura interna: globo/...  (estrai nella web root del server)"
Write-Host "  La working tree NON e' stata toccata: ambiente dev intatto." -ForegroundColor Yellow

# ── PROMEMORIA DEPLOY: passi manuali NON inclusi nello zip (solo codice) ──────
# Lo zip contiene solo la web root: migrazioni DB e cron vanno fatti a parte.
# Questo blocco viene stampato a OGNI build apposta, perche' il promemoria resti.
# NB: le migrazioni pendenti si annotano SOLO in STATO.md (procedura in docs/deploy.md)
# - non ripeterle qui (lezione 2026-07-06: un elenco hardcoded qui
# si era disallineato, citando solo una delle migration gia' pendenti).
Write-Host ""
Write-Host "== PROMEMORIA DEPLOY (passi manuali, NON nello zip) ==" -ForegroundColor Magenta
Write-Host "  Migrazioni DB e registrazione cron pendenti: vedi" -ForegroundColor Magenta
Write-Host "  STATO.md e docs/deploy.md, rileggerli ad ogni build." -ForegroundColor Magenta
