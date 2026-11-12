<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $data          = $_POST["data"]          ?? '';
  $ora           = $_POST["ora"]           ?? '';
  $durata        = $_POST["durata"]        ?? '';
  if ($durata == "24:00") $ora = "00:00";
  $descrizione   = globo_converti_in_html(globo_elimina_spazi_multipli($_POST["descrizione"] ?? ''));
  $responsabile  = $_POST["responsabile"]  ?? '';
  $flag_notifica = $_POST["flag_notifica"] ?? '';
  $cal_vis       = $_POST["cal_vis"]       ?? $_SESSION["ksglobo_nome_utente"];

  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $num = 0;
  $impegno_inseribile = true;

  $stmtIns = $db->prepare("INSERT INTO impegno (data, ora, durata, descrizione, responsabile, utente_ultima_modifica) VALUES (?, ?, ?, ?, ?, ?)");

  if ($responsabile == "amm") {
    $res_utenti_liv_1 = $db->query("SELECT nome_utente FROM utente WHERE livello = 1 ORDER BY nome_utente")->fetchAll();
    foreach ($res_utenti_liv_1 as $r) {
      $nome_utente = $r["nome_utente"];
      $impegno_inseribile = globo_controlla_se_impegno_inseribile($db, $data, $ora, $durata, $nome_utente, -1);
      if ($impegno_inseribile) {
        $db->beginTransaction();
        $stmtIns->execute([$data, $ora, $durata, $descrizione, $nome_utente, $_SESSION["ksglobo_nome_utente"]]);
        $num += $stmtIns->rowCount();
        $db->commit();
      } else break;
    }
  } else {
    $impegno_inseribile = globo_controlla_se_impegno_inseribile($db, $data, $ora, $durata, $responsabile, -1);
    if ($impegno_inseribile) {
      $db->beginTransaction();
      $stmtIns->execute([$data, $ora, $durata, $descrizione, $responsabile, $_SESSION["ksglobo_nome_utente"]]);
      $num += $stmtIns->rowCount();
      $db->commit();
    }
  }

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));

  if (!$impegno_inseribile) {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Impegno non inserito — controllare gli impegni già presenti.'];
    $_SESSION['form_prefill_imp'] = [
      'ora'          => $_POST["ora"]           ?? '',
      'durata'       => $_POST["durata"]        ?? '',
      'descrizione'  => $_POST["descrizione"]   ?? '',
      'responsabile' => $responsabile,
    ];
    header("Location: cal_imp_ins_1.php?data=" . urlencode($data) . "&cal_vis=" . urlencode($cal_vis));
  } else {
    $text = $num > 0 ? 'Impegno inserito.' : 'Impegno non inserito.';
    if ($num > 0) globo_log_attivita($db, 'crea', 'impegno', null, $descrizione);
    if ($num > 0 && $flag_notifica == 1) {
      $oggetto = "nuovo impegno - inserito da " . $_SESSION["ksglobo_nome_utente"];
      $testo   = "data: " . globo_converti_data_in_formato_ita($data) . "<br />";
      $testo  .= ($ora == "00:00") ? "ora: " . globo_converti_ora_da_dec(CAL_ORA_INIZIO_GIO) . "<br />" : "ora: $ora<br />";
      $testo  .= ($durata == "24:00") ? "durata: tutto il giorno<br />" : "durata: $durata<br />";
      $testo  .= "descrizione: $descrizione";
      if ($responsabile == "amm")        $esito = globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, 1, "", "");
      elseif ($responsabile == "tutti")  $esito = globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, 2, "", "");
      else                               $esito = globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $responsabile, "");
      $text .= ($esito["email"] > 0 || $esito["globomail"]) ? ' Notifica inviata.' : ' Notifica non inviata.';
    }
    $_SESSION['flash'] = ['type' => $num > 0 ? 'ok' : 'err', 'text' => $text];
    header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  }
  exit;
