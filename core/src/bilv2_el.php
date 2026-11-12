<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
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
  $id_bilanciov2 = $_POST["id_bilanciov2"] ?? '';
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
  
  $el = false;

  $query = "SELECT * FROM bilanciov2 WHERE id = '$id_bilanciov2'";
  $res = $db->query($query)->fetchAll();
  $data = $res[0]["data"];
  $cliente_fornitore = $res[0]["cliente_fornitore"];
  $fattura = $res[0]["fattura"];
  
  $query = "DELETE FROM bilanciov2 WHERE tipo = '$tipo_bilancio' AND id = '$id_bilanciov2'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  if ($num == 1) globo_log_attivita($db, 'elimina', 'bilanciov2', $id_bilanciov2, $tipo_bilancio);

  if ($num == 1) {
    $query = "DELETE FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2'";
    $db->beginTransaction();
    $db->exec($query);
    $db->commit();
  }
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
    
  if ($num == 1) {
    $el = true;
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

  echo "</table>";   
  
  if ($el && $tipo_bilancio == "entrata") {
    $oggetto = "entrata eliminata";
    $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha eliminato la seguente entrata";
    $testo .= "\n\n---";
    $testo .= "\n\nid_bilanciov2: " . $id_bilanciov2;
    $testo .= "\n\ndata: " . globo_converti_data_in_formato_ita($data);
    $testo .= "\n\ncliente: $cliente_fornitore";
    $testo .= "\n\nfattura: $fattura";
    mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
  }
        
  echo "<form action='bilv2_lista.php' method='post'>";
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
