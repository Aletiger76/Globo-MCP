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

  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  
  try {
    $query_1 = "SELECT id FROM bilanciov2 WHERE id_bilanciov2_agente = '$id_bilanciov2_agente'";
    $res_1 = $db->query($query_1)->fetchAll();
    $num_1 = count($res_1);
  } catch (PDOException $e) {
    $num_1 = 0;
  }
  
  if ($num_1 == 0 && MOD_ADHOC_KSH5S) {
    try {    
      $db_globo_ksh5s = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_KSH5S, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
      $db_globo_ksh5s->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      $query_1 = "SELECT id FROM h5s WHERE globo_id_bilanciov2_agente = '$id_bilanciov2_agente'";
      $res_1 = $db_globo_ksh5s->query($query_1)->fetchAll();
      $num_1 = count($res_1);
    } catch (PDOException $e) {
      $num_1 = 0;
    }
  }

  if ($num_1 > 0) {
    echo "<b>agente non eliminabile - sono presenti uno o pi� elementi associati ad esso</b>";
  }
  else {
    $query_2 = "DELETE FROM bilanciov2_agente WHERE id = '$id_bilanciov2_agente'";
    $db->beginTransaction();
    $num_2 = $db->exec($query_2);
    $db->commit();
    if ($num_2 == 1) globo_log_attivita($db, 'elimina', 'bilanciov2_agente', $id_bilanciov2_agente, '');
    
    if ($num_2 == 1) echo "<b>agente eliminato</b>";
    else echo "<b>agente non eliminato</b>";
  }
  
  echo "</td>";
  echo "</tr>";
  echo "</table>";

  echo "<form action='bilv2ag_lista.php' method='post'>";
  echo "<input type='submit' value='elenco agenti' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php $db_globo_ksh5s = NULL; require("varie/common_2.php"); ?>
