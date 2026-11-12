<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? '';

  $stmtChk = $db->prepare("SELECT id FROM progetto WHERE id_progetto_procedura = ?");
  $stmtChk->execute([$id_progetto_procedura]);

  if (count($stmtChk->fetchAll()) > 0) {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Procedura non eliminata — esistono progetti collegati.'];
  } else {
    $stmtDel = $db->prepare("DELETE FROM progetto_procedura WHERE id = ?");
    $db->beginTransaction(); $stmtDel->execute([$id_progetto_procedura]); $num = $stmtDel->rowCount(); $db->commit();
    if ($num == 1) {
      globo_log_attivita($db, 'elimina', 'procedura', $id_progetto_procedura, '');
      $stmtDelStep = $db->prepare("DELETE FROM progetto_procedura_step WHERE id_progetto_procedura = ?");
      $db->beginTransaction(); $stmtDelStep->execute([$id_progetto_procedura]); $db->commit();
      $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Procedura eliminata.'];
    } else {
      $_SESSION['flash'] = ['type' => 'err', 'text' => 'Procedura non eliminata.'];
    }
  }

  header("Location: pr_proced_lista.php");
  exit;
