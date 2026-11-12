<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && defined('BILV2_AG') && BILV2_AG && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
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
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>AGENTI</b></td></tr></table>
<?php  
  $id_bilanciov2_agente = $_POST["id_bilanciov2_agente"] ?? '';
  $nome = globo_converti_in_html($_POST["nome"] ?? '');
  $nome_old = globo_converti_in_html($_POST["nome_old"] ?? '');
  $email = trim($_POST["email"] ?? '');
  $note = globo_converti_in_html($_POST["note"] ?? '');
  $fattura = $_POST["fattura"] ?? '';
  $fatture_provvigioni_pagate_old = $_POST["fatture_provvigioni_pagate_old"] ?? '';
  $provvigioni_pagate = globo_converti_in_html($_POST["provvigioni_pagate"] ?? '');
  $totale_provvigioni_pagate = $_POST["totale_provvigioni_pagate"] ?? '';
  $totale_provvigioni_pagate_old = $_POST["totale_provvigioni_pagate_old"] ?? '';
  
  if (TUTTO_MINUSCOLO) {
    $nome = strtolower($nome);
    $email = strtolower($email);
    $note = strtolower($note);
  }
  
  $fatture_provvigioni_pagate = "";
  
  for ($i = 0; $i < count($fattura); $i++) {
    $fatture_provvigioni_pagate .= $fattura[$i] . ";";
  }

  $query = "UPDATE bilanciov2_agente SET nome = '$nome', email = '$email', note = '$note', fatture_provvigioni_pagate = '$fatture_provvigioni_pagate', provvigioni_pagate = '$provvigioni_pagate', totale_provvigioni_pagate = '$totale_provvigioni_pagate'";
  $query .= " WHERE id = '$id_bilanciov2_agente'";

  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  
  if ($num > 0) {
    echo "<b>agente aggiornato</b>";
    
    $oggetto = "agente modificato";
    $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha modificato il seguente agente";
    $testo .= "\n\n---";
    $testo .= "\n\nid: $id_bilanciov2_agente";
    $testo .= "\n\nnome: " . globo_converti_da_html($nome);
    if ($nome != $nome_old) $testo .= "\n\nnome PRECEDENTE: " . globo_converti_da_html($nome_old);
    $testo .= "\n\nfatture provvigioni pagate: $fatture_provvigioni_pagate";
    if ($fatture_provvigioni_pagate != $fatture_provvigioni_pagate_old) $testo .= "\n\nfatture provvigioni pagate PRECEDENTE: $fatture_provvigioni_pagate_old";
    $testo .= "\n\ntotale provvigioni pagate: $totale_provvigioni_pagate";
    if ($totale_provvigioni_pagate != $totale_provvigioni_pagate_old) $testo .= "\n\ntotale provvigioni pagate PRECEDENTE: " . $totale_provvigioni_pagate_old;
    mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
  }
  else {
    echo "<b>agente non aggiornato</b>";
  }
  
  echo "</td>";
  echo "</tr>";
  echo "</table>";

  echo "<form action='bilv2ag_vis.php' method='post'>";
  echo "<input type='hidden' name='id_bilanciov2_agente' value='$id_bilanciov2_agente' />";
  echo "<input type='submit' value='visualizza agente' class='input_bottone' />";
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
