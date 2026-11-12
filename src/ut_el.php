<?php require("varie/common_1.php");

  // Eliminazione utenti riservata all'amministratore globale (livello 0 = ks-globo).
  globo_richiedi_accesso($_SESSION["ksglobo_livello_utente"] == 0);

  $nome_utente = $_POST["nome_utente"] ?? '';

  $stmt = $db->prepare("DELETE FROM utente WHERE nome_utente = ?");
  $db->beginTransaction();
  $stmt->execute([$nome_utente]);
  $num = $stmt->rowCount();
  $db->commit();
  if ($num == 1) globo_log_attivita($db, 'elimina', 'utente', null, $nome_utente);

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Utente eliminato.']
    : ['type' => 'err', 'text' => 'Utente non eliminato.'];

  header("Location: ut_lista.php");
  exit;
