<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? '';
  $descrizione           = globo_converti_in_html($_POST["descrizione"] ?? '');
  $data_scadenza         = globo_converti_data_in_formato_mysql($_POST["data_scadenza"] ?? '');
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtIns = $db->prepare("INSERT INTO progetto_procedura_step (id_progetto_procedura, descrizione, data_scadenza) VALUES (?, ?, ?)");
  $db->beginTransaction();
  try { $stmtIns->execute([$id_progetto_procedura, $descrizione, $data_scadenza]); $num = $stmtIns->rowCount(); }
  catch (PDOException $e) { $num = 0; }
  $db->commit();

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Step procedura inserito.']
    : ['type' => 'err', 'text' => 'Step procedura non inserito.'];

  header("Location: pr_proced_vis.php?id=" . (int)$id_progetto_procedura);
  exit;
