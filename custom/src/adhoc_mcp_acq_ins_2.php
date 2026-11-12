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

  $flag_stato = $_POST["flag_stato"];
  $id_cliente = $_POST["id_cliente"];
  $id_servizio = $_POST["id_servizio"];
  $prezzo_servizio = $_POST["prezzo_servizio"];
  $dati = globo_converti_in_html($_POST["dati"]);
  $note = globo_converti_in_html($_POST["note"]);

  $stmt_rica = $db->prepare("SELECT SUM(importo) as tot_rica FROM ricarica_credito_cliente WHERE id_cliente = ?");
  $stmt_rica->execute([$id_cliente]);
  $tot_rica = $stmt_rica->fetchColumn();
  $stmt_acq = $db->prepare("SELECT SUM(prezzo_servizio) as tot_acq FROM acquisto_servizio WHERE id_cliente = ?");
  $stmt_acq->execute([$id_cliente]);
  $tot_acq = $stmt_acq->fetchColumn();
  $credito = $tot_rica - $tot_acq;

  if ($credito < $prezzo_servizio) $num = 0;
  else {
    $data = date("Y-m-d");
    $ora = date("H:i:s");

    $stmt_ser = $db->prepare("SELECT nome FROM servizio WHERE id = ?");
    $stmt_ser->execute([$id_servizio]);
    $nome_servizio = $stmt_ser->fetchColumn();

    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO acquisto_servizio (data, ora, id_cliente, id_servizio, nome_servizio, prezzo_servizio, dati, flag_stato, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data, $ora, $id_cliente, $id_servizio, $nome_servizio, $prezzo_servizio, $dati, $flag_stato, $note]);
    $num = $stmt->rowCount();
    // lastInsertId() va letto PRIMA del commit: con questo driver PDO/mysqlnd
    // commit() azzera il valore (verificato empiricamente in dev).
    $id_acquisto_servizio = $db->lastInsertId();
    $db->commit();
  }

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Acquisto servizio inserito.'];
    header("Location: adhoc_mcp_acq_vis.php?id=" . (int)$id_acquisto_servizio);
  }
  else {
    $text = 'Acquisto servizio non inserito.';
    if ($credito < $prezzo_servizio) $text .= ' Credito cliente insufficiente.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
    header("Location: adhoc_mcp_acq_ins_1.php");
  }
  exit;
