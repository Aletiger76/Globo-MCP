# refactoring-notes.md — Osservazioni per la Fase 2 (separazione core/custom)

Registro centralizzato delle osservazioni raccolte durante la Fase 1 (migrazione PHP 8).
Popolato progressivamente da Claude Code man mano che si lavora sui file.
Formato di ogni voce: `path/al/file.php:NN — breve descrizione`

---

## SICUREZZA — controllo accessi

### 2026-06-18 — Bonifica access control sulle pagine (core, commit `73f8541`→`26dc171`)

**Problema.** `varie/common_1.php` garantisce solo il **login**, non il livello.
Decine di pagine affidavano il gating dei diritti solo ai link di menu (home/header),
senza guardia a inizio pagina → raggiungibili via URL da qualsiasi utente loggato.
Inoltre le guardie esistenti facevano `header("Location: index.php");` **senza `exit()`**:
lo script proseguiva, quindi mutazioni (`*_el`) ed echo dei dati avvenivano comunque
(redirect solo cosmetico). Due export erano persino **senza login** (`bil_xls`, `cont_xls`).

**Helper introdotto.** `varie/funzioni.php` → `globo_richiedi_accesso(bool $consentito, string $dest = "index.php")`:
fa `header()+exit()` garantiti. Convenzione: ogni pagina che richiede diritti chiama in cima,
subito dopo `require("varie/common_1.php")`, es.
`globo_richiedi_accesso(MOD_X && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_X);`.

**Regole applicate (per famiglia):**
- `ut_lista/ut_ins_1/ut_ins_2/ut_el` → solo admin globale (`livello == 0` = ks-globo).
- `ut_vis/ut_agg/ut_destroy_token` → accessibili a tutti ma **vincolate al proprio account**
  (`nome_utente` forzato da sessione per i non-admin); in `ut_agg` il `livello` è riletto dal DB, mai dal POST.
- `pr_template_*`/`pr_tem_step_*`/`pr_proced_*`/`pr_proc_step_*` → `MOD_PROGETTI && livello == 0`.
- `bil_*` → `MOD_BILANCIO`; `bilv2_*` → `MOD_BILANCIOV2` (viste lista/vis consentono utenti `BILV2_UTENTE_RO`, mutazioni no);
  `bilv2ag_*` → `MOD_BILANCIOV2 && BILV2_AG`; `globomail_*` → `MOD_GLOBOMAIL && livello>0`.
- `cal_*` → `MOD_CALENDARIO`; `cont_*` → `MOD_CONTATTI`; `prog_*`/`pr_nota_*` → `MOD_PROGETTI`.
- `ins_invio_promemoria/scadx` (upload CSV + invio massivo email, non linkate da menu) → `MOD_NEWSLETTER`.
- `bil_xls`/`cont_xls` → aggiunto `require(common_1)` (login) + guardia; mantenuto il loro `$db` legacy (charset) per non alterare l'export.
- `exit()` aggiunto alle 22 guardie inefficaci (`adhoc_ks_h5s_*`, `adhoc_ks_web_*`, `adhoc_post_*`, `bil_home`, `bilv2_home`).
- Bonus: SQL injection corretta in `globomail_lista` (DELETE) e `bil_xls` (`$anno_bilancio`) → prepared statement;
  il delete globomail dei selezionati vincola anche `utente_dest` (no cancellazione messaggi altrui).

**⚠️ IMPORTANTISSIMO per i cloni (Fase 2).** Il fix nel core è **inefficace dove un clone ha un
override `custom/src/<pagina>.php`**: la webroot composta serve la versione custom, non quella core.
Ogni clone va ricontrollato: trovare gli override custom che ombreggiano le pagine blindate e
applicare le **stesse** guardie. In 5stelle fatto il 2026-06-18 (16 override: `ut_lista`, `ut_vis`,
`cal_*`, `prog_*`, `pr_nota_*`; gli `adhoc_ks_h5s_*` custom avevano già la guardia con `exit`).

**NON ancora coperto (debito residuo):**
- Audit SQLi **completo** del codebase (corretti solo i punti incrociati qui).
- Endpoint cron `ins_invio_*_2.php`: protetti da chiave giornaliera **debole** hardcoded `md5(data."globok0s")`.
- `authorization.php`: imposta il cookie token da URL (by design, validato al login) — lasciato com'è.

---

## CORE

<!-- File e funzioni identificate come parte del core comune e riusabile -->

### 2026-05-04 — `src/varie/funzioni.php`: architettura centralizzata per l'invio email

Refactoring dell'infrastruttura email: tutte le email del core ora passano per un'unica catena di funzioni in `funzioni.php`.

- `src/varie/funzioni.php:12` — `globo_crea_phpmailer()`: unico punto di configurazione PHPMailer (SMTP, auth, charset UTF-8). Nessun altro file deve istanziare PHPMailer direttamente.
- `src/varie/funzioni.php:26` — `globo_invia_email($destinatario, $oggetto, $testo)`: invio diretto a singolo indirizzo, senza dipendenza dal DB. Da usare per email di sistema (errori, notifiche one-shot).
- `src/varie/funzioni.php:41` — `globo_invia_messaggio(...)`: notifiche agli utenti interni con logica visibilità, dual-channel (email + globomail), log su file.
- `src/varie/funzioni.php:131` — `sendErrorEmail($errorMessage)`: wrapper di 3 righe per errori DB critici, chiama `globo_invia_email()`.

Invariante da mantenere in Fase 2: ogni nuovo punto di invio email nel core deve usare `globo_invia_email()` o `globo_invia_messaggio()`, mai istanziare PHPMailer direttamente.

---

## CUSTOM-CANDIDATE

<!-- File o funzioni che sembrano specifici di un dominio/cliente: candidati a finire in custom/ -->

### 2026-09-07 — `ins_invio_scadx_2.php` / `ins_invio_promemoria_2.php`: endpoint di invio massivo di KosmoSol

Leggono un CSV da `upload/scadx/` e `upload/promemoria/` con **path assoluto** sulla webroot
di globo-ks e spediscono email in massa. Nessuna pagina di Globo li referenzia: sono
richiamati dall'esterno, protetti solo dalla chiave giornaliera debole `md5(Ymd."globok0s")`
(già segnalata in questo documento). Specifici di KosmoSol a tutti gli effetti.

Per i cloni: **non vanno overridati, vanno esclusi dal pacchetto** — in un clone sarebbero
codice morto ma raggiungibile, con un path che non esiste e la capacità di inviare mail.
Già esclusi dal build di MCP (`tools/build-package.ps1`, 2026-09-07).

### 2026-05-04 — Moduli Bilancio V1 e Bilancio V2: specifici di questo progetto

I moduli bilancio (`bil_*.php`, `bilv2_*.php`, `bilv2ag_*.php`) sono usati esclusivamente in questo progetto Globo e non fanno parte del core comune. Verranno gestiti nel progetto cliente separato durante la Fase 2.

Implicazione per la migrazione corrente:
- **Prepared statements (SECURITY-DEBT)**: esclusi dalla migrazione incrementale del core; da affrontare all'interno del progetto cliente in Fase 2.
- **PHP 8**: i file sono già funzionanti su PHP 8.3 (testati in Step 4F), nessun intervento urgente.

File coinvolti:
- `src/bil_home.php`, `src/bil_lista.php`, `src/bil_vis.php`, `src/bil_ins_1.php`, `src/bil_ins_2.php`, `src/bil_agg.php`, `src/bil_el.php`, `src/bil_xls.php`
- `src/bilv2_*.php` (bilancio V2)
- `src/bilv2ag_*.php` (agenti bilancio V2)

---

### 2026-05-04 — Modulo GloboMail (`globomail_*.php`): disabilitato, SQL injection da risolvere se riattivato

`MOD_GLOBOMAIL = false` nella config: il modulo è disabilitato e non accessibile dall'interfaccia. Le SQL injection nei file del modulo non sono quindi attivamente sfruttabili, ma il codice contiene vulnerabilità reali da risolvere prima di qualsiasi riattivazione.

File con query vulnerabili:
- `src/globomail_lista.php:9` — DELETE in foreach su `$_POST["id_globomail_el"][]`
- `src/globomail_lista.php:65` — SELECT con LIKE su `$ricerca_testo` da POST
- `src/globomail_vis.php:22` — UPDATE con `$_GET["id"]`
- `src/globomail_vis.php:30` — SELECT con `$_GET["id"]`
- `src/globomail_el.php:16` — DELETE con `$_POST["id_globomail"]`
- `src/globomail_ins_1.php:28` — SELECT con `$_POST["id_globomail"]`

**Azione**: se `MOD_GLOBOMAIL` venisse riportato a `true`, migrare tutte le query sopra a prepared statement PDO prima del deploy. `globomail_ins_2.php` non ha query dirette (usa `globo_invia_messaggio()`).

---

### 2026-05-04 — `src/ins_invio_solleciti_2.php`: invio solleciti fatture, specifico KosmoSol

