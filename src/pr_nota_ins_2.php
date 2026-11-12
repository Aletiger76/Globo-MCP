<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto        = $_POST["id_progetto"]        ?? '';
  $visibilita         = $_POST["visibilita"]         ?? '';
  $data_creazione     = globo_converti_data_in_formato_mysql($_POST["data_creazione"] ?? '');
  $descrizione_nota   = globo_converti_in_html($_POST["descrizione_nota"] ?? '');
  $flag_notifica_nota = $_POST["flag_notifica_nota"] ?? '';
  $flag_scadenza      = $_POST["flag_scadenza"]      ?? '';
  if ($flag_scadenza == 1) {
    $data_scadenza       = globo_converti_data_in_formato_mysql($_POST["data_scadenza"] ?? '');
    $responsabile_scadenza = $_POST["responsabile_scadenza"] ?? '';
  } else {
    $data_scadenza = ""; $responsabile_scadenza = "";
  }
  $flag_impegno = $_POST["flag_impegno"] ?? '';
  if ($flag_impegno == 1) {
    $data_impegno        = globo_converti_data_in_formato_mysql($_POST["data_impegno"] ?? '');
    $ora_impegno         = $_POST["ora_impegno"]         ?? '';
    $durata_impegno      = $_POST["durata_impegno"]      ?? '';
    if ($durata_impegno == "24:00") $ora_impegno = "00:00";
    $descrizione_impegno  = globo_converti_in_html($_POST["descrizione_impegno"] ?? '');
    $responsabile_impegno = $_POST["responsabile_impegno"] ?? '';
    $flag_notifica_impegno = $_POST["flag_notifica_impegno"] ?? '';
  } else {
    $data_impegno = ""; $ora_impegno = ""; $descrizione_impegno = ""; $responsabile_impegno = ""; $flag_notifica_impegno = 0;
  }

  if (TUTTO_MINUSCOLO) {
    $descrizione_nota    = strtolower($descrizione_nota);
    $descrizione_impegno = strtolower($descrizione_impegno);
  }

  $stmtInsNota = $db->prepare("INSERT INTO progetto_nota (id_progetto, tipo, visibilita, data_creazione, utente_creazione, descrizione, utente_ultima_modifica) VALUES (?, 'nota', ?, ?, ?, ?, ?)");
  $db->beginTransaction();
  $stmtInsNota->execute([$id_progetto, $visibilita, $data_creazione, $_SESSION["ksglobo_nome_utente"], $descrizione_nota, $_SESSION["ksglobo_nome_utente"]]);
  $num    = $stmtInsNota->rowCount();
  $id_nota = $db->lastInsertId();
  $db->commit();

  $text = 'Nota progetto inserita.';

  if ($num == 1) {
    if ($visibilita == 2) {
      $stmtUpdProg = $db->prepare("UPDATE progetto SET visibilita = '2' WHERE id = ?");
      $db->beginTransaction(); $stmtUpdProg->execute([$id_progetto]); $db->commit();
    }

    $stmtSelProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
    $stmtSelProg->execute([$id_progetto]);
    $res_prog        = $stmtSelProg->fetchAll();
    $nome_progetto   = $res_prog[0]["nome"];
    $desc_progetto   = globo_converti_acapo_da_html_in_spazi($res_prog[0]["descrizione"]);

    globo_log_attivita($db, 'crea', 'nota', $id_nota, $nome_progetto);

    if ($flag_notifica_nota == 1) {
      $oggetto = "nuova nota progetto - " . $_SESSION["ksglobo_nome_utente"];
      $testo   = "progetto: $nome_progetto<br />descrizione: $desc_progetto<br /><br />$descrizione_nota";
      globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, $visibilita, "", "");
    }

    if ($flag_scadenza == 1 && trim($data_scadenza) != "") {
      $desc_scad = "progetto: $nome_progetto<br />descrizione: $desc_progetto<br /><br />$descrizione_nota";
      $stmtInsScad = $db->prepare("INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica) VALUES (?, 'progetto', ?, '0', ?, ?, ?, ?, ?)");
      $db->beginTransaction();
      $stmtInsScad->execute([$data_scadenza, $visibilita, $id_progetto, $id_nota, $desc_scad, $responsabile_scadenza, $_SESSION["ksglobo_nome_utente"]]);
      if ($stmtInsScad->rowCount() == 0) $text .= ' Scadenza non inserita.';
      $db->commit();
    }

    if ($flag_impegno == 1) {
      $desc_imp_tmp = $descrizione_impegno . " - progetto: " . globo_converti_in_html($nome_progetto);
      $stmtInsImp   = $db->prepare("INSERT INTO impegno (data, ora, durata, descrizione, responsabile, utente_ultima_modifica) VALUES (?, ?, ?, ?, ?, ?)");
      $num_imp = 0; $impegno_inseribile = true;

      if ($responsabile_impegno == "amm") {
        $stmtSelAmm = $db->prepare("SELECT nome_utente FROM utente WHERE livello = 1 ORDER BY nome_utente");
        $stmtSelAmm->execute([]);
        foreach ($stmtSelAmm->fetchAll() as $r) {
          $nu = $r["nome_utente"];
          $impegno_inseribile = globo_controlla_se_impegno_inseribile($db, $data_impegno, $ora_impegno, $durata_impegno, $nu, -1);
          if ($impegno_inseribile) {
            $db->beginTransaction(); $stmtInsImp->execute([$data_impegno, $ora_impegno, $durata_impegno, $desc_imp_tmp, $nu, $_SESSION["ksglobo_nome_utente"]]); $num_imp += $stmtInsImp->rowCount(); $db->commit();
          }
        }
      } else {
        $impegno_inseribile = globo_controlla_se_impegno_inseribile($db, $data_impegno, $ora_impegno, $durata_impegno, $responsabile_impegno, -1);
        if ($impegno_inseribile) {
          $db->beginTransaction(); $stmtInsImp->execute([$data_impegno, $ora_impegno, $durata_impegno, $desc_imp_tmp, $responsabile_impegno, $_SESSION["ksglobo_nome_utente"]]); $num_imp += $stmtInsImp->rowCount(); $db->commit();
        }
      }

      if (!$impegno_inseribile) {
        $text .= ' Impegno non inserito — controllare gli impegni già presenti.';
      } elseif ($num_imp > 0 && $flag_notifica_impegno == 1) {
        $oggetto = "nuovo impegno - inserito da " . $_SESSION["ksglobo_nome_utente"];
        $testo   = "data: " . globo_converti_data_in_formato_ita($data_impegno) . "<br />";
        $testo  .= ($ora_impegno == "00:00") ? "ora: " . globo_converti_ora_da_dec(CAL_ORA_INIZIO_GIO) . "<br />" : "ora: $ora_impegno<br />";
        $testo  .= ($durata_impegno == "24:00") ? "durata: tutto il giorno<br />" : "durata: $durata_impegno<br /><br />";
        $testo  .= "descrizione:<br />$descrizione_impegno";
        if ($responsabile_impegno == "amm")       globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, 1, "", "");
        elseif ($responsabile_impegno == "tutti")  globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, 2, "", "");
        else                                       globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $responsabile_impegno, "");
      } elseif ($num_imp == 0 && $impegno_inseribile) {
        $text .= ' Impegno non inserito.';
      }
    }

    $_SESSION['flash'] = ['type' => 'ok', 'text' => $text];
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Nota progetto non inserita.'];
  }

  header("Location: prog_vis.php?id=" . (int)$id_progetto);
  exit;
