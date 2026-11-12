# Fase 2 — Core + Custom: architettura e playbook cloni

> **Riferimento generico** della Fase 2: come è strutturato il core/custom, come un
> clone importa/aggiorna il core, dove vivono tema/config/personalizzazioni, e quali
> migliorie di Fase 1 ogni clone deve **ereditare** o **applicare**.
>
> Le **analisi specifiche di un clone** (divergenza dal core, superficie custom,
> stato di migrazione) vivono nel **repo del clone** (`CLAUDE.md` e `STATO.md` del
> clone), non qui. Questo doc resta neutro rispetto ai singoli clienti.
>
> Decisioni architetturali confermate con l'utente il 2026-06-08.

---

## 1. Obiettivo e principi

Globo è il "core pulito" di una famiglia di ~10 applicazioni che condividono il
70-90% del codice (vedi CLAUDE.md §8). La Fase 2 separa il monolite in:

- **`globo-core`** — il cuore comune, un repository unico, riusabile da tutti i
  cloni. È l'attuale Globo (migrato a PHP 8.3 in Fase 1). **Decisione: il repo
  `Globo8` fa da `globo-core`** (sorgente del subtree), senza creare un repo nuovo.
- **`globo-cliente-X`** — un repository per ogni cliente, che importa il core e
  vi sovrappone solo le proprie differenze. Il primo è `globo-cliente-5stelle`.

Principi guida:

1. **Il core non sa nulla dei cloni.** Nessun `if (cliente == "5stelle")` nel
   core. Le differenze vivono nel clone, non nel core.
2. **Il clone contiene solo le differenze**, non una copia integrale di Globo.
   Tutto ciò che non è sovrascritto viene dal core.
3. **Aggiornare il core è un'operazione di routine**, non un merge manuale
   doloroso. Un clone tira gli aggiornamenti del core con un comando.
4. **Niente rifattorizzazione del meccanismo di include di Globo.** Sfruttiamo
   com'è già fatto (include relativi alla webroot, config host-based, tema a
   costante). La separazione è strutturale, non invasiva sul codice esistente.

---

## 2. Meccanismo di override: overlay a path (file-shadowing)

**Decisione confermata (2026-06-08): opzione A — overlay a path.**

La webroot che Apache serve è l'**unione di due alberi**:

1. si parte dai file del **core**;
2. si sovrappongono i file del **custom**: un file custom con lo *stesso path
   relativo* di uno del core lo **oscura** (override); un file custom che nel
   core non esiste viene semplicemente **aggiunto** (es. `cal_tutti.php`).

```
  core/src/cont_lista.php   +   custom/src/cont_lista.php   →   override (vince custom)
  core/src/varie/funzioni.php   (nessun corrispettivo custom)  →   viene dal core
  (nessun corrispettivo core)   +   custom/src/cal_tutti.php   →   pagina nuova del clone
```

L'unità di override è il **file intero**, identificato dal suo path relativo
alla webroot. Non si fanno override "a riga" o "a funzione".

### Perché questa scelta

- **Zero modifiche agli include di Globo.** Gli `require("varie/funzioni.php")`,
  `require("config/config.php")` ecc. continuano a funzionare identici, perché la
  webroot composta è una normale installazione Globo. Non serve un resolver, non
  serve toccare i punti di include.
- **Non dipende dalla mappa INCLUDE-POINT** (mai compilata: non serve). L'overlay oscura interi file per path e non ha bisogno di
  conoscere ogni singola riga di include. È il motivo tecnico per cui A è
  preferibile a un resolver via `include_path`, che quella mappa la richiederebbe.
- **Il config per-cliente è naturalmente un file custom**: è già un file a sé in
  `config/`, separato dalla logica.

### Raffinamento: hook per le modifiche additive (non override a file intero)

