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
  $flag_online = $_POST["flag_online"];
  if (isset($_POST["data"])) $data = globo_converti_data_in_formato_mysql($_POST["data"]);
  else $data = "0000-00-00";
  if (isset($_POST["data_scadenza"]) && trim($_POST["data_scadenza"]) != "") $data_scadenza = globo_converti_data_in_formato_mysql($_POST["data_scadenza"]);
  else $data_scadenza = "0000-00-00";
  if (isset($_POST["num_ordine"]) && is_numeric($_POST["num_ordine"]) && $_POST["num_ordine"] > 0) $num_ordine = $_POST["num_ordine"];
  else $num_ordine = 1;
  if (isset($_POST["sottocategoria"])) $sottocategoria = $_POST["sottocategoria"];
  else $sottocategoria = -1;
  if (isset($_POST["tag"])) $tag = $_POST["tag"];
  else $tag = array();
  if (isset($_POST["titolo_it"])) $titolo_it = globo_converti_in_html($_POST["titolo_it"]);
  else $titolo_it = "";
  if (isset($_POST["titolo_en"])) $titolo_en = globo_converti_in_html($_POST["titolo_en"]);
  else $titolo_en = "";
  if (isset($_POST["descrizione_it"])) $descrizione_it = globo_converti_in_html_da_editor($_POST["descrizione_it"]);
  else $descrizione_it = "";
  if (isset($_POST["descrizione_en"])) $descrizione_en = globo_converti_in_html_da_editor($_POST["descrizione_en"]);
  else $descrizione_en = "";
  if (isset($_POST["prezzo"]) && is_numeric($_POST["prezzo"])) $prezzo = $_POST["prezzo"];
  else $prezzo = 0;

  $dt_inserimento = date("Y-m-d H:i:s");
  
  $query = "INSERT INTO " . MYSQL_TAB_POST . " (categoria, sottocategoria, flag_online, data, data_scadenza, num_ordine, titolo_it, titolo_en, descrizione_it, descrizione_en, prezzo, dt_inserimento)";
  $query .= " VALUES ('" . $_SESSION["ksglobo_categoria_post"] . "', '$sottocategoria', '$flag_online', '$data', '$data_scadenza', '$num_ordine', '$titolo_it', '$titolo_en', '$descrizione_it', '$descrizione_en', '$prezzo', '$dt_inserimento')";
  $db->beginTransaction();
  try { $num = $db->exec($query); } catch (PDOException $e) { $num = 0; }
  $db->commit();
  
  if ($num <= 0) { // retrocompatibilità
    $query = "INSERT INTO " . MYSQL_TAB_POST . " (categoria, sottocategoria, flag_online, data, data_scadenza, num_ordine, titolo_it, titolo_en, descrizione_it, descrizione_en, dt_inserimento)";
    $query .= " VALUES ('" . $_SESSION["ksglobo_categoria_post"] . "', '$sottocategoria', '$flag_online', '$data', '$data_scadenza', '$num_ordine', '$titolo_it', '$titolo_en', '$descrizione_it', '$descrizione_en', '$dt_inserimento')";
    $db->beginTransaction();
    try { $num = $db->exec($query); } catch (PDOException $e) { $num = 0; }
    $db->commit();
  }
  
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  if ($num > 0) echo "<b>post inserito</b>";
  else echo "<b>post non inserito</b>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  if ($num > 0) {
    $query = "SELECT id FROM " . MYSQL_TAB_POST . " WHERE data = '$data' AND titolo_it = '$titolo_it' ORDER BY dt_inserimento DESC LIMIT 1";
    $res = $db->query($query)->fetchAll();
    $id_post = $res[0]["id"];
    
    if (count($tag) > 0) {
      for ($i = 0; $i < count($tag); $i++) {
        $query_tag = "INSERT INTO " . MYSQL_TAB_POST_TAG . " (id_post, tag) VALUES ('$id_post', '" . $tag[$i] . "')";
        $db->beginTransaction();
        $db->exec($query_tag);
        $db->commit();
      }
    }
    
    echo "<form action='adhoc_post_vis.php' method='post'>";
    echo "<input type='hidden' name='id_post' value='$id_post' />";
    echo "<input type='submit' value='dettagli post' class='input_bottone' />";
    echo "</form>";
    
    if ($POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]] > 0) {
      echo "<form action='adhoc_post_doc.php' method='post' class='margin_top_bottoni'>";
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='submit' value='documenti post' class='input_bottone' />";
      echo "</form>";
    }
    
    if ($POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] > 0) {
      echo "<form action='adhoc_post_img.php' method='post' class='margin_top_bottoni'>";
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='submit' value='immagini post' class='input_bottone' />";
      echo "</form>";
    }
    
    if ($POST_NUM_AV[$_SESSION["ksglobo_categoria_post"]] > 0) {
      echo "<form action='adhoc_post_av.php' method='post' class='margin_top_bottoni'>";
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='submit' value='audio/video post' class='input_bottone' />";
      echo "</form>";
    }
  }
  
  echo "<form action='adhoc_post_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco post' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>