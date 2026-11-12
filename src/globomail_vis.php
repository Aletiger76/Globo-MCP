<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_GLOBOMAIL') && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] > 0 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<?php
  // sposto questa parte sopra header.php per aggiornare subito l'icona di globomail
  $id_globomail = $_GET["id"];
  
  $query = "UPDATE globomail SET flag_lettura = '1' WHERE id = '$id_globomail'";
  $db->beginTransaction();
  $db->exec($query);
  $db->commit();
?>
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>GLOBOMAIL</b></td></tr></table>
<?php
  $query = "SELECT * FROM globomail WHERE id = '$id_globomail'";
  $res = $db->query($query)->fetchAll();
  $id_globomail = $res[0]["id"];
  $dt = $res[0]["dt"];
  $utente_mitt = $res[0]["utente_mitt"];
  $oggetto = $res[0]["oggetto"];
  $testo = $res[0]["testo"];
  
  $data = globo_data_da_mysql_datetime($dt);
  $ora = globo_ora_da_mysql_datetime($dt);
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>DETTAGLI MESSAGGIO</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>mittente</b></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'>$utente_mitt</td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>data</b></td><td class='tab_int_td_3' align='left' valign='middle'>" . globo_converti_data_in_formato_ita($data) . "</td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>ora</b></td><td class='tab_int_td_3' align='left' valign='middle'>$ora</td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>oggetto</b></td><td class='tab_int_td_3' align='left' valign='middle'>$oggetto</td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>testo</b></td><td class='tab_int_td_3' align='left' valign='middle'>$testo</td></tr>";
  echo "</table>";

  $testo_rispondi_inoltra = "\n\n\n--- messaggio originale ---\nmittente: $utente_mitt\ndata/ora: $dt\ndestinatario: " . $_SESSION["ksglobo_nome_utente"] . "\noggetto: $oggetto\n\n" . globo_converti_da_html($testo);
  $oggetto_rispondi = "r: " . globo_converti_da_html($oggetto);
  $oggetto_inoltra = "i: " . globo_converti_da_html($oggetto);
    
  if ($utente_mitt != "globo") {  
    echo "<form action='globomail_ins_1.php' method='post' class='margin_bottom_bottoni'>";
    echo "<input type='hidden' name='azione' value='1' />";
    echo "<input type='hidden' name='id_globomail' value='$id_globomail' />";
    echo "<input type='submit' value='rispondi' class='input_bottone' />";
    echo "</form>";
  }
  
  echo "<form action='globomail_ins_1.php' method='post' class='margin_bottom_bottoni'>";
  echo "<input type='hidden' name='azione' value='2' />";
  echo "<input type='hidden' name='id_globomail' value='$id_globomail' />";
  echo "<input type='submit' value='inoltra' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='globomail_el.php' method='post' class='margin_bottom_bottoni' onsubmit='return confermaEliminazione();'>";
  echo "<input type='hidden' name='id_globomail' value='$id_globomail' />";
  echo "<input type='submit' value='elimina' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='globomail_lista.php' method='post'>";
  echo "<input type='submit' value='messaggi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
