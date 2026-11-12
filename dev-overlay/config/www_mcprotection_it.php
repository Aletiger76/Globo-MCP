<?php
  /* CONFIGURAZIONE MCP — versione DEV per Docker locale.
   *
   * Basata sul config di produzione (C:\Temp\Globo-MCP-dump-lavorazione\src\
   * extracted\globo-mcp\config\www_mcprotection_it.php): adattati i SOLI valori
   * d'ambiente (DB, URL, log, email, auth). NON contiene credenziali di
   * produzione. Le costanti di business (moduli, calendario, progetti) sono
   * riprodotte invariate. Vedi README.md.
   */

  /* CONFIGURAZIONE GENERALE */
  // DEV: host e credenziali del container (non di produzione)
  define("MYSQL_HOST_GLOBO", "db");
  define("MYSQL_USER_GLOBO", "globomcp");
  define("MYSQL_PASSWORD_GLOBO", "globomcppass_dev");
  define("MYSQL_DB_GLOBO", "globo_mcprotection");
  // DEV: URL locale. In Docker src/ e' la web root, senza sottocartella /globo-mcp.
  //      Prod: http://www.mcprotection.it/globo-mcp
  define("URL_GLOBO", "http://localhost:8100");
  // DEV: log nella web root del container. Prod: /var/www/vhosts/.../globo_log/
  define("DIR_LOG", "/var/www/html/globo_log/");
  define("EMAIL_GLOBO", "globo@mcprotection.it");
  // DEV: destinatario di test per le email di sistema. Prod: globo@kosmosol.it
  define("EMAIL_KOSMOSOL", "alessandro@zooga.vc");
  define("TITLE", "MCP GLOBO (dev)");
  define("PREFISSO_FILE", "mcprotection");
  define("IMG_LOGO", "logo-mcp.png");
  define("FAVICON", "immagini/favicon-mcp.png");
  define("FAVICON_TOUCH", "immagini/favicon-mcp-touch.png");
  define("TUTTO_MINUSCOLO", false);
  // DEV: il config di produzione di MCP e' precedente al sistema di temi del
  // redesign (nessun GLOBO_THEME) — il core moderno lo richiede. Logo MC
  // Protection verde (mcprotection.it): "verde-querce" nel core (#1E8A4D) e'
  // il match piu' vicino, non assegnato ad altri clienti — usato finche' non
  // si conferma l'esatto colore brand.
  define("GLOBO_THEME", "verde-querce");
  $IP_OK = array();
  $IP_KO = array();

  /* ATTIVAZIONE MODULI */
  define("MOD_CALENDARIO", true); define("LIV_MOD_CALENDARIO", 2);
  define("MOD_PROGETTI", true); define("LIV_MOD_PROGETTI", 2);
  define("MOD_CONTATTI", true); define("LIV_MOD_CONTATTI", 2);
  // DEV: newsletter attiva in prod ma dormiente dal 2013 (vedi CLAUDE.md) — disattivata qui.
  define("MOD_NEWSLETTER", false); define("LIV_MOD_NEWSLETTER", 1);
  define("MOD_GESTIONE_UT", true); define("LIV_MOD_GESTIONE_UT", 2);
  // DEV: modulo adhoc_mcp_* — Fase A (conversione PDO) in corso, attivato per test.
  define("MOD_ADHOC_MCP", true); define("LIV_MOD_ADHOC_MCP", 1);

  /* EXPIRE TOKEN COOKIE */
  define("DATE_EXPIRE_TOKEN", 180); // 180 giorni
  /* ATTIVAZIONE AUTH CON TOKEN */
  // Il nuovo sistema di auth e' stato aggiunto al core DOPO la generazione di
  // questo custom: per ora non lo si adotta (vedi CLAUDE.md). false
  // anche in prod per il momento, quindi qui coerente.
  define("AUTH_TOKEN", false);

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
  // MCP ha riabilitato "elimina progetto" per tutti i livelli (baseline: solo admin). Vedi CLAUDE.md.
  define("PROG_AGG_ARCH_ELIM_SOLO_ADMIN", false);
  define("PROG_MAX_NUM_DOC", 50);
  define("PROG_MAX_DIM_DOC_B", 5000000); define("PROG_MAX_DIM_DOC_MB", 5);
  define("PROG_DIR_DOC", "");
  $PROG_DOC_FORM_OK = array();

  /* CONFIGURAZIONE MODULO ADHOC MC PROTECTION */
  // DEV: stesso DB del container (globomcp/globomcppass_dev), nome DB invariato.
  define("MYSQL_DB_MCP", "mcprotection");
  define("MCP_MAX_NUM_DOC", 50);
  define("MCP_MAX_DIM_DOC_B", 15000000); define("MCP_MAX_DIM_DOC_MB", 10);
  define("MCP_DIR_DOC", "/var/www/html/documenti/upload/");
  $MCP_DOC_FORM_OK = array("pdf", "doc", "xls", "afp", "rar", "zip");
?>
