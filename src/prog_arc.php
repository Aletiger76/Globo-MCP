<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? '';

  $stmtProg = $db->prepare("SELECT nome, descrizione, flag_archivio FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res = $stmtProg->fetchAll();
  $nome_progetto        = $res[0]["nome"];
  $descrizione_progetto = $res[0]["descrizione"];
  $flag_archivio        = $res[0]["flag_archivio"];

  $stmtUtC = $db->prepare("SELECT DISTINCT(utente_creazione) FROM progetto_nota WHERE id_progetto = ?");
  $stmtUtC->execute([$id_progetto]);
  $res_ut_creazione = $stmtUtC->fetchAll();

  $stmtUtM = $db->prepare("SELECT DISTINCT(utente_ultima_modifica) FROM progetto_nota WHERE id_progetto = ?");
  $stmtUtM->execute([$id_progetto]);
  $res_ut_ultima_modifica = $stmtUtM->fetchAll();

  $nuovo_flag    = $flag_archivio == 0 ? 1 : 0;
  $nuova_data    = $flag_archivio == 0 ? date("Y-m-d") : "0000-00-00";

  $stmtUpd = $db->prepare("UPDATE progetto SET flag_archivio = ?, data_archiviazione = ? WHERE id = ?");
  $db->beginTransaction();
  $stmtUpd->execute([$nuovo_flag, $nuova_data, $id_progetto]);
  $num = $stmtUpd->rowCount();
  $db->commit();

  if ($num == 1) {
    if ($nuovo_flag == 1) {
      $stmtDelStep = $db->prepare("DELETE FROM progetto_nota WHERE id_progetto = ? AND tipo = 'step'");
      $db->beginTransaction(); $stmtDelStep->execute([$id_progetto]); $db->commit();

      $stmtDelScad = $db->prepare("DELETE FROM scadenza WHERE id_progetto = ?");
      $db->beginTransaction(); $stmtDelScad->execute([$id_progetto]); $db->commit();
    }

    if (PROG_NOTIF) {
      $oggetto = $nuovo_flag == 1 ? "progetto archiviato" : "progetto ripristinato";
      $testo   = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha " . ($nuovo_flag == 1 ? "archiviato" : "ripristinato") . " il seguente progetto, che contiene note create o modificate da te";
      $testo  .= "<br /><br />---<br /><br />nome progetto: $nome_progetto<br /><br />descrizione progetto: " . globo_converti_acapo_da_html_in_spazi($descrizione_progetto);
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

    $text = $nuovo_flag == 1 ? 'Progetto archiviato.' : 'Progetto ripristinato.';
    $_SESSION['flash'] = ['type' => 'ok', 'text' => $text];
  } else {
    $text = $nuovo_flag == 1 ? 'Progetto non archiviato.' : 'Progetto non ripristinato.';
    $_SESSION['flash'] = ['type' => 'err', 'text' => $text];
  }

  header("Location: prog_vis.php?id=" . (int)$id_progetto);
  exit;