L'override a file intero è giusto per i **file nuovi** del clone e per i **file
core riscritti** da un clone. **Non** è giusto per le modifiche *additive* a file
di infrastruttura condivisa (es. un clone che aggiunge funzioni a `funzioni.php` o
voci di menu a `header.php`): sovrascrivere l'intero file farebbe **perdere al
clone ogni futuro aggiornamento del core** di quel file — l'opposto dell'obiettivo.

Per quei casi il core espone **pochi punti di estensione (hook)** mirati — es. un
`include` opzionale `custom/funzioni_custom.php` in fondo a `funzioni.php`, un hook
per le voci di menu in `header.php`. Quali hook servono si scopre **dal diff del
clone** (vedi playbook §7), non mappando i ~225 include alla
cieca. Quando se ne aggiunge uno, si documenta in
`refactoring-notes.md` (sezione CORE).

### Costo accettato

Serve un passo di **composizione** della webroot (fondere i due alberi). In dev
lo facciamo con i bind-mount di Docker; in produzione/build con una copia
(rsync) o un'immagine costruita. Vedi §5.

---

## 3. Topologia dei repository: core via git subtree

**Decisione confermata (2026-06-08): git subtree.** Il clone incorpora il core con
`git subtree`, non submodule, non copia manuale:

- **clone-and-go**: chi clona `globo-cliente-X` ottiene già `core/` popolato, senza
  `submodule init/update` e senza detached HEAD;
- la storia del core è inclusa nel repo del clone;
- aggiornare il core è un singolo comando di pull del subtree.

### Struttura proposta di un clone

```
globo-cliente-X/
├── core/                       # globo-core importato via git subtree (NON si edita qui)
│   ├── src/                    #   la webroot del core
│   ├── database/
│   └── docs/
├── custom/
│   └── src/                    # SOLO le differenze del clone, stesso layout della webroot
│       ├── config/
│       │   └── globo-X_it.php          # config del clone (DB, TITLE, GLOBO_THEME, moduli…)
│       ├── <pagine nuove>.php          # file aggiuntivi del clone
│       └── …                           # eventuali override / funzioni_custom.php
├── docker/                     # Dockerfile + vhost del clone (possono ereditare da quelli core)
├── docker-compose.yml          # compone la webroot (overlay) e avvia lo stack
├── docs/                       # deploy.md e documenti SPECIFICI del clone
└── README.md
```

**Convenzione di lavoro:** `core/` è di sola lettura dal punto di vista del clone.
Un bug del core si corregge **nel repo del core** e poi si tira con un update di
subtree; non si patcha `core/` dentro il clone (la patch andrebbe persa o in
conflitto al prossimo update). Se serve un comportamento diverso *solo* per il
clone, è un file in `custom/`.

### Flusso di aggiornamento del core

- **Correzione/feature nel core** → commit sul repo core (`Globo8`).
- **Il clone tira l'aggiornamento** → pull del subtree dentro `core/`.
- Eventuali file custom che oscurano un file core aggiornato vanno **riconciliati
  a vista**. È il motivo per cui si tiene il set di override **il più piccolo
  possibile** (e si preferiscono gli hook, §2).

Comandi in uso: `git subtree pull --prefix=core globo-core main --squash` (sempre
`--squash`, altrimenti "unrelated histories"); il remote `globo-core` punta al repository
GitHub di Globo8. Un clone nuovo nasce con `git subtree add --prefix=core globo-core main
--squash`.

### Un deploy per repository

Ogni repository (core e clienti) ha un deploy e un URL di produzione distinti, anche
quando condividono infrastruttura: i loro documenti di deploy non si fondono. Mappa dei
repository e degli URL: `README.md` di Globo8; procedura: `docs/deploy.md` di ciascuno;
versione in produzione: `STATO.md` di ciascuno. Un clone nuovo aggiunge la sua riga
alla mappa nel README di Globo8.

---

## 4. Dove vivono tema, config e personalizzazioni

### 4.1 Tema (colore brand) — già pronto nel core

