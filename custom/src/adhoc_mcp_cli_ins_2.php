<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

  // CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Handler puro: processa e
  // reindirizza con flash. Logica DB invariata.
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
  $flag_attivo = $_POST["flag_attivo"];
  $password = $_POST["password"];
  $descrizione = globo_converti_in_html($_POST["descrizione"]);
  $email = $_POST["email"];
  $id_listino = $_POST["id_listino"];
  $id_contatto_globo = $_POST["id_contatto_globo"];

  $dt = date("Y-m-d H:i:s");

  $db->beginTransaction();
  $stmt = $db->prepare("INSERT INTO cliente (id, flag_attivo, password, descrizione, email, id_listino, id_contatto_globo, dt_inserimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$id_cliente, $flag_attivo, $password, $descrizione, $email, $id_listino, $id_contatto_globo, $dt]);
  $num = $stmt->rowCount();
  $db->commit();

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Cliente inserito.'];
    header("Location: adhoc_mcp_cli_vis.php?id=" . urlencode($id_cliente));
  }
  else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Cliente non inserito.'];
    header("Location: adhoc_mcp_cli_ins_1.php");
  }
  exit;