Script batch per invio email solleciti pagamento ai clienti KosmoSol. Completamente fuori dal core Globo:
- Si connette al DB `kosmosol` (non `globo`) con credenziali hardcoded separate (utente del DB di produzione, oggi in `tools/prod-secrets.local.ps1`)
- Opera su tabelle `h5s_pagamenti` e `h5s` — appartenenti al modulo `MOD_ADHOC_KSH5S`
- Invia email via cURL diretto alle API SendGrid (non usa PHPMailer/`globo_invia_email()`)
- Contiene SQL injection nei parametri di INSERT/UPDATE dentro `InsertFattureDB()` — da risolvere in Fase 2, nel progetto cliente

Il modulo è già disabilitato in dev (`MOD_ADHOC_KSH5S = false`). **Non migrare nel core.**

- `src/ins_invio_solleciti_2.php:4` — funzione `InsertFattureDB()`: SQL injection su INSERT e UPDATE dentro loop
- `src/ins_invio_solleciti_2.php:200` — API key SendGrid hardcoded (`Bearer SG.…`)

---

### 2026-04-28 — Modulo KosmoSol: customizzazione cliente che usa un secondo DB

Durante Step 4F (test login + navigazione iniziale) è emerso che `home.php` include `varie/cron-schedule.php`, che apre una connessione al database `kosmosol` (separato dal DB principale `globo`). Il DB `kosmosol` non esiste nell'ambiente Docker dev e non è parte del core di Globo: è una customizzazione specifica del cliente KosmoSol.

L'analisi del codebase ha identificato cinque aree di customizzazione legate al DB `kosmosol`, da estrarre in Fase 2 verso il progetto cliente dedicato (`globo-cliente-kosmosol`):

**1. Gestione strutture ricettive H5S (`MOD_ADHOC_KSH5S`)**
- File: `src/adhoc_ks_h5s_lista.php`, `src/adhoc_ks_h5s_ins_vis.php`, `src/adhoc_ks_h5s_ins_agg.php`, `src/adhoc_ks_h5s_el.php`, `src/adhoc_ks_h5s_sinc_nl.php`
- Tabelle DB `kosmosol`: `h5s`, `h5s_server`, `h5s_channel_manager`, `h5s_servizio`
- Funzione: anagrafica strutture ricettive (hotel) con dati su PMS, booking online, channel manager. Include sincronizzazione email verso Mailchimp.

**2. Gestione domini web (`MOD_ADHOC_KS`)**
- File: `src/adhoc_ks_web_lista.php`, `src/adhoc_ks_web_ins_1.php`, `src/adhoc_ks_web_ins_2.php`, `src/adhoc_ks_web_vis.php`, `src/adhoc_ks_web_agg.php`, `src/adhoc_ks_web_el.php`
- Tabella DB `kosmosol`: `web`
- Funzione: CRUD sui domini web dei clienti KosmoSol con date di scadenza e dati di backend.

**3. Cron scheduling MEXAL (`src/varie/cron-schedule.php`)** — ⚠️ **RIMOSSO il 2026-06-17** (integrazione MEXAL dismessa: eliminati `cron-schedule.php`, `cron/cron-runner.php`, `varie/mexal-api.php`, la colonna FT/€ della lista H5S e `ajax-call.php`; rimosso il token `MEXAL_PROD_API_AUTH` da `htaccess`. Da fare in produzione: `DROP TABLE cron_schedule` sul DB `kosmosol` + rimozione della voce crontab.)
- Tabella DB `kosmosol`: `cron_schedule`
- Funzione: flag on/off per un job di sincronizzazione con MEXAL (gestionale esterno).
- Particolarità: è l'unico file con il nome DB `"kosmosol"` hardcoded come stringa letterale invece che tramite la costante `MYSQL_DB_KS`. Vedi sezione `HARDCODED`.

**4. Cross-check con Bilancio V2 (`src/bilv2ag_el.php`)**
- Prima di eliminare un agente dal modulo bilancio, verifica se è associato a strutture H5S nel DB `kosmosol`. Da gestire separatamente in Fase 2.

**5. Script batch Mailchimp**
- File: `src/script/globo_sync_mailchimp.php`, `src/script/globo_delete_members_mailchimp.php`
- Funzione: sincronizzazione programmata di email da `h5s` verso liste Mailchimp.

**Costanti coinvolte (in `src/config/globo-ks_kosmosol_it.php`):**
- `MOD_ADHOC_KS`, `LIV_MOD_ADHOC_KS`, `MYSQL_DB_KS` (riga 33)
- `MOD_ADHOC_KSH5S`, `LIV_MOD_ADHOC_KSH5S`, `MYSQL_DB_KSH5S` (riga 34)

---

## FILE ELIMINATI — da applicare ai cloni

Registro dei file e moduli **rimossi definitivamente** da questo progetto durante la migrazione.
Ogni clone di Globo che condivide lo stesso codebase di partenza conterrà probabilmente gli stessi file: al momento dell'analisi di un clone, questi possono essere eliminati immediatamente senza ulteriore analisi.

Formato: `pattern o lista file — motivo della rimozione — data`

---

### 2026-09-18 — Documenti di progetto (`prog_doc.php`, `prog_file.php`)

**Motivo**: funzione mai usata da nessun cliente, e con un download vulnerabile.

- **Nessuno la usa.** `MOD_PROGETTI_DOC` e' spento (o non definito) in tutte le installazioni, e
  la tabella `progetto_documento` e' **vuota** ovunque: KosmoSol, 5stelle, MC Protection, ML
  Benessere (Aversa, Piacenza, ML Acque Piacenza), Verdecasa, Studio CIA (Fidenza, San Secondo).
  Verificato il 2026-09-18 sulle copie dev dei database.
- **`prog_file.php` leggeva qualunque file.** Prendeva il nome da `$_GET["file"]`, gli anteponeva
  `PROG_DIR_DOC` e lo passava a `readfile()` senza controllare il percorso. Provato in dev:
  `prog_file.php?tipo=1&file=config/config_smtp.php` restituiva il sorgente del config (chiave
  SendGrid compresa) e `file=../../../etc/hostname` usciva dalla webroot. Bastava un utente
  autenticato qualsiasi. Esposte le installazioni con `PROG_DIR_DOC` definita: KosmoSol (con
  `../`), 5stelle, MC Protection, Studio CIA. Il flag spento nascondeva solo i link, non la pagina.
- **Il difetto era gia' stato trovato e corretto due volte, ma solo nei cloni**:
  `Globo-MCP/custom/src/adhoc_mcp_file.php` e `Globo-Verdecasa/custom/src/adhoc_vc_file.php`
  (entrambe con `basename()` sull'input e un commento che descrive l'attacco). Nessuna delle due
  correzioni era risalita al core. Lezione: **un difetto di sicurezza trovato in una copia va
  cercato anche nell'originale da cui la copia e' nata**.
- **Non sono documenti di Verdecasa ne' di MC Protection**: quelli hanno pagine, tabelle e cartelle
  proprie (`adhoc_vc_*` + `documento_immobile`, `adhoc_mcp_*`) e non sono toccati.

**Cosa e' cambiato nel core**:
- eliminati `prog_doc.php` e `prog_file.php`;
- `prog_lista.php`: tolta la colonna "Doc." (intestazione e riga, simmetriche) e la query che
  contava i documenti;
- `prog_vis.php`: tolto il pulsante "Documenti" e la parola "documenti" dal messaggio di conferma
  dell'eliminazione;
- `prog_el.php`: tolto l'`unlink()` dei file (dipendeva da `PROG_DIR_DOC`); resta la `DELETE` delle
  righe di `progetto_documento`, per coerenza se un database ne avesse di vecchie.
- **Non toccati**: la tabella `progetto_documento` (schema), le costanti `MOD_PROGETTI_DOC`,
  `PROG_DIR_DOC`, `PROG_MAX_*`, `$PROG_DOC_FORM_OK` nei config (innocue, toglierle vorrebbe dire
  toccare il config di ogni clone), e la libreria `shadowbox/`, che serviva a `prog_doc.php` ma e'
  ancora usata da `adhoc_post_*` ed e' fra le librerie da non modificare (CLAUDE.md §7).
- `prog_doc.php` era l'**unica** pagina del core con la validazione async dei form (pattern 22): il
  codice di riferimento e' stato spostato in `ui-redesign-patterns.md` §22 prima dell'eliminazione.

**Nei cloni**: nessun clone sovrascrive `prog_doc.php` o `prog_file.php` in `custom/src/`, quindi
l'eliminazione arriva col prossimo `subtree pull`. I commenti di Verdecasa che citano
`core/prog_doc.php` come modello (`adhoc_vc_imm_doc/img/ins_1/vis`, `adhoc_vc_ric_ins_1/vis`) vanno
aggiornati verso `ui-redesign-patterns.md` §22 nella sessione di propagazione.

### 2026-05-06 — Modulo Pharmextracta/Omeopiacenza (`adhoc_phex_*.php`)

