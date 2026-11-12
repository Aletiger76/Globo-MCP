<?php 
  require("varie/common_1.php"); 
  if (!MOD_BILANCIOV2 || ($_SESSION["ksglobo_livello_utente"] > LIV_MOD_BILANCIOV2 && (!isset($BILV2_UTENTE_RO) || !in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)))) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><strong>BILANCIO</strong></td></tr></table>
<?php 
  //$anno_oggi = date("Y"); // set anno corrente
  $anno_oggi = "2018";
  
  $anno_lim_inf = BILV2_ANNO_INIZIO;
  $anno_lim_sup = $anno_oggi;
  
  if (isset($_POST["ricerca_anno"])) $anno_bilancio = $_POST["ricerca_anno"];  
  else if (isset($_SESSION["ksglobo_bilanciov2_anno"])) $anno_bilancio = $_SESSION["ksglobo_bilanciov2_anno"];
  else $anno_bilancio = $anno_lim_sup;
  if ($anno_bilancio < $anno_lim_inf || $anno_bilancio > $anno_lim_sup) $anno_bilancio = $anno_lim_sup;
  $_SESSION["ksglobo_bilanciov2_anno"] = $anno_bilancio;
  
  echo "<table class='tab_int' cellspacing='3' width='100%' style='margin-bottom: 0px;'>";  
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><strong>BILANCIO $anno_bilancio</strong></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><strong>anno</strong></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";
  echo "<form method='post' action='bilv2_home.php'>";
  echo "<select name='ricerca_anno' onchange='this.form.submit();'>"; 
  for ($anno = $anno_lim_sup; $anno >= $anno_lim_inf; $anno--) {
    if ($anno_bilancio == $anno) echo "<option value='$anno' selected='selected'>$anno</option>";
    else echo "<option value='$anno'>$anno</option>";
  }
  echo "</select>";
  echo "</form>";
  echo "</td>";
  echo "</tr>";

  $query = "SELECT bilanciov2_dettaglio.imponibile, bilanciov2_dettaglio.iva";
  $query .= " FROM bilanciov2 JOIN bilanciov2_dettaglio ON bilanciov2.id = bilanciov2_dettaglio.id_bilanciov2";
  $query .= " WHERE bilanciov2.tipo = 'entrata' AND bilanciov2.data LIKE '$anno_bilancio%'";
  $res = $db->query($query)->fetchAll();

  $imponibile_entrate = 0;
  $iva_entrate = 0;
  
  foreach ($res as $r) { 
    $imponibile = $r["imponibile"];
    $iva = $r["iva"];
  
    $imponibile_entrate += $imponibile;
    $iva_entrate += $imponibile * ($iva / 100);
  }
  
  $imponibile_entrate = globo_2dec($imponibile_entrate);
  $iva_entrate = globo_2dec($iva_entrate);
  
  /*
  $query = "SELECT bilanciov2_dettaglio.imponibile, bilanciov2_dettaglio.iva";
  $query .= " FROM bilanciov2 JOIN bilanciov2_dettaglio ON bilanciov2.id = bilanciov2_dettaglio.id_bilanciov2";
  $query .= " WHERE bilanciov2.tipo = 'uscita' AND bilanciov2.data LIKE '$anno_bilancio%'";
  $res = $db->query($query)->fetchAll();
  
  $imponibile_uscite = 0;
  $iva_uscite = 0;
  
  foreach ($res as $r) { 
    $imponibile = $r["imponibile"];
    $iva = $r["iva"];
  
    $imponibile_uscite += $imponibile;
    $iva_uscite += $imponibile * ($iva / 100);
  }

  $imponibile_uscite = globo_2dec($imponibile_uscite);
  $iva_uscite = globo_2dec($iva_uscite);
  */
  
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><strong>imponibile entrate</strong></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$imponibile_entrate</strong></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><strong>iva entrate</strong></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$iva_entrate</strong></td></tr>";
  // echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><strong>imponibile uscite</strong></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$imponibile_uscite</strong></td></tr>";
  // echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><strong>iva uscite</strong></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$iva_uscite</strong></td></tr>";
  echo "</table>";
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
  echo "<tr><td class='tab_int_td_1' colspan='3' align='center'><strong>CREDITI</strong></td></tr>";
  
  $intest = false;
  $tot_crediti_esig = 0;
  $tot_crediti_non_esig = 0;
  
  if (in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) $anno_inizio_cred = BILV2_ANNO_INIZIO;
  else $anno_inizio_cred = 2005;

  for ($anno_cred = $anno_oggi; $anno_cred >= $anno_inizio_cred; $anno_cred--) {
    $crediti_esig = 0;
    $crediti_non_esig = 0;
    
    $query = "SELECT SUM(importo) AS crediti FROM bilancio";
    $query .= " WHERE tipo = 'entrata' AND data LIKE '$anno_cred-%' AND flag_esigibilita = '1' AND flag_pagamento = '0'";
    $res = $db->query($query)->fetchAll();
    $crediti_esig += $res[0]["crediti"];
    
    $query = "SELECT SUM(importo) AS crediti FROM bilancio";
    $query .= " WHERE tipo = 'entrata' AND data LIKE '$anno_cred-%' AND flag_esigibilita = '0' AND flag_pagamento = '0'";
    $res = $db->query($query)->fetchAll();
    $crediti_non_esig += $res[0]["crediti"];

    $query = "SELECT bilanciov2_dettaglio.imponibile, bilanciov2_dettaglio.iva";
    $query .= " FROM bilanciov2 JOIN bilanciov2_dettaglio ON bilanciov2.id = bilanciov2_dettaglio.id_bilanciov2";
    $query .= " WHERE bilanciov2.tipo = 'entrata' AND bilanciov2.data LIKE '$anno_cred-%' AND bilanciov2.flag_esigibilita = '1' AND bilanciov2.flag_pagamento = '0'";
    $res = $db->query($query)->fetchAll();

    foreach ($res as $r) { 
      $imponibile = $r["imponibile"];
      $iva = $r["iva"];
      $crediti_esig += $imponibile + $imponibile * ($iva / 100);
    }
    
    $query = "SELECT SUM(credito_debito_attuale) AS crediti_pag_parz FROM bilanciov2";
    $query .= " WHERE bilanciov2.tipo = 'entrata' AND bilanciov2.data LIKE '$anno_cred-%' AND bilanciov2.flag_esigibilita = '1' AND bilanciov2.flag_pagamento = '2'";
    $res = $db->query($query)->fetchAll();
    $crediti_esig += $res[0]["crediti_pag_parz"];

    $query = "SELECT bilanciov2_dettaglio.imponibile, bilanciov2_dettaglio.iva";
    $query .= " FROM bilanciov2 JOIN bilanciov2_dettaglio ON bilanciov2.id = bilanciov2_dettaglio.id_bilanciov2";
    $query .= " WHERE bilanciov2.tipo = 'entrata' AND bilanciov2.data LIKE '$anno_cred-%' AND bilanciov2.flag_esigibilita = '0' AND bilanciov2.flag_pagamento = '0'";
    $res = $db->query($query)->fetchAll();

    foreach ($res as $r) { 
      $imponibile = $r["imponibile"];
      $iva = $r["iva"];
      $crediti_non_esig += $imponibile + $imponibile * ($iva / 100);
    }
    
    $tot_crediti_esig += $crediti_esig;
    $tot_crediti_non_esig += $crediti_non_esig;
    $crediti_esig = globo_2dec($crediti_esig);
    $crediti_non_esig = globo_2dec($crediti_non_esig);
    
    if ($crediti_esig > 0 || $crediti_non_esig > 0) {
      if (!$intest) {
        echo "<tr>";
        echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'></td>";
        echo "<td class='tab_int_td_2' width='25%' align='left' valign='middle'><strong>esigibili</strong></td>";
        echo "<td class='tab_int_td_2' width='25%' align='left' valign='middle'><strong>non esigibili</strong></td>";
        echo "</tr>";
        $intest = true;          
      }

      echo "<tr>";
      echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><strong>$anno_cred</strong></td>";
      echo "<td class='tab_int_td_3' width='25%' align='left' valign='middle'>EUR <strong>$crediti_esig</strong>"; if ($crediti_esig > 0) echo " iva inc"; echo "</td>";
      echo "<td class='tab_int_td_3' width='25%' align='left' valign='middle'>EUR <strong>$crediti_non_esig</strong>"; if ($crediti_non_esig > 0) echo " iva inc"; echo "</td>";
      echo "</tr>";
    }
  }
  
  $tot_crediti_esig = globo_2dec($tot_crediti_esig);
  $tot_crediti_non_esig = globo_2dec($tot_crediti_non_esig);
  
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><strong>totale</strong></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$tot_crediti_esig</strong>"; if ($tot_crediti_esig > 0) echo " iva inc"; echo "</td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>EUR <strong>$tot_crediti_non_esig</strong>"; if ($tot_crediti_non_esig > 0) echo " iva inc"; echo "</td>";
  echo "</tr>";
  echo "</table>";

  echo "<form action='bilv2_lista.php' method='post'>";
  echo "<input type='hidden' name='tipo_bilancio' value='entrata' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='elenco entrate' class='input_bottone' />";
  echo "</form>";
  
  if (!in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO)) {
    echo "<form action='bilv2_lista.php' method='post' class='margin_top_bottoni'>";
    echo "<input type='hidden' name='tipo_bilancio' value='uscita' />";
    echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
    echo "<input type='submit' value='elenco uscite' class='input_bottone' />";
    echo "</form>";
  }
  
  /*
  echo "<form action='bil_xls.php' method='post' target='_blank' class='margin_top_bottoni'>";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='scarica xls' class='input_bottone' />";
  echo "</form>";
  */

  echo "<form action='home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='home' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='index.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='logout' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>