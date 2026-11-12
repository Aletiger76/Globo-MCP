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

  $id_cliente = $_POST["id_cliente"];
  $importo = $_POST["importo"];
  $note = globo_converti_in_html($_POST["note"]);

  $data = date("Y-m-d");
  $ora = date("H:i:s");

  $db->beginTransaction();
  $stmt = $db->prepare("INSERT INTO ricarica_credito_cliente (data, ora, id_cliente, importo, note) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$data, $ora, $id_cliente, $importo, $note]);
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok', 'text' => 'Ricarica credito inserita.']
    : ['type' => 'err', 'text' => 'Ricarica credito non inserita.'];

  header("Location: adhoc_mcp_rica_lista.php");
  exit;
