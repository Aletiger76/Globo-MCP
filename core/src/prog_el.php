<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? '';

  $stmtProg = $db->prepare("SELECT nome, descrizione, flag_archivio FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res = $stmtProg->fetchAll();
  $flag_archivio        = $res[0]["flag_archivio"];
  $nome_progetto        = $res[0]["nome"];
  $descrizione_progetto = $res[0]["descrizione"];

  $stmtUtC = $db->prepare("SELECT DISTINCT(utente_creazione) FROM progetto_nota WHERE id_progetto = ?");
  $stmtUtC->execute([$id_progetto]);
  $res_ut_creazione = $stmtUtC->fetchAll();

  $stmtUtM = $db->prepare("SELECT DISTINCT(utente_ultima_modifica) FROM progetto_nota WHERE id_progetto = ?");
  $stmtUtM->execute([$id_progetto]);
  $res_ut_ultima_modifica = $stmtUtM->fetchAll();

  $stmtDel = $db->prepare("DELETE FROM progetto WHERE id = ?");
  $db->beginTransaction();
  $stmtDel->execute([$id_progetto]);
  $num = $stmtDel->rowCount();
  $db->commit();

  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'progetto', $id_progetto, $nome_progetto);
    $stmtDelNote = $db->prepare("DELETE FROM progetto_nota WHERE id_progetto = ?");
    $db->beginTransaction(); $stmtDelNote->execute([$id_progetto]); $db->commit();

    $stmtDelScad = $db->prepare("DELETE FROM scadenza WHERE id_progetto = ?");
    $db->beginTransaction(); $stmtDelScad->execute([$id_progetto]); $db->commit();

    $stmtSelDoc = $db->prepare("SELECT file FROM progetto_documento WHERE id_progetto = ?");
    $stmtSelDoc->execute([$id_progetto]);
    foreach ($stmtSelDoc->fetchAll() as $r_doc) unlink(PROG_DIR_DOC . $r_doc["file"]);

    $stmtDelDoc = $db->prepare("DELETE FROM progetto_documento WHERE id_progetto = ?");
    $db->beginTransaction(); $stmtDelDoc->execute([$id_progetto]); $db->commit();

    if (PROG_NOTIF) {
      $oggetto = "progetto eliminato";
      $testo   = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha eliminato il seguente progetto, che conteneva note create o modificate da te";
      $testo  .= "<br /><br />---<br /><br />id progetto: $id_progetto<br /><br />nome progetto: $nome_progetto";
      $testo  .= "<br /><br />descrizione progetto: " . globo_converti_acapo_da_html_in_spazi($descrizione_progetto);
      $ut_inv = [];
      foreach (array_merge($res_ut_creazione, $res_ut_ultima_modifica) as $r_ut) {
        $nome_utente = $r_ut["utente_creazione"] ?? $r_ut["utente_ultima_modifica"];
        $exclude = array_merge([$_SESSION["ksglobo_nome_utente"]], $ut_inv);
        $placeholders = implode(',', array_fill(0, count($exclude), '?'));
        $stmtNotif = $db->prepare("SELECT livello FROM utente WHERE nome_utente = ? AND nome_utente NOT IN ($placeholders)");
        $stmtNotif->execute(array_merge([$nome_utente], $exclude));
        $res_n = $stmtNotif->fetchAll();
        $livello_utente = count($res_n) > 0 ? $res_n[0]["livello"] : 999;
        if ($livello_utente <= $_SESSION["ksglobo_livello_utente"]) {
          globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $nome_utente, "");
          $ut_inv[] = $nome_utente;
        }
      }
    }

    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Progetto eliminato.'];
    header("Location: " . ($flag_archivio == 1 ? 'prog_lista.php?a=1' : 'prog_lista.php'));
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Progetto non eliminato.'];
    header("Location: prog_lista.php");
  }
  exit;
