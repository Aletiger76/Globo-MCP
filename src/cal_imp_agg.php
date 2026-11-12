<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_impegno   = $_POST["id_impegno"]  ?? '';
  $data         = globo_converti_data_in_formato_mysql($_POST["data"] ?? '');
  $ora          = $_POST["ora"]         ?? '';
  $durata       = $_POST["durata"]      ?? '';
  if ($durata == "24:00") $ora = "00:00";
  $descrizione  = globo_converti_in_html(globo_elimina_spazi_multipli($_POST["descrizione"] ?? ''));
  $responsabile = $_POST["responsabile"] ?? '';
  $cal_vis      = $_POST["cal_vis"]      ?? $_SESSION["ksglobo_nome_utente"];

  if (TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);

  $num = 0;
  $impegno_inseribile = globo_controlla_se_impegno_inseribile($db, $data, $ora, $durata, $responsabile, $id_impegno);

  if ($impegno_inseribile) {
    $stmtUpd = $db->prepare("UPDATE impegno SET data = ?, ora = ?, durata = ?, descrizione = ?, responsabile = ?, utente_ultima_modifica = ? WHERE id = ?");
    $db->beginTransaction();
    $stmtUpd->execute([$data, $ora, $durata, $descrizione, $responsabile, $_SESSION["ksglobo_nome_utente"], $id_impegno]);
    $num = $stmtUpd->rowCount();
    $db->commit();
  }

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));

  if (!$impegno_inseribile) {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Impegno non aggiornato — controllare gli impegni già presenti.'];
    header("Location: cal_imp_vis.php?id_impegno=" . (int)$id_impegno . "&cal_vis=" . urlencode($cal_vis));
  } elseif ($num == 1) {
    $_SESSION['flash'] = ['type' => 'ok',  'text' => 'Impegno aggiornato.'];
    header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Impegno non aggiornato.'];
    header("Location: cal_lista.php?giorno=$giorno&mese=$mese&anno=$anno&cal_vis=" . urlencode($cal_vis));
  }
  exit;
