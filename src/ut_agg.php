<?php require("varie/common_1.php");

  $is_admin = ($_SESSION["ksglobo_livello_utente"] == 0);

  $nome_utente = $_POST["nome_utente"] ?? '';
  // Un utente non-admin può aggiornare SOLO il proprio profilo: si ignora il
  // nome_utente ricevuto e si forza quello di sessione (anti-takeover password).
  if (!$is_admin) $nome_utente = $_SESSION["ksglobo_nome_utente"];

  $password_1  = $_POST["password_1"] ?? '';
  $password_2  = $_POST["password_2"] ?? '';
  $livello     = $_POST["livello"]    ?? '';
  // Il livello NON è modificabile da un non-admin: lo si rilegge dal DB, così un
  // utente normale non può promuoversi (es. a livello 0) con un POST forgiato.
  if (!$is_admin) {
    $stmtLiv = $db->prepare("SELECT livello FROM utente WHERE nome_utente = ?");
    $stmtLiv->execute([$nome_utente]);
    $livello = $stmtLiv->fetchColumn();
  }
  $email       = isset($_POST["email"]) ? strtolower($_POST["email"]) : "";
  $flag_notifiche_email     = $_POST["flag_notifiche_email"]     ?? 0;
  $flag_notifiche_globomail = $_POST["flag_notifiche_globomail"] ?? 0;

  $db->beginTransaction();
  if (trim($password_1) != "") {
    $hashed_password = password_hash($password_1, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE utente SET password = ?, livello = ?, email = ?, flag_notifiche_email = ?, flag_notifiche_globomail = ? WHERE nome_utente = ?");
    $stmt->execute([$hashed_password, $livello, $email, $flag_notifiche_email, $flag_notifiche_globomail, $nome_utente]);
  } else {
    $stmt = $db->prepare("UPDATE utente SET livello = ?, email = ?, flag_notifiche_email = ?, flag_notifiche_globomail = ? WHERE nome_utente = ?");
    $stmt->execute([$livello, $email, $flag_notifiche_email, $flag_notifiche_globomail, $nome_utente]);
  }
  $num = $stmt->rowCount();
  $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Utente aggiornato.']
    : ['type' => 'err', 'text' => 'Utente non aggiornato.'];

  header("Location: ut_vis.php?nome_utente=" . urlencode($nome_utente));
  exit;
