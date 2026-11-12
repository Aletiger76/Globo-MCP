<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

  // CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Handler puro: processa e
  // reindirizza con flash (pattern core, es. prog_el.php) invece di
  // renderizzare una propria pagina di risultato. Logica DB invariata.
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

  $stmt_acq = $db->prepare("SELECT id FROM acquisto_servizio WHERE id_cliente = ?");
  $stmt_acq->execute([$id_cliente]);
  $num_acq = $stmt_acq->rowCount();

  $stmt_rica = $db->prepare("SELECT id FROM ricarica_credito_cliente WHERE id_cliente = ?");
  $stmt_rica->execute([$id_cliente]);
  $num_rica = $stmt_rica->rowCount();

  if ($num_acq <= 0 && $num_rica <= 0) {
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM cliente WHERE id = ?");
    $stmt->execute([$id_cliente]);
    $num = $stmt->rowCount();
    $db->commit();
  }
  else $num = 0;

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Cliente eliminato.'];
    header("Location: adhoc_mcp_cli_lista.php");
  }
  else {
    $text = 'Cliente non eliminato.';
    if ($num_acq > 0 || $num_rica > 0) $text .= ' Sono presenti acquisti di servizi o ricariche credito associate a questo cliente.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
    header("Location: adhoc_mcp_cli_vis.php?id=" . urlencode($id_cliente));
  }
  exit;
