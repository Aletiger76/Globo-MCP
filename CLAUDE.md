# CLAUDE.md — Globo-MCP

**Prima leggere `core/CLAUDE.md`**: regole comuni a tutta la famiglia Globo (lingua,
privacy e segreti, soglie di approvazione, commit, core e cloni, guardrail). Qui solo
quello che vale per questo clone. Stato attuale: `STATO.md`. Deploy: `docs/deploy.md`.

## Il cliente

**MC Protection**. Globo in produzione su `www.mcprotection.it/globo-mcp` (webroot
`httpdocs/globo-mcp/` su Aruba). Sigla tecnica: `mcp` (container `globomcp-*`). Dev:
web 8100, DB 3308, phpMyAdmin 8101.

## Cosa si usa davvero

Verificato sui dati (luglio 2026): il cliente usa ogni giorno solo **Progetti e
Scadenze**. Contatti fermi dal 2021, calendario quasi mai, newsletter dal 2013, modulo
custom dal 2022. Tutti i moduli restano però attivi com'erano nel config originale:
la migrazione non ha acceso né spento niente.

- **MCP è un custom vecchio e semplice**, non uno snellimento mirato come 5stelle: ha
  tenuto un sottoinsieme del core di allora. Le funzioni del core moderno mai arrivate
  qui non sono perdite. In particolare il nuovo sistema di autenticazione
  (`AUTH_TOKEN`) **non è adottato** (`AUTH_TOKEN=false`; le colonne `auth_token_*` non
  esistono nella tabella `utente` di MCP): decisione del 2026-07-10, da riprendere solo
  come scelta esplicita, con la migrazione dello schema.
- Per decisioni di merge non usare 5stelle come modello: verificare caso per caso.

## Il modulo custom `adhoc_mcp_*`

36 file, 6 entità: `cli` (clienti), `catserv` (categorie servizi), `lis` (listini),
`serv` (servizi), `rica` (ricariche credito, solo inserimento/elenco/eliminazione),
`acq` (acquisti, con documenti allegati). Serve a MC Protection per tenere traccia di
servizi, acquisti e ricariche dei suoi clienti.

- Attivato da `MOD_ADHOC_MCP` (già `true` nel config originale). Tabelle in un
  **secondo database** `mcprotection` (`MYSQL_DB_MCP`), separato da
  `globo_mcprotection` (`MYSQL_DB_GLOBO`): `cli` legge anche `contatto` dal DB principale.
- Navigazione: `custom/src/varie/home-tiles-custom.php` (le 6 voci, "categorie servizi"
  solo livello 0) e `header-tabs-custom.php` (Clienti, Ricariche, Acquisti in alto).
- Documenti degli acquisti in `MCP_DIR_DOC` = `httpdocs/documenti/upload/`, **fuori**
  dalla webroot dell'app, per scelta (cartella storica dal 2017). Il pacchetto non la
  tocca. `adhoc_mcp_file.php` ha controllo di accesso e `basename()` (prima chiunque
  scaricava senza login).
- Nelle query con JOIN servono alias espliciti: colonne omonime (`nome`) tra
  `servizio` e `categoria_servizio` col fetch associativo PDO si sovrascrivono.

## Comportamenti da preservare

- **`prog_vis.php`**: il pulsante "elimina progetto" è attivo (nel core era
  commentato); oggi è guidato dalla config, nessun override.
- **`custom/src/script/invia_scadenze.php`**: manda le notifiche in modo diverso dal
  core (agli amministratori per visibilità; agli altri utenti due messaggi, uno mirato
  e uno per visibilità). È il comportamento in produzione da anni, considerato
  intenzionale: non riportarlo a quello del core.
- Le mail delle scadenze partono **solo quando la scadenza è arrivata**
  (`data <= oggi`): non c'è preavviso. Se il cliente chiede un avviso prima, è una
  funzione nuova da fare, non un guasto.

## Script da cron (lezione pagata)

Il pacchetto 1.0.0 conteneva gli script di `script/` del core, con i path di
`globo-ks.kosmosol.it`: su mcprotection.it morivano alla terza riga, senza mail e
senza log, e il cliente se n'è accorto dopo due settimane (2026-09-07). Da allora
`custom/src/script/` ha gli override di **tutti e tre** gli script usati
(`invia_scadenze.php`, `invia_impegni.php`, `purga_log_attivita.php`) con i path di
mcprotection.it, e il build si ferma se nel pacchetto compare `globo-ks.kosmosol.it`.
A ogni deploy si lancia a mano almeno uno script da CLI e si controlla `globo_log/`.

## Esclusi per scelta

Mai entrati in `custom/src/`: i moduli `adhoc_*` di altri clienti storici presenti nel
vecchio codice condiviso, e `script/nl_salva_apertura.php` (webhook newsletter
abbandonato, con credenziali in chiaro e SQL injection: se servisse di nuovo si riparte
dal live). Nel pacchetto ci sono 19 file `adhoc_ks_*`/`adhoc_post_*` del core, spenti
dai flag `MOD_*`: è normale, non escluderli dal build.
