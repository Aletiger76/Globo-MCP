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
  $nome = globo_converti_in_html($_POST["nome"]);
  $prezzo = $_POST["prezzo"];

  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE listino SET nome = ? WHERE id = ?");
  $stmt->execute([$nome, $id_listino]);
  $num_1 = $stmt->rowCount();
  $db->commit();

  $res_serv = $db->query("SELECT id FROM servizio ORDER BY id_categoria_servizio, id")->fetchAll();
  $num_serv = count($res_serv);

  $num_2 = 0;

  $stmt_chk = $db->prepare("SELECT COUNT(*) FROM listino_servizio WHERE id_listino = ? AND id_servizio = ?");
  $stmt_ins = $db->prepare("INSERT INTO listino_servizio (id_listino, id_servizio, prezzo) VALUES (?, ?, ?)");
  $stmt_upd = $db->prepare("UPDATE listino_servizio SET prezzo = ? WHERE id_listino = ? AND id_servizio = ?");

  for ($i = 0; $i < $num_serv; $i++) {
    $id_servizio = $res_serv[$i]["id"];
    $prezzo_servizio = $prezzo[$id_servizio] ?? null;

    if (is_numeric($prezzo_servizio)) {
      $stmt_chk->execute([$id_listino, $id_servizio]);
      $num = $stmt_chk->fetchColumn();

      $db->beginTransaction();
      if ($num <= 0) {
        $stmt_ins->execute([$id_listino, $id_servizio, $prezzo_servizio]);
        $num_2 += $stmt_ins->rowCount();
      }
      else {
        $stmt_upd->execute([$prezzo_servizio, $id_listino, $id_servizio]);
        $num_2 += $stmt_upd->rowCount();
      }
      $db->commit();
    }
  }

  $_SESSION['flash'] = ($num_1 > 0 || $num_2 > 0)
    ? ['type' => 'ok', 'text' => 'Listino aggiornato.']
    : ['type' => 'err', 'text' => 'Listino non aggiornato.'];

  header("Location: adhoc_mcp_lis_vis.php?id=" . (int)$id_listino);
  exit;
