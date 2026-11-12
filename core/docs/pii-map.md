# Mappa PII — Schema Globo

Generato da analisi dello schema `database/globo_schema.sql` (38 tabelle, nessun dato reale).
Le 11 tabelle `nl_log_invio_AAAA` hanno struttura identica: documentate una volta sola.
Tutti i 42 casi DA_VERIFICARE sono stati risolti con decisione esplicita del responsabile del progetto.

---

## Legenda categorie

| Codice | Significato |
|---|---|
| NON_SENSIBILE | ID numerici, flag, date di sistema, valori tecnici, riferimenti a entità non sensibili |
| PII_EMAIL | Indirizzi email |
| PII_NOME | Nomi, cognomi, ragioni sociali, username in formato leggibile |
| PII_TELEFONO | Numeri di telefono, fax, cellulare |
| PII_INDIRIZZO | Vie, città, CAP, civico, provincia, regione (anche separatamente) |
| PII_CODICE_FISCALE | Codici fiscali italiani |
| PII_PIVA | Partite IVA |
| CREDENZIALI | Password (anche hashate), token, chiavi API, codici monouso |
| TESTO_LIBERO | Note, descrizioni, testi che possono contenere PII miste |

## Legenda strategie

| Codice | Significato |
|---|---|
| mantieni | Non toccare |
| fake_email | Sostituire con email fittizia tipo `utente_<ID>@example.invalid` |
| fake_nome | Sostituire con nome di persona italiano casuale via Faker('it_IT').name() |
| fake_azienda | Sostituire con ragione sociale fittizia via Faker('it_IT').company() |
| fake_telefono | Sostituire con numero finto formalmente valido |
| fake_indirizzo | Sostituire con indirizzo completo monoblocco (via + numero + città) — usare solo per campi che contengono l'indirizzo intero in un'unica stringa |
| fake_via | Sostituire con via italiana fittizia (es. "Via Garibaldi, 12") |
| fake_cap | Sostituire con CAP italiano fittizio (5 cifre) |
| fake_citta | Sostituire con città italiana fittizia |
| fake_provincia | Sostituire con sigla provincia italiana fittizia (2 lettere, es. "MI") |
| fake_regione | Sostituire con regione italiana fittizia (es. "Lombardia") |
| fake_cf | Sostituire con CF formalmente valido ma non reale |
| fake_piva | Sostituire con PIVA formalmente valida ma non reale |
| fake_iban | Sostituire con IBAN finto formalmente valido |
| fake_username | Sostituire con username deterministico fittizio nello stesso formato `<iniziale>.<cognome>` (es. `m.rossi` → `a.bianchi`), **mantenendo coerenza cross-tabella**: ogni valore originale mappa sempre allo stesso valore fittizio in tutte le tabelle e colonne |
| fake_ip_documentazione | Sostituire con IP del range `192.0.2.0/24` (TEST-NET-1, RFC 5737, riservato per documentazione) in modo deterministico: stesso IP originale → stesso IP fittizio |
| hash_o_mantieni | Se già hash (bcrypt/md5), lasciare; se plaintext, sostituire con hash di stringa fissa |
| svuota | Sostituire con stringa vuota `''` (per colonne NOT NULL — il campo esiste ma non contiene informazione) |
| svuota_null | Sostituire con `NULL` (per colonne nullable dove `NULL` è il valore semanticamente corretto in assenza di dato) |
| tronca_o_offusca | Sostituire con `[anonimizzato]` o pattern simile |

---

