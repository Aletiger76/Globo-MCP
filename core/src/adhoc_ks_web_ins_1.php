<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_KS || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KS) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  
  function controllaForm() {
    var re = / /g;
    var re_email = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
    var nome = document.globo.nome.value.replace(re, "");
    
    if (nome == "") { alert("compilare il campo \"nome\""); document.globo.nome.focus(); return false; }
  }
  //
</script>

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
  echo "<form name='globo' action='adhoc_ks_web_ins_2.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>NUOVO ELEMENTO</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>nome</b> <font color='red'>obb</font></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='250' name='nome' value='' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>dominio/sito</b> <font color='red'>es. www.dominio.it</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='100' name='sito' value='' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>backend</b> <font color='red'>es. www.dominio.it/wp-admin</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='100' name='backend' value='' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>data scadenza dominio</b> <font color='red'>gg/mm</font></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text data' style='width: 44px;' maxlength='5' name='data_scad' value='' /></td></tr>";
  echo "</table>";
  echo "<input type='submit' value='inserisci' class='input_bottone' />";
  echo "</form>";

  echo "<form action='adhoc_ks_web_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco elementi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>