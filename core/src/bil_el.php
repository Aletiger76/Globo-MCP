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
  $id_bilancio = $_POST["id_bilancio"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';

  $query = "SELECT * FROM bilancio WHERE id = '$id_bilancio'";
  $res = $db->query($query)->fetchAll();

  $importo = $res[0]["importo"];
  $fattura = $res[0]["fattura"];
  
  $query = "DELETE FROM bilancio WHERE tipo = '$tipo_bilancio' AND id = '$id_bilancio'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  if ($num == 1) globo_log_attivita($db, 'elimina', 'bilancio', $id_bilancio, $tipo_bilancio);
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
    
  if ($num == 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata eliminata</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita eliminata</b>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center'>";
    if ($tipo_bilancio == "entrata") echo "<b>entrata non eliminata</b>";
    else if ($tipo_bilancio == "uscita") echo "<b>uscita non eliminata</b>";
    echo "</td>";
    echo "</tr>";
  }

  if ($num == 1) {  
    $query = "DELETE FROM scadenza WHERE id_bilancio = '$id_bilancio'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();

    if ($num > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($num == 1) echo "<b>1 scadenza eliminata</b>";
      else echo "<b>$num scadenze eliminate</b>";
      echo "</td>";
      echo "</tr>";
      echo "<tr>";
    }
  }

  echo "</table>";   
        
  echo "<form action='bil_lista.php' method='post'>";
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
