<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_POST || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_POST) { header("Location: index.php"); exit(); }
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
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b><?php echo strtoupper($POST_CATEGORIA[$_SESSION["ksglobo_categoria_post"]]); ?></b></td></tr></table>
<?php
  $db = NULL;
  
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_POST, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }
?>
<?php
  $id_post = $_POST["id_post"];  

  $query = "DELETE FROM " . MYSQL_TAB_POST . " WHERE id = '$id_post'";
  $db->beginTransaction();
  $num = $db->exec($query);
  $db->commit();
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  
  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'post', $id_post, '');
    try {
      $query_doc = "SELECT file FROM " . MYSQL_TAB_POST_DOC . " WHERE id_post = '$id_post'";
      $res_doc = $db->query($query_doc)->fetchAll();
      $num_doc = count($res_doc);
      
      for ($i = 0; $i < $num_doc; $i++) {
        unlink(POST_DIR_DOC . $res_doc[$i]["file"]);
      }
    
      $query = "DELETE FROM " . MYSQL_TAB_POST_DOC . " WHERE id_post = '$id_post'";
      $db->beginTransaction();
      $db->exec($query);
      $db->commit();
    } catch (PDOException $e) {
    }

    try {
      $query_img = "SELECT file_big, file_small FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post'";
      $res_img = $db->query($query_img)->fetchAll();
      $num_img = count($res_img);
      
      for ($i = 0; $i < $num_img; $i++) {
        unlink(POST_DIR_IMG . $res_img[$i]["file_big"]);
        unlink(POST_DIR_IMG . $res_img[$i]["file_small"]);
      }
      
      $query = "DELETE FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post'";
      $db->beginTransaction();
      $db->exec($query);
      $db->commit();
    } catch (PDOException $e) {
    }

    try {
      $query_av = "SELECT file FROM " . MYSQL_TAB_POST_AV . " WHERE id_post = '$id_post'";
      $res_av = $db->query($query_av)->fetchAll();
      $num_av = count($res_av);
      
      for ($i = 0; $i < $num_av; $i++) {
        if (trim($res_av[$i]["file"]) != "") unlink(POST_DIR_AV . $res_av[$i]["file"]);
      }
      
      $query = "DELETE FROM " . MYSQL_TAB_POST_AV . " WHERE id_post = '$id_post'";
      $db->beginTransaction();
      $db->exec($query);
      $db->commit();
    } catch (PDOException $e) {
    }
    
    try {
      $query_tag = "DELETE FROM " . MYSQL_TAB_POST_TAG . " WHERE id_post = '$id_post'";
      $db->beginTransaction();
      $num_tag_tmp = $db->exec($query_tag);
      $db->commit();
    } catch (PDOException $e) {
    }
      
    echo "<b>post eliminato</b>";
  }
  else echo "<b>post non eliminato</b>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  echo "<form action='adhoc_post_lista.php' method='post'>";
  echo "<input type='submit' value='elenco post' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>