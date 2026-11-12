<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_scadenza  = $_POST["id_scadenza"]  ?? '';
  $data         = globo_converti_data_in_formato_mysql($_POST["data"] ?? '');
  $descrizione  = globo_converti_in_html(globo_elimina_spazi_multipli($_POST["descrizione"] ?? ''));
  $responsabile = $_POST["responsabile"] ?? '';
  $visibilita   = $_POST["visibilita"]   ?? '';
  $cal_vis      = $_POST["cal_vis"]      ?? $_SESSION["ksglobo_nome_utente"];

  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $stmtUpd = $db->prepare("UPDATE scadenza SET data = ?, descrizione = ?, responsabile = ?, visibilita = ?, utente_ultima_modifica = ? WHERE id = ?");
  $db->beginTransaction();
  $stmtUpd->execute([$data, $descrizione, $responsabile, $visibilita, $_SESSION["ksglobo_nome_utente"], $id_scadenza]);
  $num = $stmtUpd->rowCount();
  $db->commit();

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));

  $_SESSION['flash'] = $num == 1
    ? ['type' => 'ok',  'text' => 'Scadenza aggiornata.']
    : ['type' => 'err', 'text' => 'Scadenza non aggiornata.'];

  header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  exit;
