<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_impegno = $_POST["id_impegno"] ?? '';
  $data       = $_POST["data"]       ?? '';
  $cal_vis    = $_POST["cal_vis"]    ?? $_SESSION["ksglobo_nome_utente"];

  $stmtDel = $db->prepare("DELETE FROM impegno WHERE id = ?");
  $db->beginTransaction();
  $stmtDel->execute([$id_impegno]);
  $num = $stmtDel->rowCount();
  $db->commit();
  if ($num === 1) globo_log_attivita($db, 'elimina', 'impegno', $id_impegno, '');

  $_SESSION['flash'] = ($num === 1)
    ? ['type' => 'ok',  'text' => 'Impegno eliminato.']
    : ['type' => 'err', 'text' => 'Impegno non eliminato.'];

  $giorno = date("d", strtotime($data));
  $mese   = date("m", strtotime($data));
  $anno   = date("Y", strtotime($data));

  header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  exit;
