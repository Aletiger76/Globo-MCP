#!/bin/sh
# Compone la webroot del clone fondendo tre livelli, poi avvia Apache.
# Eseguito a ogni avvio del container (ENTRYPOINT). Vedi docs/fase2-core-custom.md §5.
set -e

# Livello 1 — core (base). --delete: i file rimossi dal core o sfoltiti da custom
# spariscono dalla webroot al restart. Escluse le cartelle scrivibili a runtime.
rsync -a --delete --exclude 'globo_log/' --exclude 'upload/' --exclude 'documenti/' /opt/core/ /var/www/html/

# Livello 2 — custom (override + aggiunte del clone): vince sul core.
rsync -a /opt/custom/ /var/www/html/

# Livello 3 (solo dev) — config adattata all'ambiente Docker locale.
rsync -a /opt/dev-overlay/config/ /var/www/html/config/

mkdir -p /var/www/html/globo_log /var/www/html/upload /var/www/html/documenti/upload
chown -R www-data:www-data /var/www/html

exec apache2-foreground
