<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && defined('BILV2_AG') && BILV2_AG && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function controllaForm() {
    var re = / /g;
    var re_email = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
    var nome = document.globo.nome.value.replace(re, "");
    var email = document.globo.email.value;
    var totale_provvigioni_pagate = document.globo.totale_provvigioni_pagate.value;
    if (nome == "") { alert("compilare il campo \"nome/azienda\""); document.globo.nome.focus(); return false; }
    if (email != "" && !re_email.test(email)) { alert("indirizzo e-mail non corretto"); document.globo.email.focus(); return false; }
    if (isNaN(totale_provvigioni_pagate) || totale_provvigioni_pagate < 0) { alert("compilare il campo \"totale provvigioni pagate\" con un valore numerico maggiore o uguale a zero"); document.globo.totale_provvigioni_pagate.focus(); return false; }
  }
  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }
  function cambiaSfondo(fattura) {
    if (document.getElementById("checkbox_" + fattura).checked) document.getElementById("div_" + fattura).style.backgroundColor="#ffff9a";
    else document.getElementById("div_" + fattura).style.backgroundColor="#e8e8e8";
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<?php if (!isset($_GET["sv"])) { ?>
<div class="header"><?php require("varie/header.php"); ?></div>
<?php } ?>
<table class="tab_int" cellspacing="3" width="100%">
<?php if (!isset($_GET["sv"])) { ?>
<tr><td class="tab_int_td_titolo" align="center"><b>AGENTI</b></td></tr></table>
<?php } ?>
<?php
  $id_bilanciov2_agente = $_POST["id_bilanciov2_agente"] ?? '';
  
  $query = "SELECT * FROM bilanciov2_agente WHERE id = '$id_bilanciov2_agente'";
  $res = $db->query($query)->fetchAll();
  
  $nome = globo_converti_virgolette_apici_in_html($res[0]["nome"]);
  $email = $res[0]["email"];
  $note = globo_converti_acapo_da_html($res[0]["note"]);
  $fatture_provvigioni_pagate = $res[0]["fatture_provvigioni_pagate"];
  $fatture_provvigioni_pagate_arr = explode(";", $fatture_provvigioni_pagate);
  $provvigioni_pagate = globo_converti_acapo_da_html($res[0]["provvigioni_pagate"]);
  $totale_provvigioni_pagate = $res[0]["totale_provvigioni_pagate"];
  
  echo "<form name='globo' action='bilv2ag_agg.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";  
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>DETTAGLI AGENTE</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>nome/azienda</b> <font color='red'>obb</font></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='250' name='nome' value='$nome' /></td></tr>";
  echo "<input type='hidden' name='nome_old' value='$nome' />";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>e-mail sollecito pagamento</b> <font color='red'>se presente, l'agente riceve i solleciti di pagamento delle fatture associate ad esso</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='100' name='email' value='$email' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>note</b></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='note' rows='6' cols='' class='standard'>$note</textarea></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='top'>";
  echo "<b>fatture e provvigioni</b> iva esc";
  echo "<div style='padding-top: 10px;'>";
  echo "<span style='color: #a900c8;'>viola</span> = fattura non esigibile<br />";
  echo "<span style='color: red;'>rosso</span> = fattura non pagata<br />";
  echo "<span style='color: green;'>verde</span> = fattura pagata<br />";
  echo "<span style='color: blue;'>blu</span> = fattura pagata parzialmente<br />";
  echo "<span style='background-color: #ffff9a; padding: 0px 4px 0px 4px;'>sfondo giallo</span> = provvigione pagata<br />";
  echo "</div>";
  echo "</td>";
  echo "<td class='tab_int_td_3' align='left' valign='top'>";
  
  $query_1 = "SELECT * FROM bilanciov2 WHERE tipo = 'entrata' AND flag_esigibilita = '1' AND id_bilanciov2_agente = '$id_bilanciov2_agente' ORDER BY data ASC";
  $res_1 = $db->query($query_1)->fetchAll();
  $num_1 = count($res_1);
  
  $i = 0;
  $totale_provvigioni = 0;
  $totale_provvigioni_pagabili = 0;
  
  if ($num_1 == 0) echo "nessuna fattura associata a questo agente";
  
  foreach ($res_1 as $r_1) { 
    $id_bilanciov2 = $r_1["id"];
    $data = globo_converti_data_in_formato_ita($r_1["data"]);
    $anno = date("Y", strtotime($r_1["data"]));
    $cliente_fornitore = $r_1["cliente_fornitore"];
    $fattura = $r_1["fattura"];
    $flag_esigibilita = $r_1["flag_esigibilita"];
    $flag_pagamento = $r_1["flag_pagamento"];
    $perc_provvigione = $r_1["perc_provvigione"];
    $imponibile = 0;
    
    $query_2 = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2'";
    $res_2 = $db->query($query_2)->fetchAll();
    $num_2 = count($res_2);
    
    foreach ($res_2 as $r_2) {
      $imponibile += $r_2["imponibile"];
    }
    
    $provvigione = $imponibile * $perc_provvigione / 100;
    
    $imponibile = number_format($imponibile, 2, '.', '');
    $provvigione = number_format($provvigione, 2, '.', '');
    
    $totale_provvigioni += $provvigione;
    if ($flag_pagamento == 1 && !in_array($fattura . "_" . $anno, $fatture_provvigioni_pagate_arr)) $totale_provvigioni_pagabili += $provvigione;

    if ($i > 0) echo "<table cellpadding='0' cellspacing='0' border='0' style='margin-top: 10px;'>";
    else echo "<table cellpadding='0' cellspacing='0' border='0'>";
    echo "<tr>";
    echo "<td valign='middle' style='padding-right: 4px;'>";
    
    if (in_array($fattura . "_" . $anno, $fatture_provvigioni_pagate_arr)) {
      $checked = "checked='checked'";
      $style = "background-color: #ffff9a;";
    }
    else {
      $checked = "";
      $style = "";
    }
    
    echo "<input type='checkbox' $checked name='fattura[]' id='checkbox_" . $fattura . "_" . $anno . "' value='" . $fattura . "_" . $anno . "' onchange='cambiaSfondo(\"" . $fattura . "_" . $anno . "\");' />";
    echo "</td>";
    echo "<td id='div_" . $fattura . "_" . $anno . "' valign='middle' style='padding: 1px 4px 2px 4px;$style'>";
    if ($flag_esigibilita == 0) echo "<div style='color: #a900c8;'>";
    else if ($flag_pagamento == 0) echo "<div style='color: red;'>";
    else if ($flag_pagamento == 1) echo "<div style='color: green;'>";
    else if ($flag_pagamento == 2) echo "<div style='color: blue;'>";
    echo "fattura $fattura/$anno del $data ($imponibile)";
    echo "<br />";
    echo $cliente_fornitore;
    echo "</div>";
    echo "provvigione $perc_provvigione% ($provvigione)";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    
    $i++;
  }

  echo "</td>";
  echo "</tr>"; 
  echo "<input type='hidden' name='fatture_provvigioni_pagate_old' value='$fatture_provvigioni_pagate' />"; 
  
  $totale_provvigioni = number_format($totale_provvigioni, 2, '.', '');
  $totale_provvigioni_pagabili = number_format($totale_provvigioni_pagabili, 2, '.', '');
  
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>totale provvigioni</b> iva esc</td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>$totale_provvigioni</td>";
  echo "</tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>provvigioni pagate</b> iva esc</td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='provvigioni_pagate' rows='12' cols='' class='standard'>$provvigioni_pagate</textarea></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>totale provvigioni pagate</b> iva esc</td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' maxlength='10' name='totale_provvigioni_pagate' value='$totale_provvigioni_pagate' style='width: 70px;' /></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>totale provvigioni attualmente pagabili</b> iva esc<br /><font color='red'>calcolato sulla base della sezione fatture e provvigioni</font></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>$totale_provvigioni_pagabili</td>";
  echo "</tr>";  
  echo "<input type='hidden' name='totale_provvigioni_pagate_old' value='$totale_provvigioni_pagate' />";
  echo "</table>";
  echo "<input type='hidden' name='id_bilanciov2_agente' value='$id_bilanciov2_agente' />";
  echo "<input type='submit' value='aggiorna' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bilv2ag_el.php' method='post' onsubmit='return confermaEliminazione();' class='margin_top_bottoni'>";
  echo "<input type='hidden' name='id_bilanciov2_agente' value='$id_bilanciov2_agente' />";
  echo "<input type='submit' value='elimina' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bilv2ag_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco agenti' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