**File eliminati (13):**
`adhoc_phex_prod_agg.php`, `adhoc_phex_prod_doc.php`, `adhoc_phex_prod_el.php`, `adhoc_phex_prod_img.php`, `adhoc_phex_prod_ins_2.php`, `adhoc_phex_prod_ins_vis.php`, `adhoc_phex_prod_lista.php`, `adhoc_phex_pubb_agg.php`, `adhoc_phex_pubb_doc.php`, `adhoc_phex_pubb_el.php`, `adhoc_phex_pubb_ins_2.php`, `adhoc_phex_pubb_ins_vis.php`, `adhoc_phex_pubb_lista.php`

**Motivo**: modulo specifico per il cliente Pharmextracta/Omeopiacenza, dismesso. Gestiva schede prodotto bilingue (IT/EN) con CKEditor. Si connetteva al DB `MYSQL_DB_PHEX`. Non fa parte del core Globo.

**Commit**: `2393cf9`

---

### 2026-05-06 — Modulo Strada del Sagrantino (`adhoc_sds_*.php`)

**File eliminati (15):**
`adhoc_sds_ass_agg.php`, `adhoc_sds_ass_el.php`, `adhoc_sds_ass_img.php`, `adhoc_sds_ass_ins_vis.php`, `adhoc_sds_ass_lista.php`, `adhoc_sds_news_agg.php`, `adhoc_sds_news_el.php`, `adhoc_sds_news_img.php`, `adhoc_sds_news_ins_vis.php`, `adhoc_sds_news_lista.php`, `adhoc_sds_off_agg.php`, `adhoc_sds_off_el.php`, `adhoc_sds_off_img.php`, `adhoc_sds_off_ins_vis.php`, `adhoc_sds_off_lista.php`

**Motivo**: modulo specifico per il cliente Strada del Sagrantino, dismesso. Gestiva associati, news/eventi e offerte con CKEditor (IT/EN/DE) e Google Maps API v2. Si connetteva al DB `MYSQL_DB_SDS`. Non fa parte del core Globo.

**Commit**: `2393cf9`

---

### 2026-05-06 — Modulo Newsletter (`nl_*.php`)

**File eliminati (18):**
`nl_coda_agg.php`, `nl_coda_el.php`, `nl_coda_lista.php`, `nl_coda_vis.php`, `nl_imp_agg.php`, `nl_imp_vis.php`, `nl_ins_1.php`, `nl_ins_2.php`, `nl_inv_el.php`, `nl_inv_lista.php`, `nl_inv_vis.php`, `nl_lis_agg.php`, `nl_lis_file.php`, `nl_lis_lista.php`, `nl_lis_txt.php`, `nl_lis_vis.php`, `nl_lis_xls.php`, `nl_xls.php`

**Motivo**: modulo newsletter dismesso. Gestiva creazione, invio e storico newsletter con CKEditor, liste di distribuzione, template header/body/footer e export XLS/TXT dei contatti. Usava tabelle `nl_*`. Non fa parte del core Globo attivo.

**Commit**: vedi log git

---

### 2026-05-06 — CKEditor versioni 3.5.3 e 4.2.1 (`src/ckeditor/ckeditor_3.5.3/`, `src/ckeditor/ckeditor_4.2.1/`)

**Eliminati:** le due cartelle delle versioni obsolete e le funzioni helper corrispondenti in `src/varie/funzioni.php` (`globo_ckeditor_3_5_3`, `globo_ckeditor_4_2_1`).

**Motivo:** non referenziate da nessun file PHP attivo. Solo `ckeditor_4.5.2` e `globo_ckeditor_4_5_2()` restano, usate da `adhoc_post_ins_1.php` e `adhoc_post_vis.php`.

**CKEditor 4.5.2 non può essere rimosso** finché il modulo ADHOC_POST è out of scope (vedi voce dedicata sotto).

**Commit**: vedi log git

---

### 2026-05-06 — Modulo ADHOC_POST (`adhoc_post_*.php`): out of scope

Il modulo gestisce un CMS generico configurabile per categorie di contenuto (notizie, offerte, articoli, ecc.). È presente nel codebase ma **non attivato in questo progetto** (nessuna definizione di `MOD_ADHOC_POST` nel config KosmoSol → default `false`). Le variabili di configurazione `$POST_CATEGORIA`, `$POST_DESCRIZIONE`, ecc. non sono definite in questo progetto: evidentemente appartengono a un file config specifico di un altro clone Globo che usa il modulo.

**Azione**: non eliminare. Analizzare e trattare nel progetto clone che usa effettivamente il modulo. A quel punto valutare anche la rimozione completa di CKEditor 4.5.2 da questo progetto.

File presenti: `adhoc_post_lista.php`, `adhoc_post_ins_1.php`, `adhoc_post_ins_2.php`, `adhoc_post_vis.php`, `adhoc_post_agg.php`, `adhoc_post_el.php`, `adhoc_post_doc.php`, `adhoc_post_img.php`, `adhoc_post_av.php`

---

### 2026-05-06 — MDBootstrap (`src/mdbootstrap/`)

**Eliminato:** intera cartella `src/mdbootstrap/` (Bootstrap 4.3.1 + MDB PRO 4.9.0 + jQuery + Popper.js).

**Motivo:** usata solo in `authorization.php`, e solo per layout base e la classe `z-depth-1`. Sostituita con un blocco `<style>` inline di ~10 righe. Font Awesome resta (CDN esterno, non in questa cartella).

**Modifica a `src/authorization.php`:** rimossi 3 `<link>` CSS e 4 `<script>` JS verso `mdbootstrap/`; sostituito il markup Bootstrap grid con `<div class="container">` semplice; `z-depth-1` sostituita con `box-shadow` CSS nativo.

**Commit**: vedi log git

---

### 2026-05-07 — FPDF v1.7 (`src/fpdf/`) e generazione PDF fatture da BilancioV2

**File eliminati:**
- `src/fpdf/` — intera cartella della libreria FPDF v1.7
- `src/bilv2_pdf.php` — pagina standalone che generava PDF fatture (IT/EN) usando FPDF

**Codice rimosso:**
- `src/bilv2_lista.php` — blocco `if ($tipo_bilancio == "entrata" && BILV2_PDF && ...)` con i due pulsanti "genera pdf italiano/inglese" (ex righe 394–418)
- `src/config/globo-ks_kosmosol_it.php` — sei costanti: `BILV2_PDF`, `BILV2_PDF_LOGO`, `BILV2_PDF_DATI_FATT_OLD`, `BILV2_PDF_DATI_FATT`, `BILV2_PDF_PIE_PAG`, `BILV2_PDF_PIE_PAG_EN`

**Motivo:** FPDF era usata esclusivamente da `bilv2_pdf.php`. Il modulo BilancioV2 resta (è specifico di KosmoSol), ma la funzionalità di generazione PDF non è più usata dal 2018 e viene dismessa.

**Commit**: vedi log git

---

### 2026-05-07 — Modulo Immobiliare La Fontana (`adhoc_font_*.php`)

**File eliminati (15):**
`adhoc_font_file.php`, `adhoc_font_imm_agg.php`, `adhoc_font_imm_doc.php`, `adhoc_font_imm_el.php`, `adhoc_font_imm_img.php`, `adhoc_font_imm_ins_1.php`, `adhoc_font_imm_ins_2.php`, `adhoc_font_imm_lista.php`, `adhoc_font_imm_vis.php`, `adhoc_font_ric_agg.php`, `adhoc_font_ric_el.php`, `adhoc_font_ric_ins_1.php`, `adhoc_font_ric_ins_2.php`, `adhoc_font_ric_lista.php`, `adhoc_font_ric_vis.php`

**Motivo:** modulo specifico per il cliente Immobiliare La Fontana. Gestiva immobili e ricerche con galleria immagini, documenti e Shadowbox. `MOD_ADHOC_FONT` non attivo nel config KosmoSol.

**Commit:** `5bdfa71`

---

### 2026-05-07 — Modulo Gruppo Imprese Artigiane (`adhoc_gia_*.php`)

**File eliminati (5):**
`adhoc_gia_ass_agg.php`, `adhoc_gia_ass_doc.php`, `adhoc_gia_ass_img.php`, `adhoc_gia_ass_lista.php`, `adhoc_gia_ass_vis.php`

**Motivo:** modulo specifico per il cliente Gruppo Imprese Artigiane. Gestiva associati con documenti e galleria immagini. `MOD_ADHOC_GIA` non attivo nel config KosmoSol.

**Commit:** `a6be0de`

---

### 2026-05-07 — Modulo Il Mondo di Ciccio (`adhoc_imdc_*.php`)

**File eliminati (6):**
`adhoc_imdc_lib_agg.php`, `adhoc_imdc_lib_lista.php`, `adhoc_imdc_lib_vis.php`, `adhoc_imdc_ven_el.php`, `adhoc_imdc_ven_lista.php`, `adhoc_imdc_ven_vis.php`

**Motivo:** modulo specifico per il cliente Il Mondo di Ciccio. Gestiva catalogo libri e vendite. `MOD_ADHOC_IMDC` non attivo nel config KosmoSol.

**Commit:** `132c400`

---

### 2026-05-07 — Modulo Contratti LG (`adhoc_lg_*.php`)

**File eliminati (6):**
`adhoc_lg_contr_agg.php`, `adhoc_lg_contr_el.php`, `adhoc_lg_contr_ins_1.php`, `adhoc_lg_contr_ins_2.php`, `adhoc_lg_contr_lista.php`, `adhoc_lg_contr_vis.php`