## Tabella: `bilancio`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| tipo | varchar(20) | NON_SENSIBILE | mantieni | tipo movimento contabile |
| data | date | NON_SENSIBILE | mantieni | data contabile |
| descrizione | longtext | TESTO_LIBERO | tronca_o_offusca | può contenere nomi cliente o riferimenti a terzi |
| fattura | varchar(5) | NON_SENSIBILE | mantieni | numero o flag fattura |
| importo | decimal(10,2) | NON_SENSIBILE | mantieni | |
| flag_esigibilita | tinyint(1) | NON_SENSIBILE | mantieni | |
| flag_pagamento | int(1) | NON_SENSIBILE | mantieni | |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | formato `<iniziale>.<cognome>` — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `bilanciov2`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| tipo | enum('entrata','uscita') | NON_SENSIBILE | mantieni | |
| data | date | NON_SENSIBILE | mantieni | |
| cliente_fornitore | varchar(255) | PII_NOME | fake_azienda | nome del cliente o fornitore — trattato come ragione sociale per sicurezza |
| email | varchar(500) | PII_EMAIL | fake_email | |
| id_bilanciov2_tipo_cliente | int(3) | NON_SENSIBILE | mantieni | FK |
| dati_fatturazione | text | TESTO_LIBERO | tronca_o_offusca | testo libero con dati fatturazione: può contenere ragione sociale, indirizzo, P.IVA, CF |
| fattura | varchar(10) | NON_SENSIBILE | mantieni | numero fattura |
| modalita_pagamento | text | NON_SENSIBILE | mantieni | contiene solo etichette generiche (Bonifico, RID, Contanti) — confermato |
| banca | text | NON_SENSIBILE | mantieni | contiene solo nome istituto bancario — confermato |
| note_fattura | text | TESTO_LIBERO | tronca_o_offusca | |
| flag_esigibilita | tinyint(1) | NON_SENSIBILE | mantieni | |
| flag_pagamento | int(1) | NON_SENSIBILE | mantieni | |
| credito_debito_attuale | decimal(10,2) | NON_SENSIBILE | mantieni | |
| data_pagamento | date | NON_SENSIBILE | mantieni | |
| data_scadenza_pagamento | date | NON_SENSIBILE | mantieni | |
| flag_sollecito | tinyint(1) | NON_SENSIBILE | mantieni | |
| id_bilanciov2_agente | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| perc_provvigione | int(2) | NON_SENSIBILE | mantieni | |
| note | text | TESTO_LIBERO | tronca_o_offusca | |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | formato `<iniziale>.<cognome>` — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `bilanciov2_agente`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| nome | varchar(255) | PII_NOME | fake_nome | nome dell'agente (persona fisica esterna, non utente di sistema) |
| email | varchar(255) | PII_EMAIL | fake_email | |
| note | longtext | TESTO_LIBERO | tronca_o_offusca | |
| fatture_provvigioni_pagate | longtext | NON_SENSIBILE | mantieni | contiene solo dati strutturati ID/importi — confermato |
| provvigioni_pagate | longtext | NON_SENSIBILE | mantieni | contiene solo dati strutturati ID/importi — confermato |
| totale_provvigioni_pagate | decimal(10,2) | NON_SENSIBILE | mantieni | |
| dt_inserimento | datetime | NON_SENSIBILE | mantieni | |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `bilanciov2_categoria`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | int(3) | NON_SENSIBILE | mantieni | PK |
| tipo | enum('entrata','uscita') | NON_SENSIBILE | mantieni | |
| nome | varchar(50) | NON_SENSIBILE | mantieni | nome categoria (es. "Stipendi", "Consulenze") — dato di sistema |

---

## Tabella: `bilanciov2_dettaglio`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_bilanciov2 | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| id_bilanciov2_categoria | varchar(50) | NON_SENSIBILE | mantieni | FK (identificatore categoria) |
| descrizione | text | TESTO_LIBERO | tronca_o_offusca | descrizione riga dettaglio: può contenere riferimenti a persone |
| flag_canone | tinyint(1) | NON_SENSIBILE | mantieni | |
| data_scadenza_canone | date | NON_SENSIBILE | mantieni | |
| flag_ricorda | tinyint(1) | NON_SENSIBILE | mantieni | |
| imponibile | decimal(10,2) | NON_SENSIBILE | mantieni | |
| iva | int(2) | NON_SENSIBILE | mantieni | |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | formato `<iniziale>.<cognome>` — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `bilanciov2_tipo_cliente`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | int(3) | NON_SENSIBILE | mantieni | PK |
| nome | varchar(50) | NON_SENSIBILE | mantieni | tipo cliente (es. "Azienda", "Privato") — dato di sistema |

---

## Tabella: `contatto`