Design system a tre livelli (vedi `src/varie/head.php` e i tre CSS):

| Livello | File | Varia per clone? |
|---|---|---|
| tokens | `css/globo.tokens.css` | no (palette neutra, tipografia, spaziature) |
| **themes** | `css/globo.themes.css` | **sì, ma solo le 7 variabili brand** |
| app | `css/globo.app.css` | no (componenti e layout) |

`head.php` emette `<body data-theme="<?= GLOBO_THEME ?>">`. Per un clone basta
`GLOBO_THEME = "<tema>"` nel config — **nessun altro file da toccare**. I blocchi
tema di *tutti* i cloni stanno nel core (`globo.themes.css`), uno per `data-theme`:
sono pochi byte ciascuno e tenerli insieme evita divergenze del design system. Un
clone con esigenze grafiche oltre il colore brand aggiunge un proprio `<link>` CSS
dopo `head.php` — quel CSS extra vive in `custom/`.

### 4.2 Configurazione — è la cucitura custom principale

Il file di config contiene **già tutto ciò che è per-cliente**: connessione DB
(`MYSQL_*`), `URL_GLOBO`, `EMAIL_*`, `TITLE`, `PREFISSO_FILE`, `IMG_LOGO`,
`GLOBO_THEME`, liste IP, e l'**attivazione dei moduli** (`MOD_*` / `LIV_MOD_*`).

→ Nel modello core/custom, **il config di un clone è un file custom**
(`custom/src/config/globo-X_it.php`).

**Punto da risolvere — selezione del config.** Oggi `config/config.php` sceglie il
file in base a `HTTP_HOST`. Per un clone servono (1) il suo file di config e (2) un
modo perché `config.php` lo selezioni. Due strade (vedi §6):

- **(b) `config.php` reso parametrico nel core** — preferita. Override via env, es.
  *se* `GLOBO_CONFIG` è impostata usala, altrimenti la logica host-based attuale. Il
  container del clone imposta `GLOBO_CONFIG=globo-X_it.php`. Piccola modifica al core
  che **beneficia tutti i cloni** ed elimina un hardcoding. `config.php` resta core.
- **(a) override di `config.php` in custom** — fallback. Il clone mette il proprio
  `config.php`. Semplice, ma fa di un file core un override in più da riconciliare.

### 4.3 Personalizzazioni di codice — `custom/src/`

- **File nuovi del clone** (non esistono nel core): vivono solo in `custom/src/` e
  vengono *aggiunti* dall'overlay.
- **Override di file core** (stesso path del core): da usare con parsimonia — ogni
  override è un punto da riconciliare quando il core cambia. Per le aggiunte
  additive preferire un **hook** (§2) invece dell'override a file intero.

---

## 5. Composizione della webroot (overlay) in pratica

### 5.1 Dev (Docker) — composizione all'avvio del container

In tutti i cloni l'entrypoint `docker/php8/compose-webroot.sh` monta in sola lettura
`core/src`, `custom/src` e `dev-overlay/config` e li copia (rsync) uno sopra l'altro
nella webroot del container, in quest'ordine: vince l'ultimo. `dev-overlay/config/`
contiene i config con i valori dev (DB del container, URL localhost), così i config di
`custom/src/config/` restano quelli di produzione (con i segreti come segnaposti).

Dopo una modifica a uno dei tre alberi: `docker compose restart web`. La webroot **non
è pronta subito** (l'rsync precede l'avvio di Apache): ricontrollare il file composto
prima di concludere che una modifica non ha effetto.

### 5.2 Produzione / build — webroot composta una volta

In deploy la webroot si compone una volta: copia del core, poi rsync del custom
sopra. Può diventare un'immagine Docker buildata (artefatto immutabile) o uno step
di deploy. Oggi lo fa `tools/build-package.ps1` di ogni clone (vedi il suo `docs/deploy.md`).

---

## 6. Decisioni

