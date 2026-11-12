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

  $nome = globo_converti_in_html($_POST["nome"]);

  $db->beginTransaction();
  $stmt = $db->prepare("INSERT INTO categoria_servizio (nome) VALUES (?)");
  $stmt->execute([$nome]);
  $num = $stmt->rowCount();
  $id_categoria_servizio = $db->lastInsertId();
  $db->commit();

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Categoria servizio inserita.'];
    header("Location: adhoc_mcp_catserv_vis.php?id=" . (int)$id_categoria_servizio);
  }
  else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Categoria servizio non inserita.'];
    header("Location: adhoc_mcp_catserv_ins_1.php");
  }
  exit;
