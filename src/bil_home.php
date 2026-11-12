<?php 
  require("varie/common_1.php"); 
  if (!MOD_BILANCIO || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_BILANCIO) { header("Location: index.php"); exit(); }
?>
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
  $anno_oggi = date("Y");

  $anno_lim_inf = BIL_ANNO_INIZIO;
  if (MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2) $anno_lim_sup = BILV2_ANNO_INIZIO - 1;
  else $anno_lim_sup = $anno_oggi;
  
  if (isset($_POST["ricerca_anno"])) $anno_bilancio = $_POST["ricerca_anno"];  
  else if (isset($_SESSION["ksglobo_bilancio_anno"])) $anno_bilancio = $_SESSION["ksglobo_bilancio_anno"];
  else $anno_bilancio = $anno_lim_sup;
  if ($anno_bilancio < $anno_lim_inf || $anno_bilancio > $anno_lim_sup) $anno_bilancio = $anno_lim_sup;
  $_SESSION["ksglobo_bilancio_anno"] = $anno_bilancio;

  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>BILANCIO $anno_bilancio</b></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>anno</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";
  echo "<form method='post' action='bil_home.php'>";
  echo "<select name='ricerca_anno' onchange='this.form.submit();'>"; 
  for ($anno = $anno_lim_sup; $anno >= $anno_lim_inf; $anno--) {
    if ($anno_bilancio == $anno) echo "<option value='$anno' selected='selected'>$anno</option>";
    else echo "<option value='$anno'>$anno</option>";
  }
  echo "</select>";
  echo "</form>";
  echo "</td>";
  echo "</tr>";
  
  $query = "SELECT * FROM bilancio WHERE tipo = 'entrata' AND data LIKE '$anno_bilancio%'";
  $res = $db->query($query)->fetchAll();

  $tot_entrate = 0;
  
  foreach ($res as $r) { 
    $importo = $r["importo"];
    $tot_entrate = $tot_entrate + $importo;
  }
  
  $query = "SELECT * FROM bilancio WHERE tipo = 'uscita' AND data LIKE '$anno_bilancio%'";
  $res = $db->query($query)->fetchAll();
  
  $tot_uscite = 0;
  
  foreach ($res as $r) { 
    $importo = $r["importo"];
    $tot_uscite = $tot_uscite + $importo;
  }
  
  $tot_entrate = globo_2dec($tot_entrate);
  $tot_uscite = globo_2dec($tot_uscite);
  
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>entrate</b></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <b>$tot_entrate</b>"; if ($tot_entrate > 0) echo " iva inc"; echo "</td></tr>";
  // echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>uscite</b></td><td class='tab_int_td_3' align='left' valign='middle'>EUR <b>$tot_uscite</b>"; if ($tot_uscite > 0) echo " iva inc"; echo "</td></tr>";
  echo "</table>";

  echo "<form action='bil_lista.php' method='post'>";
  echo "<input type='hidden' name='tipo_bilancio' value='entrata' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='elenco entrate' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='bil_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='hidden' name='tipo_bilancio' value='uscita' />";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='elenco uscite' class='input_bottone' />";
  echo "</form>";

  echo "<form action='bil_xls.php' method='post' target='_blank' class='margin_top_bottoni'>";
  echo "<input type='hidden' name='anno_bilancio' value='$anno_bilancio' />";
  echo "<input type='submit' value='scarica xls' class='input_bottone' />";
  echo "</form>";

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