| # | Tema | Stato |
|---|---|---|
| 1 | **Sorgente del core** (subtree) | **Deciso**: `Globo8` fa da `globo-core`. |
| 2 | **Selezione config del clone** | Preferita (b) `config.php` parametrico via env `GLOBO_CONFIG`. È modifica a file *core di config* → piano + ok (CLAUDE.md). Da implementare. |
| 3 | **Mappa INCLUDE-POINT** | Con l'overlay non è prerequisito. Si riempie con i **soli hook** che emergono dai diff dei cloni (§2), non a tappeto. |
| 4 | **DB / host-porta del clone in dev** | **Per-clone**: si decide nel repo del clone (porte: `README.md` di Globo8). |
| 5 | **Pulizia core per il subtree** | Gli *Adattamenti per Docker dev* (config dev, cookie `domain`/`secure`, `cron-schedule` commentato) sono per-ambiente: candidati a parametrizzazione nel core. Da pianificare a parte. |

---

## 7. Playbook: migrare un clone

### Regola d'oro: NON applicare tutto a mano su ogni clone

Il senso del core è che le migliorie si scrivono **una volta** e i cloni le
**ereditano**. Riapplicarle a mano clone per clone è l'anti-pattern degli "N fork
indipendenti": moltiplica il lavoro e allontana il clone dal core. Ogni miglioria
si colloca in **uno di tre secchi**, a seconda di *dove vive il file*:

| Secchio | Quali file | Come il clone ottiene la miglioria |
|---|---|---|
| **EREDITA dal core** | file core comuni (`funzioni.php`, `common_*`, `header/footer/head`, moduli `cal_* cont_* prog_* pr_* ut_*`, CSS, `index.php`) | adottando la versione del core (overlay/subtree). **Non si patcha a mano.** |
| **APPLICA nel custom** | file **solo-custom** del clone (non esistono nel core) | si applicano le migliorie del catalogo §8, guidati dalla lista |
| **RUN-BLOCKER (ora)** | qualunque file che impedisce l'esecuzione in dev | passata **minima e mirata** per far girare il clone, in attesa della migrazione vera |

Una stessa miglioria (es. "fix undefined array key") ricade in secchi diversi a
seconda del file: nei file core la eredita, nei file custom la applica, e diventa
run-blocker quando rompe una pagina che stai testando.

### Procedura per migrare un clone

1. **Diff alla pari** del clone contro il baseline PHP 5 (commit `0317623`) → i tre
   insiemi: file core comuni / file custom / cruft. (Le analisi svolte per i cloni esistenti sono nella
   cronologia locale archiviata, non nei repository condivisi.)
2. **File core comuni** → adotta la versione **moderna del core** (overlay/subtree).
   Le migliorie del §8 arrivano **gratis**. Non hand-portare.
3. **File solo-custom** → applica il catalogo §8 a quei file, con la lista come
   checklist. Per le aggiunte additive ai file core, usa un **hook** (§2).
4. **Eliminazioni** → applica il registro FILE ELIMINATI di `refactoring-notes.md`.
5. **Run-blocker** → passata minima per far girare il clone in dev.

### I file del core NON sono tutti neutri: `script/*.php` (lezione MCP, 2026-09-07)

Il core è estratto dall'installazione **Globo KosmoSol**. Quasi tutti i suoi file sono
neutri, ma alcuni hanno l'installazione di origine **cablata nel codice**. I quattro
script in `core/src/script/` sono i file di produzione di KosmoSol:

```php
$dominio = "globo-ks.kosmosol.it";
require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/config/globo-ks_kosmosol_it.php");
```

Girano da **cron (CLI)**: non passano da `index.php`, quindi la config non può essere
risolta da `HTTP_HOST` e i path sono per forza assoluti. Un clone che non li sovrascrive
in `custom/src/script/` se li porta in produzione **così come sono**, e lì:

