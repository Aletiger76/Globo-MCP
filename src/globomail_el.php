<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_GLOBOMAIL') && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] > 0 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL); ?>
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
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>GLOBOMAIL</b></td></tr></table>
<?php  
  $id_globomail = $_POST["id_globomail"];  

  $query = "DELETE FROM globomail WHERE id = '$id_globomail'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  if ($num == 1) globo_log_attivita($db, 'elimina', 'globomail', $id_globomail, '');
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  if ($num == 1) echo "<b>messaggio eliminato</b>";
  else echo "<b>messaggio non eliminato</b>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  echo "<form action='globomail_lista.php' method='post'>";
  echo "<input type='submit' value='messaggi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
