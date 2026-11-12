<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && ($_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2 || (isset($BILV2_UTENTE_RO) && in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)))); ?>
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
  
  if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["tipo"]) && $_SESSION["ksglobo_ricerca_bilanciov2"]["tipo"] != $tipo_bilancio) 
    unset($_SESSION["ksglobo_ricerca_bilanciov2"]);

  if (isset($_POST["ricerca_mese"])) $ricerca_mese = $_POST["ricerca_mese"];  
  else if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["mese"])) $ricerca_mese = $_SESSION["ksglobo_ricerca_bilanciov2"]["mese"];
  else $ricerca_mese = date("m");
  
  if (isset($_POST["ricerca_testo"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"])); 
    else $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]); 
  }
  else if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_bilanciov2"]["testo"];
  else $ricerca_testo = ""; 

  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) {
    if (isset($_POST["ricerca_id_bilanciov2_categoria"])) $ricerca_id_bilanciov2_categoria = $_POST["ricerca_id_bilanciov2_categoria"];  
    else if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_categoria"])) $ricerca_id_bilanciov2_categoria = $_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_categoria"];
    else $ricerca_id_bilanciov2_categoria = -1;

    if (isset($_POST["ricerca_id_bilanciov2_agente"])) $ricerca_id_bilanciov2_agente = $_POST["ricerca_id_bilanciov2_agente"];  
    else if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_agente"])) $ricerca_id_bilanciov2_agente = $_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_agente"];
    else $ricerca_id_bilanciov2_agente = -1;
  }
  else {
    $ricerca_id_bilanciov2_categoria = -1;
    $ricerca_id_bilanciov2_agente = -1;
  }
  
  if (isset($_POST["ricerca_flag_pagamento"])) $ricerca_flag_pagamento = $_POST["ricerca_flag_pagamento"];  
  else if (isset($_SESSION["ksglobo_ricerca_bilanciov2"]["flag_pagamento"])) $ricerca_flag_pagamento = $_SESSION["ksglobo_ricerca_bilanciov2"]["flag_pagamento"];
  else $ricerca_flag_pagamento = -1;  
  
  unset($_SESSION["ksglobo_ricerca_bilanciov2"]);
  $_SESSION["ksglobo_ricerca_bilanciov2"]["tipo"] = $tipo_bilancio;
  $_SESSION["ksglobo_ricerca_bilanciov2"]["mese"] = $ricerca_mese;
  $_SESSION["ksglobo_ricerca_bilanciov2"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_categoria"] = $ricerca_id_bilanciov2_categoria;
  $_SESSION["ksglobo_ricerca_bilanciov2"]["id_bilanciov2_agente"] = $ricerca_id_bilanciov2_agente;
  $_SESSION["ksglobo_ricerca_bilanciov2"]["flag_pagamento"] = $ricerca_flag_pagamento;

  if (strlen($ricerca_mese) == 1) $ricerca_mese = "0" . $ricerca_mese;
  if ($ricerca_mese == "01") $ricerca_mese_lettere = "GENNAIO";
  else if ($ricerca_mese == "02") $ricerca_mese_lettere = "FEBBRAIO";
  else if ($ricerca_mese == "03") $ricerca_mese_lettere = "MARZO";
  else if ($ricerca_mese == "04") $ricerca_mese_lettere = "APRILE";
  else if ($ricerca_mese == "05") $ricerca_mese_lettere = "MAGGIO";
  else if ($ricerca_mese == "06") $ricerca_mese_lettere = "GIUGNO";
  else if ($ricerca_mese == "07") $ricerca_mese_lettere = "LUGLIO";
  else if ($ricerca_mese == "08") $ricerca_mese_lettere = "AGOSTO";
  else if ($ricerca_mese == "09") $ricerca_mese_lettere = "SETTEMBRE";
  else if ($ricerca_mese == "10") $ricerca_mese_lettere = "OTTOBRE";
  else if ($ricerca_mese == "11") $ricerca_mese_lettere = "NOVEMBRE";
  else if ($ricerca_mese == "12") $ricerca_mese_lettere = "DICEMBRE";
  else if ($ricerca_mese == "") $ricerca_mese_lettere = "";
  
  $query_cat = "SELECT * FROM bilanciov2_categoria WHERE tipo = '$tipo_bilancio' ORDER BY nome";
  $res_cat = $db->query($query_cat)->fetchAll();
  $num_cat = count($res_cat);
  
  $query_ag = "SELECT id, nome FROM bilanciov2_agente ORDER BY nome";
  $res_ag = $db->query($query_ag)->fetchAll();
  $num_ag = count($res_ag);
  
  echo "<form action='bilv2_lista.php' method='post' class='margin_bottom_bottoni'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>RICERCA</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left'><b>mese</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left'>";
  echo "<select name='ricerca_mese' onchange='this.form.submit();'>";
  if ($ricerca_mese == "") echo "<option value='' selected='selected'>---</option>";
  else echo "<option value=''>---</option>";
  if ($ricerca_mese == "01") echo "<option value='01' selected='selected'>gennaio</option>";
  else echo "<option value='01'>gennaio</option>";
  if ($ricerca_mese == "02") echo "<option value='02' selected='selected'>febbraio</option>";
  else echo "<option value='02'>febbraio</option>";
  if ($ricerca_mese == "03") echo "<option value='03' selected='selected'>marzo</option>";
  else echo "<option value='03'>marzo</option>";
  if ($ricerca_mese == "04") echo "<option value='04' selected='selected'>aprile</option>";
  else echo "<option value='04'>aprile</option>";
  if ($ricerca_mese == "05") echo "<option value='05' selected='selected'>maggio</option>";
  else echo "<option value='05'>maggio</option>";
  if ($ricerca_mese == "06") echo "<option value='06' selected='selected'>giugno</option>";
  else echo "<option value='06'>giugno</option>";
  if ($ricerca_mese == "07") echo "<option value='07' selected='selected'>luglio</option>";
  else echo "<option value='07'>luglio</option>";
  if ($ricerca_mese == "08") echo "<option value='08' selected='selected'>agosto</option>";
  else echo "<option value='08'>agosto</option>";
  if ($ricerca_mese == "09") echo "<option value='09' selected='selected'>settembre</option>";
  else echo "<option value='09'>settembre</option>";
  if ($ricerca_mese == "10") echo "<option value='10' selected='selected'>ottobre</option>";
  else echo "<option value='10'>ottobre</option>";
  if ($ricerca_mese == "11") echo "<option value='11' selected='selected'>novembre</option>";
  else echo "<option value='11'>novembre</option>";
  if ($ricerca_mese == "12") echo "<option value='12' selected='selected'>dicembre</option>";
  else echo "<option value='12'>dicembre</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left'>";
  if ($tipo_bilancio == "entrata") echo "<b>cliente/fattura</b>";
  else echo "<b>fornitore</b>";
  echo "</td>";
  echo "<td class='tab_int_td_3' width='50%' align='left'>";
  echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
  echo "</td>";
  echo "</tr>";

  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) {
    if ($num_cat > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' align='left'><b>categoria</b> di almeno una delle voci della fattura</font></td>";
      echo "<td class='tab_int_td_3' align='left'>";
      echo "<select name='ricerca_id_bilanciov2_categoria' onchange='this.form.submit();'>";
      if ($ricerca_id_bilanciov2_categoria == -1) echo "<option value='-1' selected='selected'>---</option>";
      else echo "<option value='-1'>---</option>";
      foreach ($res_cat as $r_cat) { 
        if ($ricerca_id_bilanciov2_categoria == $r_cat["id"])
          echo "<option value='" . $r_cat["id"] . "' selected='selected'>" . $r_cat["nome"] . "</option>";
        else
          echo "<option value='" . $r_cat["id"] . "'>" . $r_cat["nome"] . "</option>";
      }
      echo "</select>";
      echo "</td>";
      echo "</tr>";
    }
    else echo "<input type='hidden' name='ricerca_id_bilanciov2_categoria' value='-1' />";
  
    if ($num_ag > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' align='left'><b>agente</b></td>";
      echo "<td class='tab_int_td_3' align='left'>";
      echo "<select name='ricerca_id_bilanciov2_agente' onchange='this.form.submit();'>";
      if ($ricerca_id_bilanciov2_agente == -1) echo "<option value='-1' selected='selected'>---</option>";
      else echo "<option value='-1'>---</option>";
      if ($ricerca_id_bilanciov2_agente == 9999999999999) echo "<option value='9999999999999' selected='selected'>tutti</option>";
      else echo "<option value='9999999999999'>tutti</option>";
      foreach ($res_ag as $r_ag) { 
        if ($ricerca_id_bilanciov2_agente == $r_ag["id"])
          echo "<option value='" . $r_ag["id"] . "' selected='selected'>" . $r_ag["nome"] . "</option>";
        else
          echo "<option value='" . $r_ag["id"] . "'>" . $r_ag["nome"] . "</option>";
      }
      echo "</select>";
      echo "</td>";
      echo "</tr>";
    }
    else echo "<input type='hidden' name='ricerca_id_bilanciov2_agente' value='-1' />";
  }
  
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_flag_pagamento' onchange='this.form.submit();'>";
  if ($ricerca_flag_pagamento == -1) echo "<option value='-1' selected='selected'>---</option>";
  else echo "<option value='-1'>---</option>";
  if ($ricerca_flag_pagamento == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
  else echo "<option value='1'>s&igrave;</option>";
  if ($ricerca_flag_pagamento == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";
  if ($ricerca_flag_pagamento == 100) echo "<option value='100' selected='selected'>pagamento non esigibile</option>";
  else echo "<option value='100'>pagamento non esigibile</option>";
  if ($ricerca_flag_pagamento == 2) echo "<option value='2' selected='selected'>pagamento parziale</option>";
  else echo "<option value='2'>pagamento parziale</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='ricerca' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bilv2_ins_1.php' method='post' class='margin_bottom_blocchi'>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata" && !in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='nuova entrata' class='input_bottone' />";
  else if ($tipo_bilancio == "entrata" && in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='nuova entrata' class='input_bottone_dis' disabled='disabled' />";
  else echo "<input type='submit' value='nuova uscita' class='input_bottone' />";
  echo "</form>";

  $query_1 = "SELECT bilanciov2.id AS id_bilanciov2, bilanciov2.data AS data, bilanciov2.cliente_fornitore AS cliente_fornitore,";
  $query_1 .= " bilanciov2.fattura AS fattura, bilanciov2.flag_esigibilita AS flag_esigibilita, bilanciov2.flag_pagamento AS flag_pagamento, bilanciov2.credito_debito_attuale AS credito_debito_attuale,";
  $query_1 .= " bilanciov2.data_scadenza_pagamento AS data_scadenza_pagamento, bilanciov2.flag_sollecito AS flag_sollecito,";
  $query_1 .= " bilanciov2.id_bilanciov2_agente AS id_bilanciov2_agente, bilanciov2.perc_provvigione AS perc_provvigione";
  $query_1 .= " FROM bilanciov2 LEFT JOIN bilanciov2_dettaglio ON bilanciov2.id = bilanciov2_dettaglio.id_bilanciov2";
  $query_1 .= " WHERE bilanciov2.tipo = '$tipo_bilancio'";
  if ($ricerca_mese == "") $query_1 .= " AND bilanciov2.data LIKE '$anno_bilancio%'";
  else $query_1 .= " AND bilanciov2.data LIKE '$anno_bilancio-$ricerca_mese-%'";
  // $query_1 .= " AND (bilanciov2.cliente_fornitore LIKE '%$ricerca_testo%' OR bilanciov2.dati_fatturazione LIKE '%$ricerca_testo%' OR bilanciov2.fattura LIKE '%$ricerca_testo%' OR bilanciov2_dettaglio.descrizione LIKE '%$ricerca_testo%')";
  if ($ricerca_testo != "") $query_1 .= " AND (bilanciov2.cliente_fornitore LIKE '%$ricerca_testo%' OR bilanciov2.email LIKE '%$ricerca_testo%' OR bilanciov2.dati_fatturazione LIKE '%$ricerca_testo%' OR bilanciov2.fattura LIKE '%$ricerca_testo%')";
  if ($ricerca_flag_pagamento == 0) $query_1 .= " AND bilanciov2.flag_pagamento = '0' AND bilanciov2.flag_esigibilita = '1'"; // fattura non pagata e pagamento esigibile
  else if ($ricerca_flag_pagamento == 1) $query_1 .= " AND bilanciov2.flag_pagamento = '1'"; // fattura pagata
  else if ($ricerca_flag_pagamento == 2) $query_1 .= " AND bilanciov2.flag_pagamento = '2'"; // fattura pagata parzialmente
  else if ($ricerca_flag_pagamento == 100) $query_1 .= " AND bilanciov2.flag_esigibilita = '0'"; // pagamento non esigibile
  if ($ricerca_id_bilanciov2_agente > 0 && $ricerca_id_bilanciov2_agente != 9999999999999) $query_1 .= " AND (bilanciov2.id_bilanciov2_agente = '$ricerca_id_bilanciov2_agente')";
  else if ($ricerca_id_bilanciov2_agente == 9999999999999) $query_1 .= " AND (bilanciov2.id_bilanciov2_agente > 0)";
  if ($ricerca_id_bilanciov2_categoria > 0) $query_1 .= " AND bilanciov2_dettaglio.id_bilanciov2_categoria = '$ricerca_id_bilanciov2_categoria'";
  $query_1 .= " GROUP BY bilanciov2.id";
  $query_1 .= " ORDER BY bilanciov2.data, bilanciov2.fattura, bilanciov2.id";
  $res_1 = $db->query($query_1)->fetchAll();
  $num_1 = count($res_1);
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 

  if ($num_1 == 0) {
    if ($tipo_bilancio == "entrata") {
      echo "<tr><td class='tab_int_td_1' align='center'><b>ENTRATE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
      echo "<tr><td class='tab_int_td_3' align='center'><b>nessuna entrata</b></td></tr>";
      echo "</table>";
    }
    else {
      echo "<tr><td class='tab_int_td_1' align='center'><b>USCITE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
      echo "<tr><td class='tab_int_td_3' align='center'><b>nessuna uscita</b></td></tr>";
      echo "</table>";
    }
  }
  else {
    if ($tipo_bilancio == "entrata") {
      $colspan = 10;
      if (BILV2_SOLL) $colspan++;
      if (BILV2_AG) $colspan++;
      if (BILV2_PDF) $colspan++;
      if (in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) $colspan--;
      echo "<tr><td class='tab_int_td_1' colspan='$colspan' align='center'><b>ENTRATE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
    }
    else {
      echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>USCITE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
    }
  }

  if ($num_1 > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='center'><b>data</b></td>";
    if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='left' width='100%'><b>cliente</b></td>";
    else echo "<td class='tab_int_td_2' align='left' width='100%'><b>fornitore</b></td>";
    if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='center'><b>fatt</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='right'><b>imponibile</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='right'><b>totale</b></td>";
    if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='center'><b>c</b></td>";
    if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='center'><b>r</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>pag</b></td>";
    if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2 nobr' align='center'><b>scad pag</b></td>";
    if ($tipo_bilancio == "entrata" && BILV2_SOLL) echo "<td class='tab_int_td_2 nobr' align='center'><b>soll</b></td>";
    if ($tipo_bilancio == "entrata" && BILV2_AG) echo "<td class='tab_int_td_2 nobr' align='center'><b>ag</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    if ($tipo_bilancio == "entrata" && BILV2_PDF && !in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<td class='tab_int_td_2' align='center'><b>pdf</b></td>";
    echo "</tr>";
    
    $TOT_imponibile = 0;
    $TOT_totale = 0;
    $TOT_totale_esigibile = 0;
    $mese = 0;
    $mese_precedente = 0;

    foreach ($res_1 as $r_1) { 
      $id_bilanciov2 = $r_1["id_bilanciov2"];
      $data = $r_1["data"];
      $mese = date("m", strtotime($data));
      $cliente_fornitore = $r_1["cliente_fornitore"];
      $fattura = globo_converti_num_fattura($r_1["fattura"], date("Y", strtotime($data)));
      $flag_esigibilita = $r_1["flag_esigibilita"];
      $flag_pagamento = $r_1["flag_pagamento"];
      $credito_debito_attuale = $r_1["credito_debito_attuale"];
      if ($tipo_bilancio == "entrata") $credito_debito_attuale_str = "credito attuale&nbsp;&nbsp;" . $credito_debito_attuale;
      else $credito_debito_attuale_str = "debito attuale&nbsp;&nbsp;" . $credito_debito_attuale;
      if ($flag_esigibilita == 0) $pagamento = "<span style='color: #a900c8;'>ne</span>";
      else if ($flag_pagamento == 0) $pagamento = "<span style='color: red;'>no</span>";
      else if ($flag_pagamento == 1) $pagamento = "<span style='color: green;'>s&igrave;</span>";
      else if ($flag_pagamento == 2) $pagamento = "<a title='$credito_debito_attuale_str' style='color: blue;'>pp</a>";  
      $data_scadenza_pagamento = $r_1["data_scadenza_pagamento"];
      $flag_sollecito = $r_1["flag_sollecito"];
      if ($flag_pagamento == 1 || $flag_esigibilita == 0) $sollecito = "";
      else if ($flag_sollecito == 0) $sollecito = "no";
      else if ($flag_sollecito == 1 && strtotime($data_scadenza_pagamento) < strtotime(date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")) - (60 * 60 * 24 * BILV2_SOLL_GIO_RIT)))) $sollecito = "<span style='color: red;'>s&igrave;</span>";
      else $sollecito = "s&igrave;";
      $id_bilanciov2_agente = $r_1["id_bilanciov2_agente"];
      $perc_provvigione = $r_1["perc_provvigione"];
      
      if ($flag_pagamento == 1 || $data_scadenza_pagamento == "0000-00-00") $data_scadenza_pagamento = "";
      else if (strtotime($data_scadenza_pagamento) < strtotime(date("Y-m-d"))) $data_scadenza_pagamento = "<span style='color: red;'>" . globo_converti_data_in_formato_ita($data_scadenza_pagamento) . "</span>";
      else $data_scadenza_pagamento = globo_converti_data_in_formato_ita($data_scadenza_pagamento);

      $TMP_imponibile = 0;
      $TMP_totale = 0;
      $TMP_flag_canone = 0;
      $TMP_flag_ricorda = 0;
      
      $query_2 = "SELECT bilanciov2_dettaglio.imponibile, bilanciov2_dettaglio.iva, bilanciov2_dettaglio.flag_canone, bilanciov2_dettaglio.flag_ricorda FROM bilanciov2_dettaglio";
      $query_2 .= " WHERE bilanciov2_dettaglio.id_bilanciov2 = '$id_bilanciov2'";
      $res_2 = $db->query($query_2)->fetchAll();
      
      foreach ($res_2 as $r_2) { 
        $imponibile = $r_2["imponibile"];
        $iva = $r_2["iva"];
        $flag_canone = $r_2["flag_canone"];
        $flag_ricorda = $r_2["flag_ricorda"];
        
        $TMP_imponibile += $imponibile;
        $TMP_totale += $imponibile + $imponibile * ($iva / 100);
        $TMP_flag_canone += $flag_canone;
        $TMP_flag_ricorda += $flag_ricorda;
      }
      
      if ($TMP_flag_ricorda != $TMP_flag_canone) $TMP_flag_ricorda = "<span style='color: red;'>$TMP_flag_ricorda</span>";
      
      $TOT_imponibile += globo_2dec($TMP_imponibile);
      $TOT_totale += globo_2dec($TMP_totale);
      
      if ($flag_esigibilita == 1) {
        if ($flag_pagamento == 0) $TOT_totale_esigibile += globo_2dec($TMP_totale);
        else if ($flag_pagamento == 2) $TOT_totale_esigibile += globo_2dec($credito_debito_attuale);
      }
      
      if ($mese != $mese_precedente) {        
        if ($ricerca_mese == "") {
          if ($tipo_bilancio == "entrata") {
            if (BILV2_SOLL) $colspan++;
            if (BILV2_AG) $colspan++;
            if (BILV2_PDF) $colspan++;  
            if (in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) $colspan--;
            echo "<tr><td class='tab_int_td_2' colspan='$colspan' align='center'>";
          }
          else {
            echo "<tr><td class='tab_int_td_2' colspan='6' align='center'>";
          }
          if ($mese == 1) echo "<b>gennaio</b>";
          else if ($mese == 2) echo "<b>febbraio</b>";
          else if ($mese == 3) echo "<b>marzo</b>";
          else if ($mese == 4) echo "<b>aprile</b>";
          else if ($mese == 5) echo "<b>maggio</b>";
          else if ($mese == 6) echo "<b>giugno</b>";
          else if ($mese == 7) echo "<b>luglio</b>";
          else if ($mese == 8) echo "<b>agosto</b>";
          else if ($mese == 9) echo "<b>settembre</b>";
          else if ($mese == 10) echo "<b>ottobre</b>";
          else if ($mese == 11) echo "<b>novembre</b>";
          else if ($mese == 12) echo "<b>dicembre</b>";
          echo "</td></tr>";
        }
      }
      
      echo "<tr>";
      echo "<td class='tab_int_td_3 nobr' align='center'>" . globo_converti_data_in_formato_ita($data) . "</td>";
      echo "<td class='tab_int_td_3' align='left'>$cliente_fornitore</td>";
      if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_3' align='center'><a name='$fattura'></a>$fattura</td>";
      echo "<td class='tab_int_td_3' align='right'>" . globo_2dec($TMP_imponibile) . "</td>";
      echo "<td class='tab_int_td_3' align='right'>" . globo_2dec($TMP_totale) . "</td>";
      if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_3' align='right'>$TMP_flag_canone</td>";
      if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_3' align='right'>$TMP_flag_ricorda</td>";
      echo "<td class='tab_int_td_3 nobr' align='center'>$pagamento</td>";
      if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_3 nobr' align='center'>$data_scadenza_pagamento</td>";
      if ($tipo_bilancio == "entrata" && BILV2_SOLL) echo "<td class='tab_int_td_3 nobr' align='center'>$sollecito</td>";
      if ($tipo_bilancio == "entrata" && BILV2_AG) {
        echo "<td class='tab_int_td_3 nobr' align='center'>";
        if ($id_bilanciov2_agente > 0) {
          $query_ag = "SELECT nome FROM bilanciov2_agente WHERE id = '$id_bilanciov2_agente'";
          $res_ag = $db->query($query_ag)->fetchAll();
          $agente = $res_ag[0]["nome"];
          $agente .= "\n$perc_provvigione%";
          $agente .= "\n" . globo_2dec($TMP_imponibile * $perc_provvigione / 100);
          echo "<a title='$agente'>s�</a>";
        }
        else echo "&nbsp;";
        echo "</td>";
      }

      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='bilv2_vis.php' method='post'>";
      echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
      echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
      echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
      echo "<input type='image' src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</td>";
      
      echo "</tr>";
      
      $mese_precedente = $mese;
    }
    
    /*
    if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) {
    */
      echo "<tr>";
      if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' colspan='3'>&nbsp;</td>";
      else echo "<td class='tab_int_td_2' colspan='2'>&nbsp;</td>";
      echo "<td class='tab_int_td_2' colspan='1' align='right' valign='top'><b>" . globo_2dec($TOT_imponibile) . "</b></td>";
      echo "<td class='tab_int_td_2 nobr' colspan='1' align='right' valign='top'>";
      echo "<b>" . globo_2dec($TOT_totale) . "</b>";
      if ($TOT_totale_esigibile != $TOT_totale && $TOT_totale_esigibile != 0) echo "<div style='padding-top: 2px;'>di cui<br />" . globo_2dec($TOT_totale_esigibile) . "<br />da incassare</div>";
      echo "</td>";
      if ($tipo_bilancio == "entrata") {
        $colspan = 5;
        if (BILV2_SOLL) $colspan++;
        if (BILV2_AG) $colspan++;
        if (BILV2_PDF) $colspan++;
        if (in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) $colspan--;
        echo "<td class='tab_int_td_2' colspan='$colspan'>&nbsp;</td>";
      }
      else {
        echo "<td class='tab_int_td_2' colspan='2'>&nbsp;</td>";
      }
      echo "</td>";
      echo "</tr>";
    /*
    }
    else {
      $colspan = 10;
      if (BILV2_SOLL) $colspan++;
      if (BILV2_AG) $colspan++;
      if (BILV2_PDF) $colspan++;
      if (in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) $colspan--;
      echo "<tr><td class='tab_int_td_2' colspan='$colspan'>&nbsp;</td></tr>";
    }
    */
    
    echo "</table>";
  }
  
  echo "<form action='bilv2_ins_1.php' method='post'>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata" && !in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='nuova entrata' class='input_bottone' />";
  else if ($tipo_bilancio == "entrata" && in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='nuova entrata' class='input_bottone_dis' disabled='disabled' />";
  else echo "<input type='submit' value='nuova uscita' class='input_bottone' />";
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