- su **un'altra macchina** → il `require` fallisce, **fatal error alla terza riga**:
  niente mail, e **nemmeno una riga di log** (il log lo scrive `funzioni.php`, che non
  è ancora stato caricato). Guasto perfettamente silenzioso;
- sulla **stessa macchina** → peggio in modo diverso: carica la config di KosmoSol, si
  connette al **DB di KosmoSol** e scrive nel `globo_log` di KosmoSol.

**È successo davvero.** Su MC Protection l'override di `invia_scadenze.php` era nato
copiando il file **dal core** e riapplicando solo la differenza funzionale, senza toccare
l'intestazione; `invia_impegni.php` e `purga_log_attivita.php` non erano stati overridati
affatto. Dal giorno del deploy le mail delle scadenze si sono fermate: il cliente se n'è
accorto **due settimane dopo**, per caso, aprendo il calendario.

**Perché non l'ha intercettato nessuno**: questi script sono l'unica parte del codice che
l'ambiente dev **non esegue mai** — niente cron nel container, e quei path non esistono
nemmeno lì. Nessun collaudo funzionale li tocca, per quanto accurato. Si verificano solo
**guardandoli**, oppure con un controllo automatico sul pacchetto.

**Regola per ogni clone**: prima del primo deploy, `custom/src/script/` deve contenere
l'override di **ogni** script di `core/src/script/` usato dal clone, con `$dominio` e i
`require` sui path reali di quell'installazione. Da riverificare a ogni `subtree pull`
che porti script nuovi nel core. Stato al 2026-09-07:

| Clone | Override in `custom/src/script/` | Stato |
|---|---|---|
| 5stelle | tutti presenti, path `globo-h5s` | ✅ corretto (importati dal live del cliente, non dal core) |
| MC Protection | 3/3 dopo l'hotfix del 2026-09-07 | ✅ corretto (era il guasto) |
| ML Benessere | `purga_log_attivita.php` (path da `__DIR__`, sede come argomento); script di posta **esclusi** dal pacchetto | ✅ risolto al deploy del 2026-09-14. Con tre domini su un solo pacchetto un path assoluto non poteva funzionare: la sede arriva come argomento del cron, e `MOD_CALENDARIO` spento rende inutili gli script di posta. Primo modello del fix di fondo annotato in `refactoring-notes.md` → HARDCODED |
| Verdecasa | `purga_log_attivita.php` (path da `__DIR__`, dominio come argomento, da Studio CIA); script di posta **esclusi** dal pacchetto | ✅ risolto al deploy del 2026-10-09 (pacchetto 1.0.1). Le notifiche non servono, nonostante `MOD_CALENDARIO` attivo: sul server di verdecasa.com non c'era nessuna operazione pianificata, scadenze inutilizzate dal 2013, notifiche email attive per 1 utente su 7 (decisione 2026-09-15). Unico cron: la pulizia di `attivita_log` |

Stessa natura, altro punto del codice: `ins_invio_scadx_2.php` e `ins_invio_promemoria_2.php`
leggono un CSV da `upload/` con path assoluto su globo-ks. Sono endpoint di invio massivo
specifici di KosmoSol, non referenziati da nessuna pagina e protetti solo da una chiave
giornaliera debole: nei cloni vanno **esclusi dal pacchetto**, non overridati.

### Adozione del redesign UI in un clone — cutover coordinato

> Lezione dal pilota 5stelle (2026-06-10). Vale per ogni clone.

Adottare il redesign di Fase 1 (§8.4) in un clone **non è pruning incrementale**: è un
workstream a sé, da fare come **cutover coordinato**, per un vincolo strutturale.

**Lo shell e le pagine sono legate da un contratto.** Nel core `varie/head.php` **apre il
documento** (emette `<head>…</head>` + `<body data-theme>` + sprite + flash); le pagine core
fanno solo `require("varie/head.php")`. Le pagine **pre-redesign** invece costruiscono a mano
`<!DOCTYPE><html><head> … require(head.php) … </head><body>`, usando head.php come frammento.
Mescolare una pagina vecchia col `head.php` del core produce `<head>` annidati e `<body>`
doppio → **rotto** (non solo "imbruttito"). Poiché head.php è condiviso da tutte le pagine,
**o tutte usano il contratto nuovo, o nessuna**: niente adozione a goccia.

