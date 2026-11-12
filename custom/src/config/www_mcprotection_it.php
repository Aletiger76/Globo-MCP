<?php  
  /* CONFIGURAZIONE GENERALE */
  define("MYSQL_HOST_GLOBO", "127.0.0.1");
  define("MYSQL_USER_GLOBO", "__PROD_DB_USER__");
  define("MYSQL_PASSWORD_GLOBO", "__PROD_DB_PASSWORD__");
  define("MYSQL_DB_GLOBO", "globo_mcprotection");
  define("URL_GLOBO", "http://www.mcprotection.it/globo-mcp");
  define("DIR_LOG", "/var/www/vhosts/mcprotection.it/httpdocs/globo-mcp/globo_log/");
  define("EMAIL_GLOBO", "globo@mcprotection.it");
  define("EMAIL_KOSMOSOL", "globo@kosmosol.it");
  define("TITLE", "KosmoSol GLOBO");
  define("PREFISSO_FILE", "mcprotection");
  define("IMG_LOGO", "logo-mcp.png");
  // Favicon MC Protection (leone del logo, ritagliato) — aggiunta 2026-07-10,
  // core moderno la richiede (fallback a favicon.ico se assente).
  define("FAVICON", "immagini/favicon-mcp.png");
  define("FAVICON_TOUCH", "immagini/favicon-mcp-touch.png");
  define("TUTTO_MINUSCOLO", false);
  // Aggiunto 2026-07-10 per il core moderno (redesign): il config originale è
  // precedente al sistema di temi/al nuovo auth, nessuna delle due costanti
  // esisteva. Senza valore esplicito il core moderno da' fatal error
  // ("Undefined constant") già al primo caricamento.
  // GLOBO_THEME: logo MC Protection verde — "verde-querce" (#1E8A4D) e' il
  // match più vicino nel core, non assegnato ad altri clienti. Da confermare/
  // affinare con il colore brand esatto.
  define("GLOBO_THEME", "verde-querce");
  // AUTH_TOKEN: nuovo sistema di auth aggiunto al core DOPO la generazione di
  // questo custom — per ora non adottato (vedi docs/analisi-mcp.md), quindi
  // false anche qui.
  define("AUTH_TOKEN", false);
  define("DATE_EXPIRE_TOKEN", 180);

  /* ATTIVAZIONE MODULI */
  define("MOD_CALENDARIO", true); define("LIV_MOD_CALENDARIO", 2);
  define("MOD_PROGETTI", true); define("LIV_MOD_PROGETTI", 2);
  define("MOD_CONTATTI", true); define("LIV_MOD_CONTATTI", 2);
  define("MOD_NEWSLETTER", true); define("LIV_MOD_NEWSLETTER", 1);
  define("MOD_GESTIONE_UT", true); define("LIV_MOD_GESTIONE_UT", 2);
  define("MOD_ADHOC_MCP", true); define("LIV_MOD_ADHOC_MCP", 1); 

  /* CONFIGURAZIONE MODULO CALENDARIO */
  define("CAL_ORA_INIZIO_GIO", 8);
  define("CAL_ORA_FINE_GIO", 19);
  define("CAL_MAX_DURATA_IMP", 6);
  define("CAL_FLAG_INVIO_IMP", 2);
  
  /* CONFIGURAZIONE MODULO PROGETTI */
  define("PROC_ARC", false); // archiviazione progetti
  define("PROG_RIC_PROG_CON_NOTE", false);
  define("PROG_RIC_CONT", true);
  define("PROG_NOTIF", false);
  define("PROG_AGG_ARCH_ELIM_SOLO_ADMIN", false);
  define("PROG_MAX_NUM_DOC", 50);
  define("PROG_MAX_DIM_DOC_B", 5000000); define("PROG_MAX_DIM_DOC_MB", 5);
  define("PROG_DIR_DOC", ""); 
  $PROG_DOC_FORM_OK = array(); 
  
  /* CONFIGURAZIONE MODULO NEWSLETTER */
  define("NL_NUM_CONT_PER_PAG", 50);
  define("NL_NOME", "MC PROTECTION"); // nome visualizzato nel campo "da" della newsletter e nella richiesta/conferma di iscrizione
  define("NL_EMAIL", "info@mcprotection.it"); // indirizzo a cui vengono inviate le notifiche e da cui viene inviata la newsletter
  define("NL_NOTIFICHE", false); // se ? true viene inviata una notifica ad ogni iscrizione/conferma/cancellazione
  define("NL_FIRMA", "---\nMC Protection\nVia Niviano di Sopra, 22\n29018 Lugagnano Val d'Arda (PC)\nWeb: www.mcprotection.it\nE-mail: info@mcprotection.it");
  define("NL_URL_CONF", "");
  define("NL_URL_VIS", "http://www.mcprotection.it/newsletter-visualizza.php");
  define("NL_CONF_EXTRA_INFO_IT", "");
  define("NL_CONF_EXTRA_INFO_EN", "");
  define("NL_OLDEDITOR_PATH_UPLOAD_IMG", "src=\"/newsletter/upload/");
  define("NL_OLDEDITOR_URL_UPLOAD_IMG", "src=\"http://www.mcprotection.it/newsletter/upload/");
  define("NL_PATH_UPLOAD", "/var/www/vhosts/mcprotection.it/httpdocs/newsletter/upload/"); // /var/www/vhosts/mcprotection.it/httpdocs/newsletter/upload/
  define("NL_URL_UPLOAD", "http://www.mcprotection.it/newsletter/upload/"); // web server (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  // define("NL_PATH_UPLOAD", "../../../../KosmoHome/newsletter/upload/"); // locale (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  // define("NL_URL_UPLOAD", "http://localhost/KosmoSol/KosmoHome/newsletter/upload/"); // locale (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  
/******************************************************************* CONFIGURAZIONE MODULI ADHOC *******************************************************************/

  /* CONFIGURAZIONE MODULO ADHOC MC PROTECTION */
  define("MYSQL_DB_MCP", "mcprotection");
  define("MCP_MAX_NUM_DOC", 50);
  define("MCP_MAX_DIM_DOC_B", 15000000); define("MCP_MAX_DIM_DOC_MB", 10);
  define("MCP_DIR_DOC", "/var/www/vhosts/mcprotection.it/httpdocs/documenti/upload/");
  $MCP_DOC_FORM_OK = array("pdf", "doc", "xls", "afp", "rar", "zip"); 
?>