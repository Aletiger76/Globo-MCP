<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template      = $_POST["id_progetto_template"]      ?? '';
  $id_progetto_template_step = $_POST["id_progetto_template_step"] ?? '';

  $stmtDel = $db->prepare("DELETE FROM progetto_template_step WHERE id = ?");
  $db->beginTransaction(); $stmtDel->execute([$id_progetto_template_step]); $num = $stmtDel->rowCount(); $db->commit();
  if ($num == 1) globo_log_attivita($db, 'elimina', 'template_step', $id_progetto_template_step, '');

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Step template eliminato.']
    : ['type' => 'err', 'text' => 'Step template non eliminato.'];

  header("Location: pr_template_vis.php?id=" . (int)$id_progetto_template);
  exit;
