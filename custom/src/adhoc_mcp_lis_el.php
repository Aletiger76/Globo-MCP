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

  $id_listino = $_POST["id_listino"];

  $stmt_cli = $db->prepare("SELECT COUNT(*) FROM cliente WHERE id_listino = ?");
  $stmt_cli->execute([$id_listino]);
  $num_cli = $stmt_cli->fetchColumn();

  if ($num_cli <= 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM listino WHERE id = ?");
    $stmt->execute([$id_listino]);
    $num = $stmt->rowCount();
    $db->commit();
  }
  else $num = 0;

  if ($num > 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM listino_servizio WHERE id_listino = ?");
    $stmt->execute([$id_listino]);
    $db->commit();
  }

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Listino eliminato.'];
    header("Location: adhoc_mcp_lis_lista.php");
  }
  else {
    $text = 'Listino non eliminato.';
    if ($num_cli > 0) $text .= ' Il listino è associato a uno o più clienti.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
    header("Location: adhoc_mcp_lis_vis.php?id=" . (int)$id_listino);
  }
  exit;
