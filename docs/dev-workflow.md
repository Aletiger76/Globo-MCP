# Dev Workflow — Globo8

Cheat-sheet operativo per le sessioni di sviluppo sull'ambiente Docker.

## Avvio sessione

```powershell
cd C:\Progetti\Globo8\
docker compose up -d
docker compose ps
```

Atteso: `globo-web`, `globo-db`, `globo-pma` tutti `Up`, `globo-db` `(healthy)`.

## Tail log in parallelo (seconda finestra PowerShell)

```powershell
cd C:\Progetti\Globo8\
docker compose logs -f web --tail=50
```

Stop tail: `Ctrl+C`.

## Configurazione errori PHP (già attiva nel Dockerfile)

| Direttiva         | Valore                              |
|-------------------|-------------------------------------|
| `display_errors`  | `STDOUT`                            |
| `error_reporting` | `E_ALL`                             |
| `log_errors`      | `On`                                |
| `error_log`       | `/var/log/apache2/php_errors.log`   |

Verifica:
```powershell
docker compose exec web php -i | Select-String "display_errors|error_reporting|log_errors|error_log"
```

## URL

- Globo: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## Shell nel container web

```powershell
docker compose exec web bash
```

Root web: `/var/www/html/`. Credenziali DB: vedere `src/config/globo-ks_kosmosol_it.php`.