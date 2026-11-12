<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_POST || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_POST) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<?php
  if (isset($_POST["categoria"])) $_SESSION["ksglobo_categoria_post"] = $_POST["categoria"];
?>
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
  if (isset($_POST["id_post"])) { // modifica del campo flag_online del post
    $id_post = $_POST["id_post"];
    
    $query = "SELECT flag_online FROM " . MYSQL_TAB_POST . " WHERE id = '$id_post'";
    $res = $db->query($query)->fetchAll();
    $flag_online = $res[0]["flag_online"];

    if ($flag_online == 0) $flag_online = 1;
    else $flag_online = 0;

    $query = "UPDATE " . MYSQL_TAB_POST . " SET flag_online = '$flag_online' WHERE id = '$id_post'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();
  }
   
  if (isset($_POST["ricerca_testo"])) $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]); 
  else if (isset($_SESSION["ksglobo_ricerca_post"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_post"]["testo"];
  else $ricerca_testo = ""; 
  
  if (isset($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) && count($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) > 0) {
    if (isset($_POST["ricerca_tag"])) $ricerca_tag = $_POST["ricerca_tag"]; 
    else if (isset($_SESSION["ksglobo_ricerca_post"]["tag"])) $ricerca_tag = $_SESSION["ksglobo_ricerca_post"]["tag"];
    else $ricerca_tag = ""; 
  }
  else $ricerca_tag = ""; 
  
  unset($_SESSION["ksglobo_ricerca_post"]);
  $_SESSION["ksglobo_ricerca_post"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_post"]["tag"] = $ricerca_tag;
  
  if ($ricerca_tag == "") {
    $query = "SELECT * FROM " . MYSQL_TAB_POST . " WHERE categoria = '" . $_SESSION["ksglobo_categoria_post"] . "'";
    $query .= " AND (titolo_it LIKE '%$ricerca_testo%' OR descrizione_it LIKE '%$ricerca_testo%')";
  }
  else {
    $query = "SELECT * FROM " . MYSQL_TAB_POST . " JOIN " . MYSQL_TAB_POST_TAG . " ON " . MYSQL_TAB_POST . ".id = " . MYSQL_TAB_POST_TAG . ".id_post";
    $query .= " WHERE " . MYSQL_TAB_POST . ".categoria = '" . $_SESSION["ksglobo_categoria_post"] . "'";
    $query .= " AND (" . MYSQL_TAB_POST . ".titolo_it LIKE '%$ricerca_testo%' OR " . MYSQL_TAB_POST . ".descrizione_it LIKE '%$ricerca_testo%')";
    $query .= " AND " . MYSQL_TAB_POST_TAG . ".tag = '$ricerca_tag'";
  }

  $order_by = "";
  if (count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]) > 1) {
    if ($order_by == "") $order_by = "sottocategoria ASC";
    else $order_by .= ", sottocategoria ASC";
  }
  if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) {
    if ($order_by == "") $order_by = "data DESC, id DESC";
    else $order_by .= ", data DESC, id DESC";
  }
  if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) {
    if ($order_by == "") $order_by = "num_ordine ASC, id ASC";
    else $order_by .= ", num_ordine ASC, id ASC";
  }
  if ($order_by == "") $order_by = "titolo_it ASC";
  else $order_by .= ", titolo_it ASC";
  
  $query .= " ORDER BY " . $order_by;
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  if ($num > 0 || trim($ricerca_testo) != "" || trim($ricerca_tag) != "") {
    echo "<form action='adhoc_post_lista.php' method='post' class='margin_bottom_bottoni'>";
    echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
    echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>RICERCA</b></td></tr>"; 
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='50%' align='left'>";
    echo "<b>";
    if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]]) echo $POST_LABEL_TITOLO_IT[$_SESSION["ksglobo_categoria_post"]];
    if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]] && $POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]]) echo " / ";
    if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]]) echo $POST_LABEL_DESCRIZIONE_IT[$_SESSION["ksglobo_categoria_post"]];
    echo "</b>";
    echo "</td>";
    echo "<td class='tab_int_td_3' width='50%' align='left'>";
    echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
    echo "</td>";
    echo "</tr>";
    
    if (isset($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) && count($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' align='left'><b>tag</b></td>";
      echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>";
      echo "<select name='ricerca_tag' onchange='this.form.submit();'>";
      if ($ricerca_tag == "") echo "<option value='' selected='selected'>---</option>";
      else echo "<option value=''>---</option>";
      
      $post_tag_keys = array_keys($POST_TAG[$_SESSION["ksglobo_categoria_post"]]);
      
      for ($i = 0; $i < count($post_tag_keys); $i++) {
        if ($ricerca_tag == $post_tag_keys[$i]) echo "<option value='" . $post_tag_keys[$i] . "' selected='selected'>" . $POST_TAG[$_SESSION["ksglobo_categoria_post"]][$post_tag_keys[$i]] . "</option>";
        else echo "<option value='" . $post_tag_keys[$i] . "'>" . $POST_TAG[$_SESSION["ksglobo_categoria_post"]][$post_tag_keys[$i]] . "</option>";
      }
      echo "</select>";
      echo "</td>";
    }
    echo "</table>";
    echo "<input type='submit' value='ricerca' class='input_bottone' />";
    echo "</form>";
    
    echo "<form action='adhoc_post_ins_1.php' method='post' class='margin_bottom_blocchi'>";  
    echo "<input type='submit' value='nuovo post' class='input_bottone' />";
    echo "</form>";
  }

  if ($num <= 0) {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";  
    echo "<tr><td class='tab_int_td_1' align='center'><b>LISTA POST</b></td></tr>";
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun post</b></td></tr>";
    echo "</table>";
  }
  else {
    $num_col = 3;
    if (count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]) > 1) $num_col++;
    if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) $num_col++;
    if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) $num_col++;
    if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]]) $num_col++;
    if ($POST_PREZZO[$_SESSION["ksglobo_categoria_post"]]) $num_col++;
    if ($POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]] > 0) $num_col++;
    if ($POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] > 0) $num_col++;
    if ($POST_NUM_AV[$_SESSION["ksglobo_categoria_post"]] > 0) $num_col++;
  
    echo "<table class='tab_int' cellspacing='3' width='100%'>";  
    echo "<tr><td class='tab_int_td_1' colspan='$num_col' align='center'><b>LISTA POST ($num)</b></td></tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='center'><b>stato</b></td>";
    if (count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]) > 1) echo "<td class='tab_int_td_2' align='center'><b>tipologia</b></td>";
    if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_2' align='center'><b>data</b></td>";
    if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_2' align='center'><b>#</b></td>";
    if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_2' width='100%' align='left'><b>" . $POST_LABEL_TITOLO_IT[$_SESSION["ksglobo_categoria_post"]] . "</b></td>";
    if ($POST_PREZZO[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_2' align='right'><b>prezzo</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    if ($POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]] > 0) echo "<td class='tab_int_td_2' align='center'><b>doc</b></td>";
    if ($POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] > 0) echo "<td class='tab_int_td_2' align='center'><b>img</b></td>";
    if ($POST_NUM_AV[$_SESSION["ksglobo_categoria_post"]] > 0) echo "<td class='tab_int_td_2' align='center'><b>a/v</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>elim</b></td>";
    echo "</tr>";
    
    foreach ($res as $r) { 
      $id = $r["id"];
      $sottocategoria = $r["sottocategoria"];
      $flag_online = $r["flag_online"];
      $data = $r["data"];
      $data_scadenza = $r["data_scadenza"];
      $num_ordine = $r["num_ordine"];
      $titolo_it = $r["titolo_it"];
      if (isset($r["prezzo"])) $prezzo = $r["prezzo"]; // retrocompatibilità
      else $prezzo = "0.00";

      echo "<tr>";
      echo "<td class='tab_int_td_3' align='center'>";

      if ($data_scadenza != "0000-00-00" && strtotime($data_scadenza) < strtotime(date("Y-m-d"))) { // post scaduto
        echo "<img src='immagini/ico_col_g.gif' width='8' height='8' border='0' alt='scaduto' title='scaduto'>";
      }
      else {
        if ($flag_online == 0) $submit = "<input type='image' src='immagini/ico_col_r.gif' width='8' height='8' border='0' alt='non pubblicato' title='non pubblicato' />";
        else $submit = "<input type='image' src='immagini/ico_col_v.gif' width='8' height='8' border='0' alt='pubblicato' title='pubblicato' />";
        
        echo "<form action='adhoc_post_lista.php' method='post'>";
        echo "<input type='hidden' name='id_post' value='$id' />";
        echo $submit;
        echo "</form>";
      }
      echo "</td>";

      if (count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]) > 1) {
        $tmp = $POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]][$sottocategoria];
        if (trim($tmp) == "") $tmp = "-";
        echo "<td class='tab_int_td_3' align='center'><nobr>" . $tmp . "</nobr></td>";
      }
      
      if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_3 nobr' align='center'>" . globo_converti_data_in_formato_ita($data) . "</td>";
      if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_3' align='center'>$num_ordine</td>";
      if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_3' align='left'>$titolo_it</td>";
      if ($POST_PREZZO[$_SESSION["ksglobo_categoria_post"]]) echo "<td class='tab_int_td_3' align='right'>$prezzo</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='adhoc_post_vis.php' method='post'>";
      echo "<input type='hidden' name='id_post' value='$id' />";
      echo "<input type='image' src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</td>";
      
      if ($POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]] > 0) {
        $query_doc = "SELECT * FROM " . MYSQL_TAB_POST_DOC . " WHERE id_post = '$id'";
        $res_doc = $db->query($query_doc)->fetchAll();
        $num_doc = count($res_doc);
          
        if ($num_doc > 0) $img = "immagini/ico_lente_p.gif";
        else $img = "immagini/ico_lente.gif";
  
        echo "<td class='tab_int_td_3' align='center'>";
        echo "<form action='adhoc_post_doc.php' method='post'>";
        echo "<input type='hidden' name='id_post' value='$id' />";
        echo "<input type='image' src='$img' width='16' height='16' border='0' alt='visualizza documenti' title='visualizza documenti' />";
        echo "</form>";
        echo "</td>";
      }
      
      if ($POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] > 0) {
        $query_img = "SELECT * FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id'";
        $res_img = $db->query($query_img)->fetchAll();
        $num_img = count($res_img);
        
        if ($num_img > 0) $img = "immagini/ico_lente_p.gif";
        else $img = "immagini/ico_lente.gif";
  
        echo "<td class='tab_int_td_3' align='center'>";
        echo "<form action='adhoc_post_img.php' method='post'>";
        echo "<input type='hidden' name='id_post' value='$id' />";
        echo "<input type='image' src='$img' width='16' height='16' border='0' alt='visualizza immagini' title='visualizza immagini' />";
        echo "</form>";
        echo "</td>";
      }
      
      if ($POST_NUM_AV[$_SESSION["ksglobo_categoria_post"]] > 0) {
        $query_av = "SELECT * FROM " . MYSQL_TAB_POST_AV . " WHERE id_post = '$id'";
        $res_av = $db->query($query_av)->fetchAll();
        $num_av = count($res_av);
        
        if ($num_av > 0) $img = "immagini/ico_lente_p.gif";
        else $img = "immagini/ico_lente.gif";
  
        echo "<td class='tab_int_td_3' align='center'>";
        echo "<form action='adhoc_post_av.php' method='post'>";
        echo "<input type='hidden' name='id_post' value='$id' />";
        echo "<input type='image' src='$img' width='16' height='16' border='0' alt='visualizza contenuti audio/video' title='visualizza contenuti audio/video' />";
        echo "</form>";
        echo "</td>";
      }
      
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='adhoc_post_el.php' method='post' onsubmit='return confermaEliminazione();'>";
      echo "<input type='hidden' name='id_post' value='$id' />";
      echo "<input type='image' src='immagini/ico_x.gif' width='16' height='16' border='0' alt='elimina' title='elimina' />";
      echo "</form>";
      echo "</td>";
      echo "</tr>";
    }
    
    echo "</table>";
  }
  
  echo "<form action='adhoc_post_ins_1.php' method='post'>";
  echo "<input type='submit' value='nuovo post' class='input_bottone' />";
  echo "</form>";

  echo "<form action='home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='home' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='index.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='logout' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
<?php if (isset($popup)) echo $popup; ?>