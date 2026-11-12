<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_KS || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KS) { header("Location: index.php"); exit(); }
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
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>KOSMOSOL - DOMINI WEB</b></td></tr></table>
<?php
  $db = NULL;
  
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_KS, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }
?>
<?php  
  if (isset($_POST["ricerca_testo"])) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"])); 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_web"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["testo"];
  else $ricerca_testo = "";  
  if (isset($_POST["ricerca_mese_scad"])) $ricerca_mese_scad = $_POST["ricerca_mese_scad"];
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_web"]["mese_scad"])) $ricerca_mese_scad = $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["mese_scad"];
  else $ricerca_mese_scad = "";
  if (isset($_POST["ricerca_ordinamento"])) $ricerca_ordinamento = $_POST["ricerca_ordinamento"];  
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_web"]["ordinamento"])) $ricerca_ordinamento = $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["ordinamento"];
  else $ricerca_ordinamento = "nome";
  
  unset($_SESSION["ksglobo_ricerca_adhoc_ks_web"]);
  $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["mese_scad"] = $ricerca_mese_scad;
  $_SESSION["ksglobo_ricerca_adhoc_ks_web"]["ordinamento"] = $ricerca_ordinamento;

  $query = "SELECT * FROM web";
  $query .= " WHERE (nome LIKE '%$ricerca_testo%' OR sito LIKE '%$ricerca_testo%')";
  if ($ricerca_mese_scad != "") $query .= " AND data_scad LIKE '%-$ricerca_mese_scad-%'";
  if ($ricerca_ordinamento == "nome") $query .= " ORDER BY nome, sito";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  echo "<form action='adhoc_ks_web_lista.php' method='post' class='margin_bottom_bottoni'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>RICERCA</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left'><b>testo</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left'>";
  echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>mese scadenza dominio</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_mese_scad' onchange='this.form.submit();'>";
  if ($ricerca_mese_scad == "") echo "<option value='' selected='selected'>---</option>";
  else echo "<option value=''>---</option>";
  if ($ricerca_mese_scad == "01") echo "<option value='01' selected='selected'>gennaio</option>";
  else echo "<option value='01'>gennaio</option>";
  if ($ricerca_mese_scad == "02") echo "<option value='02' selected='selected'>febbraio</option>";
  else echo "<option value='02'>febbraio</option>";
  if ($ricerca_mese_scad == "03") echo "<option value='03' selected='selected'>marzo</option>";
  else echo "<option value='03'>marzo</option>";
  if ($ricerca_mese_scad == "04") echo "<option value='04' selected='selected'>aprile</option>";
  else echo "<option value='04'>aprile</option>";
  if ($ricerca_mese_scad == "05") echo "<option value='05' selected='selected'>maggio</option>";
  else echo "<option value='05'>maggio</option>";
  if ($ricerca_mese_scad == "06") echo "<option value='06' selected='selected'>giugno</option>";
  else echo "<option value='06'>giugno</option>";
  if ($ricerca_mese_scad == "07") echo "<option value='07' selected='selected'>luglio</option>";
  else echo "<option value='07'>luglio</option>";
  if ($ricerca_mese_scad == "08") echo "<option value='08' selected='selected'>agosto</option>";
  else echo "<option value='08'>agosto</option>";
  if ($ricerca_mese_scad == "09") echo "<option value='09' selected='selected'>settembre</option>";
  else echo "<option value='09'>settembre</option>";
  if ($ricerca_mese_scad == "10") echo "<option value='10' selected='selected'>ottobre</option>";
  else echo "<option value='10'>ottobre</option>";
  if ($ricerca_mese_scad == "11") echo "<option value='11' selected='selected'>novembre</option>";
  else echo "<option value='11'>novembre</option>";
  if ($ricerca_mese_scad == "12") echo "<option value='12' selected='selected'>dicembre</option>";
  else echo "<option value='12'>dicembre</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";  
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>ordina per</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<table cellpadding='0' cellspacing='0' border='0'>";
  echo "<tr>";
  if ($ricerca_ordinamento == "nome") echo "<td><input type='radio' name='ricerca_ordinamento' value='nome' checked='checked' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>nome</td>";
  else echo "<td><input type='radio' name='ricerca_ordinamento' value='nome' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>nome</td>";
  if ($ricerca_ordinamento == "sito") echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='sito' checked='checked' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>dominio/sito</td>";
  else echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='sito' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>dominio/sito</td>";
  echo "</tr>";
  echo "</table>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='submit' value='ricerca' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='adhoc_ks_web_ins_1.php' method='post' class='margin_bottom_blocchi'>";  
  echo "<input type='submit' value='nuovo elemento' class='input_bottone' />";
  echo "</form>";

  if ($num <= 0) {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' align='center'><b>LISTA ELEMENTI</b></td></tr>";
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun elemento</b></td></tr>";
    echo "</table>";
  }
  else {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' colspan='5' align='center'><b>LISTA ELEMENTI ($num)</b></td></tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='100%' align='left'><b>nome</b></td>";
    echo "<td class='tab_int_td_2' align='left'><b>dominio/sito</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>backend</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>scad dominio</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    echo "</tr>";
    
    foreach ($res as $r) { 
      $id = $r["id"];
      $nome = $r["nome"];
      $sito = $r["sito"];
      $backend = $r["backend"];
      $data_scad = substr(globo_converti_data_in_formato_ita($r["data_scad"]), 0, 5);
      
      echo "<tr>";
      echo "<td class='tab_int_td_3' align='left'>$nome</td>";
      /*
      if (substr_count($sito, "www.") > 0) echo "<td class='tab_int_td_3 nobr' align='center'><a href='http://$sito' target='_blank' style='color: blue;'>$sito</a></td>";
      else echo "<td class='tab_int_td_3 nobr' align='center'>$sito</td>";
      */
      echo "<td class='tab_int_td_3 nobr' align='left'><a href='http://$sito' target='_blank' style='color: blue;'>$sito</a></td>";
      echo "<td class='tab_int_td_3 nobr' align='center'>";
      if (trim($backend) != "") echo "<a href='http://$backend' title='$backend' target='_blank'><img src='immagini/ico_lente.png' width='16' height='16' border='0' alt='' /></a>";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='center'>$data_scad</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='adhoc_ks_web_vis.php' method='post'>";
      echo "<input type='hidden' name='id' value='$id' />";
      echo "<input type='image' src='immagini/ico_lente.png' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</td>";
      echo "</tr>";
    }
    
    echo "</table>";
  }
  
  echo "<form action='adhoc_ks_web_ins_1.php' method='post'>";
  echo "<input type='submit' value='nuovo elemento' class='input_bottone' />";
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