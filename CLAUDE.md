# CLAUDE.md — Globo (core) e famiglia di cloni

Istruzioni per Claude Code e per chi lavora sul progetto. Valgono per **questo
repository (Globo8, il core)** e per **tutti i cloni**: in un clone questo file arriva
come `core/CLAUDE.md` e il `CLAUDE.md` del clone aggiunge solo le sue particolarità.

Il progetto è condiviso tra più persone, ognuna con il proprio Claude Code: la memoria
personale di Claude **non** è condivisa. Tutto quello che serve a lavorare sta scritto
nei repository (questo file, `README.md`, `STATO.md`, `docs/`). Se una regola manca, si
aggiunge qui, non nella memoria.

## 1. Cos'è Globo

Gestionale aziendale PHP "vanilla" (senza framework), nato da KosmoSol su sé stessa e
poi adattato a più clienti. Migrato da PHP 5 a **PHP 8.3** (Fase 1, conclusa). Database
MariaDB 10.11 per Globo KosmoSol; alcuni clienti hanno **MySQL 5.6** (vedi il loro
CLAUDE.md). Tabelle `latin1` legacy, connessioni e pagine UTF-8.

**Globo KosmoSol è il core**: è l'installazione da cui deriva tutto, e questo repository
è sia il core della famiglia sia l'installazione di KosmoSol (`globo-ks.kosmosol.it`).
I cloni (un repository per cliente) importano il core e ci sovrappongono solo le loro
differenze (Fase 2). Mappa dei repository, URL e porte: `README.md`.

## 2. Lingua e nomi

- Risposte, commenti nel codice, documenti e messaggi di commit **in italiano**, col
  **tu** tra le persone del progetto.
- Codice PHP di Globo: segue lo stile esistente (`adhoc_*`, `cal_*`, `prog_*`…).
- Script di supporto (Python, Bash, PowerShell, Docker): identificatori in inglese,
  commenti in italiano.
- Nei **testi visibili all'utente** (pagine, messaggi, conferme) accenti veri: "verrà",
  "è", "perché", mai "verra'". L'apostrofo resta solo negli script `.ps1`, perché
  PowerShell 5.1 legge l'UTF-8 senza BOM come Windows-1252 e rovinerebbe gli accenti.

## 3. Dove si legge e dove si scrive

| Cosa | Dove |
|---|---|
| Cosa gira in produzione, cosa è in corso e chi lo fa, cosa resta aperto | `STATO.md` (in ogni repo) — **leggerlo a inizio lavoro** |
| Come partire su un PC nuovo, mappa dei repository | `README.md` |
| Come si fa un deploy | `docs/deploy.md` (in ogni repo) |
| Architettura core/custom e playbook per un nuovo clone | `docs/fase2-core-custom.md` |
| Pattern del design system (validi per tutti) | `docs/ui-redesign-patterns.md` |
| Pattern di un solo clone | nel doc del clone (es. `docs/ui-patterns-5stelle.md`), **mai** nel core |
| Debito tecnico, file eliminati, valori cablati, sicurezza | `docs/refactoring-notes.md` |
| Mappa dei dati personali nello schema (per l'anonimizzatore) | `docs/pii-map.md` |

- **I documenti di stato possono essere indietro.** Prima di pianificare su "il doc dice
  che manca X", verificare con `git log` e col codice. E lo stato della **produzione**
  non si deduce dal repository: prima di diagnosticare un guasto in produzione chiedere
  cosa è stato deployato e quando. Quando si trova una discrepanza, si corregge il doc
  subito.
- Scrivere mentre si lavora: un lavoro deve poter essere ripreso da un'altra persona (o
  da una nuova sessione) leggendo `STATO.md` e i commit.

## 4. Lavorare in più persone

- `git pull` prima di cominciare; commit e `git push` alla fine, anche a lavoro a metà.
- **Una modifica in produzione la fa una persona sola**, e prima la annuncia in
  `STATO.md` ("in corso: …, chi, quando"). Vale per deploy, cron, migrazioni DB.
- Le copie Docker locali sono personali e possono divergere (porte, dati). Il DB dev di
  ognuno vive in un volume Docker, fuori da Git.
- Dati grandi o sensibili (dump, `tools/prod-secrets.local.ps1`, file `upload/`) si
  passano **a mano** (chiavetta, rete interna), **mai via cloud**.
- Ogni PC installa l'hook di controllo (§10) dopo ogni clone.