> Tabella principale anagrafica. È la fonte PII più densa del database.

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK — usato come FK in nl_lista_contatto |
| azienda | varchar(255) | PII_NOME | fake_azienda | ragione sociale |
| nome | varchar(255) | PII_NOME | fake_nome | |
| cognome | varchar(255) | PII_NOME | fake_nome | |
| qualifica | varchar(255) | NON_SENSIBILE | mantieni | ruolo professionale: non è PII diretta |
| telefono | varchar(255) | PII_TELEFONO | fake_telefono | |
| cellulare | varchar(255) | PII_TELEFONO | fake_telefono | |
| fax | varchar(255) | PII_TELEFONO | fake_telefono | numero fax |
| email | varchar(255) | PII_EMAIL | fake_email | |
| sito | varchar(255) | NON_SENSIBILE | mantieni | sito web aziendale: dato pubblico |
| indirizzo | varchar(255) | PII_INDIRIZZO | fake_via | via e numero civico |
| cap | varchar(255) | PII_INDIRIZZO | fake_cap | |
| citta | varchar(255) | PII_INDIRIZZO | fake_citta | |
| provincia | varchar(255) | PII_INDIRIZZO | fake_provincia | sigla a 2 lettere |
| regione | varchar(255) | PII_INDIRIZZO | fake_regione | |
| area | varchar(255) | NON_SENSIBILE | mantieni | area geografica generica (es. "Nord-Est") — confermato |
| nazione | varchar(255) | NON_SENSIBILE | mantieni | paese: dato aggregato non identificativo |
| pi | varchar(255) | PII_PIVA | fake_piva | Partita IVA |
| cf | varchar(255) | PII_CODICE_FISCALE | fake_cf | Codice Fiscale |
| note | longtext | TESTO_LIBERO | tronca_o_offusca | campo note libero: alta probabilità di PII miste |
| nl_flag_confermato | tinyint(1) | NON_SENSIBILE | mantieni | flag consenso newsletter |
| nl_codice_conferma | varchar(255) | CREDENZIALI | svuota | token monouso di conferma iscrizione newsletter |
| dt_inserimento | datetime | NON_SENSIBILE | mantieni | |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `globomail`

> Sistema di messaggistica interna. I testi quasi certamente contengono PII di clienti.

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| dt | datetime | NON_SENSIBILE | mantieni | |
| utente_mitt | varchar(50) | PII_NOME | fake_username | username mittente — coerenza cross-tabella richiesta |
| utente_dest | varchar(50) | PII_NOME | fake_username | username destinatario — coerenza cross-tabella richiesta |
| oggetto | varchar(255) | TESTO_LIBERO | tronca_o_offusca | oggetto del messaggio: può contenere nomi o riferimenti a clienti |
| testo | longtext | TESTO_LIBERO | tronca_o_offusca | corpo del messaggio: alta probabilità di PII miste |
| flag_lettura | tinyint(1) | NON_SENSIBILE | mantieni | |

---

## Tabella: `impegno`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| data | date | NON_SENSIBILE | mantieni | |
| ora | varchar(10) | NON_SENSIBILE | mantieni | |
| durata | varchar(10) | NON_SENSIBILE | mantieni | |
| descrizione | longtext | TESTO_LIBERO | tronca_o_offusca | descrizione impegno/appuntamento: può contenere nomi |
| responsabile | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `login`

> Log di accesso. Contiene IP degli utenti.

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| nome_utente | varchar(50) | PII_NOME | fake_username | username di login — coerenza cross-tabella richiesta |
| ip | varchar(50) | NON_SENSIBILE | fake_ip_documentazione | non è PII cliente, ma sostituito con IP del range 192.0.2.0/24 (RFC 5737) per buona pratica |
| accesso | tinyint(1) | NON_SENSIBILE | mantieni | flag login ok/ko |
| dt | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `login_old`

> Storico login precedente (struttura ridotta).

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| nome_utente | varchar(50) | PII_NOME | fake_username | username — coerenza cross-tabella richiesta |
| dt | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `nl_lista`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | int(11) | NON_SENSIBILE | mantieni | PK |
| nome | varchar(255) | NON_SENSIBILE | mantieni | nome della lista newsletter (es. "Clienti attivi") — dato di sistema |
| flag_stato | tinyint(1) | NON_SENSIBILE | mantieni | |

---

## Tabella: `nl_lista_contatto`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id_lista | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK → nl_lista |
| id_contatto | int(11) | NON_SENSIBILE | mantieni | FK → contatto — l'anonimizzazione avviene sulla tabella contatto |
| flag_stato | tinyint(1) | NON_SENSIBILE | mantieni | |
| dt_inserimento | datetime | NON_SENSIBILE | mantieni | |

---

## Tabelle: `nl_log_invio_2009` … `nl_log_invio_2019` (11 tabelle — struttura identica)

