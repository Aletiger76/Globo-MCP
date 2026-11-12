<?php
  require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);
  
  if (isset($_GET["file"]) && isset($_GET["tipo"])) {
    $file = $_GET["file"];
    $tipo = $_GET["tipo"]; // $tipo = "1" --> documenti progetti
    
    if ($tipo == "1") {
      if ($tipo == "1") {
        $file = PROG_DIR_DOC . $file;
        $nome_file = PREFISSO_FILE . "_progetti_" . basename($file);
      }
      
      $dimensione_file = filesize($file);

      header('Expires: 0');
      header('Last-Modified: ' .
      gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
      header('Pragma: public');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Accept-Ranges: bytes');
      header('Content-Length: ' . $dimensione_file);
      header('Content-Type: Application/octet-stream');
      header('Content-Disposition: attachment; filename="' . $nome_file . '"');
      header('Connection: close');
      
      readfile($file);
    }
  }
  
  require("varie/common_2.php"); 
?>