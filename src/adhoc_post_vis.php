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
  function controllaForm() {
    var re = / /g;
    <?php if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var data = document.globo.data.value; 
      if (data == "") { alert("compilare il campo \"data\""); document.globo.data.focus(); return false; }
    <?php } ?>
    <?php if ($POST_DATA_SCADENZA[$_SESSION["ksglobo_categoria_post"]] && $POST_DATA_SCADENZA_OBB[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var data_scadenza = document.globo.data_scadenza.value; 
      if (data_scadenza == "") { alert("compilare il campo \"data scadenza\""); document.globo.data_scadenza.focus(); return false; }
    <?php } ?>
    <?php if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var num_ordine = document.globo.num_ordine.value;
      if (isNaN(num_ordine) || parseInt(num_ordine) <= 0) { alert("inserire nel campo \"numero d'ordine\" un valore numerico positivo"); document.globo.num_ordine.focus(); return false; }
    <?php } ?>
    <?php if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]] && $POST_TITOLO_OBB[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var titolo_it = document.globo.titolo_it.value.replace(re, "");
      if (titolo_it == "") { alert("compilare il campo \"<?php echo $POST_LABEL_TITOLO_IT[$_SESSION["ksglobo_categoria_post"]]; ?>\""); document.globo.titolo_it.focus(); return false; }
    <?php } ?>
    <?php if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]] && POST_LINGUA_EN && $POST_TITOLO_OBB[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var titolo_en = document.globo.titolo_en.value.replace(re, "");
      if (titolo_en == "") { alert("compilare il campo \"<?php echo $POST_LABEL_TITOLO_EN[$_SESSION["ksglobo_categoria_post"]]; ?>\""); document.globo.titolo_en.focus(); return false; }
    <?php } ?>
    <?php if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]] && $POST_DESCRIZIONE_OBB[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var descrizione_it = CKEDITOR.instances.ckeditor_1.getData().replace(re, "");
      if (descrizione_it == "") { alert("compilare il campo \"<?php echo $POST_LABEL_DESCRIZIONE_IT[$_SESSION["ksglobo_categoria_post"]]; ?>\""); return false; }
    <?php } ?>
    <?php if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]] && POST_LINGUA_EN && $POST_DESCRIZIONE_OBB[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var descrizione_en = CKEDITOR.instances.ckeditor_2.getData().replace(re, "");
      if (descrizione_en == "") { alert("compilare il campo \"<?php echo $POST_LABEL_DESCRIZIONE_EN[$_SESSION["ksglobo_categoria_post"]]; ?>\""); return false; }
    <?php } ?>
    <?php if ($POST_PREZZO[$_SESSION["ksglobo_categoria_post"]]) { ?> 
      var prezzo = document.globo.prezzo.value;
      if (prezzo == "" || isNaN(prezzo) || prezzo < 0) { alert("inserire nel campo \"prezzo\" un valore numerico maggiore o uguale a zero"); document.globo.prezzo.focus(); return false; }
    <?php } ?>
  }
</script>
<?php
  if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]] && POST_LINGUA_EN) globo_ckeditor_4_5_2("config_post.js", 2);
  else if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]]) globo_ckeditor_4_5_2("config_post.js", 1);
?>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
<link rel="stylesheet" href="css/flatpickr.min.css" />
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
  
  $query = "SELECT * FROM " . MYSQL_TAB_POST . " WHERE id = '$id_post'";
  $res = $db->query($query)->fetchAll();
  
  $flag_online = $res[0]["flag_online"];
  $data = $res[0]["data"];
  $data_scadenza = $res[0]["data_scadenza"];
  $num_ordine = $res[0]["num_ordine"];
  $sottocategoria = $res[0]["sottocategoria"];
  $titolo_it = $res[0]["titolo_it"];
  $titolo_en = $res[0]["titolo_en"];
  $descrizione_it = $res[0]["descrizione_it"];
  $descrizione_en = $res[0]["descrizione_en"];
  if (isset($res[0]["prezzo"])) $prezzo = $res[0]["prezzo"]; // retrocompatibilit�
  else $prezzo = "0.00";