> Log storici degli invii newsletter. Email denormalizzata al momento dell'invio: va anonimizzata in tutte e 11 le tabelle indipendentemente da `contatto.email`.

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_newsletter | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK → nl_newsletter |
| id_lista | int(11) | NON_SENSIBILE | mantieni | FK → nl_lista |
| nome_lista | varchar(255) | NON_SENSIBILE | mantieni | snapshot del nome lista al momento dell'invio |
| email | varchar(255) | PII_EMAIL | fake_email | email destinatario denormalizzata — anonimizzare in tutte e 11 le tabelle |
| dt_invio | datetime | NON_SENSIBILE | mantieni | |
| flag_invio_ok | tinyint(1) | NON_SENSIBILE | mantieni | |
| errore | text | TESTO_LIBERO | svuota | messaggi di errore SMTP possono contenere email nel testo — svuotare |
| flag_apertura | tinyint(1) | NON_SENSIBILE | mantieni | |

---

## Tabella: `nl_newsletter`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| data_invio | date | NON_SENSIBILE | mantieni | |
| oggetto | varchar(255) | NON_SENSIBILE | mantieni | oggetto della newsletter: tipicamente generico |
| testo | longtext | NON_SENSIBILE | mantieni | contenuto editoriale generico — confermato |
| flag_invio | tinyint(1) | NON_SENSIBILE | mantieni | |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `nl_newsletter_lista`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id_newsletter | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK → nl_newsletter |
| id_lista | int(11) | NON_SENSIBILE | mantieni | FK → nl_lista |

---

## Tabella: `nl_template`

> Template HTML per newsletter. Contengono testi vari da neutralizzare.

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | int(11) | NON_SENSIBILE | mantieni | PK |
| header | longtext | TESTO_LIBERO | tronca_o_offusca | HTML: neutralizzare per dump di test pulito |
| body | longtext | TESTO_LIBERO | tronca_o_offusca | HTML: neutralizzare per dump di test pulito |
| footer | longtext | TESTO_LIBERO | tronca_o_offusca | HTML: neutralizzare per dump di test pulito |

---

## Tabella: `progetto`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_progetto_procedura | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| visibilita | int(1) | NON_SENSIBILE | mantieni | |
| data_creazione | date | NON_SENSIBILE | mantieni | |
| flag_archivio | tinyint(1) | NON_SENSIBILE | mantieni | |
| data_archiviazione | date | NON_SENSIBILE | mantieni | |
| nome | varchar(255) | PII_NOME | fake_azienda | il nome del progetto cita spesso clienti reali (es. "Progetto Rossi srl") |
| descrizione | longtext | TESTO_LIBERO | tronca_o_offusca | |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `progetto_documento`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_progetto | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| visibilita | int(1) | NON_SENSIBILE | mantieni | |
| nome | varchar(255) | TESTO_LIBERO | tronca_o_offusca | nome del documento: può includere nome cliente |
| file | varchar(255) | TESTO_LIBERO | tronca_o_offusca | percorso file su disco: può rivelare il cliente nel path (es. `upload/rossi/contratto.pdf`) |
| dt_upload | datetime | NON_SENSIBILE | mantieni | |
| utente_upload | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |

---

## Tabella: `progetto_nota`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_progetto | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| tipo | varchar(20) | NON_SENSIBILE | mantieni | tipo nota |
| visibilita | int(1) | NON_SENSIBILE | mantieni | |
| data_creazione | date | NON_SENSIBILE | mantieni | |
| utente_creazione | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| descrizione | longtext | TESTO_LIBERO | tronca_o_offusca | corpo della nota: alta probabilità di PII miste riferite al progetto/cliente |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `progetto_procedura`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| descrizione | varchar(255) | NON_SENSIBILE | mantieni | nome della procedura: template interno, non PII cliente |

---

## Tabella: `progetto_procedura_step`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_progetto_procedura | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| descrizione | longtext | NON_SENSIBILE | mantieni | descrizione dello step: testo di template interno |
| data_scadenza | date | NON_SENSIBILE | mantieni | |

---

## Tabella: `progetto_procedura_step_chiuso`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id_progetto | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK (PK composita) |
| id_progetto_procedura_step | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK (PK composita) |
| utente | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `progetto_procedura_step_non_attivo`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id_progetto | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK (PK composita) |
| id_progetto_procedura_step | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK (PK composita) |
| utente | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `progetto_template`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| descrizione | varchar(255) | NON_SENSIBILE | mantieni | nome del template: dato di sistema |

---

## Tabella: `progetto_template_step`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| id_progetto_template | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| descrizione | longtext | NON_SENSIBILE | mantieni | descrizione step template: testo interno |
| giorni_scadenza | int(11) | NON_SENSIBILE | mantieni | |

---