**Motivo:** modulo specifico per cliente esterno (probabilmente LG Consulting). Gestiva contratti con campi POD/PDR (forniture energetiche). `MOD_ADHOC_LG` non attivo nel config KosmoSol. Referenze in `home.php` e `header.php` già protette da guardia `MOD_ADHOC_LG`.

**Commit:** `382f3f2`

---

### 2026-05-07 — Modulo MC Protection (`adhoc_mcp_*.php`)

**File eliminati (36):**
`adhoc_mcp_acq_*.php` (7), `adhoc_mcp_catserv_*.php` (6), `adhoc_mcp_cli_*.php` (6), `adhoc_mcp_file.php`, `adhoc_mcp_lis_*.php` (6), `adhoc_mcp_rica_*.php` (4), `adhoc_mcp_serv_*.php` (6)

**Motivo:** modulo specifico per il cliente MC Protection. Gestiva clienti, servizi, categorie servizi, listini, acquisizioni e ricambi. `MOD_ADHOC_MCP` non attivo nel config KosmoSol.

**Commit:** `ee2e5b9`

---

### 2026-05-07 — Modulo ML Benessere (`adhoc_mlb_*.php`)

**File eliminati (35):**
`adhoc_mlb_add_*.php` (4), `adhoc_mlb_age_*.php` (4), `adhoc_mlb_carmag_*.php` (4), `adhoc_mlb_cli_*.php` (4), `adhoc_mlb_forn_*.php` (4), `adhoc_mlb_ord_*.php` (5), `adhoc_mlb_pre_*.php` (4), `adhoc_mlb_prod_*.php` (4), `adhoc_mlb_prov_*.php` (2)

**Motivo:** modulo specifico per il cliente ML Benessere. Gestiva clienti, fornitori, agenti, prodotti, ordini, preventivi, carichi magazzino e provvigioni. `MOD_ADHOC_MLB` non attivo nel config KosmoSol.

**Commit:** `3443c45`

---

### 2026-05-07 — Modulo Verde Casa (`adhoc_vc_*.php`)

**File eliminati (15):**
`adhoc_vc_file.php`, `adhoc_vc_imm_agg.php`, `adhoc_vc_imm_doc.php`, `adhoc_vc_imm_el.php`, `adhoc_vc_imm_img.php`, `adhoc_vc_imm_ins_1.php`, `adhoc_vc_imm_ins_2.php`, `adhoc_vc_imm_lista.php`, `adhoc_vc_imm_vis.php`, `adhoc_vc_ric_agg.php`, `adhoc_vc_ric_el.php`, `adhoc_vc_ric_ins_1.php`, `adhoc_vc_ric_ins_2.php`, `adhoc_vc_ric_lista.php`, `adhoc_vc_ric_vis.php`

**Motivo:** modulo specifico per il cliente Verde Casa (agenzia immobiliare). Struttura analoga al modulo Immobiliare La Fontana. `MOD_ADHOC_VC` non attivo nel config KosmoSol.

**Commit:** `90d4ce0`

---

### 2026-05-07 — `src/varie/funzioni_sito.php`

**File eliminato:** `src/varie/funzioni_sito.php`

**Motivo:** libreria per la newsletter pubblica (iscrizione, conferma doppio opt-in, cancellazione, visualizzazione). Dead code completo per KosmoSol:
- Nessun file in `src/` la include
- Tutte le costanti `NL_*` che usa sono commentate nel config KosmoSol
- I path di `require` nel blocco iniziale puntano a vecchie installazioni Windows IIS non più esistenti
- Il modulo newsletter (`nl_*.php`) era già stato eliminato

**Nota per i cloni:** i cloni che usano attivamente la newsletter pubblica devono mantenere questo file e aggiornarlo — richiede migrazione a PHPMailer 6.x (namespace `PHPMailer\PHPMailer\PHPMailer`), path relativi nel blocco `require` iniziale, e prepared statement PDO sulle query.

**Commit:** vedi log git

---

### 2026-05-07 — Script cron newsletter e MailChimp (`src/script/`)

**File eliminati (7):**
- `src/script/invia_newsletter.php` — invio newsletter via PEAR Mail (modulo newsletter dismesso; PEAR non presente nel progetto)
- `src/script/nl_elimina_email.php` — webhook disiscrizione newsletter (modulo newsletter dismesso)
- `src/script/function_mailchimp.php` — libreria wrapper MailChimp API v3 (progetto Globo 5 Stelle, non KosmoSol)
- `src/script/globo_sync_mailchimp.php` — sincronizzazione contatti KosmoSol → MailChimp (progetto Globo 5 Stelle)
- `src/script/globo_delete_members_mailchimp.php` — eliminazione membri MailChimp (progetto Globo 5 Stelle)
- `src/script/globo_sync_mailchimp.bat` — batch Windows che lanciava `globo_sync_mailchimp.php`
- `src/script/newsletter_kosmosol.bat` — batch Windows che lanciava `invia_newsletter.php`

**Motivo:** script newsletter e MailChimp eliminati perché: (1) il modulo newsletter (`nl_*.php`) è già stato dismesso; (2) gli script MailChimp appartengono al progetto Globo 5 Stelle, non a KosmoSol; (3) i due `.bat` chiamavano solo script eliminati e usavano path Windows assoluti con PHP 5.5 non più rilevanti.

**Nota per i cloni:** i cloni che usano attivamente MailChimp (es. Globo 5 Stelle) devono mantenere `function_mailchimp.php`, `globo_sync_mailchimp.php`, `globo_delete_members_mailchimp.php`. Eliminare solo dopo aver verificato che nessun modulo attivo li includa.

**Commit:** vedi log git

---

### 2026-09-07 — Invio "promemoria" e "scadenze X" (`ins_invio_promemoria*.php`, `ins_invio_scadx*.php`)

**File eliminati (4):**
- `src/ins_invio_scadx.php` — pagina con form di upload di un CSV in `upload/scadx/`
- `src/ins_invio_scadx_2.php` — invio effettivo via API SendGrid (cURL): sollecito di mancato saldo rata, testo/IBAN/firma KosmoSol cablati nel file
- `src/ins_invio_promemoria.php` — stessa pagina, upload in `upload/promemoria/`
- `src/ins_invio_promemoria_2.php` — stesso invio

**Motivo:** vecchia funzione del modulo bilancio (solleciti di pagamento ai clienti), dismessa — confermato da Alessandro il 2026-09-07. Chiude la famiglia iniziata con `ins_invio_solleciti*` (voce del 2026-05-15).

**Contraddice la voce del 2026-05-15**, che dava questi due "ancora in uso". Elementi raccolti il 2026-09-07, che risolvono a favore dell'eliminazione: `MOD_NEWSLETTER=false` nel config di produzione KosmoSol **fin dalla baseline del 24/04** (non l'abbiamo messo noi), e la guardia d'accesso aggiunta il 18/06 (`4763337`) lega le due pagine a quel flag → **dal 18 giugno erano irraggiungibili in produzione**, e in 81 giorni nessuno ha segnalato nulla. La verifica che chiuderebbe la questione senza margini è sul server: le date dei CSV in `upload/scadx/` e `upload/promemoria/` dicono quando la funzione è stata usata l'ultima volta. Se saltasse fuori che servivano, il ripristino è un `git revert` di questo commit.

**Perché sono stati cercati (nota di sicurezza):** i due `_2` erano gli unici 2 handler su 18 del core a non includere `common_1.php` — nessuna sessione, nessuna guardia di modulo/livello, unica difesa il token giornaliero `md5(Ymd."globok0s")` calcolabile da chi legge il sorgente. `$_POST["nomeFileUpload"]` finiva nel path del file da leggere senza `basename()` né controlli (path traversal: leggere un file arbitrario e spedirne il contenuto a un destinatario scelto da chi chiama), e la API key SendGrid era in chiaro nel sorgente — **diversa da quella in uso oggi** in `config_smtp.php`, quindi verosimilmente vecchia. Cancellare i file **non la revoca**: va revocata dal pannello SendGrid.

