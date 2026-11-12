<?php
  require("varie/common_1.php");
  // CUSTOM-MCP: fix sicurezza (2026-07-10) - l'originale non aveva NESSUN controllo
  // di accesso (a differenza di ogni altra pagina del modulo) ne' sanitizzava
  // $_GET["file"], permettendo path traversal (es. file=../../../etc/passwd)
  // per leggere file arbitrari sul server. Comportamento normale invariato.
  if (!MOD_ADHOC_MCP || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_MCP) { header("Location: index.php"); exit(); }

  if (isset($_GET["file"]) && isset($_GET["tipo"])) {
    $file = basename($_GET["file"]); // basename: elimina qualsiasi componente di percorso
    $tipo = $_GET["tipo"]; // $tipo = "1" --> documenti progetti

    if ($tipo == 1) {
      $file = MCP_DIR_DOC . $file;
      if (is_file($file)) {
        $nome_file = basename($file);
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
  }

  require("varie/common_2.php");
?>
