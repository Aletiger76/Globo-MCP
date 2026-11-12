<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<?php
  $tipo_bilancio = $_POST["tipo_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
  
  $_SESSION["ksglobo_bilanciov2_anno"] = $anno_bilancio; // imposto nuovamente il valore nella sessione per gestire il "copia entrata" e il "copia intestazione" nella visualizzazione dell'entrata

  $query_cat = "SELECT * FROM bilanciov2_categoria WHERE tipo = '$tipo_bilancio' ORDER BY nome";
  $res_cat = $db->query($query_cat)->fetchAll();
  $num_cat = count($res_cat);
  
  $query_tipocli = "SELECT * FROM bilanciov2_tipo_cliente ORDER BY nome";
  $res_tipocli = $db->query($query_tipocli)->fetchAll();
  $num_tipocli = count($res_tipocli);
  
  $query_ag = "SELECT id, nome FROM bilanciov2_agente ORDER BY nome";
  $res_ag = $db->query($query_ag)->fetchAll();
  $num_ag = count($res_ag);
  
  if (isset($_POST["id_bilanciov2"])) {
    $query = "SELECT * FROM bilanciov2 WHERE id = '" . $_POST["id_bilanciov2"] . "'";
    $res = $db->query($query)->fetchAll();

    $cliente_fornitore = $res[0]["cliente_fornitore"];
    $email = $res[0]["email"];
    $id_bilanciov2_tipo_cliente = $res[0]["id_bilanciov2_tipo_cliente"];
    $dati_fatturazione = globo_converti_acapo_da_html($res[0]["dati_fatturazione"]);
    $modalita_pagamento = globo_converti_acapo_da_html($res[0]["modalita_pagamento"]);
    $banca = ""; // $banca = globo_converti_acapo_da_html($res[0]["banca"]);
    $note_fattura = globo_converti_acapo_da_html($res[0]["note_fattura"]);
    $id_bilanciov2_agente = $res[0]["id_bilanciov2_agente"];
    $perc_provvigione = $res[0]["perc_provvigione"];
  }
  else {
    $cliente_fornitore = "";
    $email = "";
    $id_bilanciov2_tipo_cliente = -1;
    $dati_fatturazione = "";
    $modalita_pagamento = "";
    $banca = "";
    $note_fattura = "";
    $id_bilanciov2_agente = 0;
    $perc_provvigione = "";
  }
  
  if ($modalita_pagamento == "") $modalita_pagamento = BILV2_MOD_PAG;
  if ($banca == "") $banca = BILV2_BANCA;
  
  if ($tipo_bilancio == "entrata") {
    $query = "SELECT fattura FROM bilanciov2 WHERE tipo = 'entrata' AND data LIKE '" . date("Y") . "-%' AND fattura > 0 ORDER BY fattura DESC LIMIT 1";
    $res = $db->query($query)->fetchAll();
    $num = count($res);

    if ($num > 0) {
      $fattura = $res[0]["fattura"];
      $fattura = (int)$fattura + 1;
    }
    else {
      $fattura = 1;
    }
  }
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
  echo "<form name='globo' action='bilv2_ins_2.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%' style='margin-bottom: -3px;'>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_1' colspan='2' align='center'>";
  if ($tipo_bilancio == "entrata") echo "<b>NUOVA ENTRATA</b>";
  else if ($tipo_bilancio == "uscita") echo "<b>NUOVA USCITA</b>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>data</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";
  
  $data = date("Y-m-d");
  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data"
        value="<?php echo globo_converti_data_in_formato_ita($data); ?>" data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  if ($tipo_bilancio == "entrata") echo "<td class='tab_int_td_2' align='left' valign='middle'><b>cliente</b> <font color='red'>obb</font></td>";
  else echo "<td class='tab_int_td_2' align='left' valign='middle'><b>fornitore</b> <font color='red'>obb</font></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='200' name='cliente_fornitore' value='$cliente_fornitore' /></td>";
  echo "</tr>";
  
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
    echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>fattura</b> <font color='red'>obb</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='6' maxlength='8' name='fattura' value='" . globo_converti_num_fattura($fattura, date("Y")) . "' /></td></tr>";
  }
  else {
    echo "<input type='hidden' name='fattura' value='' />";
  }
  
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
    echo" <input type='hidden' name='modalita_pagamento' value='' />";  
    echo" <input type='hidden' name='banca' value='' />";  
    echo" <input type='hidden' name='note_fattura' value='' />";  
  }

  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>totale</b></td><td class='tab_int_td_3' align='left' valign='middle'><span id='totale'>0.00</span></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='flag_pagamento'>";
  echo "<option value='0' selected='selected'>no</option>";
  echo "<option value='1'>s&igrave;</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>scadenza pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";

  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data_scadenza_pagamento" value=""
        data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
      <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";
  
  if ($tipo_bilancio == "entrata") {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>sollecito pagamento via e-mail</b> <font color='red'>" . BILV2_SOLL_GIO_RIT . " gg dopo scad pag</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_sollecito'>";
    echo "<option value='0' selected='selected'>no</option>";
    echo "<option value='1'>s&igrave;</option>";  
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
    
    if (isset($_POST["id_bilanciov2"]) && (isset($_POST["copia"]) && $_POST["copia"] == "entrata")) {
      $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '" . $_POST["id_bilanciov2"] . "' ORDER BY id";
      $res = $db->query($query)->fetchAll();
      
      foreach ($res as $r) { 
        $imponibile = $r["imponibile"];
        $totale_provvigione += $imponibile * ($perc_provvigione / 100);
      }
    }
    
    if ($id_bilanciov2_agente == 0 || $totale_provvigione == 0) echo "&nbsp;&nbsp;&nbsp;<span id='totale_provvigione' style='visibility='hidden;'>";
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

  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>note</b></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='note' rows='8' cols='' class='standard'></textarea></td></tr>";
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
  
  
  if (isset($_POST["id_bilanciov2"]) && (isset($_POST["copia"]) && $_POST["copia"] == "entrata")) {
    $query = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '" . $_POST["id_bilanciov2"] . "' ORDER BY id";
    $res = $db->query($query)->fetchAll();
    $num = count($res);
  }
  else {
    $num = 0;
  }

  for ($i = 0; $i < BILV2_NUM_RIGHE; $i++) {
    if ($i < $num) {
      $id_bilanciov2_categoria = $res[$i]["id_bilanciov2_categoria"];
      $descrizione = globo_converti_acapo_da_html($res[$i]["descrizione"]);
      $flag_canone = $res[$i]["flag_canone"];
      $data_scadenza_canone = globo_converti_data_in_formato_ita($res[$i]["data_scadenza_canone"]);
      $imponibile = $res[$i]["imponibile"];
      $iva = BILV2_IVA; // $res[$i]["iva"];
    }
    else {
      $id_bilanciov2_categoria = -1;
      $descrizione = "";
      $flag_canone = -1;
      $data_scadenza_canone = "";
      $imponibile = "";
      $iva = BILV2_IVA;
    }

    echo "<tr>";
    
    if ($num_cat > 1) {
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<select name='id_bilanciov2_categoria_" . $i . "'>";
      if ($id_bilanciov2_categoria == -1) echo "<option value='-1' selected='selected'></option>";
      else echo "<option value='-1'></option>";
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
      echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 95%;' maxlength='250' name='descrizione_" . $i . "' value='' /></td>";
    }
    
    echo "<td class='tab_int_td_3' align='center'>";
    echo "<select name='flag_canone_" . $i . "'>";
    if ($flag_canone == -1) echo "<option value='-1' selected='selected'></option>";
    else echo "<option value='-1'></option>";
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
          value="<?php echo $data_scadenza_canone; ?>" data-input autocomplete="off" />
        <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
        <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
      </span>
    <?php
    
    echo "</td>";
    echo "<td class='tab_int_td_3' align='center'>";
    echo "<select name='flag_ricorda_" . $i . "'>";
    if ($data_scadenza_canone == "") {
      echo "<option value='-1' selected='selected'></option>";
      echo "<option value='1'>s&igrave;</option>";
      echo "<option value='0'>no</option>";
    } 
    else {
      echo "<option value='-1'></option>";
      echo "<option value='1' selected='selected'>s&igrave;</option>";
      echo "<option value='0'>no</option>";
    }
    echo "</select>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 60px; text-align: right;' maxlength='10' name='imponibile_" . $i . "' id='imponibile_" . $i . "' value='$imponibile' onchange='aggiornaTot();' /></td>";
    echo "<td class='tab_int_td_3' align='center'><input type='text' class='input_text' style='width: 25px; text-align: right;' maxlength='2' name='iva_" . $i . "' id='iva_" . $i . "' value='$iva' onchange='aggiornaTot();' /></td>";
    echo "</tr>";  
  }
  
  echo "</table>";
  
/******************************************************************** fine DETTAGLI ********************************************************************/

  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='inserisci' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bilv2_lista.php' method='post' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='elenco entrate' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='elenco uscite' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bilv2_home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='bilancio' class='input_bottone' />";
  echo "</form>";
?>
<script type="text/javascript">aggiornaTot();</script>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
