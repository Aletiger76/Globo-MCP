<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_KSH5S || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KSH5S) { header("Location: index.php"); exit(); }
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
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>KOSMOSOL - 5stelle*</b></td></tr></table>
<?php
  $db = NULL;
  
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_KSH5S, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
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
  
  $query = "SELECT nome, location FROM h5s WHERE id = '$id'";
  $res = $db->query($query)->fetchAll();
  
  $nome = $res[0]["nome"];
  $location = $res[0]["location"];
  
  $query = "DELETE FROM h5s WHERE id = '$id'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  
  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'h5s', $id, '');
    echo "<b>elemento eliminato</b>";
  
    $oggetto = "elemento h5s eliminato";
    $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha eliminato il seguente elemento h5s";
    $testo .= "\n\n---";
    $testo .= "\n\nid: " . $id;
    $testo .= "\n\nnome: " . globo_converti_da_html($nome);
    $testo .= "\n\nlocation: " . globo_converti_da_html($location);
    mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
  }
  else echo "<b>elemento non eliminato</b>";
  
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  echo "<form action='adhoc_ks_h5s_lista.php' method='post'>";
  echo "<input type='submit' value='elenco elementi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>