## 5. Cosa non entra mai in un repository

- **Dati personali** di clienti e utenti (nomi, email, telefoni, indirizzi, note libere,
  ticket): né nel codice, né nei documenti, né nei messaggi di commit, né negli output
  di Claude. Un dato finito nella cronologia di Git è di fatto irrecuperabile.
- **Dump** del DB, `upload/`, `globo_log/`, `dist/`, `backup/`: già in `.gitignore`.
- **Segreti** (password DB di produzione, chiavi API SendGrid/Mailchimp, credenziali
  SMTP, liste IP degli uffici, l'hash del super amministratore `ks-globo` in
  `src/index.php`): nei sorgenti sono **segnaposti** `__PROD_<NOME>__`; i
  valori veri stanno in `tools/prod-secrets.local.ps1` (ignorato da Git, uno per repo,
  modello in `tools/prod-secrets.example.ps1`) e li rimette `tools/build-package.ps1`
  nella copia del pacchetto. Un segreto nuovo segue lo stesso schema: segnaposto nel
  sorgente, valore nel file locale, voce nel file `.example`.
- **IP interi** nei documenti: si scrivono accorciati (`93.71.x.x`).
- File oltre 1 MB.

Lettura di dati reali sul proprio PC (dump, DB dev popolato, copie dei siti live):
**consentita**, senza chiedere ogni volta. Il divieto riguarda la loro propagazione.

## 6. Regole assolute

**Operazioni distruttive**
- Claude non cancella file e non sovrascrive file esistenti senza conferma esplicita.
- Nessuna modifica alla produzione da qui (niente collegamenti ai server).
- **DB dev: `DELETE`/`UPDATE` solo per chiave primaria**, mai `WHERE <campo>='<valore>'`
  su colonne non univoche. Il DB dev contiene dati reali, non c'è binlog né backup: una
  cancellazione è irreversibile. Per ripulire dati di test, cancellare gli `id` esatti
  restituiti dall'INSERT; in dubbio, `SELECT` di conferma prima. Se un test modifica un
  record reale preesistente, annotare prima il valore originale e ripristinarlo per id.
  (Lezione 2026-06-11: persa una nota reale con un `DELETE … WHERE numero_ticket=…`.)

**Soglie di approvazione**
- 1-3 file: si applica e si descrive cosa è cambiato.
- 4+ file: piano scritto, attesa dell'ok, poi applicazione.
- Logiche critiche (autenticazione, utenti, query principali, configurazione,
  sicurezza): **sempre** piano + ok, anche per un solo file.
- Refactoring automatici (Rector…): proposti come blocco unico.
- Dopo un piano multi-passo approvato si procede passo per passo senza chiedere conferma
  a ogni passo, con un resoconto finale; ci si ferma per imprevisti o ambiguità nuove.

**Commit**: sempre con conferma esplicita; Claude propone il messaggio (in italiano,
cosa è cambiato e perché), la persona conferma o corregge. Mai credenziali, `.env`, dump.

**Cadenza**
- Un sotto-obiettivo per volta: ogni fase (modifica, test, commit) si completa e si
  verifica prima della successiva.
- Davanti a un errore ci si ferma a capire: ipotesi → una correzione → verifica. Niente
  raffiche di tentativi.
- L'output di un comando è un momento di riflessione: dire cosa conferma, cosa è
  inatteso, cosa cambia nel piano, poi proporre il passo successivo.

**Interpretazione del codice — prevale su tutto il resto.** Se non si capisce con
sicurezza cosa fa una porzione di codice, una variabile globale, un valore cablato, un
costrutto che cambia tra PHP 5/7/8, un file incluso da molti altri, del codice che
sembra morto: **non fare assunzioni, fermarsi e chiedere**, mostrando file e riga e le
ipotesi possibili. Globo ha una lunga storia: dietro ogni stranezza c'è quasi sempre una
ragione che conosce solo una persona del progetto.

## 7. Core e cloni

Dettagli e playbook in `docs/fase2-core-custom.md`. Le regole operative:

- Un clone ha `core/` (il core importato con **git subtree**, **sola lettura**) e
  `custom/src/` (solo le differenze: config, pagine nuove, override a file intero). La
  webroot è l'unione dei due, con `custom/` che vince; in dev la compone l'entrypoint
  `docker/php8/compose-webroot.sh` (più `dev-overlay/config/`, i config dev).
