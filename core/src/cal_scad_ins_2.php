<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $data            = $_POST["data"]            ?? '';
  $descrizione     = globo_converti_in_html(globo_elimina_spazi_multipli($_POST["descrizione"] ?? ''));
  $responsabile    = $_POST["responsabile"]    ?? '';
  $visibilita      = $_POST["visibilita"]      ?? '';
  $flag_ricorrenza = $_POST["flag_ricorrenza"] ?? '';
  $num_ricorrenze  = (int)($_POST["num_ricorrenze"] ?? 0);
  $cal_vis         = $_POST["cal_vis"]         ?? $_SESSION["ksglobo_nome_utente"];

  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $anno        = date("Y", strtotime($data));
  $mese        = date("m", strtotime($data));
  $giorno      = date("d", strtotime($data));
  $anno_scad   = (int)$anno;
  $mese_scad   = (int)$mese;
  $giorno_scad = (int)$giorno;

  $stmtIns = $db->prepare("INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica) VALUES (?, 'memo', ?, '0', '0', '0', ?, ?, ?)");

  if ($flag_ricorrenza == "1") {
    $data_scad = "$anno_scad-$mese_scad-$giorno_scad";
    $db->beginTransaction();
    $stmtIns->execute([$data_scad, $visibilita, $descrizione, $responsabile, $_SESSION["ksglobo_nome_utente"]]);
    $num = $stmtIns->rowCount();
    $db->commit();
    $text = $num == 1 ? 'Scadenza inserita.' : 'Scadenza non inserita.';
    $type = $num == 1 ? 'ok' : 'err';
  } elseif ($flag_ricorrenza == "2") {
    $num_tot = 0;
    for ($i = 0; $i < $num_ricorrenze; $i++) {
      $data_scad = "$anno_scad-$mese_scad-$giorno_scad";
      $db->beginTransaction();
      $stmtIns->execute([$data_scad, $visibilita, $descrizione, $responsabile, $_SESSION["ksglobo_nome_utente"]]);
      $num_tot += $stmtIns->rowCount();
      $db->commit();
      $mese_scad++;
      if ($mese_scad > 12) { $mese_scad = 1; $anno_scad++; }
    }
    $text = $num_tot > 0 ? "Scadenza inserita per $num_tot mesi." : 'Scadenza non inserita.';
    $type = $num_tot > 0 ? 'ok' : 'err';
  } elseif ($flag_ricorrenza == "3") {
    $num_tot = 0;
    for ($i = 0; $i < $num_ricorrenze; $i++) {
      $data_scad = "$anno_scad-$mese_scad-$giorno_scad";
      $db->beginTransaction();
      $stmtIns->execute([$data_scad, $visibilita, $descrizione, $responsabile, $_SESSION["ksglobo_nome_utente"]]);
      $num_tot += $stmtIns->rowCount();
      $db->commit();
      $anno_scad++;
    }
    $text = $num_tot > 0 ? "Scadenza inserita per $num_tot anni." : 'Scadenza non inserita.';
    $type = $num_tot > 0 ? 'ok' : 'err';
  } else {
    $text = 'Scadenza non inserita.';
    $type = 'err';
  }

  $_SESSION['flash'] = ['type' => $type, 'text' => $text];
  header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  exit;
