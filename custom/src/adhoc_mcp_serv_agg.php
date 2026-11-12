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
  $id_categoria_servizio = $_POST["id_categoria_servizio"];
  $flag_attivo = $_POST["flag_attivo"];
  $nome = globo_converti_in_html($_POST["nome"]);
  $livello = $_POST["livello"];
  $flag_acq_dir = $_POST["flag_acq_dir"];

  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE servizio SET id_categoria_servizio = ?, flag_attivo = ?, nome = ?, livello = ?, flag_acq_dir = ? WHERE id = ?");
  $stmt->execute([$id_categoria_servizio, $flag_attivo, $nome, $livello, $flag_acq_dir, $id_servizio]);
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num > 0
    ? ['type' => 'ok', 'text' => 'Servizio aggiornato.']
    : ['type' => 'err', 'text' => 'Servizio non aggiornato.'];

  header("Location: adhoc_mcp_serv_vis.php?id=" . (int)$id_servizio);
  exit;
