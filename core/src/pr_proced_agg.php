<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? '';
  $descrizione = $_POST["descrizione"] ?? '';
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtUpd = $db->prepare("UPDATE progetto_procedura SET descrizione = ? WHERE id = ?");
  $db->beginTransaction(); $stmtUpd->execute([$descrizione, $id_progetto_procedura]); $num = $stmtUpd->rowCount(); $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Procedura aggiornata.']
    : ['type' => 'err', 'text' => 'Procedura non aggiornata.'];

  header("Location: pr_proced_vis.php?id=" . (int)$id_progetto_procedura);
  exit;
