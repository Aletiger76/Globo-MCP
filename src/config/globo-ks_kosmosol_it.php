<?php  
  /* CONFIGURAZIONE GENERALE */
  define("MYSQL_HOST_GLOBO",     "db");
  define("MYSQL_USER_GLOBO",     "globo");
  define("MYSQL_PASSWORD_GLOBO", "globopass_dev");
  define("MYSQL_DB_GLOBO",       "globo");
  define("MYSQL_DB_GLOBO_KS",    "globo");
  define("MYSQL_DB_GLOBO_H5S",   "globo_h5s_inesistente");
  //define("MYSQL_DB_BOL", "bookingonline");
  // DEV: adattato per Docker locale (src/ è web root, senza sottocartella /globo/). Produzione: https://globo-ks.kosmosol.it/globo — vedi docs/refactoring-notes.md
  define("URL_GLOBO", "http://localhost:8080");
  // DEV: path adattato per Docker locale. Produzione: /var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/globo_log/ — vedi docs/refactoring-notes.md
  define("DIR_LOG", "/var/www/html/globo_log/");
  define("DIR_FT_PDF_SOLLECITI", "/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/upload/solleciti_fatture_pdf/");
  define("DIR_FT_PDF_PROMEMORIA", "/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/upload/promemoria_fatture_pdf/");
  define("EMAIL_GLOBO", "globo-ks@kosmosol.it");
  // DEV: indirizzo di test per dev locale. Produzione: "globo@kosmosol.it" — vedi docs/refactoring-notes.md
  define("EMAIL_KOSMOSOL", "alessandro@zooga.vc");
  define("TITLE", "KosmoSol GLOBO");
  define("PREFISSO_FILE", "kosmosol");
  define("IMG_LOGO", "logo-ks.png");
  // Favicon per-clone (servita dalla webroot; parametrizzata in varie/head.php).
  define("FAVICON", "immagini/ks-logo.jpg");
  // Branding email (usato da sendTokenMail in varie/funzioni.php). Per-clone.
  define("BRAND_NAME", "KosmoSol");                  // label "Globo <BRAND_NAME>" + alt logo
  define("BRAND_COLOR", "#005da3");                  // colore header email
  define("EMAIL_LOGO", "kosmosol_dashwhite.png");    // logo bianco email, servito da URL_GLOBO/immagini/
  define("TUTTO_MINUSCOLO", true);
  // Tema UI: deve corrispondere a un data-theme definito in globo.themes.css.
  // Ogni clone imposta il proprio tema qui — nessun altro file va modificato.
  define("GLOBO_THEME", "blu-ks");
  $IP_OK = array();
  $IP_KO = array();
  
  /* ATTIVAZIONE MODULI */
  define("MOD_CALENDARIO", true); define("LIV_MOD_CALENDARIO", 2);
  // Moduli Bilancio disabilitati: non più necessari, ancora vecchio design (vedi docs/refactoring-notes.md, audit redesign)
  define("MOD_BILANCIO", false); define("LIV_MOD_BILANCIO", 1);
  define("MOD_BILANCIOV2", false); define("LIV_MOD_BILANCIOV2", 1);
  define("MOD_PROGETTI", true); define("LIV_MOD_PROGETTI", 2);
  define("MOD_PROGETTI_DOC", false); define("LIV_MOD_PROGETTI_DOC", 2);
  define("MOD_CONTATTI", true); define("LIV_MOD_CONTATTI", 2);
  define("MOD_NEWSLETTER", false); define("LIV_MOD_NEWSLETTER", 2);
  define("MOD_GLOBOMAIL", false); define("LIV_MOD_GLOBOMAIL", 2);
  define("MOD_GESTIONE_UT", true); define("LIV_MOD_GESTIONE_UT", 2);
  // Moduli Adhoc KosmoSol disabilitati anche in produzione per la 1.1: tool su DB separato "kosmosol",
  // non parte del core, orfani nella nuova UI (nessun link in home/header). Da riprendere/ridisegnare
  // in una release successiva. Vedi docs/refactoring-notes.md e docs/deploy-checklist.md
  define("MOD_ADHOC_KS", false); define("LIV_MOD_ADHOC_KS", 2); define("MYSQL_DB_KS", "kosmosol");
  define("MOD_ADHOC_KSH5S", false); define("LIV_MOD_ADHOC_KSH5S", 2); define("MYSQL_DB_KSH5S", "kosmosol");

  /* EXPIRE TOKEN COOKIE */
  define("DATE_EXPIRE_TOKEN", 180); //180 giorni
  /* ATTIVAZIONE AUTH CON TOKEN */
  define("AUTH_TOKEN", true);

  /* CONFIGURAZIONE MODULO CALENDARIO */
  define("CAL_ORA_INIZIO_GIO", 8);
  define("CAL_ORA_FINE_GIO", 22);
  define("CAL_MAX_DURATA_IMP", 6);
  define("CAL_FLAG_INVIO_IMP", 1); // 1 = tutti ricevono gli impegni di tutti, 2 = gli amministratori ricevono gli impegni di tutti mentre gli utenti solo quelli degli utenti
  
  /* CONFIGURAZIONE MODULO PROGETTI */
  define("PROC_ARC", false); // archiviazione progetti
  define("PROG_RIC_PROG_CON_NOTE", false);
  define("PROG_RIC_CONT", false);
  define("PROG_NOTIF", true); // se � true vengono inviate le notifiche ad ogni modifica/eliminazione nota o eliminazione progetto
  define("PROG_AGG_ARCH_ELIM_SOLO_ADMIN", false);
  define("PROG_MAX_NUM_DOC", 50);
  define("PROG_MAX_DIM_DOC_B", 2000000); define("PROG_MAX_DIM_DOC_MB", 2);
  define("PROG_DIR_DOC", "../GLOBO_File/www.kosmosol.it/progetti/"); 
  $PROG_DOC_FORM_OK = array("pdf", "doc", "xls", "ppt"); 
  
  /* CONFIGURAZIONE MODULO BILANCIO */
  define("BIL_ANNO_INIZIO", 2005);
  
  /* CONFIGURAZIONE MODULO BILANCIOV2 */
  define("BILV2_ANNO_INIZIO", 2012);
  define("BILV2_NUM_RIGHE", 10);
  define("BILV2_IVA", 22);
  define("BILV2_MOD_PAG", "Bonifico bancario");
  define("BILV2_BANCA", "Banca di Piacenza - Filiale di Fidenza (Parma)\nIBAN: IT45Q0515665730CC0460005362\nBIC/SWIFT: BCPCIT2P");
  define("BILV2_EMAIL", "amministrazione@kosmosol.it");
  define("BILV2_EMAIL_NOME", "Amministrazione - KosmoSol");
  define("BILV2_SOLL", true);
  define("BILV2_SOLL_GIO_INV", 2); // giorno della settimana in cui vengono inviati i solleciti (0 = domenica, ..., 6 = sabato)
  define("BILV2_SOLL_GIO_RIT", 7); // giorni di attesa dalla data di scadenza pagamento all'invio del primo sollecito
  
  $tmp = "Gentile Cliente,\n";
  $tmp .= "questa è una mail generata automaticamente dal nostro sistema di contabilità.\n\n";
  $tmp .= "Da un controllo Le segnaliamo che alla data odierna la fattura specificata in oggetto risulta scaduta e non ancora pagata, oppure, nel caso sia stato concordato un pagamento dilazionato, non è stata rispettata la scadenza.\n";
  $tmp .= "La invitiamo pertanto a verificare e nel caso a provvedere al più presto a regolarizzare la sua posizione.\n\n";
  //$tmp .= "Le ricordiamo che, in caso di mancato pagamento, KosmoSol si riserva il diritto di sospendere i servizi attualmente erogati applicando una penale per la riattivazione degli stessi.\n\n";
  $tmp .= "Qualora non avesse ricevuto la fattura che le è stata spedita via e-mail al presente indirizzo, può richiedercene copia rispondendo alla presente comunicazione o telefonando al numero 0524 597845.\n\n";
  $tmp .= "Nel caso in cui avesse già provveduto al pagamento, La preghiamo di non considerare il presente avviso.\n\n";
  $tmp .= "Distinti saluti.\n\n";
  $tmp .= "---\n\n";
  $tmp .= "Dear Customer,\n";
  $tmp .= "This email is automatically generated by our accounts system.\n\n";
  $tmp .= "Further to a check by our administration department, we would like to inform you that, as of today, the invoice specified in the subject of this e-mail results as due and unpaid or, if a deferred payment has been previously agreed, the deadline was not met.\n";
  $tmp .= "We kindly invite you to verify the situation and if there is a missed payment, to settle it as soon as possible.\n\n";
  //$tmp .= "We remind you that in case of missed payment, KosmoSol reserves the right to suspend the services currently provided applying a penalty for the restoring of the aforementioned services.\n\n";
  $tmp .= "If  you have not received the invoice, which was sent to this email address, you can request a copy by replying to this e-mail or by telephoning  the +39 0524 597845.\n\n";
  $tmp .= "If you have already settled the payment, please ignore this communication.\n\n";
  $tmp .= "Kind regards.\n\n";
  $tmp .= "---\nKosmoSol S.r.l. - 5stelle*\nTel. 0524 597845\nFax 0524 371043\nWeb www.kosmosol.it\nEmail info@kosmosol.it";

  define("BILV2_SOLL_TESTO_EMAIL", $tmp);
  define("BILV2_AG", true);
  $BILV2_UTENTE_RO = array(""); // utenti "read only", che possono accedere alle entrate non pagate in sola lettura, indipendentemente dal loro livello
  
  /* CONFIGURAZIONE MODULO NEWSLETTER */
  //define("NL_NUM_CONT_PER_PAG", 50);
  //define("NL_NOME", "KosmoSol"); // nome visualizzato nel campo "da" della newsletter e nella richiesta/conferma di iscrizione
  //define("NL_EMAIL", "newsletter@kosmosol.it"); // indirizzo a cui vengono inviate le notifiche e da cui viene inviata la newsletter
  //define("NL_NOTIFICHE", false); // se � true viene inviata una notifica ad ogni iscrizione/conferma/cancellazione
  //define("NL_FIRMA", "---\nKosmosol S.r.l.\nSoragna (Parma)\nTel.: 0524597845\nWeb: www.kosmosol.it\nE-mail: info@kosmosol.it");
  //define("NL_URL_CONF", "http://globo-ks.kosmosol.it/newsletter-conferma.php");
  //define("NL_URL_VIS", "http://globo-ks.kosmosol.it/newsletter-visualizza.php");
  //define("NL_URL_CANC", "http://globo-ks.kosmosol.it/newsletter-cancella.php");
  //define("NL_MAGENTO_URL_CANC", ""); 
  //define("NL_CONF_EXTRA_INFO_IT", "");
  //define("NL_CONF_EXTRA_INFO_EN", "");
  //define("NL_OLDEDITOR_PATH_UPLOAD_IMG", "src=\"/newsletter/upload/");
  //define("NL_OLDEDITOR_URL_UPLOAD_IMG", "src=\"http://www.kosmosol.it/newsletter/upload/");
  //define("NL_PATH_UPLOAD", "../../../../KosmoHome/newsletter/upload/"); // web server (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  //define("NL_URL_UPLOAD", "http://www.kosmosol.it/newsletter/upload/"); // web server (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  // define("NL_PATH_UPLOAD", "../../../../KosmoHome/newsletter/upload/"); // locale (--> elfinder\elfinder_2.0-rc1\php\connector.php)
  // define("NL_URL_UPLOAD", "http://localhost/KosmoSol/KosmoHome/newsletter/upload/"); // locale (--> elfinder\elfinder_2.0-rc1\php\connector.php)
?>