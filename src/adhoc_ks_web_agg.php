<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_KS || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KS) { header("Location: index.php"); exit(); }
?>
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
  $id = $_POST["id"];  
  
  $nome = strtolower(globo_converti_in_html($_POST["nome"]));
  $sito = strtolower($_POST["sito"]);
  $backend = strtolower($_POST["backend"]);
  if (trim($_POST["data_scad"]) == "") $data_scad = "0000-00-00";
  else {
    $data_scad = globo_converti_data_in_formato_mysql($_POST["data_scad"] . "/" . date("Y"));
    if (!globo_controlla_data_datetime($data_scad)) $data_scad = "0000-00-00";
  }

  $query = "UPDATE web SET nome = '$nome', sito = '$sito', backend = '$backend', data_scad = '$data_scad' WHERE id = '$id'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  
  if ($num == 1)
    echo "<tr><td class='tab_int_td_3' align='center'><b>elemento aggiornato</b></td></tr>";
  else
    echo "<tr><td class='tab_int_td_3' align='center'><b>elemento non aggiornato</b></td></tr>";
  
  echo "</table>";
  
  echo "<form action='adhoc_ks_web_vis.php' method='post'>";
  echo "<input type='hidden' name='id' value='$id' />";
  echo "<input type='submit' value='visualizza elemento' class='input_bottone' />";
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