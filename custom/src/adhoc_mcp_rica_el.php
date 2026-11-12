<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

  // CUSTOM-MCP: Fase B (redesign UI), 2026-07-13. Handler puro.
  $db = NULL;
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_MCP . ";charset=utf8mb4", MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }

  $id_ricarica_credito_cliente = $_POST["id_ricarica_credito_cliente"];

  $db->beginTransaction();
  $stmt = $db->prepare("DELETE FROM ricarica_credito_cliente WHERE id = ?");
  $stmt->execute([$id_ricarica_credito_cliente]);
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok', 'text' => 'Ricarica credito eliminata.']
    : ['type' => 'err', 'text' => 'Ricarica credito non eliminata.'];

  header("Location: adhoc_mcp_rica_lista.php");
  exit;
