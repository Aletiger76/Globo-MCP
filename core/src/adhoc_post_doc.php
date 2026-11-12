<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_POST || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_POST) { header("Location: index.php"); exit(); }

  if (POST_LINGUA_EN) 
    $label_nome_documento_it = "nome documento italiano";
  else 
    $label_nome_documento_it = "nome documento";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<?php require("shadowbox/shadowbox.php"); ?>
<script language="javascript" type="text/javascript">
  function controllaForm() {
    var re = / /g;
    var nome_it = document.globo.nome_it.value.replace(re, "");
    <?php if (POST_LINGUA_EN) { ?> var nome_en = document.globo.nome_en.value.replace(re, ""); <?php } ?>
    var file = document.globo.file.value.replace(re, "");
    var estensione = "";
    if (file.substring(file.length - 4, file.length - 3) == ".") estensione = file.substring(file.length - 3, file.length).toLowerCase();
    else if (file.substring(file.length - 5, file.length - 4) == ".") estensione = file.substring(file.length - 4, file.length).toLowerCase();
    if (nome_it == "") { alert("compilare il campo \"<?php echo $label_nome_documento_it; ?>\""); document.globo.nome_it.focus(); return false; }
    <?php if (POST_LINGUA_EN) { ?> if (nome_it == "") { alert("compilare il campo \"nome documento inglese\""); document.globo.nome_it.focus(); return false; } <?php } ?>
    if (file == "") { alert("compilare il campo \"file\""); document.globo.file.focus(); return false; }
    <?php
      echo "if (";
      for ($i = 0; $i < count($POST_DOC_FORM_OK); $i++) {
        echo "estensione != \"" . $POST_DOC_FORM_OK[$i] . "\"";
        if ($i < (count($POST_DOC_FORM_OK) - 1)) echo " && ";
      }
      echo ") { alert(\"formato file non valido\"); document.globo.file.value = \"\"; return false; }"; 
    ?>
    openLoadingShadowbox();
  }
  function confermaEliminazione() {
  return confirm("confermi eliminazione documento?");
  }
