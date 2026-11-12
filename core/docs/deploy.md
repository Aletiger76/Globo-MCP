# Deploy di Globo KosmoSol

Procedura per `globo-ks.kosmosol.it/globo`. Riguarda **solo** questa installazione:
ogni clone ha il suo `docs/deploy.md`. Prima di cominciare: annunciare il deploy in
`STATO.md` (CLAUDE.md §4).

## 1. Build

```
powershell -ExecutionPolicy Bypass -File tools\build-package.ps1 -Version "1.4.7"
```

Produce `dist/globo-<versione>.zip`, con dentro la cartella `globo/` da estrarre nella
webroot. Lo script lavora su una copia (i sorgenti restano in configurazione dev) e:

- mette al riparo i pacchetti già presenti in `dist/` in `C:\Temp\Globo-core-pacchetti\`;
- trasforma la configurazione dev in quella di produzione e si ferma se un valore
  atteso non c'è:

  | Dove | Dev | Produzione |
  |---|---|---|
  | `config/globo-ks_kosmosol_it.php` `URL_GLOBO` | `http://localhost:8080` | `https://globo-ks.kosmosol.it/globo` |
  | idem `DIR_LOG` | `/var/www/html/globo_log/` | `/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/globo_log/` |
  | idem `EMAIL_KOSMOSOL` | indirizzo dev | `globo@kosmosol.it` |
  | idem `MYSQL_*` | container `db` | da `tools/prod-secrets.local.ps1` (`$ProdDb`) |
  | `authorization.php` `setcookie()` | `domain` vuoto, `secure` 0 | `globo-ks.kosmosol.it`, `secure` 1 |
  | `config/config.php` `GLOBO_VERSION` | `dev` | la versione del pacchetto |

- rimette i segreti al posto dei segnaposti `__PROD_*__` (credenziali SMTP in
  `config/config_smtp.php`) da `tools/prod-secrets.local.ps1` (`$ProdSecrets`);
- esclude `upload/` e `globo_log/` (restano sul server).

Su un PC nuovo `tools/prod-secrets.local.ps1` va ricevuto a mano (modello:
`tools/prod-secrets.example.ps1`), altrimenti il build si ferma.

I moduli `MOD_ADHOC_KS`, `MOD_ADHOC_KSH5S`, `MOD_BILANCIO`, `MOD_BILANCIOV2` restano
spenti anche in produzione (vecchio design, orfani nella nuova interfaccia).

## 2. Prima di estrarre

- Requisiti: PHP 8.x (in uso 8.3), MariaDB 10.11, estensioni `pdo_mysql`, `gd`,
  `mbstring`, `openssl`; `upload/` e la cartella di `DIR_LOG` scrivibili.
- Verifica rapida: caricare `tools/preflight.php` nella cartella `globo/`, aprirlo nel
  browser, leggere l'elenco verde/rosso (PHP, estensioni, DB, colonne token, cartelle).
  **Cancellarlo subito dopo**: espone informazioni di sistema.
- Le colonne `auth_token_*` della tabella `utente` servono solo dove `AUTH_TOKEN` è
  attivo (su KosmoSol sì).
- Migrazioni DB in `database/migrations/`: si applicano a mano, prima del codice che le
  usa. Ad oggi nessuna pendente; quando se ne aggiunge una, annotarla in `STATO.md`.

## 3. Estrazione

Estrarre lo zip nella webroot. **Estrarre non cancella**: i file tolti dal codice
restano sul server finché non si eliminano a mano. Il prossimo pacchetto dopo il 1.4.6
toglie `prog_doc.php` e `prog_file.php`.

## 4. Cron

- `script/purga_log_attivita.php`: settimanale (domenica 00:00), già pianificato.
- Nel pannello Aruba scegliere sempre la versione **PHP CLI esplicita** (es. 8.3.x), non
  il path generico `php-internal`: quello non è eseguibile dall'account cliente e il cron
  fallisce con "Permesso negato" (successo il 2026-07-28).

## 5. Test dopo il deploy

- [ ] Login corretto → `home.php`
- [ ] Login con password errata → messaggio d'errore ed email di notifica a `globo@kosmosol.it`
- [ ] Logout → `index.php`; `home.php` senza sessione → `index.php`
- [ ] Calendario: crea/modifica/elimina impegno
- [ ] Contatti: crea/modifica/elimina contatto
- [ ] Progetti: crea progetto con nota
- [ ] Gestione utenti: crea/modifica utente

## 6. Rollback

Ricaricare il pacchetto precedente da `C:\Temp\Globo-core-pacchetti\`. Il deploy non
modifica il DB: il rollback del DB serve solo dopo una migrazione applicata.

## 7. Dopo

Aggiornare `STATO.md` (versione in produzione, data, cosa resta da fare) e togliere
l'annuncio "in corso".