**Il 3-way merge NON aiuta sui file riscritti dal redesign.** Dove il core ha riscritto il
file, `git merge-file` (base = baseline `0317623`, parti = core e clone) produce un
**conflitto a tutto-file**: non c'è terreno comune su cui ancorare le personalizzazioni. Il
merge serve **solo** dove il clone == baseline (override eliminabile, conflitti = 0).

**La metrica giusta è il diff `baseline→clone`** (non il conflitto del merge): è la
personalizzazione *reale* del clone. Smista in tre secchi:

| Secchio | Criterio | Azione |
|---|---|---|
| **Inherit** | clone == baseline | elimina l'override → eredita la pagina redisegnata del core |
| **Si dissolve** | diff ≠ ∅ ma cosmetico / vecchio-contratto / toggle gated da config (`MOD_*`) | eredita + sistema la config; la personalizzazione sparisce nel core moderno |
| **Feature da portare** | diff = feature reale del clone | **port a mano** sul file redisegnato del core (hook se additivo-separabile, altrimenti override sottile). Il diff `baseline→clone` dice *cosa* ri-aggiungere |

**Procedura (cutover coordinato):**
1. Classifica ogni file custom col diff `baseline→clone` nei tre secchi.
2. Inherit/dissolve → predisponi l'eliminazione degli override + la config.
3. Feature da portare → produci a mano i file fusi e porta le pagine clone-nuove al
   contratto nuovo.
4. **Landa tutto insieme** (shell + pagine) → cutover → verifica la webroot composta come
   **insieme coerente**.

Il lavoro pesante è il solo secchio "feature da portare" (di norma pochi file: i moduli
verticali del clone), non tutti i file toccati: il grosso **eredita** o **si dissolve**.

---

## 8. Catalogo delle migliorie di Fase 1

Cosa il **core** ha acquisito nella migrazione PHP 8, e che ogni clone deve
ritrovarsi (per **eredità** dai file core, o **applicato** ai file custom).
Generato da `git log` di Globo8 + `refactoring-notes.md`.

### 8.1 Compatibilità PHP 8 (per far *girare* il codice)

| Miglioria | Dove nel core | Note |
|---|---|---|
| Fix `Undefined array key` / `array offset on null` → guardia `?? <default>` | per-modulo (`b424d91`, `a1968ce`, `64e49e0`, `589eb9b`, `3a2fcec`, `58df851`…) | la classe **più frequente**; run-blocker tipico |
| Residui `mysql_*` → PDO | core già su PDO | nei cloni controllare (es. 5stelle: ~16 in `funzioni.php`) |
| `mysql_close()` rimosso | `common_2.php` (`c9837b4`) | non esiste più in PHP 8 |
| `random_int()` al posto di `rand()`, `preg_replace` spazi | `funzioni.php` (`5d71494`) | |
| Costruttori PHP4 → `__construct()` | `imageuploader/` (`ab4313d`) | libreria di terze parti |

### 8.2 Sicurezza

| Miglioria | Dove nel core | Note |
|---|---|---|
| Password **bcrypt** + transizione MD5→bcrypt on-the-fly | `index.php`, `ut_ins_2.php`, `ut_agg.php` (`f63d0b8`) | rimigrazione al primo login |
| **Prepared statement PDO** (anti SQL-injection) | login (`74fb876`), `ut_*` (`28befd0`), `cont_*` (`224bdf4`), `prog_*`/`pr_*` (`3cd4810`), `cal_*` (`adb6829`), `globo_invia_messaggio()` | strategia incrementale (vedi SECURITY-DEBT) |
| **HTTP security headers** su tutte le pagine | `common_1.php` (`8d9fcc8`) | |
| Credenziali DB via **file segreti** | config/release (`0ec8993`) | |