## Tabella: `scadenza`

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| id | bigint(20) unsigned | NON_SENSIBILE | mantieni | PK |
| data | date | NON_SENSIBILE | mantieni | |
| tipo | varchar(20) | NON_SENSIBILE | mantieni | |
| visibilita | int(1) | NON_SENSIBILE | mantieni | |
| id_bilancio | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| id_progetto | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| id_progetto_nota | bigint(20) unsigned | NON_SENSIBILE | mantieni | FK |
| descrizione | longtext | TESTO_LIBERO | tronca_o_offusca | descrizione della scadenza: può contenere nomi o riferimenti |
| responsabile | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| utente_ultima_modifica | varchar(50) | PII_NOME | fake_username | username interno — coerenza cross-tabella richiesta |
| dt_ultima_modifica | timestamp | NON_SENSIBILE | mantieni | |

---

## Tabella: `utente`

> Tabella degli utenti interni. Contiene credenziali. La PK `nome_utente` è referenziata logicamente in 16 colonne di altre tabelle (nessuna FK dichiarata nel DB — PHP vanilla).

| Colonna | Tipo SQL | Categoria | Strategia | Note |
|---|---|---|---|---|
| nome_utente | varchar(50) | PII_NOME | fake_username | **PK** — la sostituzione deve essere propagata in cascata a tutte le 16 colonne dipendenti (vedi Note tecniche) |
| password | varchar(255) | CREDENZIALI | hash_o_mantieni | Confermato hashata, formato esatto da verificare durante import in MariaDB locale |
| livello | int(1) | NON_SENSIBILE | mantieni | livello di accesso |
| email | varchar(50) | PII_EMAIL | fake_email | email dell'utente interno |
| flag_notifiche_email | tinyint(1) | NON_SENSIBILE | mantieni | |
| flag_notifiche_globomail | tinyint(1) | NON_SENSIBILE | mantieni | |
| auth_token_login | varchar(200) | CREDENZIALI | svuota_null | token di sessione attiva (nullable): svuotare a NULL |
| auth_token_create | date | NON_SENSIBILE | mantieni | data creazione token |
| auth_token_expire | date | NON_SENSIBILE | mantieni | data scadenza token |

---

## Sintesi

### Conteggi finali

| Metrica | Valore |
|---|---|
| Tabelle analizzate | 38 |
| Colonne totali analizzate | 285 |
| Colonne NON_SENSIBILE | 206 |
| Colonne sensibili (tutte le categorie PII + CREDENZIALI + TESTO_LIBERO) | 79 |
| Colonne DA_VERIFICARE residue | **0** |

### Distribuzione per categoria

| Categoria | Colonne | Tabelle principali coinvolte |
|---|---|---|
| NON_SENSIBILE | 206 | tutte |
| PII_NOME | 23 | bilancio, bilanciov2, bilanciov2_agente, contatto (×3), globomail (×2), impegno (×2), login, login_old, progetto, progetto_documento (×2), progetto_nota (×2), progetto_procedura_step_chiuso, progetto_procedura_step_non_attivo, scadenza (×2), utente |
| TESTO_LIBERO | 28 | bilancio, bilanciov2 (×3), bilanciov2_agente, bilanciov2_dettaglio, globomail (×2), impegno, nl_log_invio_* (×11), nl_template (×3), progetto, progetto_documento (×2), progetto_nota, scadenza |
| PII_EMAIL | 15 | bilanciov2, bilanciov2_agente, contatto, nl_log_invio_* (×11), utente |
| PII_INDIRIZZO | 5 | contatto (×5) |
| PII_TELEFONO | 3 | contatto (×3) |
| CREDENZIALI | 3 | contatto (nl_codice_conferma), utente (×2) |
| PII_CODICE_FISCALE | 1 | contatto |
| PII_PIVA | 1 | contatto |

### Distribuzione per strategia

| Strategia | Colonne | Note |
|---|---|---|
| mantieni | 206 | include hash_o_mantieni (utente.password: nessuna modifica) |
| fake_username | 17 | mapping deterministico cross-tabella (NT-1) |
| tronca_o_offusca | 17 | sostituisce con `[anonimizzato]` |
| fake_email | 15 | idempotente: salta valori già `*@example.invalid` |
| svuota | 12 | nl_log_invio_*.errore (×11) + contatto.nl_codice_conferma |
| fake_nome | 3 | contatto.nome, contatto.cognome, bilanciov2_agente.nome |
| fake_azienda | 3 | contatto.azienda, bilanciov2.cliente_fornitore, progetto.nome |
| fake_telefono | 3 | contatto: telefono, cellulare, fax |
| fake_via | 1 | contatto.indirizzo |
| fake_cap | 1 | contatto.cap |
| fake_citta | 1 | contatto.citta |
| fake_provincia | 1 | contatto.provincia |
| fake_regione | 1 | contatto.regione |
| fake_cf | 1 | contatto.cf |
| fake_piva | 1 | contatto.pi |
| fake_ip_documentazione | 1 | login.ip — deterministico per valore distinto |
| svuota_null | 1 | utente.auth_token_login (nullable → NULL) |
| hash_o_mantieni | 1 | utente.password — nessuna modifica effettiva |

