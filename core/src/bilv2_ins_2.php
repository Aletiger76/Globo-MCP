<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
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
  $cliente_fornitore = globo_converti_in_html($_POST["cliente_fornitore"] ?? '');
  if (TUTTO_MINUSCOLO) $cliente_fornitore = strtolower($cliente_fornitore);
  $email = $_POST["email"] ?? '';
  $id_bilanciov2_tipo_cliente = $_POST["id_bilanciov2_tipo_cliente"] ?? '';
  $dati_fatturazione = globo_converti_in_html($_POST["dati_fatturazione"] ?? '');
  $fattura = globo_converti_num_fattura($_POST["fattura"] ?? '', date("Y", strtotime($data)));
  $modalita_pagamento = globo_converti_in_html($_POST["modalita_pagamento"] ?? '');
  $banca = globo_converti_in_html($_POST["banca"] ?? '');
  $note_fattura = globo_converti_in_html($_POST["note_fattura"] ?? '');
  $flag_pagamento = $_POST["flag_pagamento"] ?? '';
  if ($flag_pagamento == 1) $data_pagamento = $data;
  else $data_pagamento = "0000-00-00";
  $data_scadenza_pagamento = globo_converti_data_in_formato_mysql($_POST["data_scadenza_pagamento"] ?? '');
  if (trim($email) == "") $flag_sollecito = 0;
  else $flag_sollecito = $_POST["flag_sollecito"] ?? '';
  if (isset($_POST["id_bilanciov2_agente"]) && $_POST["id_bilanciov2_agente"] >= 0) $id_bilanciov2_agente = $_POST["id_bilanciov2_agente"]; 
  else $id_bilanciov2_agente = 0;
  if (isset($_POST["perc_provvigione"]) && is_numeric($_POST["perc_provvigione"]) && $_POST["perc_provvigione"] > 0) $perc_provvigione = $_POST["perc_provvigione"]; 
  else $perc_provvigione = 0;
  $note = globo_converti_in_html($_POST["note"] ?? '');
  if (TUTTO_MINUSCOLO) $note = strtolower($note);

  if ($flag_pagamento == 1 || trim($data_scadenza_pagamento) == "") {
    $data_scadenza_pagamento = "0000-00-00";
    $flag_sollecito = 0;
  }
  
  if ($id_bilanciov2_agente == 0) {
    $perc_provvigione = 0;
  }
  
  $ins = false;

  $query = "INSERT INTO bilanciov2 (tipo, data, cliente_fornitore, email, id_bilanciov2_tipo_cliente, dati_fatturazione, fattura, modalita_pagamento, banca, note_fattura, flag_esigibilita, flag_pagamento, credito_debito_attuale, data_pagamento, data_scadenza_pagamento, flag_sollecito, id_bilanciov2_agente, perc_provvigione, note, utente_ultima_modifica)";
  $query .= " VALUES ('$tipo_bilancio', '$data', '$cliente_fornitore', '$email', '$id_bilanciov2_tipo_cliente', '$dati_fatturazione', '$fattura', '$modalita_pagamento', '$banca', '$note_fattura', '1', '$flag_pagamento', '0', '$data_pagamento', '$data_scadenza_pagamento', '$flag_sollecito', '$id_bilanciov2_agente', '$perc_provvigione', '$note', '" . $_SESSION["ksglobo_nome_utente"] . "')";
  $db->beginTransaction();
  $num_1 = $db->exec($query);
  $db->commit();
  
  $num_2 = 0;
  
  if ($num_1 == 1) {
    $query = "SELECT id FROM bilanciov2 WHERE utente_ultima_modifica = '" . $_SESSION["ksglobo_nome_utente"] . "' ORDER BY id DESC LIMIT 1";
    $res = $db->query($query)->fetchAll();
    $id_bilanciov2 = $res[0]["id"];

    for ($i = 0; $i < BILV2_NUM_RIGHE; $i++) {
      $id_bilanciov2_categoria = $_POST["id_bilanciov2_categoria_" . $i] ?? '';
      $descrizione = globo_converti_in_html($_POST["descrizione_" . $i] ?? '');
      if ($tipo_bilancio == "uscita" && TUTTO_MINUSCOLO) $descrizione = strtolower($descrizione);
      $flag_canone = $_POST["flag_canone_" . $i] ?? '';
      $data_scadenza_canone = globo_converti_data_in_formato_mysql($_POST["data_scadenza_canone_" . $i] ?? '');
      if ($flag_canone <= 0 || trim($data_scadenza_canone) == "" || !globo_controlla_data_datetime($data_scadenza_canone)) $data_scadenza_canone = "0000-00-00";
      $flag_ricorda = $_POST["flag_ricorda_" . $i] ?? '';
      if ($flag_canone <= 0 || $flag_ricorda <= 0) $flag_ricorda = 0;
      $imponibile = $_POST["imponibile_" . $i] ?? '';
      $iva = $_POST["iva_" . $i] ?? '';
      
      if (trim($descrizione) != "") {
        $query = "INSERT INTO bilanciov2_dettaglio (id_bilanciov2, id_bilanciov2_categoria, descrizione, flag_canone, data_scadenza_canone, flag_ricorda, imponibile, iva, utente_ultima_modifica)";
        $query .= " VALUES ('$id_bilanciov2', '$id_bilanciov2_categoria', '$descrizione', '$flag_canone', '$data_scadenza_canone', '$flag_ricorda', '$imponibile', '$iva', '" . $_SESSION["ksglobo_nome_utente"] . "')";
        $db->beginTransaction();
        $num_2 += $db->exec($query);
        $db->commit();
      }
    }
  }

  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
  
  if ($num_1 > 0 && $num_2 > 0) {
    $ins = true;
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata inserita</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita inserita</b>";
    echo "</td>";
    echo "</tr>";
    
    $anno_bilancio = substr($data, 0, 4);
  }   
  else {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata non inserita</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita non inserita</b>";
    echo "</td>";
    echo "</tr>";
  }
  
  if ($flag_pagamento == 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center' style='color: red;'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata salvata come pagata</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita salvata come pagata</b>";
    echo "</td>";
    echo "</tr>";
  }
  
  if ($ins && $tipo_bilancio == "entrata") {
    $totale = 0;
  
    $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2' ORDER BY id";
    $res = $db->query($query)->fetchAll();
    
    foreach ($res as $r) { 
      $imponibile = $r["imponibile"];
      $iva = $r["iva"];
      $totale += $imponibile + $imponibile * ($iva / 100);
    }
    
    $totale = globo_2dec($totale);
    
    $oggetto = "entrata inserita";
    $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha inserito la seguente entrata";
    $testo .= "\n\n---";
    $testo .= "\n\nid_bilanciov2: " . $id_bilanciov2;
    $testo .= "\n\ndata: " . globo_converti_data_in_formato_ita($data);
    $testo .= "\n\ncliente: " . globo_converti_da_html($cliente_fornitore);
    $testo .= "\n\nfattura: " . $fattura;
    $testo .= "\n\ntotale: " . $totale;
    if ($flag_pagamento == 1) $testo .= "\n\nflag_pagamento: 1";
    //mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
  }
    
  echo "</table>";   

  echo "<form action='bilv2_vis.php' method='post'>";
  echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='visualizza entrata' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='visualizza uscita' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bilv2_lista.php' method='post' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio'>";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='elenco entrate' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='elenco uscite' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bilv2_home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='bilancio' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
