<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_GLOBOMAIL') && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] > 0 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function controllaForm() {
    var re = / /g;
    var oggetto = document.globo.oggetto.value.replace(re, "");
    var testo = document.globo.testo.value.replace(re, "");
    if (oggetto == "") { alert("compilare il campo \"oggetto\""); document.globo.oggetto.focus(); return false; }
    if (testo == "") { alert("compilare il campo \"testo\""); document.globo.testo.focus(); return false; }
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>GLOBOMAIL</b></td></tr></table>
<?php
  if (isset($_POST["azione"]) && ($_POST["azione"] == 1 || $_POST["azione"] == 2)) { // rispondi o inoltra
    $id_globomail = $_POST["id_globomail"];
    
    $query = "SELECT * FROM globomail WHERE id = '$id_globomail'";
    $res = $db->query($query)->fetchAll();
    $id_globomail = $res[0]["id"];
    $dt = $res[0]["dt"];
    $utente_mitt = $res[0]["utente_mitt"];
    $oggetto_orig = $res[0]["oggetto"];
    $testo_orig = $res[0]["testo"];

    if ($_POST["azione"] == 1) { // rispondi
      $utente_dest = $utente_mitt;
      $oggetto = "r: " . globo_converti_da_html($oggetto_orig);
    }
    if ($_POST["azione"] == 2) { // inoltra
      $utente_dest = "";
      $oggetto = "i: " . globo_converti_da_html($oggetto_orig);  
    }
      
    $testo = "\n\n\n--- messaggio originale ---\n";
    $testo .= "mittente: $utente_mitt\ndata/ora: $dt\n";
    $testo .= "destinatario: " . $_SESSION["ksglobo_nome_utente"] . "\n";
    $testo .= "oggetto: " . globo_converti_da_html($oggetto_orig) . "\n\n";
    $testo .= globo_converti_da_html($testo_orig);
  }
  else {
    $utente_dest = "";
    $oggetto = "";
    $testo = "";
  }
  
  $query_utenti = "SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente";
  $res_utenti = $db->query($query_utenti)->fetchAll();
  $num_utenti = count($res_utenti);
  
  $query_utenti_liv_1 = "SELECT nome_utente FROM utente WHERE livello = 1 ORDER BY nome_utente";
  $res_utenti_liv_1 = $db->query($query_utenti_liv_1)->fetchAll();
  $num_utenti_liv_1 = count($res_utenti_liv_1);
  
  echo "<form name='globo' action='globomail_ins_2.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>NUOVO MESSAGGIO</b></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>destinatario</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";
  echo "<select name='utente_dest'>";
  echo "<option value='tutti'>tutti</option>";
  if ($_SESSION["ksglobo_livello_utente"] <= 1) echo "<option value='amm'>amministratori ($num_utenti_liv_1)</option>";
  foreach ($res_utenti as $r_utenti) { 
    $nome_utente = $r_utenti["nome_utente"];
    if ($nome_utente == $utente_dest) echo "<option value='$nome_utente' selected='selected'>$nome_utente</option>";
    else echo "<option value='$nome_utente'>$nome_utente</option>";
  }
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>oggetto</b> <font color='red'>obb</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='40' maxlength='100' name='oggetto' value='$oggetto' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>testo</b> <font color='red'>obb</font></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='testo' rows='14' cols='' class='standard'>$testo</textarea></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>invia anche via e-mail</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='flag_email'>";
  echo "<option value='0' selected='selected'>no</option>";
  echo "<option value='1'>s&igrave;</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='submit' value='invia' class='input_bottone' />";
  echo "</form>";

  echo "<form action='globomail_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='messaggi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
