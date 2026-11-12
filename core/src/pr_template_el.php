<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? '';

  $stmtDel = $db->prepare("DELETE FROM progetto_template WHERE id = ?");
  $db->beginTransaction(); $stmtDel->execute([$id_progetto_template]); $num = $stmtDel->rowCount(); $db->commit();

  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'template', $id_progetto_template, '');
    $stmtDelStep = $db->prepare("DELETE FROM progetto_template_step WHERE id_progetto_template = ?");
    $db->beginTransaction(); $stmtDelStep->execute([$id_progetto_template]); $db->commit();
    $_SESSION['flash'] = ['type' => 'ok',  'text' => 'Template eliminato.'];
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Template non eliminato.'];
  }

  header("Location: pr_template_lista.php");
  exit;
