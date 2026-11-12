# ============================================================================
# Modello dei segreti di produzione per tools/build-package.ps1 (Globo-MCP).
#
# COME USARLO:
#   1. Copia questo file in: tools/prod-secrets.local.ps1 (ignorato da Git).
#   2. Inserisci i valori veri: li conosce chi gestisce la produzione, si passano
#      a mano (chiavetta o rete interna), mai via cloud.
#
# Nei sorgenti ogni segreto e' un segnaposto __PROD_<NOME>__; build-package.ps1 li
# rimette nella copia del pacchetto e si ferma se ne manca uno o se uno non serve
# piu'. Apici singoli: i caratteri speciali restano letterali (un apice si scrive '').
# ============================================================================

$ProdSecrets = [ordered]@{}

# custom/src/config/www_mcprotection_it.php
$ProdSecrets['__PROD_DB_USER__']           = ''   # MYSQL_USER_GLOBO
$ProdSecrets['__PROD_DB_PASSWORD__']       = ''   # MYSQL_PASSWORD_GLOBO

# custom/src/config/config_smtp.php
$ProdSecrets['__PROD_SMTP_PASSWORD__']     = ''   # chiave API SendGrid (utente SMTP "apikey")
$ProdSecrets['__PROD_SMTP_OLD_USER__']     = ''   # vecchie credenziali Aruba, riga commentata
$ProdSecrets['__PROD_SMTP_OLD_PASSWORD__'] = ''
$ProdSecrets['__PROD_CRITSEND_USER__']     = ''
$ProdSecrets['__PROD_CRITSEND_PASSWORD__'] = ''
