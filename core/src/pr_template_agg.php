<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? '';
  $descrizione          = $_POST["descrizione"]          ?? '';
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtUpd = $db->prepare("UPDATE progetto_template SET descrizione = ? WHERE id = ?");
  $db->beginTransaction(); $stmtUpd->execute([$descrizione, $id_progetto_template]); $num = $stmtUpd->rowCount(); $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Template aggiornato.']
    : ['type' => 'err', 'text' => 'Template non aggiornato.'];

  header("Location: pr_template_vis.php?id=" . (int)$id_progetto_template);
  exit;
