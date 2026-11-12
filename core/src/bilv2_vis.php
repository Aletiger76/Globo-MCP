<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && ($_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2 || (isset($BILV2_UTENTE_RO) && in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)))); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<?php
  $id_bilanciov2 = $_POST["id_bilanciov2"] ?? '';
  $tipo_bilancio = $_POST["tipo_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
  
  $query_cat = "SELECT * FROM bilanciov2_categoria WHERE tipo = '$tipo_bilancio' ORDER BY nome";
  $res_cat = $db->query($query_cat)->fetchAll();
  $num_cat = count($res_cat);
  
  $query_tipocli = "SELECT * FROM bilanciov2_tipo_cliente ORDER BY nome";
  $res_tipocli = $db->query($query_tipocli)->fetchAll();
  $num_tipocli = count($res_tipocli);
  
  $query_ag = "SELECT id, nome FROM bilanciov2_agente ORDER BY nome";
  $res_ag = $db->query($query_ag)->fetchAll();
  $num_ag = count($res_ag);
?>
<?php require("varie/funzioni_javascript_bilv2.php"); ?>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
<link rel="stylesheet" href="css/flatpickr.min.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>BILANCIO</b></td></tr></table>
<?php
  $query = "SELECT * FROM bilanciov2 WHERE id = '$id_bilanciov2'";
  $res = $db->query($query)->fetchAll();
  
  $data = $res[0]["data"];
  $cliente_fornitore = $res[0]["cliente_fornitore"];
  $email = $res[0]["email"];
  $id_bilanciov2_tipo_cliente = $res[0]["id_bilanciov2_tipo_cliente"];
  $dati_fatturazione = globo_converti_acapo_da_html($res[0]["dati_fatturazione"]);
  $fattura = $res[0]["fattura"];
  if ($tipo_bilancio == "entrata") $fattura = globo_converti_num_fattura($fattura, date("Y", strtotime($data)));
  else $fattura = "";
  $modalita_pagamento = globo_converti_acapo_da_html($res[0]["modalita_pagamento"]);
  $banca = globo_converti_acapo_da_html($res[0]["banca"]);
  $note_fattura = globo_converti_acapo_da_html($res[0]["note_fattura"]);
  $flag_esigibilita = $res[0]["flag_esigibilita"];
  $flag_pagamento = $res[0]["flag_pagamento"];
  $credito_debito_attuale = $res[0]["credito_debito_attuale"];
  $data_pagamento = $res[0]["data_pagamento"];
  if ($flag_pagamento == 1 && $data_pagamento != "0000-00-00") $data_pagamento = globo_converti_data_in_formato_ita($data_pagamento);
  else $data_pagamento = "";
  $data_scadenza_pagamento = $res[0]["data_scadenza_pagamento"];
  $flag_sollecito = $res[0]["flag_sollecito"];
  $id_bilanciov2_agente = $res[0]["id_bilanciov2_agente"];
  $perc_provvigione = $res[0]["perc_provvigione"];
  $note = globo_converti_acapo_da_html($res[0]["note"]);
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];
  $dt_ultima_modifica = globo_converti_datetime_in_formato_ita($res[0]["dt_ultima_modifica"]);
  
  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<form name='globo' action='bilv2_agg.php' method='post' onsubmit='return controllaForm();'>";
  else echo "<form name='globo'>";
  echo "<table class='tab_int' cellspacing='3' width='100%' style='margin-bottom: -3px;'>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_1' colspan='2' align='center'>";
  if ($tipo_bilancio == "entrata") echo "<b>DETTAGLI ENTRATA</b>";
  else if ($tipo_bilancio == "uscita") echo "<b>DETTAGLI USCITA</b>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>data</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";

  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data"
        value="<?php echo globo_converti_data_in_formato_ita($data); ?>" data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";
  echo "<input type='hidden' name='data_old' value='" . globo_converti_data_in_formato_ita($data) . "' />";
  echo "<tr>";
  if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='left' valign='middle'><b>cliente</b> <font color='red'>obb</font></td>";
  else echo "<td class='tab_int_td_2' align='left' valign='middle'><b>fornitore</b> <font color='red'>obb</font></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='200' name='cliente_fornitore' value='$cliente_fornitore' /></td>";
  echo "</tr>";
  echo "<input type='hidden' name='cliente_fornitore_old' value='$cliente_fornitore' />";
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>e-mail</b> <font color='red'>separare gli indirizzi con ;</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='450' name='email' value='$email' /></td>";
    echo "</tr>";
  }
  else {
    echo" <input type='hidden' name='email' value='' />";  
  }

  if ($tipo_bilancio == "entrata" && $num_tipocli > 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>tipo</b> <font color='red'>obb</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='id_bilanciov2_tipo_cliente'>";
    if ($id_bilanciov2_tipo_cliente <= 0) echo "<option value='-1' selected='selected'></option>";
    foreach ($res_tipocli as $r_tipocli) { 
      if ($id_bilanciov2_tipo_cliente == $r_tipocli["id"]) 
        echo "<option value='" . $r_tipocli["id"] . "' selected='selected'>" . $r_tipocli["nome"] . "</option>";
      else
        echo "<option value='" . $r_tipocli["id"] . "'>" . $r_tipocli["nome"] . "</option>";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo" <input type='hidden' name='id_bilanciov2_tipo_cliente' value='-1' />";  
  }
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>dati fatturazione</b> "; if (TUTTO_MINUSCOLO) echo "<font color='red'>maiusc/minusc</font>"; echo "</td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='dati_fatturazione' rows='5' cols='' class='standard'>$dati_fatturazione</textarea></td></tr>";
  }
  else {
    echo "<input type='hidden' name='dati_fatturazione' value='' />";
  }
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>fattura</b></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='6' maxlength='8' name='fattura' value='$fattura' /></td></tr>";
  }
  else {
    echo "<input type='hidden' name='fattura' value='$fattura' />";
  }
  echo "<input type='hidden' name='fattura_old' value='$fattura' />";
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>modalit� di pagamento</b> <font color='red'>maiusc/minusc</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><textarea name='modalita_pagamento' rows='2' cols='' class='standard'>$modalita_pagamento</textarea></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>banca di riferimento</b> <font color='red'>maiusc/minusc</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><textarea name='banca' rows='2' cols='' class='standard'>$banca</textarea></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>note fattura</b> <font color='red'>maiusc/minusc</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><textarea name='note_fattura' rows='2' cols='' class='standard'>$note_fattura</textarea></td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='modalita_pagamento' value='' />";  
    echo "<input type='hidden' name='banca' value='' />";  
    echo "<input type='hidden' name='note_fattura' value='' />";  
  }
  
  $totale = 0;
  
  $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2' ORDER BY id";
  $res = $db->query($query)->fetchAll();
  
  foreach ($res as $r) { 
    $imponibile = $r["imponibile"];
    $iva = $r["iva"];
    $totale += $imponibile + $imponibile * ($iva / 100);
  }

  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>totale</b></td><td class='tab_int_td_3' align='left' valign='middle'><span id='totale'>" . globo_2dec($totale) . " iva inclusa</span></td></tr>";
  echo "<input type='hidden' name='totale_old' value='" . globo_2dec($totale) . "' />";  
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>esigibilit�</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_esigibilita'>";
    if ($flag_esigibilita == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
    else echo "<option value='1'>s&igrave;</option>";  
    if ($flag_esigibilita == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='flag_esigibilita' value='1'>";  
  }

  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='flag_pagamento' onchange='modificaFlagPagamento()'>";
  if ($flag_pagamento == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";
  if ($flag_pagamento == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
  else echo "<option value='1'>s&igrave;</option>";  
  if ($flag_pagamento == 2) echo "<option value='2' selected='selected'>pagamento parziale</option>";
  else echo "<option value='2'>pagamento parziale</option>";  
  echo "</select>";
  if ($flag_pagamento == 1) echo "&nbsp;&nbsp;&nbsp;$data_pagamento";
  else {
    echo "&nbsp;&nbsp;&nbsp;<span id='credito_debito_attuale'";
    if ($flag_pagamento != 2) echo " style='visibility: hidden;'";
    echo ">";
    if ($tipo_bilancio == "entrata") echo "<b>credito attuale</b>";
    else echo "<b>debito attuale</b>";
    echo "&nbsp;&nbsp;<input type='text' class='input_text' style='width: 60px; text-align: right;' maxlength='10' name='credito_debito_attuale' value='$credito_debito_attuale' />";
    echo "</span>";
  }
  echo "</td>";
  echo "</tr>";
  echo "<input type='hidden' name='flag_pagamento_old' value='$flag_pagamento' />";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>scadenza pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";

  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data_scadenza_pagamento"
        value="<?php echo globo_converti_data_in_formato_ita($data_scadenza_pagamento); ?>" data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
      <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";

  if ($tipo_bilancio == "entrata" && BILV2_SOLL) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>sollecito pagamento via e-mail</b> <font color='red'>" . BILV2_SOLL_GIO_RIT . " gg dopo scad pag</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_sollecito'>";
    if ($flag_sollecito == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    if ($flag_sollecito == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
    else echo "<option value='1'>s&igrave;</option>";  
    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='flag_sollecito' value='0'>";  
  }

/******************************************************************** inizio AGENTE E PROVVIGIONI ********************************************************************/

  if (BILV2_AG && $tipo_bilancio == "entrata" && $num_ag > 0) {
    $colore_bg = "#d7d78a";
    
    echo "<tr>";
    echo "<td class='tab_int_td_2' style='background-color: $colore_bg;' align='left' valign='middle'><b>agente</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='id_bilanciov2_agente' id='id_bilanciov2_agente' onchange='modificaAgente();'>";
    if ($id_bilanciov2_agente <= 0) echo "<option value='0' selected='selected'></option>";
    else echo "<option value='0'></option>";
    foreach ($res_ag as $r_ag) { 
      if ($id_bilanciov2_agente == $r_ag["id"]) 
        echo "<option value='" . $r_ag["id"] . "' selected='selected'>" . $r_ag["nome"] . "</option>";
      else
        echo "<option value='" . $r_ag["id"] . "'>" . $r_ag["nome"] . "</option>";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "<tr><td class='tab_int_td_2' style='background-color: $colore_bg;' align='left' valign='middle'><b>percentuale provvigione</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    if ($id_bilanciov2_agente == 0) echo "<input type='text' class='input_text' size='4' maxlength='2' name='perc_provvigione' id='perc_provvigione' value='$perc_provvigione' style='visibility: hidden;' onchange='aggiornaTotProvvigione();' />";
    else echo "<input type='text' class='input_text' size='4' maxlength='2' name='perc_provvigione' id='perc_provvigione' value='$perc_provvigione' onchange='aggiornaTotProvvigione();' />";
    
    $totale_provvigione = 0;
    
    $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2' ORDER BY id";
    $res = $db->query($query)->fetchAll();
    
    foreach ($res as $r) { 
      $imponibile = $r["imponibile"];
      $totale_provvigione += $imponibile * ($perc_provvigione / 100);
    }
    
    if ($id_bilanciov2_agente == 0 || $totale_provvigione == 0) echo "&nbsp;&nbsp;&nbsp;<span id='totale_provvigione' style='visibility: hidden;'>";
    else echo "&nbsp;&nbsp;&nbsp;<span id='totale_provvigione'>";
    echo globo_2dec($totale_provvigione);
    echo " iva esclusa</span>";

    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='id_bilanciov2_agente' value='0' />";
    echo "<input type='hidden' name='perc_provvigione' value='0' />";
  }

/******************************************************************** fine AGENTE E PROVVIGIONI ********************************************************************/

  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>note</b></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='note' rows='8' cols='' class='standard'>$note</textarea></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>ultima modifica</b></td><td class='tab_int_td_3' align='left' valign='middle'>$utente_ultima_modifica - " . $dt_ultima_modifica . "</td></tr>";
  echo "</table>";
  
/******************************************************************** inizio DETTAGLI ********************************************************************/

  echo "<table class='tab_int' cellspacing='3' width='100%'>";
  echo "<tr>";
  if ($num_cat > 1) echo "<td class='tab_int_td_2' align='center'><b>categoria</b></td>";
  echo "<td class='tab_int_td_2' align='center' width='100%'><b>descrizione</b> "; if ($tipo_bilancio == "entrata" && TUTTO_MINUSCOLO) echo "<font color='red'>maiusc/minusc</font>"; echo "</td>";
  echo "<td class='tab_int_td_2' align='center'><b>canone</b></td>";
  echo "<td class='tab_int_td_2 nobr' align='center'><b>scadenza canone</b></td>";
  echo "<td class='tab_int_td_2' align='center'><b>ricorda</b></td>";
  echo "<td class='tab_int_td_2' align='center'><b>imponibile</b></td>";
  echo "<td class='tab_int_td_2 nobr' align='center'><b>iva %</b></td>";
  echo "</tr>";
  
  $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2' ORDER BY id";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  for ($i = 0; $i < BILV2_NUM_RIGHE; $i++) {
    if ($i < $num) {
      $id_bilanciov2_dettaglio = $res[$i]["id"];
      $id_bilanciov2_categoria = $res[$i]["id_bilanciov2_categoria"];
      $descrizione = globo_converti_acapo_da_html($res[$i]["descrizione"]);
      $flag_canone = $res[$i]["flag_canone"];
      $data_scadenza_canone = $res[$i]["data_scadenza_canone"];
      $flag_ricorda = $res[$i]["flag_ricorda"];
      $imponibile = $res[$i]["imponibile"];
      $iva = $res[$i]["iva"];
    }
    else {
      $id_bilanciov2_dettaglio = -1;
      $id_bilanciov2_categoria = -1;
      $descrizione = "";
      $flag_canone = -1;
      $data_scadenza_canone = "";
      $flag_ricorda = -1;
      $imponibile = "";
      $iva = "";
    }

    echo "<tr>";
    
    if ($num_cat > 1) {
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<select name='id_bilanciov2_categoria_" . $i . "'>";
      if ($id_bilanciov2_categoria <= 0) echo "<option value='-1' selected='selected'></option>";
      foreach ($res_cat as $r_cat) { 
        if ($id_bilanciov2_categoria == $r_cat["id"]) 
          echo "<option value='" . $r_cat["id"] . "' selected='selected'>" . $r_cat["nome"] . "</option>";
        else
          echo "<option value='" . $r_cat["id"] . "'>" . $r_cat["nome"] . "</option>";
      }
      echo "</select>";
      echo "</td>";
    }
    else {
      echo" <input type='hidden' name='id_bilanciov2_categoria_" . $i . "' value='-1' />";  
    }
    
    if ($tipo_bilancio == "entrata") {
      echo "<td class='tab_int_td_3' align='center'><textarea name='descrizione_" . $i . "' rows='3' cols='' style='width: 95%;'>$descrizione</textarea></td>";
    }
    else {
      echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 95%;' maxlength='250' name='descrizione_" . $i . "' value='$descrizione' /></td>";
    }
    
    echo "<td class='tab_int_td_3' align='center'>";
    echo "<select name='flag_canone_" . $i . "'>";
    if ($flag_canone == -1) echo "<option value='-1' selected='selected'></option>";
    if ($flag_canone == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
    else echo "<option value='1'>s&igrave;</option>";
    if ($flag_canone == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    echo "</select>";
    echo "</td>";
    echo "<td class='tab_int_td_3 nobr' align='center'>";
    
    ?>
      <span data-flatpickr>
        <input type="text" class="input_text data" maxlength="10" name="data_scadenza_canone_<?php echo $i; ?>"
          value="<?php echo globo_converti_data_in_formato_ita($data_scadenza_canone); ?>" data-input autocomplete="off" />
        <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
        <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
      </span>
    <?php
    
    echo "</td>";
    echo "<td class='tab_int_td_3' align='center'>";
    echo "<select name='flag_ricorda_" . $i . "'>";
    if ($flag_canone == 0 || $flag_ricorda == -1) echo "<option value='-1' selected='selected'></option>";
    if ($flag_canone == 1 && $flag_ricorda == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
    else echo "<option value='1'>s&igrave;</option>";
    if ($flag_canone == 1 && $flag_ricorda == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    echo "</select>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 60px; text-align: right;' maxlength='10' name='imponibile_" . $i . "' id='imponibile_" . $i . "' value='$imponibile' onchange='aggiornaTot();' /></td>";
    echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 25px; text-align: right;' maxlength='2' name='iva_" . $i . "' id='iva_" . $i . "' value='$iva' onchange='aggiornaTot();' /></td>";
    echo "</tr>";  
    echo "<input type='hidden' name='id_bilanciov2_dettaglio_" . $i . "' value='$id_bilanciov2_dettaglio' />";
  }
  
  echo "</table>";
  
/******************************************************************** fine DETTAGLI ********************************************************************/

  echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='aggiorna' class='input_bottone' />";
  else echo "<input type='submit' value='aggiorna' class='input_bottone_dis' disabled='disabled' />";
  echo "</form>";

  echo "<form action='bilv2_el.php' method='post' onsubmit='return confermaEliminazione();' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) echo "<input type='submit' value='elimina' class='input_bottone' />";
  else echo "<input type='submit' value='elimina' class='input_bottone_dis' disabled='disabled' />";
  echo "</form>";
  
  if ($tipo_bilancio == "entrata" && !in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) {
    echo "<form action='bilv2_ins_1.php' method='post' class='margin_top_bottoni'>";
    echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
    echo "<input type='hidden' name='anno_bilancio' value='" . date("Y") . "' />";
    echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
    echo "<input type='hidden' name='copia' value='intestazione' />";
    echo "<input type='submit' value='copia intestazione' class='input_bottone' />";
    echo "</form>";
    
    echo "<form action='bilv2_ins_1.php' method='post' class='margin_top_bottoni'>";
    echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
    echo "<input type='hidden' name='anno_bilancio' value='" . date("Y") . "' />";
    echo "<input type='hidden' name='id_bilanciov2' value='$id_bilanciov2' />";
    echo "<input type='hidden' name='copia' value='entrata' />";
    echo "<input type='submit' value='copia entrata' class='input_bottone' />";
    echo "</form>";
  }
  
  if ($tipo_bilancio == "entrata") echo "<form action='bilv2_lista.php#$fattura' method='post' class='margin_top_bottoni'>";
  else if ($tipo_bilancio == "uscita") echo "<form action='bilv2_lista.php' method='post' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
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
