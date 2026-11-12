<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template       = $_POST["id_progetto_template"]       ?? '';
  $id_progetto_template_step  = $_POST["id_progetto_template_step"]  ?? '';
  $descrizione                = globo_converti_in_html($_POST["descrizione"] ?? '');
  $giorni_scadenza            = $_POST["giorni_scadenza"]            ?? '';
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtUpd = $db->prepare("UPDATE progetto_template_step SET descrizione = ?, giorni_scadenza = ? WHERE id = ?");
  $db->beginTransaction(); $stmtUpd->execute([$descrizione, $giorni_scadenza, $id_progetto_template_step]); $num = $stmtUpd->rowCount(); $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Step template aggiornato.']
    : ['type' => 'err', 'text' => 'Step template non aggiornato.'];

  if ($num == 1) {
    header("Location: pr_tem_step_vis.php?id_progetto_template=" . (int)$id_progetto_template . "&id_progetto_template_step=" . (int)$id_progetto_template_step);
  } else {
    header("Location: pr_template_vis.php?id=" . (int)$id_progetto_template);
  }
  exit;
