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
  $utente_dest = $_POST["utente_dest"];
  $oggetto = $_POST["oggetto"];
  $testo = $_POST["testo"];
  $flag_email = $_POST["flag_email"];
  
  if (TUTTO_MINUSCOLO) {
    $oggetto = strtolower($oggetto);
    $testo = strtolower($testo);
  }

  if ($utente_dest == "amm") $num_messaggi_inviati = globo_invia_messaggio($db, 0, $flag_email, 1, $oggetto, $testo, 1, "", $_SESSION["ksglobo_nome_utente"]);
  else if ($utente_dest == "tutti") $num_messaggi_inviati = globo_invia_messaggio($db, 0, $flag_email, 1, $oggetto, $testo, 2, "", $_SESSION["ksglobo_nome_utente"]);
  else $num_messaggi_inviati = globo_invia_messaggio($db, 0, $flag_email, 1, $oggetto, $testo, "", $utente_dest, $_SESSION["ksglobo_nome_utente"]);
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";
  
  if ($num_messaggi_inviati["globomail"] > 0 || $num_messaggi_inviati["email"]) {
    if ($num_messaggi_inviati["globomail"] > 0) echo "<tr><td class='tab_int_td_3' align='center'><b>messaggio inviato via globomail</b></td></tr>";  
    if ($num_messaggi_inviati["email"] > 0) echo "<tr><td class='tab_int_td_3' align='center'><b>messaggio inviato via e-mail</b></td></tr>";  
  }
  else 
    echo "<tr><td class='tab_int_td_3' align='center'><b>impossibile inviare il messaggio</b></td></tr>";
  
  echo "</table>";

  echo "<form action='globomail_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='messaggi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
