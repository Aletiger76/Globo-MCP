# STATO — Globo8 (core e Globo KosmoSol)

Da leggere a inizio lavoro. Si aggiorna quando cambia qualcosa: un deploy, un lavoro
che comincia o finisce, una decisione. Le date sono assolute.

## In produzione

- **Globo KosmoSol** (`globo-ks.kosmosol.it/globo`): pacchetto **1.4.6**, dal 2026-09-08.
  PHP 8.3, MariaDB 10.11. Cron: `script/purga_log_attivita.php` settimanale (domenica
  00:00). Nessuna migrazione DB pendente.
- Copia di ogni pacchetto spedito: `C:\Temp\Globo-core-pacchetti\` sul PC di chi ha
  fatto il deploy (Alessandro).

## In corso

- **Passaggio su GitHub** dei sei repository (Alessandro, dal 2026-11-11): avanzamento
  in [docs/passaggio-github.md](docs/passaggio-github.md).

Prima di un deploy, una migrazione o un cron in produzione: scriverlo qui ("in corso:
cosa, chi, quando") e toglierlo a lavoro finito.

## Pronto ma non ancora in produzione su KosmoSol

Modifiche al codice del core arrivate dopo il 1.4.6 (21 file): tema `blu-cia`, nomi dei
giorni con l'iniziale minuscola, eliminazione della funzione documenti di progetto
(`prog_doc.php`, `prog_file.php`: vanno **cancellati a mano** dal server al deploy),
correzioni dal collaudo di Studio CIA, aggancio `globo.custom.css`, due correzioni al
calendario (mezz'ore, impegni fuori orario), segreti SMTP come segnaposti nel build.
Nessuna è urgente per KosmoSol; vanno nel prossimo pacchetto, con i test di
`docs/deploy.md`.

## Versione del core nei cloni

| Clone | Core | Note |
|---|---|---|
| Verdecasa | `main` | allineato il 2026-11-11 |
| StudioCia | `main` | allineato il 2026-11-11 (calendario del core, che lì è spento) |
| 5stelle | istantanea del core al 2026-09-28 | manca solo il CSS del calendario, che 5stelle non usa |
| MCP, MLBenessere | istantanea del core al 2026-09-08 (stesso `src/`) | mancano 20 file, tra cui la rimozione di `prog_doc.php`/`prog_file.php` |

Allineare un clone a `main` cambia il suo funzionamento: è un rilascio a sé, con
collaudo (CLAUDE.md §7). Per 5stelle prima del pull vedere la regola del calendario nel
suo CLAUDE.md.

## Aperti

- **Testo rovinato nel core**: 35 accenti diventati `EF BF BD` in 10 pagine legacy
  (dalla migrazione di maggio-luglio). I build dei cloni lo segnalano come avviso non
  bloccante. Da recuperare dal baseline, non a mano.
- Moduli ancora col vecchio design e disattivati su KosmoSol (`adhoc_post_*`,
  `adhoc_ks_web_*`, `adhoc_ks_h5s_*`, bilancio, `globomail_*`): vedi
  `docs/refactoring-notes.md`, sezione STATO REDESIGN UI.
- Selezione del config del clone via variabile d'ambiente `GLOBO_CONFIG`: decisa,
  non implementata (`docs/fase2-core-custom.md` §6).
- **Prossimo pacchetto: fine riga.** Dal 2026-11-11 tutti i file di testo sono LF su ogni
  PC (`.gitattributes`). Rispetto al pacchetto live cambiano solo nei fine riga 202 file:
  in quel deploy caricare il pacchetto intero, oppure confrontare col live ignorando i
  fine riga (`diff -rq --strip-trailing-cr`). Dal deploy successivo il confronto torna normale.