- **Un bug del core si corregge in Globo8**, poi si porta nei cloni con
  `git subtree pull --prefix=core globo-core main --squash`. **Sempre `--squash`**:
  senza, Git rifiuta il merge ("unrelated histories"). Mai patchare `core/` in un clone.
- **Un fix del core non arriva dove il clone ha un override** dello stesso file in
  `custom/src/`: dopo il pull incrociare i file toccati con gli override del clone e
  riapplicare lì il fix, preservando le funzioni custom. Verificare sulla webroot
  composta, non sul sorgente.
- **Quello che si fa nel custom resta nel clone.** Non si promuove al core "per
  pulizia", nemmeno quando sembra utile a tutti: si chiede prima. Ogni riga del core
  arriva in tutti i cloni, anche quelli in produzione.
- **Allineare un clone al core cambia il suo funzionamento**: è un rilascio a sé, con
  collaudo. Lo stato di ogni clone (su `main` o su un'istantanea del core) è in
  `STATO.md` di Globo8.
- Il core non è tutto neutro: gli script `src/script/*.php` (cron) hanno `$dominio` e
  `require` con i path di globo-ks cablati. Ogni clone deve sovrascrivere in
  `custom/src/script/` ogni script che usa, o escluderlo dal pacchetto. Quando si crea
  un override, il file di partenza è quello **del cliente** (dal suo live), non quello
  del core.
- Un redesign di una pagina custom va **collaudato contro il live** del cliente: in
  passato il rifacimento ha perso funzioni presenti solo lì. Per il prossimo clone
  migrato da un live c'è `Globo-5stelle/tools/feature-diff`.

## 8. Regole di codice

- Target **PHP 8.3**. Query con **prepared statement PDO**; output con gli helper di
  encoding di `funzioni.php` (vedi SECURITY-DEBT in `refactoring-notes.md`).
- Email solo da `globo_invia_email()` / `globo_invia_messaggio()` di `funzioni.php`,
  mai PHPMailer istanziato altrove.
- **Prima di scrivere una pagina, consultare `docs/ui-redesign-patterns.md`**, non solo
  la pagina gemella (anche quella può essere indietro). Una deviazione da un pattern
  documentato si corregge (entro le soglie del §6); se il pattern documentato è
  superato, si aggiorna prima il documento. Pattern nuovo → si documenta nel file giusto
  (core o clone) nello stesso commit.
- **Niente `alert()`/`confirm()` nativi**: `globoAlert()`/`globoConfirm()` (modale di
  `varie/footer.php`), che sono asincroni.
- Con la validazione asincrona, `<form onsubmit="return controllaForm();">` **non blocca
  mai l'invio** (una funzione `async` restituisce sempre una Promise). Pattern corretto
  in `ui-redesign-patterns.md` §22: listener `submit` con `preventDefault()`.
- Liste (`.list`): colonna azioni con intestazione etichettata; `--list-cols` sul
  contenitore `.list`, non sull'header; nelle righe `justify-content`, mai `text-align`;
  colonne condizionali con la stessa condizione su header e righe.
- **Codifica**: Globo è UTF-8, i siti legacy dei clienti spesso ISO-8859-1. Un file
  latin-1 non si legge né si scrive come UTF-8. Dopo ogni modifica cercare i byte
  `EF BF BD` (`grep -rlP '\xEF\xBF\xBD'`): se compaiono, recuperare i caratteri da Git,
  mai riscriverli a mano.
- Commenti in italiano che spiegano il **perché**, più fitti dove la logica è sottile.
- **Librerie di terze parti da non modificare**: `src/ckeditor/`, `src/elfinder/`,
  `src/phpmailer/`, `src/shadowbox/`, `src/imageuploader/` (e nei cloni le loro copie).

## 9. Deploy

- Il pacchetto si crea **solo** con `tools/build-package.ps1` del repository: compone
  core + custom su una copia in `dist/`, verifica i valori di produzione attesi, rimette
  i segreti, fa lo zip. Su un PC senza `tools/prod-secrets.local.ps1` il build si ferma.
- Il build mette al riparo i pacchetti già in `dist/` in `C:\Temp\Globo-<nome>-pacchetti\`
  prima di cancellarla: lì c'è la copia esatta di ciò che è stato spedito, per i
  confronti e i rollback. Non si sovrascrive mai un pacchetto archiviato.
- Estrarre lo zip sul server **non cancella** i file tolti dal codice: vanno rimossi a
  mano, e annotati nella procedura.
- Gli script da cron non passano da nessuna pagina e non girano in Docker: nessun
  collaudo funzionale li tocca. Si verificano leggendoli e col controllo del build.
  Quando un deploy riguarda qualcosa che **invia** (mail, notifiche), prevedere come
  accorgersi che ha smesso (es. `globo_log/` che non cresce più).
- **Fine riga**: tutti i file di testo sono LF su ogni PC (`.gitattributes`,
  `* text=auto eol=lf`), così il pacchetto esce uguale da chiunque lo costruisca. Un
  file in CRLF che compare nella copia di lavoro va riportato in LF, non committato così.
- Procedura del singolo repository: `docs/deploy.md`.

## 10. Guardrail automatici

Controlli che non dipendono dalla buona volontà di chi lavora. Ogni guardrail nuovo va
visto **fermare un errore vero** costruito apposta (prova in negativo) e per il motivo
giusto: un "[ok]" verde da solo non dimostra niente.

1. **Hook `pre-commit`** (`tools/pre-commit`, uguale in tutti i repo; nei cloni
   `core/tools/pre-commit`). Si installa con `bash tools/install-hooks.sh` (nei cloni
   `bash core/tools/install-hooks.sh`), **dopo ogni clone** e quando la copia canonica
   cambia: `.git/hooks/` non è versionata e la sua assenza è silenziosa. Blocca:
   IP interi, i valori di `tools/prod-secrets.local.ps1`, forme di chiavi SendGrid e
   Mailchimp, `IDENTIFIED BY` fuori da `db-init/`, file oltre 1 MB; poi `php -l` del
   contenuto staged se il container web è attivo (altrimenti avvisa e lascia passare).
   Falso allarme: controllare a occhio, poi `git commit --no-verify`.
2. **Path di un'altra installazione nel pacchetto** (build dei cloni): il build si ferma
   se un `.php` del pacchetto contiene `globo-ks.kosmosol.it` fuori dai commenti. Nato
   dal guasto MC Protection (cron morti in silenzio per due settimane).
3. **Pacchetto live al riparo** (build di tutti i repo): vedi §9.
4. **Segreti** (build di tutti i repo): il build si ferma se un segnaposto
   `__PROD_*__` resta senza valore o se un segreto del file locale non trova il suo
   posto.

Come aggiungerne uno: errore concreto avvenuto → regola o controllo? → script →
prova in negativo → documentarlo qui (motivo, cosa controlla, file, come si salta) →
commit di script e documento insieme.

## 11. Trappole già pagate

- **Anonimizzazione**: le etichette delle colonne mentono (email nei campi `sito`,
  telefoni nelle `note`). Validare la mappa PII con ricerche sui dati veri, e chiudere
  con un grep "agnostico" su tutto il dump (email, CF, IBAN).
- **Un dry-run pulito** dice solo che lo script ha fatto quello che gli è stato chiesto.
- **Un guasto senza errore è peggio di un errore**: un `require` di un path inesistente
  in uno script cron muore prima di caricare il log. Nessun allarme, nessuna traccia.
- **Docker su Windows**: dopo `docker compose restart web` la webroot composta non è
  subito pronta (l'entrypoint fa l'rsync prima di Apache): ricontrollare il file
  composto prima di concludere che una modifica non ha effetto. In Git Bash
  `docker exec` con path Unix vuole `MSYS_NO_PATHCONV=1`, ma la stessa variabile rompe
  i cookie-jar di `curl`.
- **Collaudo via curl**: un 302 con corpo vuoto passa il controllo "nessun errore" ma è
  una pagina che ha rimbalzato. Guardare insieme codice HTTP, assenza di errori PHP ed
  effetto sul DB. Il login si blocca per IP dopo 5 tentativi falliti in 10 minuti.
- **Confrontare due copie di Globo**: fine riga misti (`diff -w --strip-trailing-cr`) e
  codifiche diverse; normalizzare prima di misurare una divergenza.
- **PowerShell 5.1**: `Get-Content -Encoding Byte … | programma esterno` non passa i byte
  intatti (un `php -l` così risponde sempre "No syntax errors"). Per un controllo
  affidabile: container usa e getta con la cartella montata in sola lettura, o
  `git show :file | docker exec -i … php -l` come fa l'hook. E provarlo su un file rotto.