**Da fare in produzione:** i file vanno cancellati **a mano** dalle webroot, perché estrarre uno zip non cancella nulla. Presenti su: `globo-ks` (il build del core non li escludeva), `globo-h5s` (5stelle, sono nel pacchetto 1.1.7), `mcprotection` (arrivati col deploy 1.0.0 — nella webroot originale del cliente non c'erano).

**Commit:** vedi log git

---

### 2026-05-15 — Script invio solleciti (`ins_invio_solleciti.php`, `ins_invio_solleciti_2.php`)

**File eliminati (2):**
- `src/ins_invio_solleciti.php` — form upload CSV per invio solleciti di pagamento ai clienti
- `src/ins_invio_solleciti_2.php` — script di invio effettivo via SendGrid API (cURL)

**Motivo:** funzionalità dismessa. L'utente ha confermato che i solleciti non sono più usati. Il modulo `ins_invio_promemoria` e `ins_invio_scadx` restano attivi (stesso pattern CSV+SendGrid, ma ancora in uso).

**Nota per i cloni:** stesso pattern di `ins_invio_promemoria_2.php` e `ins_invio_scadx_2.php` — SendGrid API key hardcoded (`SG.…`), path upload assoluto di produzione, token giornaliero `?k=md5(...)` come autenticazione. Se un clone usa i solleciti, mantenere questi file e risolvere prima del deploy: (1) spostare la API key in config, (2) rendere parametrico il path upload, (3) valutare se la validazione del token è sufficiente o serve autenticazione session-based.

**Commit:** vedi log git

---

### 2026-05-07 — Script cron scadenze BilancioV2 (`src/script/invia_scadenze_bilv2.php`)

**File eliminati (2):**
- `src/script/invia_scadenze_bilv2.php` — script cron che inviava notifiche scadenze di pagamento e canoni da BilancioV2, con invio solleciti via CritSend SMTP. Richiedeva PHPMailer 5.x (`class.phpmailer.php`) già incompatibile con la versione 6.x presente. Dismesso.
- `src/script/globo_adhoc_ks_kosmosol.bat` — batch Windows (PHP 5.5) che orchestrava `invia_notifica.php` + `invia_scadenze_bilv2.php`. Senza quest'ultimo il bat non aveva più senso.

**Motivo:** script non più necessario. Il modulo BilancioV2 resta attivo per la gestione dati, ma la funzionalità cron di notifica scadenze è dismessa.

**Commit:** vedi log git

---

### 2026-05-07 — Funzioni helper `globo_adhoc_*` in `src/varie/funzioni.php`

**Funzioni eliminate (5):**
`globo_adhoc_converti_per_id_str()`, `globo_adhoc_dde_genera_id_str()`, `globo_adhoc_th_genera_id_str()`, `globo_adhoc_sds_genera_id_str()`, `globo_adhoc_phex_genera_id_str()`

**Motivo:** funzioni helper specifiche dei moduli cliente (dde, th, sds, phex). Contenevano le ultime chiamate `mysql_*` rimaste nel codice attivo. Nessun file nel codebase KosmoSol le chiamava.

**Nota per i cloni:** nei cloni che usano ancora i moduli `adhoc_dde_*`, `adhoc_th_*`, `adhoc_sds_*`, `adhoc_phex_*`, queste funzioni devono essere mantenute in `funzioni.php`. Eliminare le funzioni solo contestualmente all'eliminazione del relativo modulo.

**Commit:** vedi log git

---

## HARDCODED

<!-- Valori hardcoded da rendere parametrici: nomi tabella, percorsi assoluti, configurazioni inline -->

### 2026-09-18 — Anche la NEWSLETTER si porta via qualcosa: la colonna e-mail dei contatti

`cont_lista.php` mostrava la colonna E-mail solo con `MOD_NEWSLETTER` acceso (gia' cosi' nel
Globo PHP 5). Su Studio CIA la newsletter e' stata spenta perche' ferma dal 2012, e la colonna e'
sparita: se n'e' accorto Alessandro nel collaudo manuale confrontando col live. Slegata con la
costante **`CONT_LISTA_EMAIL`**: se non definita vale il comportamento di prima, quindi negli
altri clienti non cambia nulla; lo Studio la accende nel config.

**Regola generale, dopo calendario e newsletter**: prima di spegnere un modulo in un clone,
cercare dove il suo `MOD_*` compare nelle pagine dei moduli che restano accesi
(`grep -n MOD_X` su `pr_* prog_* cont_* ut_* home index varie/`) — non solo nei file del modulo
stesso. Allo Studio, oltre a scadenze e colonna e-mail, spegnere il calendario nasconde anche la
scadenza nel form di creazione di una nota (`pr_nota_ins_1.php`, `$has_calendario`).

### 2026-09-17 — `invia_scadenze.php` vive dietro l'interruttore del CALENDARIO

Tutto il corpo di `src/script/invia_scadenze.php` e' racchiuso in:

```php
if (MOD_CALENDARIO && date("w") != 0 && date("w") != 6) {
```

E' un accoppiamento storico — le scadenze nascevano dal calendario — ma lo script fa
molto piu' di quello: i suoi blocchi sui **progetti** e sugli **step di procedura** non
hanno niente a che vedere col calendario, e in alcuni clienti sono l'unica parte viva.

**Conseguenza da conoscere prima di spegnere un modulo**: chi mette `MOD_CALENDARIO` a
`false` spegne anche le scadenze dei progetti, **in silenzio** — niente errore, niente
log, lo script semplicemente non entra mai nel blocco. Non c'e' modo di accorgersene
guardando le pagine, perche' quello script non passa mai da `index.php`.

Trovato su **Studio CIA** il 2026-09-17: il calendario era stato appena spento (fermo dal
2012, 4 impegni in tutto) e lo stesso giorno si era deciso di tenere le mail delle
scadenze; le due decisioni si sarebbero annullate a vicenda senza che nulla lo segnalasse.
Li' l'override usa `MOD_PROGETTI` come guardia (vedi
`CLAUDE.md` di Globo-StudioCia).

Da rendere parametrico nel core: la guardia dovrebbe dipendere da quali blocchi sono
attivi, non da un modulo solo. **Da verificare anche negli altri cloni** che abbiano il
calendario spento e si aspettino ancora le scadenze dei progetti.

### 2026-09-07 — `src/script/*.php`: installazione di origine cablata nel codice

Tutti gli script da cron hanno il dominio e i path assoluti dell'installazione KosmoSol
in testa al file — corretti qui (questo repo **è** Globo KosmoSol), sbagliati in ogni
clone che li erediti dal core senza override:

- `src/script/invia_scadenze.php:2,4-6` — `$dominio` + 3 `require` su `/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/`
- `src/script/invia_impegni.php:2,4-6` — idem
- `src/script/purga_log_attivita.php:7,9-11` — idem

Sono lanciati da CLI: non passano da `index.php`, quindi `config/config.php` (che sceglie
la config in base a `HTTP_HOST`) non è utilizzabile e il path assoluto è, oggi, l'unica
via. **Parametrizzarli è il fix di fondo, non ancora fatto**: servirebbe risolvere i path
da `__DIR__` e scegliere la config senza hostname (es. argomento del cron o file unico per
installazione). Finché non si fa, ogni clone deve overridare questi file in
`custom/src/script/` — vedi `fase2-core-custom.md` §7 e la lezione in `CLAUDE.md` §9.

Costato caro una volta: pacchetto MCP 1.0.0 deployato con i path di globo-ks, mail delle
scadenze ferme per due settimane senza alcun errore visibile (2026-09-07).

### 2026-06-10 — `src/varie/funzioni.php` `sendTokenMail()`: branding email — RISOLTO (parametrizzato)

L'email "riconosci dispositivo" aveva colore header, logo e label di prodotto hardcoded su KosmoSol (`#005da3`, `kosmosol_dashwhite.png`, "Globo KosmoSol Web"). Emerso durante lo sfoltimento del clone 5stelle (che li forkava). **Parametrizzato** (commit `75b0710`) con tre costanti di config, usate da `sendTokenMail` con `defined()`/fallback ai valori KosmoSol:

- `BRAND_NAME` — label `Globo <BRAND_NAME>` (tolto il suffisso "Web") + alt del logo
- `BRAND_COLOR` — colore header email
- `EMAIL_LOGO` — file del logo bianco, servito da `URL_GLOBO/immagini/<EMAIL_LOGO>`

**Implicazione per i cloni:** ogni clone deve definire queste tre costanti nel proprio config (se non lo fa, fallback ai valori KosmoSol — nessun fatal). Definite per KosmoSol in `globo-ks_kosmosol_it.php`; 5stelle in `globo-h5s_kosmosol_it.php`.

### 2026-05-04 — `src/config/globo-ks_kosmosol_it.php`: `DIR_LOG` con path assoluto di produzione

La costante `DIR_LOG` contiene il path assoluto del server di produzione (`/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/globo_log/`). In ambiente Docker dev è stata temporaneamente modificata a `/var/www/html/globo_log/`. Prima del deploy in produzione va ripristinata al valore originale, oppure resa parametrica (es. relativa alla webroot). Stessa situazione per `DIR_FT_PDF_SOLLECITI` e `DIR_FT_PDF_PROMEMORIA` sulla riga successiva (non ancora toccate).

### 2026-04-28 — `src/varie/cron-schedule.php`: nome DB `"kosmosol"` come stringa letterale

A differenza degli altri file della famiglia di customizzazioni KosmoSol (che usano la costante `MYSQL_DB_KS` definita in config), `cron-schedule.php` referenzia il database `kosmosol` come stringa letterale hardcoded. Quando il file verrà migrato in `globo-cliente-kosmosol` durante la Fase 2, andrà parametrizzato per coerenza con il resto del codice.

> **Superato (2026-06-17):** `cron-schedule.php` è stato **eliminato** con la dismissione di MEXAL. Voce non più applicabile.

### 2026-05-04 — `src/script/invia_impegni.php` e `src/script/invia_scadenze.php`: path assoluti di produzione hardcoded

Le righe di require in entrambi gli script usano path assoluti del server di produzione (`/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/...`). Questo li rende non eseguibili nell'ambiente Docker dev senza modifiche manuali. Da rendere parametrici (es. relativi a `__DIR__`) prima del deploy o della containerizzazione degli script cron.

- `src/script/invia_impegni.php`:1-6 — require con path assoluti di produzione
- `src/script/invia_scadenze.php`:1-8 (circa) — stessa struttura

---

## DECISIONI DI ANONIMIZZAZIONE

Note tecniche sul processo di anonimizzazione del dump DB di produzione (Step 3D), per riferimento futuro nelle migrazioni di altri progetti derivati di Globo.

### Bug del pii-map iniziale: colonne testo "non sensibili" con dati misti

Durante la verifica post-anonimizzazione, sono emerse email reali in colonne classificate come `NON_SENSIBILE` nel pii-map originale. Causa: dati immessi dagli operatori in colonne sbagliate rispetto alla loro etichetta semantica.

**Colonne coinvolte:**
- `contatto.sito` — etichetta "sito web", in realtà conteneva email cliente sporadiche
- `login.nome_utente` — alcuni username erano in formato email aziendale completa (`<nome>@kosmosol.it`)

**Risoluzione applicata:**
- `contatto.sito` con email → sostituite con `[anonimizzato]` via UPDATE manuale
- `login.nome_utente` con email → sostituiti con pattern `utente_anon_<hash>` via UPDATE manuale

**Lezione per i prossimi progetti:**
Quando si genera il pii-map, non fidarsi delle sole etichette delle colonne. Per ogni colonna `varchar` o `text` classificata come `NON_SENSIBILE`, fare almeno un grep di campione sui dati reali per cercare pattern di email, telefoni, CF prima di concludere l'analisi.

### Procedura di verifica finale raccomandata

Dopo l'esecuzione dello script di anonimizzazione, prima di esportare il dump definitivo:

1. Query SQL "agnostica" sul container per cercare pattern di PII in tutte le colonne testo, non solo in quelle classificate come tali.
2. Grep sul dump esportato con regex di pattern PII comuni (email, CF, IBAN).
3. Spot check qualitativo su 2-3 record di tabelle chiave.

## Adattamenti per ambiente Docker dev

### 2026-04-27 — `src/config/config.php`: aggiunto check per `localhost:8080`

**File**: `src/config/config.php` (riga ~2-7, blocco di selezione file di config)

**Modifica**: aggiunta una condizione `$_SERVER["HTTP_HOST"] == "localhost:8080"` al blocco `if` che determina quale file di config caricare.

**Motivo**: in ambiente Docker dev accediamo a Globo via `http://localhost:8080`. Il check originale `== "localhost"` non considera la porta, e `$_SERVER["HTTP_HOST"]` con porta non-default vale `"localhost:8080"` (non `"localhost"`). Senza questa aggiunta, Globo cerca di caricare un file `localhost:8080.php` inesistente.

**Tipo**: additiva. La condizione originale `== "localhost"` è preservata. In produzione (accesso via dominio) la nuova condizione semplicemente non si applica.

**Categoria**: ambiente, non logica applicativa.

**Riportare in master per progetti derivati?**: da valutare. Se altri progetti della famiglia usano la stessa logica di selezione config, potrebbe valer la pena rendere il check robusto alla porta (Opzione 3 scartata in questa fase per non rifattorizzare). Per ora annotato come problema noto.

### 2026-05-04 — `src/config/globo-ks_kosmosol_it.php`: `URL_GLOBO` adattato per Docker dev

**File**: `src/config/globo-ks_kosmosol_it.php` (riga con `define("URL_GLOBO", ...)`)

**Modifica**: costante `URL_GLOBO` portata da `https://globo-ks.kosmosol.it/globo` a `http://localhost:8080` (senza `/globo/`: in Docker, `src/` è montato direttamente su `/var/www/html/`, la web root, senza sottocartella).

**Motivo**: la costante è usata in `sendTokenMail()` (funzioni.php) per costruire il link di autorizzazione inviato via email. In dev il link deve puntare a `localhost:8080`, non alla produzione.

**Tipo**: modifica al file di config, **NON da portare in produzione**. Prima del deploy ripristinare a `https://globo-ks.kosmosol.it/globo`.

### 2026-05-04 — `src/authorization.php`: dominio cookie e flag secure adattati per Docker dev

**File**: `src/authorization.php` (riga con `setcookie(...)`)

**Modifica**: `domain` portato da `"globo-ks.kosmosol.it"` a `""` (dominio corrente automatico); `secure` portato da `1` a `0`.

**Motivo**: con `domain = "globo-ks.kosmosol.it"`, il browser non imposta il cookie su `localhost`. Con `secure = 1`, il cookie richiede HTTPS, assente in dev.

**Tipo**: modifica al file applicativo, **NON da portare in produzione**. Prima del deploy ripristinare `domain = "globo-ks.kosmosol.it"` e `secure = 1`.

### 2026-05-22 — `src/config/globo-ks_kosmosol_it.php`: `EMAIL_KOSMOSOL` cambiato per dev

**File**: `src/config/globo-ks_kosmosol_it.php` (riga con `define("EMAIL_KOSMOSOL", ...)`)

**Modifica**: costante `EMAIL_KOSMOSOL` portata da `globo@kosmosol.it` a `alessandro@zooga.vc`.

**Motivo**: in dev le email di sistema (errori di accesso, notifiche login) vengono consegnate a un indirizzo verificabile, non alla casella di produzione KosmoSol.

**Tipo**: modifica al file di config, **NON da portare in produzione**. Prima del deploy ripristinare a `globo@kosmosol.it`.

---

### 2026-05-04 — `src/config/globo-ks_kosmosol_it.php`: `AUTH_TOKEN` riabilitato in dev

**File**: `src/config/globo-ks_kosmosol_it.php`

**Modifica**: costante `AUTH_TOKEN` riportata a `true`. Era stata disabilitata in precedenza perché l'email non era funzionante in dev. Ora che l'infrastruttura email (PHPMailer/SMTP) è operativa e `sendTokenMail()` usa `globo_crea_phpmailer()`, il meccanismo funziona anche in dev.

**Dipendenze adattate contestualmente**: `URL_GLOBO` puntato a `localhost:8080`; cookie domain/secure in `authorization.php` adattati per localhost (vedi voci dedicate sopra).

### 2026-04-28 — Disabilitazione moduli KosmoSol per ambiente dev

**Contesto**: dopo il login di test su Globo è emerso "errore di connessione al database" per tentativi di connessione al DB `kosmosol` (non presente nell'ambiente Docker dev). L'analisi ha rivelato che si tratta di customizzazioni specifiche del cliente KosmoSol, non parte del core. Decisione strategica: non replicare il DB in dev, ma disabilitare i moduli temporaneamente. Estrazione dei moduli verso il progetto cliente sarà fatta in Fase 2 (vedi sezione `CUSTOM-CANDIDATE`).

**Modifiche applicate:**

1. **`src/config/globo-ks_kosmosol_it.php` riga 33**: costante `MOD_ADHOC_KS` portata da `true` a `false`. Aggiunto commento `// DEV:` sopra la riga.

2. **`src/config/globo-ks_kosmosol_it.php` riga 34**: costante `MOD_ADHOC_KSH5S` portata da `true` a `false`. Aggiunto commento `// DEV:` sopra la riga.

   **Nota interpretativa sulle righe 33-34**: ognuna di queste righe contiene **tre `define` consecutive** (la costante `MOD_*`, la costante `LIV_MOD_*`, e la costante `MYSQL_DB_*`). Il commento `// DEV:` posto sopra la riga si riferisce **solo** alla prima `define` (quella effettivamente modificata); le altre due (`LIV_MOD_*` e `MYSQL_DB_*`) restano con i loro valori originali e non sono "disabilitate". Questo non è un bug: con `MOD_*=false` il codice di Globo non utilizza più le altre due costanti, quindi i loro valori sono inerti. È solo una struttura di codice che andrà ripulita in Fase 2 (idealmente: una `define` per riga, oppure estrazione completa nel progetto cliente).

3. **`src/home.php` riga 2**: commentato il `require("varie/cron-schedule.php")` (non rimosso, solo commentato). Aggiunto commento `// DEV:` esplicativo. Il file `cron-schedule.php` continua a esistere nel codebase e può essere riattivato in qualunque momento rimuovendo il `//`. — **Superato (2026-06-17):** include e file `cron-schedule.php` **rimossi** (dismissione MEXAL).

**Tipo**: modifiche al codice di Globo (config + un file applicativo), **NON da portare in produzione**. Sono modifiche reversibili in pochi secondi (basta rimettere `true` e togliere il commento dal `require`).

**Categoria**: ambiente dev — disabilitazione customizzazioni cliente.

**Riportare in master per progetti derivati?**: no. La disabilitazione è specifica per Globo8 (il progetto core "pulito"); altri progetti derivati che riutilizzano queste customizzazioni manterranno i moduli attivi.

---

### TODO Fase 2 — Riscrittura del meccanismo di autenticazione

Indipendentemente dalla migrazione PHP 8 in corso, il meccanismo di autenticazione di Globo presenta criticità di sicurezza che andranno affrontate in una fase dedicata, **post-migrazione**:

1. ~~**Hashing password con MD5 puro**~~ ✓ **FATTO (2026-05-04)**: migrazione a `password_hash(PASSWORD_BCRYPT)` con transizione on-the-fly completata in `src/index.php`, `src/ut_ins_2.php`, `src/ut_agg.php`. Al primo login con hash MD5 legacy, la password viene automaticamente rimigrata a bcrypt.
2. **Hash password hardcoded nel sorgente** per l'utente `ks-globo` (`src/index.php:68`). Dal 2026-11-11 nel repository c'e' il segnaposto `__PROD_KS_ADMIN_MD5__` e l'hash vero lo rimette il build (CLAUDE.md §5), ma nel pacchetto e sul server resta un MD5 senza sale: credenziale embedded nel codice, da rimuovere in favore di un utente normale in tabella `utente` (con livello dedicato se serve preservare il super-admin) o di un meccanismo di provisioning esterno.
3. **Confronti SQL con concatenazione di stringhe** in tutti i punti dell'autenticazione: vulnerabili a SQL injection se le sanitizzazioni applicate altrove falliscono. Da convertire in prepared statement (PDO o `mysqli` con bind parameters).
4. **Meccanismo "trusted device" via cookie**: l'attuale implementazione (token SHA512 in chiaro nel DB, cookie 180 giorni) è un'idea ragionevole ma andrebbe rivista con criteri moderni: rotazione del token, binding al fingerprint del browser, log degli accessi, possibilità di revoca da parte dell'utente. Valutare anche se sostituire/affiancare con un vero 2FA (TOTP via app authenticator).

Questi interventi non sono obbligatori per la migrazione 5.4 → 8.x in sé, ma il salto a PHP 8 è il momento naturale per pianificarli, perché la base di codice viene comunque toccata in profondità.

---

## SECURITY-DEBT

### SQL Injection — migrazione a prepared statements (PDO)

**Contesto**: quasi tutte le query del codebase (~2.300 file) costruiscono SQL per interpolazione diretta di variabili, es. `"SELECT * FROM t WHERE id = '$id'"`. Questo è vulnerabile a SQL injection. Globo è esposto su internet aperto (URL non pubblico ma non inaccessibile).

**Priorità**: media. La maggior parte delle query è dietro autenticazione; il login (`index.php`) sanitizza già `$nome_utente` manualmente (strip caratteri speciali, riga 56). Il rischio residuo principale è un utente autenticato che sfrutta injection su query interne.

**Strategia approvata**: migrazione incrementale per priorità di esposizione, non in blocco.

1. ~~**Alta priorità — query senza autenticazione**~~ ✓ **FATTO (2026-05-04)**: `src/index.php` (11 query) e tutti i file `src/ut_*.php` (7 query su 5 file) convertiti a prepared statement PDO.
2. ~~**Media priorità — query dei moduli core**~~ ✓ **FATTO (2026-05-04)**: tutti i moduli core analizzati e chiusi. **Migrati**: **contatti** (`cont_*.php`), **progetti** (`prog_*.php`, `pr_nota_*.php`, `pr_proced_*.php`, `pr_proc_step_*.php`, `pr_template_*.php`, `pr_tem_step_*.php`), **calendario** (`cal_*.php`), query interne di `globo_invia_messaggio()` in `src/varie/funzioni.php`. **Zero query**: `home.php`, `authorization.php`. **Out of scope**: `nl_*.php` (newsletter non usata). **Esclusi/rimandati**: `bil_*.php`, `bilv2_*.php`, `bilv2ag_*.php` (CUSTOM-CANDIDATE, Fase 2); `globomail_*.php` (modulo disabilitato — vedi CUSTOM-CANDIDATE). **`ins_invio_solleciti_2.php`** eliminato (2026-05-15). **`ins_invio_promemoria_2.php`** e **`ins_invio_scadx_2.php`** non usano DB (CSV + SendGrid API) — zero query, fuori scope.

   **Audit 2026-05-15 — scope confermato**: nessuna chiamata all'estensione `mysql_*` nel codice attivo (tutti i match di `mysql_` sono nomi di funzioni helper come `globo_converti_mysql_datetime()`). Il problema è esclusivamente nell'interpolazione di variabili POST in `->query()`. Scope quantificato: **168 occorrenze** in **56 file**, tutti i moduli CUSTOM-CANDIDATE non ancora migrati. Esempio canonico: `src/bilv2_lista.php:72` — `$tipo_bilancio` da `$_POST` interpolato direttamente in `SELECT * FROM bilanciov2_categoria WHERE tipo = '$tipo_bilancio'`. **Le copie nel core restano non migrate** (incluso `adhoc_ks_h5s_*`, 5 file); il clone 5stelle ha migrato **la propria copia custom** di questo modulo il 2026-07-07 (vedi `Globo-5stelle/docs/release-v1.md` § Post-v1) — non essendo un fix nel core, non si propaga agli altri cloni.
3. ~~**Bassa priorità — script batch e utility**~~ ✓ **CHIUSO (2026-05-04)**: `invia_impegni.php` e `invia_scadenze.php` non usano input utente nelle query — le variabili interpolate derivano da `date()` o da risultati di query precedenti sullo stesso DB. Rischio reale è solo second-order injection (richiede DB già compromesso). Script non raggiungibili via browser in produzione. Nessuna migrazione necessaria. L'adattamento dei path hardcoded rimane un tema separato (vedi sezione HARDCODED).

---

### Upload immagini — mancanza validazione MIME

**Contesto**: `imageuploader/upload.php` usa `is_uploaded_file()` + `move_uploaded_file()` senza validare l'estensione né il tipo MIME reale del file.

**Audit 2026-05-15**: la protezione effettiva è più solida del previsto. Il modulo attivo (`adhoc_post_img.php`, gli altri tre erano già eliminati) applica `getimagesize()` sul file temporaneo (riga 70) e poi `create_source_image()` (riga 85, in `image.php`) che chiama `imagecreatefromjpeg/gif/png()` — queste funzioni rifiutano file non-immagine. Infine `imagejpeg()` riscrive sempre il file come JPEG nativo, neutralizzando qualsiasi attacco polyglot (PHP shell mascherato da immagine). Un file malevolo viene rifiutato e il temp file eliminato prima di essere raggiungibile via web.

**Bug residuo**: se `create_source_image()` fallisce, il codice esegue comunque l'INSERT DB con variabili `$file_big`/`$file_small` undefined → riga con valori null. Da correggere con un `else` o aggiungendo `return` nel blocco di errore.

**Stato modulo**: `MOD_ADHOC_POST` non è definito in config KosmoSol → false. Il modulo è disabilitato e il codice non raggiungibile in produzione. Rischio attuale: zero.

**Azione quando si riattiva il modulo**: correggere il bug INSERT nel branch `create_source_image() == false` in `adhoc_post_img.php`. Non toccare `imageuploader/upload.php` (libreria di terze parti, vedi CLAUDE.md sezione 7).

---

**Pattern PDO da adottare** (sostituzione uniforme in tutto il codebase):
```php
// Prima (interpolazione — da eliminare)
$res = $db->query("SELECT * FROM t WHERE id = '$id'")->fetchAll();

// Dopo (prepared statement)
$stmt = $db->prepare("SELECT * FROM t WHERE id = ?");
$stmt->execute([$id]);
$res = $stmt->fetchAll();
```

**Note operative**:
- Le query con `exec()` (INSERT/UPDATE/DELETE senza fetch) seguono lo stesso pattern.
- Le query con clausole dinamiche (es. `WHERE` opzionale, `ORDER BY` variabile) richiedono attenzione: i segnaposto `?` funzionano solo per i valori, non per nomi di colonna o tabella. Quelli vanno whitelist-ati lato PHP.
- Punto di partenza consigliato: `src/index.php` — già toccato per la migrazione bcrypt, le query di login sono le più critiche.

---

### Encoding HTML nel DB — controllo centralizzato in OUTPUT (soluzione core, da replicare sui cloni)

> **Decisione (2026-06-08).** Soluzione adottata: **controllo centralizzato in output** nel core.
> NON si modifica lo storage e NON si migrano i dati. Vedi "Perché output-only" più sotto.
> Stato: deciso, da implementare in locale. Non urgente per il live (il bug è solo estetico, niente perdita dati). Vale per Globo **e per tutti i cloni** (stesso identico formato dati).

**Problema.** Globo salva i campi di testo libero **già codificati in entità HTML** nel DB (via `globo_converti_in_html()` in `src/varie/funzioni.php`). Quella funzione converte `< > " '`, gli accenti (`&agrave;` ecc.) e gli a-capo (`\n` → `<br />`). Contratto legacy: **codifica al salvataggio → stampa grezza in visualizzazione** (il browser rende `<br />` e `&#39;`), con `globo_converti_da_html()` per ripopolare i campi in modifica.

Il **redesign UI** in visualizzazione usa `htmlspecialchars()` (per anti-XSS) **su un valore che è già HTML** → doppia codifica → a schermo si vedono i codici (`d&#39;amico`, `l&#39;altra &quot;`). Notato in produzione su nomi/descrizioni/note.

**Perché output-only (e non riscrivere lo storage).**
- Il dato nei DB è già **uniformemente codificato** allo stesso modo in Globo e in **ogni clone**. Un fix in output si scrive **una volta in `globo-core`** e tutti i cloni lo ereditano: **zero migrazioni dati**, zero modifiche ai percorsi di scrittura, ripetute × N DB di produzione.
- Riscrivere lo storage (salvare grezzo + bonificare i dati) andrebbe fatto **per ogni clone**, ognuno con il suo rischio. Sproporzionato.
- L'unico motivo che giustificherebbe lo storage grezzo sarebbe il bisogno di dato "neutro" per **integrazioni esterne / BI**. **Escluso esplicitamente (2026-06-08): non sono previste integrazioni.**
- Sicurezza: l'output grezzo in lettura è sicuro grazie all'**invariante** "ogni scrittura codifica `< >`" (oggi vero: tutte le scritture passano da `globo_converti_in_html`). **Va mantenuta la codifica in scrittura** — è la cintura, l'helper di output è le bretelle.

**Modello mentale.** Nel DB il campo è **HTML-di-presentazione** (già sicuro). Il layer di output lo adatta al contesto. La regola, per i campi che passano da `globo_converti_in_html` (vedi mappa):

| Contesto di output | Cosa fare |
|---|---|
| **HTML in sola lettura** (liste, dettagli in `<div>`, riquadri) | output **grezzo** — è già HTML sicuro. *Non* usare `htmlspecialchars`. |
| **Campo modificabile** (`<input value>`, `<textarea>`) | `globo_edit($v)` = decodifica legacy + escape per il campo |
| **Testo piano** (email di testo, CSV/XLS, PDF) | `globo_to_text($v)` (già fatto di fatto nel legacy) |

**Helper da introdurre nel core** (`src/varie/funzioni.php`):
```php
// Da "HTML di presentazione" (come nel DB) a testo piano editabile.
// Gestisce TUTTE le entità (incluse le nominate accentate) e gli a-capo.
function globo_to_text($v) {
    $v = str_ireplace(['<br />', '<br/>', '<br>'], "\n", $v);
    return html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
// Per popolare input/textarea: testo vero + escape corretto del contesto.
function globo_edit($v) {
    return htmlspecialchars(globo_to_text($v), ENT_QUOTES, 'UTF-8');
}
// (Sola lettura HTML: stampare $v grezzo. Eventuale globo_h($v){ return $v; }
//  solo per esplicitare l'intento nei template.)
```
Round-trip verificato: stored `l&#39;altra &quot;<br />riga2` → `globo_edit` → in textarea l'utente vede `l'altra "` + a-capo + `riga2` → al salvataggio `globo_converti_in_html` ri-produce l'originale.

**Mappa dei campi (2026-06-08).** `globo_converti_in_html()` / `..._da_editor()` al salvataggio in **~44 file**: contatti (`cont_*`: azienda, nome, cognome, qualifica, telefono, cellulare, fax, indirizzo, cap, città, provincia, regione, area, nazione, p.iva, cf, note), progetti (`prog_*`, `pr_*`: nome, descrizione, note, step), calendario (`cal_*`: descrizione impegni/scadenze), bilancio/adhoc (CUSTOM-CANDIDATE, disattivi). Questi sono i campi i cui **punti di display** vanno corretti secondo la tabella sopra.

**Note / punti aperti minori.**
1. **Ricerca**: il termine cercato è anch'esso codificato (`$ricerca_testo = globo_converti_in_html(...)`) per combaciare coi dati nel DB. Con output-only **si lascia com'è** (funziona). Toccare solo se un domani si cambia lo storage (escluso).
2. **Rich-text** (CKEditor, moduli `adhoc_post_*`): il dato è HTML per natura; in output va grezzo. Se quei moduli si riattivano, valutare sanitizzazione whitelist (HTML Purifier) in input. Fuori scope core attuale.
3. ~~**Anomalia**: `prog_vis.php:287,393` applicano l'encoder a un valore **letto dal DB**~~ **Risolta di fatto (verificato 2026-07-07)**: il file è stato riscritto nel redesign, quel codice non esiste più (nessuna chiamata a `globo_converti_in_html`/`globo_edit`/`globo_to_text` in `prog_vis.php`). Punto chiuso, mai spuntato prima.

**Piano applicativo (core → cloni).**
1. Aggiungere `globo_to_text` / `globo_edit` (+ eventuale `globo_h`) a `src/varie/funzioni.php`.
2. Nei template del redesign, per i campi della mappa: sola lettura → output grezzo (togliere `htmlspecialchars`); input/textarea → `globo_edit()`. Procedere per modulo (contatti, progetti, calendario, note) con verifica a vista.
3. **Non** toccare i percorsi di scrittura (la codifica in input resta — è l'invariante di sicurezza).
4. **Replica sui cloni**: essendo la modifica nel core condiviso, i cloni la ereditano. Per ogni clone verificare solo che non ci siano punti di display **custom** che applicano `htmlspecialchars` sugli stessi campi (vanno corretti allo stesso modo).

**Scartato: riscrittura dello storage (store-raw + migrazione dati).** Valutata e **scartata** il 2026-06-08: nessuna integrazione esterna che richieda dato neutro, e il costo/rischio della migrazione dati × N cloni non è giustificato.

---

## TODO FUTURO

### Nuova pagina "vista tutti" per il calendario

**Decisione (2026-05-28)**: la vista `cal_vis=tutti` è stata rimossa da `cal_lista.php`. La pagina ora mostra sempre la griglia oraria di un singolo utente (niente overlap, griglia Outlook-style).

**Motivazione**: la coesistenza della vista "tutti" (lista flat) con la nuova griglia oraria rendeva il codice complesso e l'overlap detection non banale. La vista "tutti" non aggiungeva valore nella nuova UI.

**Da fare in futuro**: creare una nuova pagina dedicata (es. `cal_tutti.php`) per visualizzare impegni e scadenze di tutti gli utenti in una data. La pagina potrà usare una struttura a colonne (una per utente) o una lista raggruppata, indipendente dalla griglia oraria di `cal_lista`.

**Pianificazione (2026-06-04)**: rinviato a **dopo il rilascio 1.1**. La funzionalità "vista tutti" è legata al clone **5stelle**, che verrà analizzato dopo la pubblicazione di questa versione. Da riprendere in quel contesto.

---

## STATO REDESIGN UI (audit 2026-06-03)

Audit del nuovo design system (`varie/head.php` + `css/globo.{tokens,themes,app}.css`) vs vecchio (`css/globo.css`, layout a `<table>`, iso-8859-1).

**Moduli core — MIGRATI** (nuovo design completo, incl. flash+toast, modal conferma, validazione nativa, paginazione standard):
- `home.php`, `index.php` (login)
- Calendario: `cal_*`
- Contatti: `cont_*`
- Progetti: `prog_*`, `pr_*`
- Utenti: `ut_*`

**Casi particolari:**
- ~~`prog_doc.php`~~ — **eliminato il 2026-09-18** (vedi FILE ELIMINATI). Usava il CSS nuovo ma **inlineava** il `<head>` invece di `varie/head.php`, perché deve iniettare `shadowbox` (lightbox documenti) dentro `<head>`. Lasciato così di proposito: convertirlo a `head.php` rischierebbe di rompere il visualizzatore per una pura questione strutturale.
- `authorization.php` — pagina standalone del flusso "riconoscimento dispositivo" (cookie trusted-device), con stile proprio + FontAwesome. Fuori dallo shell Globo per natura (sta fuori dall'app loggata). Non migrata.

**Ancora vecchio design (47 file) — tutti CUSTOM-CANDIDATE o moduli disattivati**, da affrontare in Fase 2 (separazione core/custom) e non prima:
- `adhoc_post_*` (9), `adhoc_ks_web_*` (6), `adhoc_ks_h5s_*` (5) — moduli ad hoc custom
- `bil_*` (7), `bilv2_*` (7), `bilv2ag_*` (6) — Bilancio (vedi sezione CUSTOM-CANDIDATE)
- `globomail_*` (5) — modulo disattivato
- `ins_invio_promemoria.php`, `ins_invio_scadx.php` (2) — specifici KosmoSol

> **Nota (2026-07-07)**: questo elenco è l'audit del **solo core** (`Globo8/src`). Il clone 5stelle
> ha da tempo ridisegnato la **propria** copia custom di `adhoc_ks_h5s_lista.php`/`_ins_vis.php`
> (override in `Globo-5stelle/custom/src/`, non più allineata al core su questo punto) e oggi ha
> anche corretto le SQL injection nel modulo — vedi la cronologia di Globo-5stelle.
> Le copie **nel core** restano invariate (vecchio design, `MOD_ADHOC_KSH5S` disattivato su KS):
> se un domani un altro clone abilita questo modulo senza sovrascriverlo, eredita ancora il vecchio
> design **e** query non parametrizzate — da tenere presente nel playbook di migrazione clone.
