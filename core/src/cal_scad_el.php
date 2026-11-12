<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_scadenza = $_POST["id_scadenza"] ?? '';
  $data        = $_POST["data"]        ?? '';
  $cal_vis     = $_POST["cal_vis"]     ?? $_SESSION["ksglobo_nome_utente"];

  $stmtDel = $db->prepare("DELETE FROM scadenza WHERE id = ?");
  $db->beginTransaction();
  $stmtDel->execute([$id_scadenza]);
  $num = $stmtDel->rowCount();
  $db->commit();
  if ($num === 1) globo_log_attivita($db, 'elimina', 'scadenza', $id_scadenza, '');

  $_SESSION['flash'] = ($num === 1)
    ? ['type' => 'ok',  'text' => 'Scadenza eliminata.']
    : ['type' => 'err', 'text' => 'Scadenza non eliminata.'];

  $giorno = date("d", strtotime($data));
  $mese   = date("m", strtotime($data));
  $anno   = date("Y", strtotime($data));

  header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  exit;
