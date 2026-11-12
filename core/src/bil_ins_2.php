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
  $tipo_bilancio = $_POST["tipo_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
  $data = globo_converti_data_in_formato_mysql($_POST["data"] ?? '');
  $descrizione = globo_converti_in_html($_POST["descrizione"] ?? '');
  $fattura = $_POST["fattura"] ?? '';
  if (trim($fattura) == "") $fattura = "n";
  if ($fattura != "s" && $fattura != "n") $fattura = globo_converti_num_fattura($fattura);
  $importo = $_POST["importo"] ?? '';
  if (trim($importo) == "") $importo = 0;
  $data_scad_pag = globo_converti_data_in_formato_mysql($_POST["data_scad_pag"] ?? '');
  $flag_pagamento = $_POST["flag_pagamento"] ?? '';
  $data_rinnovo = globo_converti_data_in_formato_mysql($_POST["data_rinnovo"] ?? '');
  
  if (TUTTO_MINUSCOLO) {
    $descrizione = strtolower($descrizione);
  }

  $anno_oggi = date("Y");
  $anno = date("Y", strtotime($data));
  
  $query = "INSERT INTO bilancio (tipo, data, descrizione, fattura, importo, flag_esigibilita, flag_pagamento, utente_ultima_modifica)";
  $query .= " VALUES ('$tipo_bilancio', '$data', '$descrizione', '$fattura', '$importo', '1', '$flag_pagamento', '" . $_SESSION["ksglobo_nome_utente"] . "')";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
  
  if ($num == 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata inserita</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita inserita</b>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata non inserita</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita non inserita</b>";
    echo "</td>";
    echo "</tr>";
  }

  if ($num == 1) {
    $query = "SELECT id FROM bilancio WHERE utente_ultima_modifica = '" . $_SESSION["ksglobo_nome_utente"] . "' ORDER BY id DESC LIMIT 1";
    $res = $db->query($query)->fetchAll();
  
    $id_bilancio = $res[0]["id"];
    
    if (trim($data_scad_pag) != "") {
      if ($fattura != "s" && $fattura != "n") $descrizione_scadenza = "scadenza pagamento $tipo_bilancio<br />importo: $importo euro<br />n. fattura: $fattura<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
      else $descrizione_scadenza = "scadenza pagamento $tipo_bilancio<br />importo: $importo euro<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";

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
    
    if (trim($data_rinnovo) != "") {
      if ($fattura != "s" && $fattura != "n") $descrizione_scadenza = "rinnovo $tipo_bilancio<br />importo: $importo euro<br />n. fattura: $fattura<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
      else $descrizione_scadenza = "rinnovo $tipo_bilancio<br />importo: $importo euro<br />data: " . globo_converti_data_in_formato_ita($data) . "<br /><br />descrizione:<br />$descrizione";
    
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
  
  echo "<form action='bil_lista.php' method='post'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
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
