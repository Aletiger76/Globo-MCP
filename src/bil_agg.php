<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIO') && MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>BILANCIO</b></td></tr></table>
<?php  
  $id_bilancio = $_POST["id_bilancio"] ?? '';
  $tipo_bilancio = $_POST["tipo_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
  $data = globo_converti_data_in_formato_mysql($_POST["data"] ?? '');
  $descrizione = globo_converti_in_html($_POST["descrizione"] ?? '');
  $fattura = $_POST["fattura"] ?? '';
  if (trim($fattura) == "") $fattura = "n";
  if ($fattura != "s" && $fattura != "n") $fattura = globo_converti_num_fattura($fattura);
  $importo = $_POST["importo"] ?? '';
  $data_scad_pag = globo_converti_data_in_formato_mysql($_POST["data_scad_pag"] ?? '');
  $flag_esigibilita = $_POST["flag_esigibilita"] ?? '';
  if ($flag_esigibilita == 0) $flag_pagamento = 0;
  else $flag_pagamento = $_POST["flag_pagamento"] ?? '';
  $data_rinnovo = globo_converti_data_in_formato_mysql($_POST["data_rinnovo"] ?? '');

  if (TUTTO_MINUSCOLO) {
    $descrizione = strtolower($descrizione);
  }

  $query = "SELECT * FROM bilancio WHERE id = '$id_bilancio'";
  $res = $db->query($query)->fetchAll();
  
  $importo_old = globo_2dec($res[0]["importo"]);

  $anno_oggi = date("Y");
  $anno = date("Y", strtotime($data));
  
  $query = "UPDATE bilancio SET data = '$data', descrizione = '$descrizione', fattura = '$fattura', importo = '$importo',";
  $query .= " flag_esigibilita = '$flag_esigibilita', flag_pagamento = '$flag_pagamento',";
  $query .= " utente_ultima_modifica = '" . $_SESSION["ksglobo_nome_utente"] . "'";
  $query .= " WHERE id = '$id_bilancio'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
  
  if ($num == 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata aggiornata</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita aggiornata</b>";
    echo "</td>";
    echo "</tr>";
    
    if ($tipo_bilancio == "entrata") {
      $oggetto = "entrata modificata";
      $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha modificato la seguente entrata";
      $testo .= "\n\n---";
      $testo .= "\n\nid_bilancio: " . $id_bilancio;
      $testo .= "\n\ndata: " . globo_converti_data_in_formato_ita($data);
      $testo .= "\n\ncliente: " . globo_converti_da_html(globo_converti_acapo_da_html_in_spazi($descrizione));
      $testo .= "\n\nfattura: " . $fattura;
      mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
    }
  }   
  else {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata non aggiornata</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita non aggiornata</b>";
    echo "</td>";
    echo "</tr>";
  }
    
  // se l'entrata (o l'uscita) � stata pagata (o il pagamento non � esigibile) o non � stata inserita una data di scadenza pagamento, elimino l'eventuale scadenza    
  if ($flag_pagamento == 1 || $flag_pagamento == 0 || trim($data_scad_pag) == "") { 
    $query = "DELETE FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_scad_pag'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();

    if ($num == 1) echo "<tr><td class='tab_int_td_3' align='center'><b>scadenza (scadenza pagamento) eliminata</b></td></tr>";
  }
  // se l'entrata (o l'uscita) non � stata pagata ed � stata inserita una data di scadenza pagamento, inserisco la scadenza o la aggiorno se c'� gi�
  else {
    if ($fattura != "s" && $fattura != "n") $descrizione_scadenza = "scadenza pagamento $tipo_bilancio<br />importo: $importo euro<br />n. fattura: $fattura<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
    else $descrizione_scadenza = "scadenza pagamento $tipo_bilancio<br />importo: $importo euro<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
      
    $query = "SELECT id FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_scad_pag'";
    $res = $db->query($query)->fetchAll();
    $num = count($res);
      
    if ($num > 0) {
      $id_scadenza = $res[0]["id"];
      
      $query = "UPDATE scadenza SET data = '$data_scad_pag', descrizione = '$descrizione_scadenza', utente_ultima_modifica = '" . $_SESSION["ksglobo_nome_utente"] . "'";
      $query .= " WHERE id = '$id_scadenza'";
      $db->beginTransaction();
      $num = $db->exec($query);
      $db->commit();
      
      if ($num == 1) echo "<tr><td class='tab_int_td_3' align='center'><b>scadenza (scadenza pagamento) aggiornata</b></td></tr>";
    }
    else {
      $query = "INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica)";
      $query .= " VALUES ('$data_scad_pag', 'bilancio_scad_pag', '1', '$id_bilancio', '0', '0', '$descrizione_scadenza', 'tutti', '" . $_SESSION["ksglobo_nome_utente"] . "')";
      $db->beginTransaction();
      $num = $db->exec($query);
      $db->commit();

      echo "<tr>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($num == 1) echo "<b>scadenza (scadenza pagamento) inserita</b>";
      else echo "<b>scadenza (scadenza pagamento) non inserita</b>";
      echo "</td>";
      echo "</tr>";
    }
  }
    
  // se non � stata inserita una data di rinnovo, elimino l'eventuale scadenza 
  if (trim($data_rinnovo) == "") { 
    $query = "DELETE FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_rinnovo'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();

    if ($num == 1) echo "<tr><td class='tab_int_td_3' align='center'><b>scadenza (rinnovo) eliminata</b></td></tr>";
  }
  // se � stata inserita una data di rinnovo, inserisco la scadenza o la aggiorno se c'� gi�
  else {
    if ($fattura != "s" && $fattura != "n") $descrizione_scadenza = "rinnovo $tipo_bilancio<br />importo: $importo euro<br />n. fattura: $fattura<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
    else $descrizione_scadenza = "rinnovo $tipo_bilancio<br />importo: $importo euro<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
  
    $query = "SELECT id FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_rinnovo'";
    $res = $db->query($query)->fetchAll();
    $num = count($res);
    
    if ($num > 0) {
      $id_scadenza = $res[0]["id"];

      $query = "UPDATE scadenza SET data = '$data_rinnovo', descrizione = '$descrizione_scadenza', utente_ultima_modifica = '" . $_SESSION["ksglobo_nome_utente"] . "'";
      $query .= " WHERE id = '$id_scadenza'";
      $db->beginTransaction();
      $num = $db->exec($query);
      $db->commit();     
      
      if ($num == 1) echo "<tr><td class='tab_int_td_3' align='center'><b>scadenza (rinnovo) aggiornata</b></td></tr>";
    }
    else {
      $query = "INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica)";
      $query .= " VALUES ('$data_rinnovo', 'bilancio_rinnovo', '1', '$id_bilancio', '0', '0', '$descrizione_scadenza', 'tutti', '" . $_SESSION["ksglobo_nome_utente"] . "')";
      $db->beginTransaction();
      $num = $db->exec($query);
      $db->commit();   

      echo "<tr>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($num == 1) echo "<b>scadenza (rinnovo) inserita</b>";
      else echo "<b>scadenza (rinnovo) non inserita</b>";
      echo "</td>";
      echo "</tr>";
    }
  }
  
  echo "</table>";   

  echo "<form action='bil_vis.php' method='post'>";
  echo "<input type='hidden' name='id_bilancio' value='$id_bilancio' />";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='visualizza entrata' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='visualizza uscita' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bil_lista.php' method='post' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio'>";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='elenco entrate' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='elenco uscite' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bil_home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='bilancio' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
