<?php
    // Retention dell'audit log: elimina dalla tabella attivita_log le righe più
    // vecchie di 12 mesi. Da lanciare dal cron di sistema una volta a settimana
    // (deciso col deploy 1.4.2: la finestra di 12 mesi tollera ampiamente un
    // ritardo settimanale). Path assoluti di produzione, come gli altri script
    // in questa cartella.
    // Script da cron (CLI): non passa da index.php, quindi la config non puo'
    // essere risolta dall'hostname della richiesta e i path sono assoluti,
    // specifici di questa installazione (stessa scelta degli script del core).
    // La webroot di MCP e' httpdocs/globo-mcp/ (vedi docs/note-deploy.md).
    // PHPMailer NON va incluso qui: ci pensa gia' varie/funzioni.php.
    $dominio = "www.mcprotection.it";

    require("/var/www/vhosts/mcprotection.it/httpdocs/globo-mcp/config/www_mcprotection_it.php");
    require("/var/www/vhosts/mcprotection.it/httpdocs/globo-mcp/config/config_smtp.php");
    require("/var/www/vhosts/mcprotection.it/httpdocs/globo-mcp/varie/funzioni.php");

    try {
      $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
      mail(EMAIL_KOSMOSOL, "errore mysql", "purga_log_attivita: " . $e->getMessage(), "From: " . EMAIL_GLOBO);
      exit();
    }

    try {
      $n = $db->exec("DELETE FROM attivita_log WHERE dt < (NOW() - INTERVAL 12 MONTH)");
      globo_scrivi_log("| purga_log_attivita $dominio | eliminate $n righe oltre i 12 mesi", "purga_log_attivita");
    }
    catch (PDOException $e) {
      globo_scrivi_log("| purga_log_attivita $dominio | ERRORE: " . $e->getMessage(), "purga_log_attivita");
    }