---

## Note tecniche per lo script di anonimizzazione

### NT-1 — Coerenza cross-tabella per `fake_username` (critica)

La strategia `fake_username` richiede un **mapping deterministico globale** costruito prima di eseguire qualsiasi UPDATE.

Logica: per ogni valore distinto nei campi `PII_NOME/fake_username`, generare una volta sola il corrispondente username fittizio (`<iniziale_finta>.<cognome_finto>`) e usarlo ovunque lo stesso valore originale appare.

Le 17 colonne soggette a `fake_username` sono:

| Tabella | Colonna |
|---|---|
| `utente` | `nome_utente` (PK — punto di partenza) |
| `bilancio` | `utente_ultima_modifica` |
| `bilanciov2` | `utente_ultima_modifica` |
| `bilanciov2_dettaglio` | `utente_ultima_modifica` |
| `globomail` | `utente_mitt` |
| `globomail` | `utente_dest` |
| `impegno` | `responsabile` |
| `impegno` | `utente_ultima_modifica` |
| `login` | `nome_utente` |
| `login_old` | `nome_utente` |
| `progetto_documento` | `utente_upload` |
| `progetto_nota` | `utente_creazione` |
| `progetto_nota` | `utente_ultima_modifica` |
| `progetto_procedura_step_chiuso` | `utente` |
| `progetto_procedura_step_non_attivo` | `utente` |
| `scadenza` | `responsabile` |
| `scadenza` | `utente_ultima_modifica` |

⚠️ Non esistono FK dichiarate nel DB (PHP vanilla). La propagazione del mapping è responsabilità dello script.

### NT-2 — Ordine di esecuzione consigliato

1. **Fase mapping**: leggere tutti i valori distinti dei campi `fake_username` e costruire il dizionario `{username_originale: username_fittizio}`. Leggere anche tutti i valori distinti di `PII_EMAIL` e costruire il dizionario `{email_originale: email_fittizia}`.
2. **`utente`**: anonimizzare per prima (è la tabella sorgente degli username).
3. **`contatto`**: anonimizzare per seconda (è la tabella sorgente delle email clienti).
4. **Tutte le altre tabelle**: nell'ordine, usando i mapping già costruiti.
5. **`nl_log_invio_*`** (11 tabelle): le email sono denormalizzate — anonimizzare indipendentemente usando il dizionario email (non è garantita la corrispondenza con `contatto.email`).

### NT-3 — `fake_email`: consistenza consigliata

Se la stessa email appare in più tabelle (es. `contatto.email` e `nl_log_invio_*.email`), non è detto che i valori corrispondano (dati storici, email cambiate nel tempo). La strategia sicura è usare `utente_<ID>@example.invalid` dove `ID` è la PK della riga corrente, senza tentare di allineare i valori cross-tabella.

### NT-4 — `fake_ip_documentazione`: range RFC 5737

Sostituire ogni IP nel campo `login.ip` con un indirizzo del blocco `192.0.2.0/24` (TEST-NET-1). Per mantenere la coerenza (stesso IP originale → stesso IP fittizio) usare un hash dell'IP originale modulo 254, così: `192.0.2.<hash(ip_originale) % 254 + 1>`.

### NT-5 — `progetto_documento.file`

Il campo contiene un percorso file su disco (es. `upload/clienti/rossi/contratto.pdf`). Dopo `tronca_o_offusca` il file non sarà più raggiungibile nell'ambiente di test — verificare che i test funzionali che usano `progetto_documento` non dipendano dall'esistenza del file fisico.

### NT-6 — Verificare il formato dell'hash password durante l'import in MariaDB locale

Leggere un record di esempio dalla tabella `utente` dopo l'import. Se il formato è MD5 (`$1$` o hex 32 chars) o SHA1 (hex 40 chars), segnalare come tema di sicurezza da affrontare separatamente alla migrazione (algoritmi deboli, da sostituire con bcrypt/argon2 in PHP 8).
