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

  $db->beginTransaction();
  $stmt = $db->prepare("DELETE FROM acquisto_servizio WHERE id = ?");
  $stmt->execute([$id_acquisto_servizio]);
  $num = $stmt->rowCount();
  $db->commit();

  if ($num == 1) {
    $stmt_1 = $db->prepare("SELECT * FROM acquisto_servizio_documento WHERE id_acquisto_servizio = ?");
    $stmt_1->execute([$id_acquisto_servizio]);
    $res_1 = $stmt_1->fetchAll();
    $num_1 = count($res_1);

    $stmt_2 = $db->prepare("DELETE FROM acquisto_servizio_documento WHERE id = ?");

    for ($i = 0; $i < $num_1; $i++) {
      $id_acquisto_servizio_documento = $res_1[$i]["id"];
      $file = $res_1[$i]["file"];

      $db->beginTransaction();
      $stmt_2->execute([$id_acquisto_servizio_documento]);
      $num_2 = $stmt_2->rowCount();
      $db->commit();

      if ($num_2 > 0) unlink(MCP_DIR_DOC . $file);
    }

    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Acquisto servizio eliminato.'];
  }
  else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Acquisto servizio non eliminato.'];
  }

  header("Location: adhoc_mcp_acq_lista.php");
  exit;
