<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && defined('BILV2_AG') && BILV2_AG && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function controllaForm() {
    var re = / /g;
    var re_email = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
    var nome = document.globo.nome.value.replace(re, "");
    var email = document.globo.email.value;
    if (nome == "") { alert("compilare il campo \"nome/azienda\""); document.globo.nome.focus(); return false; }
    if (email != "" && !re_email.test(email)) { alert("indirizzo e-mail non corretto"); document.globo.email.focus(); return false; }
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>AGENTI</b></td></tr></table>
<?php
  echo "<form name='globo' action='bilv2ag_ins_2.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";  
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>NUOVO AGENTE</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>nome/azienda</b> <font color='red'>obb</font></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='250' name='nome' value='' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>e-mail sollecito pagamento</b>  <font color='red'>se presente, l'agente riceve i solleciti di pagamento delle fatture associate ad esso</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='100' name='email' value='' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>note</b></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='note' rows='4' cols='' class='standard'></textarea></td></tr>";
  echo "</table>";
  echo "<input type='submit' value='inserisci' class='input_bottone' />";
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
