<?php require("varie/common_1.php");

  // Inserimento utenti riservato all'amministratore globale (livello 0 = ks-globo).
  globo_richiedi_accesso($_SESSION["ksglobo_livello_utente"] == 0);

  $nome_utente = $_POST["nome_utente"] ?? '';
  $password    = $_POST["password"]    ?? '';
  $livello     = $_POST["livello"]     ?? '';
  $email       = isset($_POST["email"]) ? strtolower($_POST["email"]) : "";
  $flag_notifiche_email     = $_POST["flag_notifiche_email"]     ?? 0;
  $flag_notifiche_globomail = $_POST["flag_notifiche_globomail"] ?? 0;

  if (TUTTO_MINUSCOLO) $nome_utente = strtolower($nome_utente);

  $stmt_1 = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
  $stmt_1->execute([$nome_utente]);
  $num_1 = count($stmt_1->fetchAll());

  if ($num_1 > 0) {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Nome utente già presente — utente non inserito.'];
    header("Location: ut_lista.php");
    exit;
  }

  $hashed_password = password_hash($password, PASSWORD_BCRYPT);
  $stmt_2 = $db->prepare("INSERT INTO utente (nome_utente, password, livello, email, flag_notifiche_email, flag_notifiche_globomail) VALUES (?, ?, ?, ?, ?, ?)");
  $db->beginTransaction();
  $stmt_2->execute([$nome_utente, $hashed_password, $livello, $email, $flag_notifiche_email, $flag_notifiche_globomail]);
  $num_2 = $stmt_2->rowCount();
  $db->commit();

  if ($num_2 == 1) {
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Utente inserito.'];
    header("Location: ut_vis.php?nome_utente=" . urlencode($nome_utente));
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Utente non inserito.'];
    header("Location: ut_lista.php");
  }
  exit;
