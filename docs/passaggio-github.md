# Passaggio della famiglia Globo su GitHub

Scheda di avanzamento del passaggio di Globo8 e dei cloni su repository GitHub
privati, per lavorarci in più persone. Procedura seguita:
`C:\Progetti\E-commerce\strumenti\condividere-un-progetto.md`.

## Decisioni prese (2026-11-11)

- Tutti (Alessandro, il collega, eventuali colleghi futuri) lavorano su **tutto**:
  Globo8 e i cloni.
- **Globo KosmoSol è il core**: KosmoSol ha sviluppato Globo e lo ha usato per
  prima su sé stessa. Resta in Globo8, non diventa un clone.
- **Struttura confermata: 6 repository** (`Globo8`, `Globo-5stelle`, `Globo-MCP`,
  `Globo-MLBenessere`, `Globo-StudioCia`, `Globo-Verdecasa`), con il core importato
  nei cloni via `git subtree`. Cambia solo la sorgente del subtree: GitHub invece
  di `C:/Progetti/Globo8`.
- **Fuori dal giro**: `Verdecasa-Sito` e `db-analysis` restano solo in locale.
- Registri e lezioni che non servono agli sviluppi futuri restano fuori dai repo;
  chi arriva deve partire dal progetto più pulito possibile ma poterlo gestire
  per intero.
- Le cartelle locali **non si rinominano**: Docker chiama i volumi (con il DB dev,
  che contiene dati reali e non ha backup) dal nome della cartella.

## Fasi

| # | Fase | Stato |
|---|---|---|
| 1 | Fotografia e ricerca dei dati sensibili su tutti i repo | ✅ fatta 2026-11-11, decisioni in attesa |
| 2 | Versione del core di ogni clone (vincolo: **nessun cambio di funzionamento**) | analisi fatta, decisione in attesa — vedi sotto |
| 3 | Segreti fuori dal codice (segnaposti `__PROD_*__` + `prod-secrets.local.ps1`) | ✅ fatta 2026-11-11 su tutti e 6 i repo |
| 4 | Pulizia (documenti, residui) e regole: CLAUDE.md di Globo8 + uno per clone | ✅ fatta 2026-11-11, commit in attesa di conferma |
| 5 | Prova generale su copie usa e getta (repo nuovi, subtree add/pull, build) | da fare |
| 6 | Push su GitHub, con conferma esplicita | da fare |

## Fase 1 — risultati (solo conteggi; i valori restano in locale)

Perimetro: file tracciati + nuovi non ignorati; nei cloni esclusa `core/`, che
verrà sostituita dal Globo8 ripulito. Nessuna modifica in sospeso, nessun file
sopra 1 MB.

| Repo | File | MB | di cui librerie terze |
|---|---|---|---|
| Globo8 | 1.115 | 10 | ~630 file, 8 MB |
| Globo-5stelle | 129 | 2,5 | — |
| Globo-MCP | 58 | 0,3 | — |
| Globo-MLBenessere | 65 | 0,4 | — |
| Globo-StudioCia | 29 | 0,2 | — |
| Globo-Verdecasa | 48 | 0,4 | — |

Trovati, da trattare nelle fasi 3-4:

- chiavi API (SendGrid, Mailchimp) scritte nel codice e nei config;
- password SMTP e password dei DB di produzione nei config dei cloni;
- IP degli uffici dei clienti nelle liste di accesso dei config;
- password delle copie Docker locali (`db-init/`, `dev-overlay/`);
- documenti di collaudo/deploy/analisi con la storia del lavoro.

Decisioni di Alessandro sulla Fase 1 (2026-11-11), tutte come proposto:

- A. chiavi API, password SMTP e password dei DB di produzione escono dal codice
  (fase 3, piano dedicato);
- B. le password delle copie Docker locali restano, documentate come valori dev;
- C. gli IP degli uffici vanno con i segreti; email, IBAN e telefoni aziendali
  restano;
