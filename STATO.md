# STATO — Globo-MCP

Da leggere a inizio lavoro; si aggiorna a ogni deploy, decisione o lavoro iniziato.

## In produzione

- `www.mcprotection.it/globo-mcp`: pacchetto **1.0.2**, dal 2026-09-08 (1.0.0 ad
  agosto 2026, data esatta non registrata; hotfix 1.0.1 degli script cron il 2026-09-07).
- **Attenzione per il prossimo deploy**: sul server non c'è un pacchetto completo
  recente. Del 1.0.1 sono stati caricati solo i 3 script, del 1.0.2 solo 9 file
  (calcolati confrontando col 1.0.0, l'ultimo caricato per intero). La base di
  confronto è quello che sta sul server, non l'ultimo pacchetto costruito.
- Cron (Aruba, PHP CLI esplicita): `invia_scadenze.php`, `invia_impegni.php`, e
  `purga_log_attivita.php` ogni domenica alle 00:30. Funzionamento verificato il
  2026-09-08 (mail arrivate al cliente).
- `attivita_log` attiva dal 2026-09-08. Nessuna migrazione DB pendente.
- Pacchetti spediti: `C:\Temp\Globo-mcp-pacchetti\` e
  `C:\Temp\Globo-MCP-dump-lavorazione\globo-mcp-1.0.0-DEPLOYATO.zip` (PC di Alessandro).

## In corso

- Nessun lavoro in corso.

## Core

Su un'**istantanea del core al 2026-09-08**: rispetto a `main` mancano 20 file che
cambierebbero il funzionamento (calendario, liste e schede progetto, temi, header e
footer, e la rimozione di `prog_doc.php`/`prog_file.php`, la funzione documenti di
progetto eliminata dal core per sicurezza). Allinearlo è un rilascio da collaudare.

**Decisione (Alessandro, 2026-11-12): l'allineamento a `main` si fa alla prossima
modifica di questo clone**, chiunque la faccia: prima `git subtree pull`, poi la modifica,
un solo collaudo e un solo pacchetto per entrambe. Al deploy togliere a mano dal server `prog_doc.php` e `prog_file.php`.

## Aperti

- `URL_GLOBO` nel config di produzione è ancora in `http://`: da valutare il passaggio a
  https.
- Al prossimo deploy: verificare che le voci di cron nel pannello puntino a
  `httpdocs/globo-mcp/script/` con PHP CLI esplicito.
- **Prossimo pacchetto: fine riga.** Dal 2026-11-11 tutti i file di testo sono LF su ogni
  PC (`.gitattributes`). Rispetto al pacchetto live cambiano solo nei fine riga 228 file:
  in quel deploy caricare il pacchetto intero, oppure confrontare col live ignorando i
  fine riga (`diff -rq --strip-trailing-cr`). Dal deploy successivo il confronto torna normale.
