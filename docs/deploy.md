# Deploy di Globo-MCP

Globo in `www.mcprotection.it/globo-mcp` (Aruba, webroot
`/var/www/vhosts/mcprotection.it/httpdocs/globo-mcp/`). Prima di cominciare: annunciare
il deploy in `STATO.md` (core/CLAUDE.md §4).

## 1. Build

```
powershell -ExecutionPolicy Bypass -File tools\build-package.ps1 -Version "1.0.3"
```

Produce `dist/globo-mcp-<versione>.zip` con dentro `globo/`. Compone `core/src` +
`custom/src` senza `dev-overlay/`, rimette i segreti da `tools/prod-secrets.local.ps1`
(DB e SMTP) e **si ferma** se un valore atteso del config di produzione non c'è, se nel
pacchetto compare `globo-ks.kosmosol.it` fuori dai commenti, o se un segnaposto
`__PROD_*__` resta senza valore. I pacchetti già in `dist/` vanno in
`C:\Temp\Globo-mcp-pacchetti\`.

Esclude: `upload/` e `globo_log/` (ricreati vuoti), la config di KosmoSol, le librerie
usate solo da `adhoc_post_*` (`ckeditor/`, `elfinder/`, `imageuploader/`, `fpdf/`,
`mdbootstrap/`), `ins_invio_scadx_2.php` e `ins_invio_promemoria_2.php` (endpoint di
KosmoSol). Contiene gli override di `script/` con i path di mcprotection.it.

## 2. Quali file caricare

Si confronta il nuovo zip, **file per file**, con quello che sta **davvero sul server**:
oggi è il 1.0.0 più i file caricati col 1.0.1 e col 1.0.2 (vedi `STATO.md`), non l'ultimo
pacchetto costruito. Un file dimenticato non dà errori, si nota dopo (successo con
`index.php` al 1.0.2). In alternativa si estrae il pacchetto intero nella webroot.

- **Estrarre non cancella**: i file tolti dal codice si eliminano a mano. Allineando il
  core a `main` andranno tolti `prog_doc.php` e `prog_file.php`.
- La cartella dei documenti `httpdocs/documenti/upload/` è fuori dalla webroot e il
  pacchetto non la tocca.
- Se lo zip passa dal server, cancellarlo subito (contiene il config con le credenziali).

## 3. Controlli dopo il deploy

- [ ] Login: in fondo alla card `v.<versione>` ("v.dev" = pacchetto non dal build).
- [ ] Home con i box del core e i 6 del modulo mcp; in alto Calendario, Contatti,
      Progetti, Clienti, Ricariche, Acquisti.
- [ ] Progetti e scadenze: dettaglio, note, scadenze.
- [ ] Modulo mcp: clienti, acquisti, download di un documento.
- [ ] **Cron**: lanciare a mano da CLI `script/invia_scadenze.php` e controllare che
      `globo_log/<oggi>.txt` si popoli (`begin script` / `end script`). È l'unica parte
      del codice che nessun collaudo tocca.
- [ ] Log errori PHP del dominio: nessuna riga nuova.
- Un cambio visibile all'utente (es. il responsabile proposto in una scadenza) va
  detto al cliente.

## 4. Cron

Nel pannello Aruba, per ogni script (`invia_scadenze.php`, `invia_impegni.php`,
`purga_log_attivita.php` domenica 00:30): versione **PHP CLI esplicita**, non il path
`php-internal`, che dall'account cliente non è eseguibile.

## 5. Rollback

Ricaricare i file dal pacchetto precedente in `C:\Temp\Globo-mcp-pacchetti\`, o
ripristinare la webroot dal backup. Nessuna migrazione distruttiva: il DB resta
coerente.

## 6. Dopo

Aggiornare `STATO.md` (versione, data, quali file sono stati caricati davvero) e
togliere l'annuncio "in corso".
