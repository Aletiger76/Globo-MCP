<?php
  /* DEV overlay MCP — selettore del file di config.
   *
   * Differenza rispetto allo snapshot: su host locale carica il config di MCP
   * (www_mcprotection_it.php). Il resto e' identico allo snapshot/core. In
   * produzione, il dominio sceglie il config via host. Vedi README.md.
   */
  if ($_SERVER["HTTP_HOST"] == "localhost" ||
    $_SERVER["HTTP_HOST"] == "localhost:8100" ||
    substr_count($_SERVER["HTTP_HOST"], "127.") > 0 ||
    substr_count($_SERVER["HTTP_HOST"], "192.") > 0) {
    $file_config = "www_mcprotection_it.php";
  }
  else {
    $file_config = str_replace(".", "_", $_SERVER["HTTP_HOST"]) . ".php";
  }

  require($file_config);
  require("config_smtp.php");

  if (!isset($IP_OK)) $IP_OK = array();
  if (!isset($IP_KO)) $IP_KO = array();

  /* MODULI STANDARD */
  if (!defined("MOD_CALENDARIO")) define("MOD_CALENDARIO", false); if (!defined("LIV_MOD_CALENDARIO")) define("LIV_MOD_CALENDARIO", 0);
  if (!defined("MOD_BILANCIO")) define("MOD_BILANCIO", false); if (!defined("LIV_MOD_BILANCIO")) define("LIV_MOD_BILANCIO", 0);
  if (!defined("MOD_BILANCIOV2")) define("MOD_BILANCIOV2", false); if (!defined("LIV_MOD_BILANCIOV2")) define("LIV_MOD_BILANCIOV2", 0);
  if (!defined("MOD_PROGETTI")) define("MOD_PROGETTI", false); if (!defined("LIV_MOD_PROGETTI")) define("LIV_MOD_PROGETTI", 0);
  if (!defined("MOD_PROGETTI_DOC")) define("MOD_PROGETTI_DOC", false); if (!defined("LIV_MOD_PROGETTI_DOC")) define("LIV_MOD_PROGETTI_DOC", 0);
  if (!defined("MOD_CONTATTI")) define("MOD_CONTATTI", false); if (!defined("LIV_MOD_CONTATTI")) define("LIV_MOD_CONTATTI", 0);
  if (!defined("MOD_NEWSLETTER")) define("MOD_NEWSLETTER", false); if (!defined("LIV_MOD_NEWSLETTER")) define("LIV_MOD_NEWSLETTER", 0);
  if (!defined("MOD_GLOBOMAIL")) define("MOD_GLOBOMAIL", false); if (!defined("LIV_MOD_GLOBOMAIL")) define("LIV_MOD_GLOBOMAIL", 0);
  if (!defined("MOD_GESTIONE_UT")) define("MOD_GESTIONE_UT", false); if (!defined("LIV_MOD_GESTIONE_UT")) define("LIV_MOD_GESTIONE_UT", 0);

  // Numero di versione mostrato in fondo alla card di login: "dev" qui (mai
  // sostituito, dev-overlay/ non viene mai incluso nel pacchetto di deploy).
  if (!defined("GLOBO_VERSION")) define("GLOBO_VERSION", "dev");

  /* MODULI AD HOC */
  if (!defined("MOD_ADHOC_POST")) define("MOD_ADHOC_POST", false); if (!defined("LIV_MOD_ADHOC_POST")) define("LIV_MOD_ADHOC_POST", 0);
  if (!defined("MOD_ADHOC_KS")) define("MOD_ADHOC_KS", false); if (!defined("LIV_MOD_ADHOC_KS")) define("LIV_MOD_ADHOC_KS", 0);
  if (!defined("MOD_ADHOC_KSH5S")) define("MOD_ADHOC_KSH5S", false); if (!defined("LIV_MOD_ADHOC_KSH5S")) define("LIV_MOD_ADHOC_KSH5S", 0);
  if (!defined("MOD_ADHOC_SDS")) define("MOD_ADHOC_SDS", false); if (!defined("LIV_MOD_ADHOC_SDS")) define("LIV_MOD_ADHOC_SDS", 0);
  if (!defined("MOD_ADHOC_MCP")) define("MOD_ADHOC_MCP", false); if (!defined("LIV_MOD_ADHOC_MCP")) define("LIV_MOD_ADHOC_MCP", 0);
  if (!defined("MOD_ADHOC_VC")) define("MOD_ADHOC_VC", false); if (!defined("LIV_MOD_ADHOC_VC")) define("LIV_MOD_ADHOC_VC", 0);
  if (!defined("MOD_ADHOC_GIA")) define("MOD_ADHOC_GIA", false); if (!defined("LIV_MOD_ADHOC_GIA")) define("LIV_MOD_ADHOC_GIA", 0);
  if (!defined("MOD_ADHOC_LG")) define("MOD_ADHOC_LG", false); if (!defined("LIV_MOD_ADHOC_LG")) define("LIV_MOD_ADHOC_LG", 0);
  if (!defined("MOD_ADHOC_IMDC")) define("MOD_ADHOC_IMDC", false); if (!defined("LIV_MOD_ADHOC_IMDC")) define("LIV_MOD_ADHOC_IMDC", 0);
  if (!defined("MOD_ADHOC_PHEX")) define("MOD_ADHOC_PHEX", false); if (!defined("LIV_MOD_ADHOC_PHEX")) define("LIV_MOD_ADHOC_PHEX", 0);
  if (!defined("MOD_ADHOC_MLB")) define("MOD_ADHOC_MLB", false); if (!defined("LIV_MOD_ADHOC_MLB")) define("LIV_MOD_ADHOC_MLB", 0);
  if (!defined("MOD_ADHOC_FONT")) define("MOD_ADHOC_FONT", false); if (!defined("LIV_MOD_ADHOC_FONT")) define("LIV_MOD_ADHOC_FONT", 0);
?>
