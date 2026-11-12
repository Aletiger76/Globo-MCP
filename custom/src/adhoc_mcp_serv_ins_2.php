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

  $id = $_POST["id"];
  $id_categoria_servizio = $_POST["id_categoria_servizio"];
  $flag_attivo = $_POST["flag_attivo"];
  $nome = globo_converti_in_html($_POST["nome"]);
  $livello = $_POST["livello"];
  $flag_acq_dir = $_POST["flag_acq_dir"];

  $db->beginTransaction();
  $stmt = $db->prepare("INSERT INTO servizio (id, id_categoria_servizio, flag_attivo, nome, livello, flag_acq_dir) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$id, $id_categoria_servizio, $flag_attivo, $nome, $livello, $flag_acq_dir]);
  $num = $stmt->rowCount();
  $db->commit();

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Servizio inserito.'];
    header("Location: adhoc_mcp_serv_vis.php?id=" . (int)$id);
  }
  else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Servizio non inserito.'];
    header("Location: adhoc_mcp_serv_ins_1.php");
  }
  exit;
