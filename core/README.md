# Globo8 — il core di Globo

Gestionale aziendale PHP (senza framework) nato da KosmoSol, migrato da PHP 5 a
**PHP 8.3**. Questo repository è insieme:

- il **core** della famiglia Globo, che i cloni dei clienti importano con `git subtree`;
- l'installazione di **Globo KosmoSol** (`globo-ks.kosmosol.it`), da cui è nato tutto.

Regole di lavoro (per le persone e per Claude Code): [CLAUDE.md](CLAUDE.md).
Stato attuale: [STATO.md](STATO.md).

## La famiglia

| Repository | Cliente | Produzione | Dev (web / DB / phpMyAdmin) |
|---|---|---|---|
| `Globo8` | KosmoSol (core) | `globo-ks.kosmosol.it/globo` | 8080 / 3306 / 8081 |
| `Globo-5stelle` | 5stelle\* (hotel, H5S) | `globo-h5s.kosmosol.it` | 8090 / 3307 / 8091 |
| `Globo-MCP` | MC Protection | `www.mcprotection.it/globo-mcp` | 8100 / 3308 / 8101 |
| `Globo-MLBenessere` | ML Benessere Piacenza e Aversa, ML Acque Piacenza (un pacchetto, tre sedi) | `globo-piacenza.mlbenessere.it/globo`, `globo-aversa.mlbenessere.it/globo`, `globo.mlacquepiacenza.it/globo` | 8110 / 3309 / 8111 |
| `Globo-Verdecasa` | Verde Casa (agenzia immobiliare) | `www.verdecasa.com/globo` | 8120 / 3310 / 8121 |
| `Globo-StudioCia` | Studio CIA (commercialisti, due sedi) | `globo-fidenza.studiocia.it/globo`, `globo-sansecondo.studiocia.it/globo` | 8140 / 3311 / 8141 |

Ogni repository ha il proprio deploy e il proprio `docs/deploy.md`: non si mescolano.
Il sito pubblico di Verde Casa (`Verdecasa-Sito`) e gli strumenti di analisi dei dati
non sono in questi repository.

## Struttura

```
src/          la webroot di Globo (core + installazione KosmoSol)
database/     schema (senza dati) e migrazioni SQL; i dump restano fuori da Git
docker/       immagine PHP 8.3 + Apache dell'ambiente dev
docs/         architettura core/custom, design system, deploy, note tecniche
tools/        build del pacchetto, hook di controllo, anonimizzatore, sonda preflight
```

Un clone ha invece `core/` (questo repository, importato), `custom/src/` (le sue
differenze), `dev-overlay/` (i config dev), `db-init/` e i propri `docker/` e `tools/`.
Dettagli: [docs/fase2-core-custom.md](docs/fase2-core-custom.md).

## Lavorare sul progetto (anche in più persone)

Per partire su un PC nuovo (Windows con Git Bash e Docker Desktop):

1. Farsi invitare come collaboratore sui repository GitHub privati, poi clonarli **uno
   accanto all'altro** nella stessa cartella (es. `C:\Progetti\`), con lo stesso nome
   del repository: alcuni documenti si citano tra repository con path relativi, e
   Docker chiama i volumi dal nome della cartella.
2. In ogni repository attivare il controllo prima di ogni commit (una volta, e di nuovo
   quando cambia `tools/pre-commit`):
   - in Globo8: `bash tools/install-hooks.sh`
   - in un clone: `bash core/tools/install-hooks.sh`
3. Farsi passare **a mano** (chiavetta o rete interna, mai via cloud):
   - `tools/prod-secrets.local.ps1` di ogni repository, solo a chi fa i deploy (senza,
     il build si ferma; il resto funziona);
   - i dump dei DB, se serve una copia dev con i dati (istruzioni nel README del
     singolo repository).
4. Avviare l'ambiente: `docker compose up -d` nella cartella del repository, poi aprire
   la porta web della tabella sopra. Vedi [docs/dev-workflow.md](docs/dev-workflow.md).
5. Ogni volta: leggere `STATO.md`, `git pull` prima di cominciare, commit e `git push`
   alla fine.

Nei cloni, per aggiornare il core:

```
git remote add globo-core <URL GitHub di Globo8>     # una volta
git subtree pull --prefix=core globo-core main --squash
```

(sempre `--squash`; prima leggere CLAUDE.md §7: allineare un clone al core cambia il suo
funzionamento ed è un rilascio da collaudare).

## Cosa non entra mai qui

Dati personali, dump, `upload/`, `globo_log/`, pacchetti di build, segreti. I segreti
nei sorgenti sono segnaposti `__PROD_*__`: vedi CLAUDE.md §5.