- D/E. documenti: restano i pattern UI e le guide, si condensano procedure di
  deploy e analisi, via collaudi e diari chiusi; via i residui. Dettaglio file
  per file in fase 4.

## Fase 2 — la versione del core nei cloni

Vincolo di Alessandro: il passaggio **non deve cambiare il funzionamento** di
nessun clone.

Codice del core che girerebbe davvero in più (file aggiornati nel core e non
oscurati da un file in `custom/`), se i cloni si allineassero a Globo8 di oggi:

| Clone | Core oggi | File attivi che cambierebbero |
|---|---|---|
| Verdecasa | 085cc88 | 0 (solo documenti) |
| 5stelle | 61f1e1c | 1 (`globo.app.css`) |
| StudioCia | bddf1da | 3 (calendario, `globo.app.css`, `head.php`) |
| MCP, MLBenessere | dfc141f / c266654 (stesso `src/`) | 20, tra cui la rimozione di `prog_doc.php` e `prog_file.php` |

Nota per la fase 3: Verdecasa non ha un `config_smtp.php` suo, usa quello del
core.

**Decisione (2026-11-11): istantanee del core.** Il Globo8 online avrà, oltre a
`main`, tre istantanee (5stelle, StudioCia, MCP+MLBenessere) con `src/` identico
a quello che il clone usa oggi, meno i segreti. Ogni clone importa la sua
istantanea, Verdecasa `main`. Controllo: webroot composta prima/dopo confrontata
file per file. L'aggiornamento al core recente resta un'operazione separata, con
collaudo, clone per clone.

Verifiche fatte nella stessa giornata:

- **Calendario 5stelle**: tutte le pagine `cal_*` sono custom (11 file), ma le 24
  classi CSS `cal-*` che usano sono **tutte definite in `globo.app.css` del core**.
  Una modifica futura al CSS del calendario nel core arriverebbe quindi anche a
  5stelle. Le due modifiche attuali al calendario toccano classi che 5stelle non
  usa: sarebbero state ininfluenti.
- **Calendario StudioCia**: nessun file custom, usa il calendario del core.
- **Email Verdecasa**: il codice può spedire tramite le credenziali SMTP di
  `config_smtp.php` del core: avvisi di errore DB a KosmoSol (`index.php`,
  `common_1.php`) e notifiche di calendario/note/progetti agli utenti con
  `flag_notifiche_email = 1`. Gli script cron di notifica sono esclusi dal
  pacchetto dal 2026-09-15.

Decisioni (2026-11-11):

