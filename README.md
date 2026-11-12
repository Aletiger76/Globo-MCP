# Globo-MCP

Globo di **MC Protection**, costruito sull'architettura core+custom
(`core/docs/fase2-core-custom.md`): il core Globo più il modulo `adhoc_mcp_*`
(clienti, servizi, listini, ricariche, acquisti). In produzione su
`www.mcprotection.it/globo-mcp`.

- Regole di lavoro: [CLAUDE.md](CLAUDE.md) (prima quelle comuni in `core/CLAUDE.md`).
- Stato e versione in produzione: [STATO.md](STATO.md).
- Deploy: [docs/deploy.md](docs/deploy.md).

## Struttura

```
core/            Globo8 importato con git subtree (NON si modifica qui)
custom/src/      le differenze del cliente: config, modulo adhoc_mcp_*, navigazione, script cron
dev-overlay/     config per l'ambiente Docker locale (valori dev)
db-init/         creazione dei database e dell'utente dev al primo avvio
docker/          immagine PHP 8.3 + Apache, entrypoint che compone la webroot
tools/           build del pacchetto, modello dei segreti
```

## Lavorare sul progetto

Le istruzioni generali (clone dei repository, hook, file da farsi passare a mano) sono
nel README di Globo8, sezione «Lavorare sul progetto». Per questo repository:

1. `bash core/tools/install-hooks.sh` (una volta dopo il clone).
2. `docker compose up -d` → Globo su http://localhost:8100, phpMyAdmin su
   http://localhost:8101, MariaDB su `localhost:3308`.
3. I dati: i dump di `globo_mcprotection` e `mcprotection` contengono dati reali e si
   passano a mano (mai via cloud, mai in Git). Sono senza `CREATE DATABASE`:

   ```
   docker exec -i globomcp-db mariadb -uroot -prootpass_dev globo_mcprotection < <dump_globo>.sql
   docker exec -i globomcp-db mariadb -uroot -prootpass_dev mcprotection < <dump_mcp>.sql
   ```
4. Dopo una modifica a `core/src`, `custom/src` o `dev-overlay/`:
   `docker compose restart web` (la webroot si ricompone; non è pronta subito).
5. Per i pacchetti di deploy serve `tools/prod-secrets.local.ps1` (passato a mano;
   modello `tools/prod-secrets.example.ps1`).

## Aggiornare il core

```
git remote add globo-core https://github.com/Aletiger76/globo8.git    # una volta, dopo il clone
git subtree pull --prefix=core globo-core main --squash
```

Sempre `--squash`. MCP oggi è su un'istantanea del core (vedi `STATO.md`): allinearlo
cambia il funzionamento ed è un rilascio da collaudare (`core/CLAUDE.md` §7).
