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

  $id_acquisto_servizio = $_POST["id_acquisto_servizio"];
  $flag_stato = $_POST["flag_stato"];
  $note = globo_converti_in_html($_POST["note"]);

  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE acquisto_servizio SET flag_stato = ?, note = ? WHERE id = ?");
  $stmt->execute([$flag_stato, $note, $id_acquisto_servizio]);
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num > 0
    ? ['type' => 'ok', 'text' => 'Acquisto servizio aggiornato.']
    : ['type' => 'err', 'text' => 'Acquisto servizio non aggiornato.'];

  header("Location: adhoc_mcp_acq_vis.php?id=" . (int)$id_acquisto_servizio);
  exit;
