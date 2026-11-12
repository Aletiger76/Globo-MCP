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

  $nome = globo_converti_in_html($_POST["nome"]);
  $prezzo = $_POST["prezzo"];

  $db->beginTransaction();
  $stmt = $db->prepare("INSERT INTO listino (nome) VALUES (?)");
  $stmt->execute([$nome]);
  $num = $stmt->rowCount();
  $db->commit();

  if ($num > 0) {
    // stesso pattern del sorgente originale (SELECT ... ORDER BY id DESC LIMIT 1
    // invece di lastInsertId): comportamento preservato identico.
    $id_listino = $db->query("SELECT id FROM listino ORDER BY id DESC LIMIT 1")->fetchColumn();

    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM listino_servizio WHERE id_listino = ?");
    $stmt->execute([$id_listino]);
    $db->commit();

    $res_serv = $db->query("SELECT id FROM servizio ORDER BY id_categoria_servizio, id")->fetchAll();
    $num_serv = count($res_serv);

    $stmt_ins = $db->prepare("INSERT INTO listino_servizio (id_listino, id_servizio, prezzo) VALUES (?, ?, ?)");

    for ($i = 0; $i < $num_serv; $i++) {
      $id_servizio = $res_serv[$i]["id"];
      $prezzo_servizio = $prezzo[$id_servizio] ?? null;

      if (is_numeric($prezzo_servizio) && $prezzo_servizio > 0) {
        $db->beginTransaction();
        $stmt_ins->execute([$id_listino, $id_servizio, $prezzo_servizio]);
        $db->commit();
      }
    }

    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Listino inserito.'];
    header("Location: adhoc_mcp_lis_vis.php?id=" . (int)$id_listino);
  }
  else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Listino non inserito.'];
    header("Location: adhoc_mcp_lis_ins_1.php");
  }
  exit;
