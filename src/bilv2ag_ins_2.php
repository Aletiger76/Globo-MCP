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
  $nome = globo_converti_in_html($_POST["nome"] ?? '');
  $email = trim($_POST["email"] ?? '');
  $note = globo_converti_in_html($_POST["note"] ?? '');
  
  if (TUTTO_MINUSCOLO) {
    $nome = strtolower($nome);
    $email = strtolower($email);
    $note = strtolower($note);
  }
  
  $dt = date("Y-m-d H:i:s");

  $query = "INSERT INTO bilanciov2_agente (nome, email, note, fatture_provvigioni_pagate, provvigioni_pagate, totale_provvigioni_pagate, dt_inserimento)";
  $query .= " VALUES ('$nome', '$email', '$note', '', '', '0', '$dt')";
  $db->beginTransaction();
  $num = $db->exec($query);
  $id_bilanciov2_agente = $db->lastInsertId();
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  if ($num == 1) echo "<b>agente inserito</b>";
  else echo "<b>agente non inserito</b>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  if ($num == 1) {
    echo "<form action='bilv2ag_vis.php' method='post' class='margin_bottom_bottoni'>";
    echo "<input type='hidden' name='id_bilanciov2_agente' value='$id_bilanciov2_agente' />";
    echo "<input type='submit' value='visualizza agente' class='input_bottone' />";
    echo "</form>";
  }
  
  echo "<form action='bilv2ag_lista.php' method='post'>";
  echo "<input type='submit' value='elenco agenti' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
