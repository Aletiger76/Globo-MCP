<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $descrizione = globo_converti_in_html($_POST["descrizione"] ?? '');
  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtIns = $db->prepare("INSERT INTO progetto_procedura (descrizione) VALUES (?)");
  $db->beginTransaction(); $stmtIns->execute([$descrizione]); $num = $stmtIns->rowCount(); $id_progetto_procedura = $db->lastInsertId(); $db->commit();

  if ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok',  'text' => 'Procedura inserita.'];
    header("Location: pr_proced_vis.php?id=" . (int)$id_progetto_procedura);
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Procedura non inserita.'];
    header("Location: pr_proced_lista.php");
  }
  exit;
