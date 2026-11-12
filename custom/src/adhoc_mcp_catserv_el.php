<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

  // CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Handler puro.
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

  $id_categoria_servizio = $_POST["id_categoria_servizio"];

  $stmt_ser = $db->prepare("SELECT id FROM servizio WHERE id_categoria_servizio = ?");
  $stmt_ser->execute([$id_categoria_servizio]);
  $num_ser = $stmt_ser->rowCount();

  if ($num_ser <= 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM categoria_servizio WHERE id = ?");
    $stmt->execute([$id_categoria_servizio]);
    $num = $stmt->rowCount();
    $db->commit();
  }
  else $num = 0;

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Categoria servizio eliminata.'];
    header("Location: adhoc_mcp_catserv_lista.php");
  }
  else {
    $text = 'Categoria servizio non eliminata.';
    if ($num_ser > 0) $text .= ' Sono presenti servizi associati a questa categoria.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
    header("Location: adhoc_mcp_catserv_vis.php?id=" . (int)$id_categoria_servizio);
  }
  exit;
