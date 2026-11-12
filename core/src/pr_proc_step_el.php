<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura      = $_POST["id_progetto_procedura"]      ?? '';
  $id_progetto_procedura_step = $_POST["id_progetto_procedura_step"] ?? '';

  $stmtDel = $db->prepare("DELETE FROM progetto_procedura_step WHERE id = ?");
  $db->beginTransaction(); $stmtDel->execute([$id_progetto_procedura_step]); $num = $stmtDel->rowCount(); $db->commit();

  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'procedura_step', $id_progetto_procedura_step, '');
    $stmtDelC = $db->prepare("DELETE FROM progetto_procedura_step_chiuso WHERE id_progetto_procedura_step = ?");
    $db->beginTransaction(); $stmtDelC->execute([$id_progetto_procedura_step]); $db->commit();
    $stmtDelN = $db->prepare("DELETE FROM progetto_procedura_step_non_attivo WHERE id_progetto_procedura_step = ?");
    $db->beginTransaction(); $stmtDelN->execute([$id_progetto_procedura_step]); $db->commit();
    $_SESSION['flash'] = ['type' => 'ok',  'text' => 'Step procedura eliminato.'];
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Step procedura non eliminato.'];
  }

  header("Location: pr_proced_vis.php?id=" . (int)$id_progetto_procedura);
  exit;
