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
  
  $query = "UPDATE " . MYSQL_TAB_POST . " SET sottocategoria = '$sottocategoria', flag_online = '$flag_online', data = '$data', data_scadenza = '$data_scadenza', num_ordine = '$num_ordine',";
  $query .= " titolo_it = '$titolo_it', titolo_en = '$titolo_en', descrizione_it = '$descrizione_it', descrizione_en = '$descrizione_en', prezzo = '$prezzo'";
  $query .= " WHERE id = '$id_post'";
  $db->beginTransaction();
  try { $num = $db->exec($query); } catch (PDOException $e) { $num = 0; }
  $db->commit();

  if ($num <= 0) { // retrocompatibilità
    $query = "UPDATE " . MYSQL_TAB_POST . " SET sottocategoria = '$sottocategoria', flag_online = '$flag_online', data = '$data', data_scadenza = '$data_scadenza', num_ordine = '$num_ordine',";
    $query .= " titolo_it = '$titolo_it', titolo_en = '$titolo_en', descrizione_it = '$descrizione_it', descrizione_en = '$descrizione_en'";
    $query .= " WHERE id = '$id_post'";
    $db->beginTransaction();
    try { $num = $db->exec($query); } catch (PDOException $e) { $num = 0; }
    $db->commit();
  }
  
  $num_tag = 0;

  if (count($tag) > 0) {
    // elimino i tag non selezionati (se sono presenti)
    $query_tag = "DELETE FROM " . MYSQL_TAB_POST_TAG . " WHERE id_post = '$id_post'";
    foreach ($tag as $t) {
      if (strlen($t) > 0) {
        $query_tag .= " AND tag <> '$t'";
      }
    }
    $db->beginTransaction();
    $num_tag_tmp = $db->exec($query_tag);
    $db->commit();
    if ($num_tag_tmp > 0) $num_tag += $num_tag_tmp;

    // inserisco i tag selezionati (se non sono presenti)
    foreach ($tag as $t){
      if (strlen($t) > 0) {
        $query_tag = "INSERT INTO " . MYSQL_TAB_POST_TAG . " (id_post, tag) VALUES ('$id_post', '$t')";
        $db->beginTransaction();
        try { $num_tag_tmp = $db->exec($query_tag); } catch (PDOException $e) { $num_tag_tmp = 0; }
        $db->commit();
        if ($num_tag_tmp > 0) $num_tag += $num_tag_tmp;
      }
    }
  }
  else {
    $query_tag = "DELETE FROM " . MYSQL_TAB_POST_TAG . " WHERE id_post = '$id_post'";
    $db->beginTransaction();
    try { $num_tag_tmp = $db->exec($query_tag);  } catch (PDOException $e) { $num_tag_tmp = 0; }
    $db->commit();
    if ($num_tag_tmp > 0) $num_tag += $num_tag_tmp;
  }

  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>"; 
  echo "<tr>";
  echo "<td class='tab_int_td_3' align='center'>";
  if ($num > 0 || $num_tag > 0) echo "<b>post aggiornato</b>";
  else echo "<b>post non aggiornato</b>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  echo "<form action='adhoc_post_vis.php' method='post'>";
  echo "<input type='hidden' name='id_post' value='$id_post' />";
  echo "<input type='submit' value='dettagli post' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='adhoc_post_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco post' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>