</script>
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
  
  if (isset($_POST["azione"]) && $_POST["azione"] == 1) { // caricamento nuovo documento    
    $nome_it = globo_converti_in_html($_POST["nome_it"]);
    $nome_en = globo_converti_in_html($_POST["nome_en"]);
    $nome_file_documento_tmp = $_FILES["file"]["tmp_name"];
    $nome_file_documento_utente = $_FILES["file"]["name"];
    $tipo_file_documento = $_FILES["file"]["type"];
    $dimensione_file_documento = $_FILES["file"]["size"];

    $dt_upload = date("Y-m-d H:i:s");
    $dt = date("YmdHis");
    
    $tmp = explode(".", strtolower($nome_file_documento_utente));
    if (!in_array(end($tmp), $POST_DOC_FORM_OK)) $stato_documento = 1;
    else if ($dimensione_file_documento > POST_DIM_DOC_B) $stato_documento = 2;
    else {
      $tmp = explode(".", strtolower($nome_file_documento_utente));
      $estensione_file_documento = end($tmp);  
      $nome_file_documento_server = $id_post . "_" . $dt . "." . $estensione_file_documento;
      $path_file_documento_server = POST_DIR_DOC . $nome_file_documento_server;
      if (move_uploaded_file($nome_file_documento_tmp, $path_file_documento_server)) $stato_documento = 0;
      else $stato_documento = 3;
    }
      
    if ($stato_documento == 0) {
      $query = "INSERT INTO " . MYSQL_TAB_POST_DOC . " (id_post, nome_it, nome_en, file, dt_upload)";
      $query .= " VALUES ('$id_post', '$nome_it', '$nome_en', '$nome_file_documento_server', '$dt_upload')";
      $db->beginTransaction();
      $num = $db->exec($query);
      $db->commit();
        
      if ($num <= 0) {
        $popup = "<script language='javascript' type='text/javascript'>alert('file non caricato - problemi legati al database');</script>";
        if (isset($stato_documento) && $stato_documento == 0) unlink(POST_DIR_DOC . $nome_file_documento_server);
      }
    }
    else {
      if ($stato_documento == 1) $popup = "<script language='javascript' type='text/javascript'>alert('file non caricato - problemi legati al tipo di file');</script>";
      if ($stato_documento == 2) $popup = "<script language='javascript' type='text/javascript'>alert('file non caricato - dimensione file eccessiva (max " . POST_DIM_DOC_MB . " mb)');</script>";
      if ($stato_documento == 3) $popup = "<script language='javascript' type='text/javascript'>alert('file non caricato - problemi legati al trasferimento del file');</script>";
    }
  }
  
  if (isset($_POST["azione"]) && $_POST["azione"] == 2) { // eliminazione documento    
    $id_documento = $_POST["id_documento"];
              
    $query = "SELECT * FROM " . MYSQL_TAB_POST_DOC . " WHERE " . MYSQL_TAB_POST_DOC . ".id = '$id_documento'";
    $res = $db->query($query)->fetchAll();
    $file = $res[0]["file"];
    
    $query = "DELETE FROM " . MYSQL_TAB_POST_DOC . " WHERE id = '$id_documento'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();
      
    if ($num > 0) unlink(POST_DIR_DOC . $file);
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 3) { // modifica nome documento    
    $id_documento = $_POST["id_documento"];
    $nome_it = globo_converti_in_html($_POST["nome_it"]);
    $nome_en = globo_converti_in_html($_POST["nome_en"]);

    if (trim($nome_en) == "") $nome_en = "-";
    
    if (trim($nome_it) != "" && trim($nome_en) != "") {       
      $query = "UPDATE " . MYSQL_TAB_POST_DOC . " SET nome_it = '$nome_it', nome_en = '$nome_en' WHERE id = '$id_documento'";
      $db->beginTransaction();
      $db->exec($query);
      $db->commit();
    }
  }

  $query = "SELECT titolo_it FROM " . MYSQL_TAB_POST . " WHERE id = '$id_post'";
  $res = $db->query($query)->fetchAll();
  $titolo_it = $res[0]["titolo_it"];

  $query_doc = "SELECT * FROM " . MYSQL_TAB_POST_DOC . " WHERE id_post = '$id_post' ORDER BY nome_it, dt_upload DESC";
  $res_doc = $db->query($query_doc)->fetchAll();
  $num_doc = count($res_doc);

  echo "<form name='globo' action='adhoc_post_doc.php' method='post' enctype='multipart/form-data' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>DOCUMENTI POST</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>titolo post</b></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'>$titolo_it</td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>$label_nome_documento_it</b> <font color='red'>obb</font></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<input type='text' class='input_text standard' maxlength='255' name='nome_it' value=''";
  if ($num_doc >= $POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
  echo " />";
  echo "</td>";
  echo "</tr>";
  
  if (POST_LINGUA_EN) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>nome documento inglese</b> <font color='red'>obb</font></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<input type='text' class='input_text standard' maxlength='255' name='nome_en' value=''";
    if ($num_doc >= $POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
    echo " />";
    echo "</td>";
    echo "</tr>";
  }
  else 
    echo "<input type='hidden' name='nome_en' value='' />";
  
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'>";
  echo "<b>file</b> ";
  echo "<font color='red'>";
  for ($i = 0; $i < count($POST_DOC_FORM_OK); $i++) echo $POST_DOC_FORM_OK[$i] . " ";
  echo "- max " . POST_DIM_DOC_MB . " mb";
  echo "</font>";
  echo "</td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<input type='file' class='input_text' size='45' maxlength='250' name='file' value=''"; 
  if ($num_doc >= $POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
  echo " />";     
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='hidden' name='id_post' value='$id_post' />";
  echo "<input type='hidden' name='azione' value='1' />";
  echo "<input type='submit' value='carica' class='input_bottone'"; 
  if ($num_doc >= $POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
  echo " />"; 
  echo "</form>";
  echo "<table class='tab_int margin_top_blocchi' cellspacing='3' width='100%'>";   
  echo "<tr>";
  
  if ($num_doc <= 0) $num_col = 1;
  else $num_col = 5;
  
  echo "<td class='tab_int_td_1' colspan='$num_col' align='center'>";
  echo "<b>documenti disponibili ($num_doc)</b>";
  if ($num_doc >= $POST_NUM_DOC[$_SESSION["ksglobo_categoria_post"]]) echo "<div style='margin-top: 2px'>raggiunto il massimo numero di documenti caricabili</div>";
  echo "</td>";
  echo "</tr>";
    
  if ($num_doc > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='100%' align='left'><b>nome</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>tipo</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>data</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>scarica</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>elim</b></td>";
    echo "</tr>";
          
    foreach ($res_doc as $r_doc) {   
      $id_documento = $r_doc["id"];
      $nome_it = $r_doc["nome_it"];
      $nome_en = $r_doc["nome_en"];
      $file = $r_doc["file"];
      $dt_upload = globo_converti_mysql_datetime($r_doc["dt_upload"]);
      $data_upload = globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($dt_upload));
      $ora_upload = globo_ora_da_mysql_datetime($dt_upload);
      
      echo "<tr>";
      echo "<td class='tab_int_td_3' align='left'>";
      echo "<form action='adhoc_post_doc.php' method='post'>";
      echo "<div><input type='text' class='input_text' style='width: 400px;' maxlength='200' name='nome_it' value='$nome_it' onblur='this.form.submit();' /></div>";
      
      if (POST_LINGUA_EN) echo "<div style='margin-top: 5px;'><input type='text' class='input_text' style='width: 400px;' maxlength='200' name='nome_en' value='$nome_en' onblur='this.form.submit();' /></div>";
      else echo "<input type='hidden' name='nome_en' value='' />";
      
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='hidden' name='id_documento' value='$id_documento' />";
      echo "<input type='hidden' name='azione' value='3' />";
      echo "</form>";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='center'>" . substr($file, strpos($file, ".") + 1) . "</td>";
      echo "<td class='tab_int_td_3' align='center'><nobr>$data_upload $ora_upload</nobr></td>";
      echo "<td class='tab_int_td_3' align='center'><a href='" . POST_URL_DOC . $file . "' target='_blank' title='scarica'><img src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='scarica' /></a></td>";
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='adhoc_post_doc.php' method='post' onsubmit='return confermaEliminazione();'>";
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='hidden' name='id_documento' value='$id_documento' />";
      echo "<input type='hidden' name='azione' value='2' />";
      echo "<input type='image' src='immagini/ico_x.gif' width='16' height='16' border='0' alt='elimina' title='elimina' />";
      echo "</form>";
      echo "</td>";
      echo "</tr>";
    }
  }
  else
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun documento</b></td></tr>";
  echo "</table>";
  
  echo "<form action='adhoc_post_vis.php' method='post'>";
  echo "<input type='hidden' name='id_post' value='$id_post' />";
  echo "<input type='submit' value='dettagli post' class='input_bottone' />";
  echo "</form>";
  
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
<?php if (isset($popup)) echo $popup; ?>