<?php require("varie/common_1.php");

  $is_admin = ($_SESSION["ksglobo_livello_utente"] == 0);
  $nome_utente = $_POST["nome_utente"] ?? '';
  // Un utente non-admin può azzerare solo le PROPRIE sessioni browser.
  if (!$is_admin) $nome_utente = $_SESSION["ksglobo_nome_utente"];

  $stmt = $db->prepare("UPDATE utente SET auth_token_login = NULL, auth_token_create = NULL, auth_token_expire = NULL WHERE nome_utente = ?");
  $db->beginTransaction();
  $stmt->execute([$nome_utente]);
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Sessioni dei browser eliminate.']
    : ['type' => 'err', 'text' => 'Sessioni dei browser non eliminate.'];

  header("Location: ut_vis.php?nome_utente=" . urlencode($nome_utente));
  exit;