### 8.3 Infrastruttura email centralizzata

| Miglioria | Dove nel core | Note |
|---|---|---|
| `globo_crea_phpmailer()` / `globo_invia_email()` / `globo_invia_messaggio()` / `sendErrorEmail()` | `funzioni.php` (`f1a949d`, `a8a6e67`) | unico punto PHPMailer; mai istanziarlo altrove |
| `mail()` → `globo_invia_email()` | `index.php` (`df7b850`) e altrove | i cloni hanno `mail()` diretti (es. nei `catch` PDO) da convertire |
| PHPMailer 6.x, `SMTPSecure`/`Port` espliciti | `funzioni.php` (`4b6632b`) | namespace `PHPMailer\PHPMailer\PHPMailer` |

### 8.4 Redesign UI / design system

| Miglioria | Dove nel core | Note |
|---|---|---|
| Design system CSS `globo.{tokens,themes,app}.css` | `css/` | tema per clone solo in `themes` |
| Partial `head.php` / `header.php` / `footer.php` | `varie/` | charset **UTF-8** (da iso-8859-1) |
| Redesign moduli: login, home, calendario, contatti, progetti/pr, utenti | rispettivi file (`12e1856`, `d203325`, `380a5c6`, `1f17af3`, `e56ed0e`, `8031ef5`…) | |
| Date picker **Flatpickr** | `784b49d` | |
| flash+redirect, no `alert()`/`confirm()`, validazione nativa, modal, toast | trasversale | |
| Paginazione standard, widget ricerca riusabile, "azzera ricerca" (X), righe clickable | trasversale | vedi `docs/ui-redesign-patterns.md` |
| Responsive mobile (fasi 1-8) | CSS + liste | |

### 8.5 Encoding HTML-in-DB (output-only)

| Miglioria | Dove nel core | Note |
|---|---|---|
| Helper `globo_to_text()` / `globo_edit()` + output grezzo in sola lettura | `funzioni.php` + template (vedi SECURITY-DEBT) | **completo** (2026-07-03); vale per core **e** cloni |
| Correzione doppia codifica nei punti di display | `c757457`, `384687d` | i cloni la ereditano |

### 8.6 Config / ambiente / deploy

| Miglioria | Dove nel core | Note |
|---|---|---|
| `config.php` robusto alla porta (`localhost:8080`) | `config/config.php` | |
| Parametrizzazione host/path DB (no `host=localhost` hardcoded, `DIR_LOG`, fopen guard) | vari (`b81618f`) | |
| Tooling di release: file segreti, sonda preflight, build, disabilitazione moduli legacy | `chore(release)` | |

### 8.7 Eliminazioni (natura diversa — non "fix da applicare")

Rimozione di moduli/librerie morte (adhoc_phex/sds/font/gia/imdc/lg/mcp/mlb/vc,
newsletter `nl_*`, CKEditor obsoleti, MDBootstrap, FPDF, script cron dismessi).
**Non** sono migliorie da portare: sono **cancellazioni per clone**, già registrate
in `refactoring-notes.md` → **FILE ELIMINATI — da applicare ai cloni**.

---

## 9. Riferimenti

- `CLAUDE.md` §8 — strategia Core + Custom.
- `docs/refactoring-notes.md` — CORE / CUSTOM-CANDIDATE /
  HARDCODED; *FILE ELIMINATI*; SECURITY-DEBT (encoding output-only, core → cloni);
  TODO FUTURO (`cal_tutti.php`).
- `docs/ui-redesign-patterns.md` — pattern per replicare il redesign UI.
- `src/varie/head.php`, `src/css/globo.themes.css`, `src/config/config.php`.
- **Particolarità dei cloni** → `CLAUDE.md` e `STATO.md` del repo di ciascun clone.
