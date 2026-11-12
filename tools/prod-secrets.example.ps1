# ============================================================================
# Template dei segreti di produzione per build-package.ps1.
#
# COME USARLO:
#   1. Copia questo file in: tools/prod-secrets.local.ps1
#   2. Inserisci i valori reali di produzione (host, utente, password, db).
#   3. Il file .local.ps1 è ignorato da git: i segreti non entrano nel repo.
#
# build-package.ps1 legge $ProdDb da prod-secrets.local.ps1 e sostituisce le
# costanti MYSQL_* (host/user/password/db) nel config del pacchetto.
# ============================================================================

$ProdDb = @{
  Host     = "127.0.0.1"        # MYSQL_HOST_GLOBO in produzione
  User     = "UTENTE_DB"        # MYSQL_USER_GLOBO
  Password = 'PASSWORD_DB'      # MYSQL_PASSWORD_GLOBO  (apici singoli: caratteri speciali literali)
  DbGlobo  = "globo_kosmosol"   # MYSQL_DB_GLOBO
  DbKs     = "globo_kosmosol"   # MYSQL_DB_GLOBO_KS
}

# Segnaposto __PROD_*__ nei sorgenti -> valore vero. build-package.ps1 li sostituisce
# nella copia del pacchetto e si ferma se ne manca uno o se uno non serve piu'.
# Apici singoli: i caratteri speciali restano letterali (un apice si scrive '').
$ProdSecrets = [ordered]@{}
$ProdSecrets['__PROD_SMTP_PASSWORD__']     = ''   # src/config/config_smtp.php: chiave API SendGrid (utente SMTP "apikey")
$ProdSecrets['__PROD_SMTP_OLD_USER__']     = ''   # config_smtp.php: vecchie credenziali Aruba, riga commentata
$ProdSecrets['__PROD_SMTP_OLD_PASSWORD__'] = ''   # config_smtp.php: idem
$ProdSecrets['__PROD_CRITSEND_USER__']     = ''   # config_smtp.php: SMTP_CRITSEND_USER
$ProdSecrets['__PROD_CRITSEND_PASSWORD__'] = ''   # config_smtp.php: SMTP_CRITSEND_PASSWORD
# src/index.php: hash MD5 della password del super amministratore "ks-globo" (livello 0,
# l'account con cui KosmoSol amministra ogni installazione). Lo usano TUTTI i cloni, che
# prendono index.php dal core: va anche nei loro prod-secrets.local.ps1.
# In dev resta il segnaposto, quindi "ks-globo" non entra: per il livello 0 si usa un
# utente di prova portato a livello 0 (per chiave primaria).
$ProdSecrets['__PROD_KS_ADMIN_MD5__']      = ''
