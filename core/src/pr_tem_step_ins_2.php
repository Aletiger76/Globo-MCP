<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? '';
  $descrizione          = globo_converti_in_html($_POST["descrizione"] ?? '');
  $giorni_scadenza      = $_POST["giorni_scadenza"]      ?? '';
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtIns = $db->prepare("INSERT INTO progetto_template_step (id_progetto_template, descrizione, giorni_scadenza) VALUES (?, ?, ?)");
  $db->beginTransaction(); $stmtIns->execute([$id_progetto_template, $descrizione, $giorni_scadenza]); $num = $stmtIns->rowCount(); $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Step template inserito.']
    : ['type' => 'err', 'text' => 'Step template non inserito.'];

  header("Location: pr_template_vis.php?id=" . (int)$id_progetto_template);
  exit;
