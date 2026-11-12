<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? '';
  $id_nota     = $_POST["id_nota"]     ?? '';
  $tipo        = $_POST["tipo"]        ?? '';

  $stmtNota = $db->prepare("SELECT * FROM progetto_nota WHERE id = ?");
  $stmtNota->execute([$id_nota]);
  $res = $stmtNota->fetchAll();
  $utente_creazione       = $res[0]["utente_creazione"];
  $descrizione            = $res[0]["descrizione"];
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];

  $stmtProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res_p            = $stmtProg->fetchAll();
  $nome_progetto    = $res_p[0]["nome"];
  $desc_progetto    = $res_p[0]["descrizione"];

  $stmtDel = $db->prepare("DELETE FROM progetto_nota WHERE id = ?");
  $db->beginTransaction(); $stmtDel->execute([$id_nota]); $num = $stmtDel->rowCount(); $db->commit();

  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'nota', $id_nota, '');
    $stmtDelScad = $db->prepare("DELETE FROM scadenza WHERE id_progetto_nota = ?");
    $db->beginTransaction(); $stmtDelScad->execute([$id_nota]); $db->commit();

    if (PROG_NOTIF && $tipo != "step") {
      $oggetto = "nota progetto eliminata";
      $testo   = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha eliminato la seguene nota, creata o precedentemente modificata da te";
      $testo  .= "<br /><br />---<br /><br />nome progetto: $nome_progetto<br /><br />descrizione nota: " . globo_converti_acapo_da_html_in_spazi($descrizione);
      $ut_inv = [];
      foreach ([$utente_creazione, $utente_ultima_modifica] as $nome_utente) {
        $exclude = array_merge([$_SESSION["ksglobo_nome_utente"]], $ut_inv);
        $placeholders = implode(',', array_fill(0, count($exclude), '?'));
        $stmtNotif = $db->prepare("SELECT livello FROM utente WHERE nome_utente = ? AND nome_utente NOT IN ($placeholders)");
        $stmtNotif->execute(array_merge([$nome_utente], $exclude));
        $res_n = $stmtNotif->fetchAll();
        if (count($res_n) > 0 && $res_n[0]["livello"] <= $_SESSION["ksglobo_livello_utente"]) {
          globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $nome_utente, "");
          $ut_inv[] = $nome_utente;
        }
      }
    }

    $label = $tipo != "step" ? 'Nota progetto eliminata.' : 'Step progetto eliminato.';
    $_SESSION['flash'] = ['type' => 'ok', 'text' => $label];
  } else {
    $label = $tipo != "step" ? 'Nota progetto non eliminata.' : 'Step progetto non eliminato.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $label];
  }

  header("Location: prog_vis.php?id=" . (int)$id_progetto);
  exit;
