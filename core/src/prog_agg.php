<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto                   = $_POST["id_progetto"]                   ?? '';
  $visibilita                    = $_POST["visibilita"]                    ?? '';
  $nome_progetto_old_nohtml      = $_POST["nome_progetto_old"]             ?? '';
  $nome_progetto_old             = globo_converti_in_html($nome_progetto_old_nohtml);
  $descrizione_progetto_old_nohtml = $_POST["descrizione_progetto_old"]   ?? '';
  $descrizione_progetto_old      = globo_converti_in_html($descrizione_progetto_old_nohtml);
  $nome_progetto                 = globo_converti_in_html($_POST["nome_progetto"]        ?? '');
  $descrizione_progetto          = globo_converti_in_html($_POST["descrizione_progetto"] ?? '');
  $id_progetto_procedura         = $_POST["id_progetto_procedura"]         ?? '';

  if (TUTTO_MINUSCOLO) {
    $nome_progetto        = strtolower($nome_progetto);
    $descrizione_progetto = strtolower($descrizione_progetto);
  }

  $stmtUpd = $db->prepare("UPDATE progetto SET id_progetto_procedura = ?, visibilita = ?, nome = ?, descrizione = ? WHERE id = ?");
  $db->beginTransaction();
  $stmtUpd->execute([$id_progetto_procedura, $visibilita, $nome_progetto, $descrizione_progetto, $id_progetto]);
  $num = $stmtUpd->rowCount();
  $db->commit();

  if ($num == 1) {
    if ($id_progetto_procedura == 0) {
      $stmtDelStep = $db->prepare("DELETE FROM progetto_procedura_step_chiuso WHERE id_progetto = ?");
      $db->beginTransaction();
      $stmtDelStep->execute([$id_progetto]);
      $db->commit();
    }

    if ($visibilita == 1) {
      $stmtUpdNote = $db->prepare("UPDATE progetto_nota SET visibilita = '1' WHERE id_progetto = ?");
      $db->beginTransaction();
      $stmtUpdNote->execute([$id_progetto]);
      $db->commit();
    }

    $stmtScad = $db->prepare("SELECT id, descrizione FROM scadenza WHERE id_progetto = ?");
    $stmtScad->execute([$id_progetto]);
    $res_1 = $stmtScad->fetchAll();
    $num_2 = 0;
    $stmtUpdScad = $db->prepare("UPDATE scadenza SET descrizione = ? WHERE id = ?");
    foreach ($res_1 as $r_1) {
      $id_scadenza         = $r_1["id"];
      $descrizione_scadenza = $r_1["descrizione"];
      $descrizione_scadenza = str_replace("cliente: $nome_progetto_old_nohtml", "progetto: $nome_progetto", $descrizione_scadenza);
      $descrizione_scadenza = str_replace("progetto: $nome_progetto_old_nohtml", "progetto: $nome_progetto", $descrizione_scadenza);
      $descrizione_scadenza = str_replace("descrizione: $descrizione_progetto_old_nohtml", "descrizione: $descrizione_progetto", $descrizione_scadenza);
      $descrizione_scadenza = str_replace("cliente: $nome_progetto_old", "progetto: $nome_progetto", $descrizione_scadenza);
      $descrizione_scadenza = str_replace("progetto: $nome_progetto_old", "progetto: $nome_progetto", $descrizione_scadenza);
      $descrizione_scadenza = str_replace("descrizione: $descrizione_progetto_old", "descrizione: $descrizione_progetto", $descrizione_scadenza);
      $db->beginTransaction();
      $stmtUpdScad->execute([$descrizione_scadenza, $id_scadenza]);
      $num_2 += $stmtUpdScad->rowCount();
      $db->commit();
    }

    if (PROG_NOTIF) {
      $oggetto = "progetto aggiornato";
      $testo   = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha aggiornato il seguente progetto, che contiene note create o modificate da te";
      $testo  .= "<br /><br />---<br /><br />nome progetto: $nome_progetto";
      if ($nome_progetto != $nome_progetto_old) $testo .= "<br /><br />nome progetto PRECEDENTE: $nome_progetto_old";
      $testo  .= "<br /><br />descrizione progetto: " . globo_converti_acapo_da_html_in_spazi($descrizione_progetto);
      if ($descrizione_progetto != $descrizione_progetto_old) $testo .= "<br /><br />descrizione progetto PRECEDENTE: " . globo_converti_acapo_da_html_in_spazi($descrizione_progetto_old);

      $ut_inv = [];
      foreach ([$db->prepare("SELECT DISTINCT(utente_creazione) as u FROM progetto_nota WHERE id_progetto = ?"), $db->prepare("SELECT DISTINCT(utente_ultima_modifica) as u FROM progetto_nota WHERE id_progetto = ?")] as $stmtUt) {
        $stmtUt->execute([$id_progetto]);
        foreach ($stmtUt->fetchAll() as $r_ut) {
          $nome_utente = $r_ut["u"];
          $exclude = array_merge([$_SESSION["ksglobo_nome_utente"]], $ut_inv);
          $placeholders = implode(',', array_fill(0, count($exclude), '?'));
          $stmtNotif = $db->prepare("SELECT livello FROM utente WHERE nome_utente = ? AND nome_utente NOT IN ($placeholders)");
          $stmtNotif->execute(array_merge([$nome_utente], $exclude));
          $res = $stmtNotif->fetchAll();
          $livello_utente = count($res) > 0 ? $res[0]["livello"] : 999;
          if ($livello_utente <= $_SESSION["ksglobo_livello_utente"]) {
            globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $nome_utente, "");
            $ut_inv[] = $nome_utente;
          }
        }
      }
    }

    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Progetto aggiornato.'];
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Progetto non aggiornato.'];
  }

  header("Location: prog_vis.php?id=" . (int)$id_progetto);
  exit;