?>
<?php
  echo "<form name='globo' action='adhoc_post_agg.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>DETTAGLI POST</b></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='35%' align='left' valign='middle'><b>stato</b></td>";
  echo "<td class='tab_int_td_3' width='65%' align='left' valign='middle'>";
  echo "<select name='flag_online'>";
  if ($flag_online == 0) echo "<option value='0' selected='selected'>non pubblicato</option>";
  else echo "<option value='0'>non pubblicato</option>";
  if ($flag_online == 1) echo "<option value='1' selected='selected'>pubblicato</option>";
  else echo "<option value='1'>pubblicato</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  
  if ($POST_DATA[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>data</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  
    ?>
      <span data-flatpickr>
        <input type="text" class="input_text data" maxlength="10" name="data"
          value="<?php echo globo_converti_data_in_formato_ita($data); ?>" data-input autocomplete="off" />
        <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
      </span>
    <?php
  
    echo "</td>";
    echo "</tr>";  
  }
  
  if ($POST_DATA_SCADENZA[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>data scadenza</b>";
    if ($POST_DATA_SCADENZA_OBB[$_SESSION["ksglobo_categoria_post"]]) echo " <font color='red'>obb</font>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  
    ?>
      <span data-flatpickr>
        <input type="text" class="input_text data" maxlength="10" name="data_scadenza"
          value="<?php if ($data_scadenza != "0000-00-00") echo globo_converti_data_in_formato_ita($data_scadenza); ?>"
          data-input autocomplete="off" />
        <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
        <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
      </span>
    <?php
    
    echo "</td>";
    echo "</tr>";  
  }

  if ($POST_NUM_ORDINE[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>numero d'ordine</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='5' maxlength='5' name='num_ordine' value='$num_ordine' /></td>";
    echo "</tr>";
  }
  
  if (count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]) > 1) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>tipologia</b> <font color='red'>obb</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='sottocategoria'>";
    for ($i = 0; $i < count($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]]); $i++) {
      if ($sottocategoria == $i) echo "<option value='$i' selected='selected'>" . $POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]][$i] . "</option>";
      else {
        if (trim($POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]][$i]) != "")
          echo "<option value='$i'>" . $POST_SOTTOCATEGORIA[$_SESSION["ksglobo_categoria_post"]][$i] . "</option>";
      }
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }

  if (isset($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) && count($POST_TAG[$_SESSION["ksglobo_categoria_post"]]) > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>tag</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<table cellpadding='0' cellspacing='0' border='0'>";
    echo "<tr>";
    
    $post_tag_keys = array_keys($POST_TAG[$_SESSION["ksglobo_categoria_post"]]);
    $lung_max = 0;
    
    for ($i = 0; $i < count($post_tag_keys); $i++) {
      if (strlen($POST_TAG[$_SESSION["ksglobo_categoria_post"]][$post_tag_keys[$i]]) > $lung_max) $lung_max = strlen($POST_TAG[$_SESSION["ksglobo_categoria_post"]][$post_tag_keys[$i]]);
    }
    
    if ($lung_max > 30) $col = 1;
    else if ($lung_max > 20) $col = 2;
    else $col = 3;
    
    for ($i = 0; $i < count($post_tag_keys); $i++) {
      $query = "SELECT * FROM " . MYSQL_TAB_POST_TAG . " WHERE id_post = '$id_post' AND tag = '" . $post_tag_keys[$i] . "'";
      $res = $db->query($query)->fetchAll();
      if (count($res)) $checked = "checked='checked'";
      else $checked = "";
      echo "<td valign='middle'><input type='checkbox' name='tag[]' value='" . $post_tag_keys[$i] . "' $checked /></td>";
      echo "<td valign='middle' style='font-size: 10px; padding-left: 3px; padding-right: 10px;'>" . $POST_TAG[$_SESSION["ksglobo_categoria_post"]][$post_tag_keys[$i]] . "</td>";
      if (($i + 1) % $col == 0) echo "</tr><tr>";
    }
    echo "</tr>";
    echo "</table>";
    echo "</td>";
    echo "</tr>";
    echo "</td>";
    echo "</tr>";
  }

  if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>" . $POST_LABEL_TITOLO_IT[$_SESSION["ksglobo_categoria_post"]] . "</b>";
    if ($POST_TITOLO_OBB[$_SESSION["ksglobo_categoria_post"]]) echo " <font color='red'>obb</font>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='300' name='titolo_it' value='$titolo_it' /></td>";
    echo "</tr>";
  }
  
  if ($POST_TITOLO[$_SESSION["ksglobo_categoria_post"]] && POST_LINGUA_EN) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>" . $POST_LABEL_TITOLO_EN[$_SESSION["ksglobo_categoria_post"]] . "</b>";
    if ($POST_TITOLO_OBB[$_SESSION["ksglobo_categoria_post"]]) echo " <font color='red'>obb</font>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='300' name='titolo_en' value='$titolo_en' /></td>";
    echo "</tr>";
  }

  if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>" . $POST_LABEL_DESCRIZIONE_IT[$_SESSION["ksglobo_categoria_post"]] . "</b>";
    if ($POST_DESCRIZIONE_OBB[$_SESSION["ksglobo_categoria_post"]]) echo " <font color='red'>obb</font>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><textarea id='ckeditor_1' name='descrizione_it'>$descrizione_it</textarea></td>";
    echo "</tr>";
  }

  if ($POST_DESCRIZIONE[$_SESSION["ksglobo_categoria_post"]] && POST_LINGUA_EN) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>" . $POST_LABEL_DESCRIZIONE_EN[$_SESSION["ksglobo_categoria_post"]] . "</b>";
    if ($POST_DESCRIZIONE_OBB[$_SESSION["ksglobo_categoria_post"]]) echo " <font color='red'>obb</font>";
    echo "</td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><textarea id='ckeditor_2' name='descrizione_en'>$descrizione_en</textarea></td>";
    echo "</tr>";  
  }

  if ($POST_PREZZO[$_SESSION["ksglobo_categoria_post"]]) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>prezzo</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' size='8' maxlength='8' name='prezzo' value='$prezzo' /></td>";
    echo "</tr>";
  }
  
  echo "</table>";
  
  echo "<input type='hidden' name='id_post' value='$id_post' />";
  echo "<input type='submit' value='aggiorna' class='input_bottone' />";
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
  
  echo "<form action='adhoc_post_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco post' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>