- **StudioCia si aggiorna al core attuale** (`main`): porta il calendario del core
  (etichette sulle mezz'ore, impegni fuori orario, ultima ora non tagliata). Unica
  modifica funzionale ammessa, chiesta da Alessandro. `head.php` cambia solo per
  caricare `globo.custom.css` se esiste: StudioCia non ce l'ha, quindi è
  ininfluente. Restano così **due** istantanee: 5stelle e MCP+MLBenessere.
- **5stelle**: regola nel suo CLAUDE.md + script che a ogni aggiornamento del core
  elenca le regole `cal-*` cambiate in `globo.app.css` (fase 4). L'isolamento vero
  (classi proprie del calendario 5stelle) eventualmente dopo il passaggio.
- **Verdecasa**: il build continua a ricevere le credenziali SMTP (fase 3).

**StudioCia allineato (2026-11-11)**: `git subtree pull --squash` (commit
`1c60fe3` + `b3dc5b7` in Globo-StudioCia, non ancora in produzione). Verifiche:

- albero `core/` identico a quello di Globo8 `cdb6813`;
- nella webroot composta i 3 file cambiati coincidono col repo, `php -l` pulito;
- pagina di login: HTTP 200, nessun errore PHP, stessi 4 CSS di prima (nessun
  `globo.custom.css`);
- **il calendario di StudioCia è spento** in produzione (`MOD_CALENDARIO` false su
  entrambe le sedi, decisione del 2026-09-17): `cal_lista.php` risponde 302 anche
  con login. Le correzioni al calendario quindi non cambiano niente oggi; ci
  saranno se un giorno il modulo verrà riacceso.

Nota per l'ambiente locale: le porte dev di StudioCia (8140, 8141, 3311) sono
occupate sul PC di Alessandro dal progetto "polo"; il collaudo è stato fatto con
un override temporaneo delle porte (web su 8150), senza toccare il compose.

## Fase 3 — segreti fuori dal codice (2026-11-11)

Meccanismo, uguale in tutti i build: nei sorgenti ogni segreto è un segnaposto
`__PROD_<NOME>__`; i valori veri stanno in `tools/prod-secrets.local.ps1` (ignorato
da Git, uno per repo, modello in `prod-secrets.example.ps1`); il build li rimette
nella copia del pacchetto, in latin-1 byte per byte, e si ferma se un segnaposto
resta senza valore o se un segreto non trova il suo posto.

| Repo | Segnaposti | Pacchetto prima/dopo | Prova in negativo |
|---|---|---|---|
| Globo8 | 5 (SMTP) | 1.091 file identici | segreto in più e segreto mancante: bloccati |
| Verdecasa | 7 (DB + SMTP del core) | 1.110 file identici | bloccata |
| MCP | 7 | 601 file identici | bloccata |
| MLBenessere | 13 (DB delle tre sedi + SMTP) | 571 file identici | bloccata |
| StudioCia | 13 (DB + `$IP_OK` delle due sedi + SMTP) | 152 file identici | bloccata |
| 5stelle | 10 (DB, 2 Mailchimp, SendGrid nel codice di 5 file, SMTP) | 586 file identici | bloccata |

Note:

- `$IP_OK` di StudioCia: il segnaposto è tra apici
  (`array("__PROD_IP_OK_FIDENZA__")`), quindi un config usato senza build lascia
  fuori tutti invece di far entrare tutti.
- Verdecasa è stato allineato al core (`main`) per prendere `config_smtp.php` con i
  segnaposti: nessun file runtime cambiato oltre a quello.
- Pacchetti live messi al riparo dal primo build: Verdecasa 1.0.5 e 5stelle 1.2.0
  (prima non erano in `C:\Temp\Globo-*-pacchetti\`).
- In dev le email (e la sync Mailchimp di 5stelle) non partono più dove si usavano
  le chiavi vere: Globo8, 5stelle, MCP, MLBenessere.
- Ricerca finale delle forme della Fase 1 (chiavi SendGrid/Mailchimp, password
  letterali, liste IP): zero risultati, salvo la password del DB **dev** nel config
  di Globo8 (decisione B).
- `dist/` dei sei repo contiene ora un pacchetto di prova `9.9.9-prova` al posto del
  live (che è in archivio): da rimettere a posto, con conferma.

## Fase 4 — pulizia e regole (2026-11-11)

- Schema uguale in tutti i repo: `CLAUDE.md` (regole; quello di Globo8 è il manuale
  comune, quello del clone rimanda a `core/CLAUDE.md`), `README.md` («Lavorare sul
  progetto»), `STATO.md` (produzione, in corso, aperti), `docs/deploy.md` (procedura).
- Distillati e tolti: analisi, collaudi, release-v1, note di deploy dei cloni (restano
  nella cronologia locale); `docker/php5/` del core, `calendar_popup.js.old` e
  `docs/script-non-in-uso/` dei cloni. `refactoring-notes.md` alleggerito solo della
  sezione vuota INCLUDE-POINT.
- Hook `tools/pre-commit` esteso: IP interi, valori di `prod-secrets.local.ps1`, forme
  di chiavi SendGrid/Mailchimp, `IDENTIFIED BY` fuori da `db-init/`, file oltre 1 MB,
  poi `php -l`. Collaudato su una copia usa e getta: un commit pulito passa, ogni tipo
  di dato sporco viene bloccato col messaggio giusto.
- 5stelle: `tools/controlla-calendario-core.sh`, provato in positivo e in negativo.
- `.gitattributes` (`*.sh` in LF) aggiunto a 5stelle, MCP, MLBenessere: senza, su un
  clone Windows nuovo l'entrypoint Docker sarebbe arrivato in CRLF.
- Verifiche: pacchetti dei sei repo identici a quelli della fase 3 (5stelle: un file
  `.old` in meno, atteso); hook nuovo passato sul contenuto in stage; nessun link rotto.

Decisioni del 2026-11-11 dopo la fase 4:

- **Hash del super amministratore `ks-globo`** (`src/index.php:68`): diventa il
  segnaposto `__PROD_KS_ADMIN_MD5__`, rimesso dal build (pacchetto di Globo8 verificato
  identico). In dev `ks-globo` non entra più. **Ogni clone prende `index.php` dal core**:
  quando lo riceverà col segnaposto (Verdecasa e StudioCia al prossimo subtree pull, gli
  altri con le istantanee della fase 5) va aggiunta la stessa voce al suo
  `tools/prod-secrets.local.ps1`, altrimenti il build si ferma (comportamento voluto).
- La chiave SendGrid principale, comparsa per errore in un output di Claude durante il
  collaudo dell'hook, **resta quella attuale** (decisione di Alessandro).
- Hook nuovo installato da Claude nei sei repo (`.git/hooks/pre-commit`).

Fase 4 committata il 2026-11-11 (Globo8 `6845256`, Verdecasa `59e5643`, MCP `a2b610e`,
MLBenessere `283541d`, StudioCia `2ecb445`, 5stelle `e62fb01`).

## Fase 5 — prova generale su copie (2026-11-11)

Repository nuovi creati nella scratchpad, senza toccare quelli veri:

- **Globo8**: un commit con il contenuto di oggi, più due rami di **istantanea**
  (`istantanea/5stelle`, `istantanea/mcp-mlb`): documenti e strumenti di oggi, `src/`
  uguale al `core/src` che il clone usa oggi, con i segreti come segnaposti.
  L'istantanea si prende dal `core/src` **del clone**, non da un commit di Globo8: in
  5stelle due file del core erano stati corretti direttamente in `core/` (luglio 2026,
  entrambi oscurati da override, quindi senza effetto).
- **Cloni**: primo commit col contenuto senza `core/`, poi `git subtree add --squash` dal
  ramo giusto (`main` per Verdecasa e StudioCia).
- **Pacchetti**: tutti e sei identici a quelli di oggi **ignorando i fine riga** (vedi
  sotto).
- **`subtree pull --squash` da `main`** su un clone nato da istantanea (5stelle): riuscito,
  `core/` uguale a `main` dopo il pull; lo script del calendario funziona anche lì.
- L'hook, passato su tutto il contenuto come in un primo commit, ha trovato un dato vero:
  l'**utente del DB di produzione di KosmoSol** citato in `docs/refactoring-notes.md`
  (corretto). Il resto erano falsi allarmi verificati: IP degli uffici KosmoSol in
  `src/index.php` e in un commento di 5stelle, numeri di versione, la rete di
  documentazione `192.0.2.0`, un esempio in StudioCia (corretto).

**Fine riga.** Nei repository Git i file di testo sono tutti in LF, ma le copie di lavoro
no: Globo8 quasi tutto LF, i cloni quasi tutto CRLF (li crea il checkout con
`core.autocrlf=true`). I pacchetti si portano dietro questo misto, e su un altro PC
verrebbero diversi. Decisione 👤 in sospeso.

## Da fare adesso

Decisione sui fine riga; commit delle due correzioni trovate in fase 5; poi fase 6.
