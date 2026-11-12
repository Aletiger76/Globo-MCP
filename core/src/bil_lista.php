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
  
  if (isset($_SESSION["ksglobo_ricerca_bilancio"]["tipo"]) && $_SESSION["ksglobo_ricerca_bilancio"]["tipo"] != $tipo_bilancio) 
    unset($_SESSION["ksglobo_ricerca_bilancio"]);

  if (isset($_POST["ricerca_mese"])) $ricerca_mese = $_POST["ricerca_mese"];  
  else if (isset($_SESSION["ksglobo_ricerca_bilancio"]["mese"])) $ricerca_mese = $_SESSION["ksglobo_ricerca_bilancio"]["mese"];
  else $ricerca_mese = "";  
  
  if (isset($_POST["ricerca_testo"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"])); 
    else $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]); 
  }
  else if (isset($_SESSION["ksglobo_ricerca_bilancio"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_bilancio"]["testo"];
  else $ricerca_testo = ""; 
  
  if (isset($_POST["ricerca_flag_pagamento"])) $ricerca_flag_pagamento = $_POST["ricerca_flag_pagamento"];  
  else if (isset($_SESSION["ksglobo_ricerca_bilancio"]["flag_pagamento"])) $ricerca_flag_pagamento = $_SESSION["ksglobo_ricerca_bilancio"]["flag_pagamento"];
  else $ricerca_flag_pagamento = -1;  

  unset($_SESSION["ksglobo_ricerca_bilancio"]);
  $_SESSION["ksglobo_ricerca_bilancio"]["tipo"] = $tipo_bilancio;
  $_SESSION["ksglobo_ricerca_bilancio"]["mese"] = $ricerca_mese;
  $_SESSION["ksglobo_ricerca_bilancio"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_bilancio"]["flag_pagamento"] = $ricerca_flag_pagamento;
  
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

  echo "<form action='bil_lista.php' method='post' class='margin_bottom_blocchi'>";
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
  echo "<td class='tab_int_td_2' width='50%' align='left'><b>testo</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left'>";
  echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
  echo "</td>";
  echo "</tr>";
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
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='ricerca' class='input_bottone' />";
  echo "</form>";
  
  /*
  echo "<form action='bil_ins_1.php' method='post' class='margin_bottom_blocchi'>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='nuova entrata' class='input_bottone' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='nuova uscita' class='input_bottone' />";
  echo "</form>";
  */

  if ($ricerca_mese == "") {
    $query = "SELECT * FROM bilancio";
    $query .= " WHERE tipo = '$tipo_bilancio'";
    $query .= " AND data LIKE '$anno_bilancio%'";
    $query .= " AND (descrizione LIKE '%$ricerca_testo%' OR fattura LIKE '%$ricerca_testo%')";
    if ($ricerca_flag_pagamento >= 0) $query .= " AND flag_pagamento = '$ricerca_flag_pagamento'";
    $query .= " ORDER BY data, fattura, id";
  }
  else {
    $query = "SELECT * FROM bilancio";
    $query .= " WHERE tipo = '$tipo_bilancio'";
    $query .= " AND data LIKE '$anno_bilancio-$ricerca_mese-%'";
    $query .= " AND (descrizione LIKE '%$ricerca_testo%' OR fattura LIKE '%$ricerca_testo%')";
    if ($ricerca_flag_pagamento >= 0) $query .= " AND flag_pagamento = '$ricerca_flag_pagamento'";
    $query .= " ORDER BY data, fattura, id";
  }
  
  $res = $db->query($query)->fetchAll();
  $num = count($res);

  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
  
  if ($num == 0) {
    if ($tipo_bilancio == "entrata") {
      echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>ENTRATE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
      echo "<tr><td class='tab_int_td_3' colspan='6' align='center'><b>nessuna entrata</b></td></tr>";
      echo "</table>";
    }
    else if ($tipo_bilancio == "uscita") {
      echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>USCITE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
      echo "<tr><td class='tab_int_td_3' colspan='6' align='center'><b>nessuna uscita</b></td></tr>";
      echo "</table>";
    }
  }
  else {
    if ($tipo_bilancio == "entrata") echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>ENTRATE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
    else if ($tipo_bilancio == "uscita") echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>USCITE $ricerca_mese_lettere $anno_bilancio</b></td></tr>";
  }

  if ($num > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='center'><b>data</b></td>";
    echo "<td class='tab_int_td_2' align='left' width='100%'><b>descrizione</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>fatt</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='right'><b>importo</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>pag</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    echo "</tr>";
    
    $tot = 0;
    $mese = 0;
    $mese_precedente = 0;
    
    foreach ($res as $r) { 
      $id_bilancio = $r["id"];
      $data = $r["data"];
      $mese = date("m", strtotime($data));
      $descrizione = $r["descrizione"];
      /*
      $lunghezza_descrizione = strlen($descrizione);
      if ($lunghezza_descrizione > 40) $descrizione_breve = substr($descrizione, 0, 40) . " ...";
      else $descrizione_breve = $descrizione;
      */
      $fattura = $r["fattura"];
      if ($fattura != "s" && $fattura != "n") $fattura = globo_converti_num_fattura($fattura);
      if ($fattura == "s") $fattura_vis = "s&igrave;";
      else if ($fattura == "n") $fattura_vis = "no";
      else $fattura_vis = $fattura;
      $importo = $r["importo"];
      $tot = $tot + $importo;
      $flag_esigibilita = $r["flag_esigibilita"];
      $flag_pagamento = $r["flag_pagamento"];
      if ($flag_esigibilita == 0) $pagamento = "<span style='color: #a900c8;'>ne</span>";
      else if ($flag_pagamento == 0) $pagamento = "<span style='color: red;'>no</span>";
      else if ($flag_pagamento == 1) $pagamento = "<span style='color: green;'>s&igrave;</span>";

      if ($mese != $mese_precedente) {
        if ($ricerca_mese == "") {
          echo "<tr><td class='tab_int_td_2' colspan='6' align='center'>";
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
      echo "<td class='tab_int_td_3' align='left'>$descrizione</td>";
      echo "<td class='tab_int_td_3' align='center'>$fattura_vis</td>";
      echo "<td class='tab_int_td_3' align='right'>$importo</td>";
      echo "<td class='tab_int_td_3' align='center'>$pagamento</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='bil_vis.php' method='post'>";
      echo "<input type='hidden' name='id_bilancio' value='$id_bilancio' />";
      echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
      echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
      echo "<input type='image' src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</td>";
      echo "</tr>";
      
      $mese_precedente = $mese;
    }
    
    $tot_parte_decimale = strstr($tot, '.');
    if (strlen($tot_parte_decimale) == 0) $tot = $tot . ".00";
    else if (strlen($tot_parte_decimale) == 2) $tot = $tot . "0";

    echo "<tr>";
    echo "<td class='tab_int_td_2' colspan='3'>&nbsp;</td>";
    echo "<td class='tab_int_td_2' colspan='1' align='right'><b>$tot</b></td>";
    echo "<td class='tab_int_td_2' colspan='2'>&nbsp;</td>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
  }
  
  echo "<form action='bil_ins_1.php' method='post'>";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  if ($tipo_bilancio == "entrata") echo "<input type='submit' value='nuova entrata' class='input_bottone' disabled='disabled' />";
  else if ($tipo_bilancio == "uscita") echo "<input type='submit' value='nuova uscita' class='input_bottone' disabled='disabled' />";
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
