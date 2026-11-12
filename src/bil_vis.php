<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIO') && MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function controllaForm() {
    var re = / /g;
    var data = document.globo.data.value.replace(re, "");
    var descrizione = document.globo.descrizione.value.replace(re, "");
    var importo = document.globo.importo.value.replace(re, "");
    if (data == "") { alert("compilare il campo \"data\""); document.globo.data.focus(); return false; }
    if (descrizione == "") { alert("compilare il campo \"descrizione\""); document.globo.descrizione.focus(); return false; }
    if (importo == "") { alert("compilare il campo \"importo\""); document.globo.importo.focus(); return false; }
    if (isNaN(importo)) { alert("inserire nel campo \"importo\" un valore numerico"); document.globo.importo.focus(); return false; }
  }
  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
<link rel="stylesheet" href="css/flatpickr.min.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>BILANCIO</b></td></tr></table>
<?php
  $id_bilancio = $_POST["id_bilancio"] ?? '';
  $tipo_bilancio = $_POST["tipo_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';

  $query = "SELECT * FROM bilancio WHERE id = '$id_bilancio'";
  $res = $db->query($query)->fetchAll();
  
  $data = $res[0]["data"];
  $descrizione = globo_converti_acapo_da_html($res[0]["descrizione"]);
  $fattura = $res[0]["fattura"];
  if ($fattura != "s" && $fattura != "n") $fattura = globo_converti_num_fattura($fattura);
  if ($fattura == "s") $fattura_vis = "s&igrave;";
  else if ($fattura == "n") $fattura_vis = "no";
  else $fattura_vis = $fattura;
  $importo = globo_2dec($res[0]["importo"]);
  $flag_esigibilita = $res[0]["flag_esigibilita"];
  $flag_pagamento = $res[0]["flag_pagamento"];
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];
  $dt_ultima_modifica = $res[0]["dt_ultima_modifica"];

  $query = "SELECT data FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_scad_pag'";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  if ($num > 0) $data_scad_pag = $res[0]["data"];
  else $data_scad_pag = "";
  
  $query = "SELECT data FROM scadenza WHERE id_bilancio = '$id_bilancio' AND tipo = 'bilancio_rinnovo'";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  if ($num > 0) $data_rinnovo = $res[0]["data"];
  else $data_rinnovo = "";
  
  echo "<form name='globo' action='bil_agg.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
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
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>descrizione</b> <font color='red'>obb</font></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='descrizione' rows='7' cols='' class='standard'>$descrizione</textarea></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>fattura</b></td><td class='tab_int_td_3' align='left' valign='middle'>$fattura_vis</td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>importo</b> <font color='red'>xx.xx</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='10' maxlength='10' name='importo' value='$importo' /></td></tr>";

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
  echo "<select name='flag_pagamento'>";
  if ($flag_pagamento == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";
  if ($flag_pagamento == 1) echo "<option value='1' selected='selected'>s&igrave;</option>";
  else echo "<option value='1'>s&igrave;</option>";  
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>scadenza pagamento</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";

  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data_scad_pag"
        value="<?php echo globo_converti_data_in_formato_ita($data_scad_pag); ?>" data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
      <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>rinnovo</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";

  ?>
    <span data-flatpickr>
      <input type="text" class="input_text data" maxlength="10" name="data_rinnovo"
        value="<?php echo globo_converti_data_in_formato_ita($data_rinnovo); ?>" data-input autocomplete="off" />
      <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
      <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
    </span>
  <?php

  echo "</td>";
  echo "</tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>ultima modifica</b></td><td class='tab_int_td_3' align='left' valign='middle'>$utente_ultima_modifica - " . globo_converti_datetime_in_formato_ita($dt_ultima_modifica) . "</td></tr>";
  echo "</table>";
  echo "<input type='hidden' name='id_bilancio' value='$id_bilancio'>";
  echo "<input type='hidden' name='fattura' value='$fattura' />";
  echo "<input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='aggiorna' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bil_el.php' method='post' onsubmit='return confermaEliminazione();' class='margin_top_bottoni'>";
  echo" <input type='hidden' name='tipo_bilancio' value='$tipo_bilancio' />";
  echo "<input type='hidden' name='id_bilancio' value='$id_bilancio' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='elimina' class='input_bottone' disabled='disabled' />";
  echo "</form>";
  
  echo "<form action='bil_lista.php' method='post' class='margin_top_bottoni'>";
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
