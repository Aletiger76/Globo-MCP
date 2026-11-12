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

  $id_servizio = $_POST["id_servizio"];

  $stmt_acq = $db->prepare("SELECT id FROM acquisto_servizio WHERE id_servizio = ?");
  $stmt_acq->execute([$id_servizio]);
  $num_acq = $stmt_acq->rowCount();

  if ($num_acq <= 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM servizio WHERE id = ?");
    $stmt->execute([$id_servizio]);
    $num = $stmt->rowCount();
    $db->commit();
  }
  else $num = 0;

  if ($num > 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM listino_servizio WHERE id_servizio = ?");
    $stmt->execute([$id_servizio]);
    $db->commit();
  }

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Servizio eliminato.'];
    header("Location: adhoc_mcp_serv_lista.php");
  }
  else {
    $text = 'Servizio non eliminato.';
    if ($num_acq > 0) $text .= ' Sono presenti acquisti associati a questo servizio.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
    header("Location: adhoc_mcp_serv_vis.php?id=" . (int)$id_servizio);
  }
  exit;
