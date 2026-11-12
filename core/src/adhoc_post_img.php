<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_POST || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_POST) { header("Location: index.php"); exit(); }
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
    var file = document.globo.file.value.replace(re, "");
    var estensione = file.substring(file.length - 3, file.length).toUpperCase();
    if (file == "") { alert("compilare il campo \"file\""); document.globo.file.focus(); return false; }
    if (estensione != "JPG" && estensione != "GIF" && estensione != "PNG") { alert("formato file non valido"); document.globo.file.value = ""; return false;  } 
    openLoadingShadowbox();
  }
  function confermaEliminazione() {
    return confirm("confermi eliminazione immagine?");
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
  $num_img = 0;

  if (isset($_POST["azione"]) && $_POST["azione"] == 1) { // caricamento nuova immagine   
    try { 
      $query_img_princ = "SELECT id FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post' AND flag_tipo = '1'";
      $res_img_princ = $db->query($query_img_princ)->fetchAll();
      $num_img_princ = count($res_img_princ);
    } catch (PDOException $e) {
      $num_img_princ = 0;
    }

    try { 
      $query_img_non_princ = "SELECT * FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post' AND flag_tipo = '0'";
      $res_img_non_princ = $db->query($query_img_non_princ)->fetchAll();
      $num_img_non_princ = count($res_img_non_princ);
    } catch (PDOException $e) {
      $num_img_non_princ = 0;
    }
    
    $num_img = $num_img_princ + $num_img_non_princ;
    
    if ($num_img < $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]]) {
      $flag_tipo = $_POST["flag_tipo"];
      $file = $_FILES["file"];
      
      list($width_img, $height_img, $tipo_img, $attr_img) = getimagesize($_FILES["file"]["tmp_name"]);

      if ($flag_tipo == 1 && ($width_img != POST_WIDTH_IMG_DF_BIG || $height_img != POST_HEIGHT_IMG_DF_BIG)) {
        $popup = "<script language='javascript' type='text/javascript'>alert('l\'immagine di dimensioni fisse deve avere dimensioni " . POST_WIDTH_IMG_DF_BIG . " x " . POST_HEIGHT_IMG_DF_BIG . " pixel');</script>";
      }
      else {
        require_once("imageuploader/image.php"); 
        require_once("imageuploader/upload.php");  
    
        $up = new imgup_upload(POST_DIR_IMG_TEMP);
        $up->rename_file($id_post . "_" . date("YmdHis"));
        $up->upload_file($file);

        $img = new imgup_image(POST_DIR_IMG_TEMP . $up->filename);
        
        if ($img->create_source_image()) {
          $file_big = $up->filename . "_big.jpg";
          $file_small = $up->filename . "_small.jpg";
          
          if ($flag_tipo == 0) { // immagine photogallery
            $dimensions = $img->get_dimensions($img->src_filename);
            $width_big = $dimensions["image_width"];
            $height_big = $dimensions["image_height"];
            $width_small = $dimensions["thumb_width"];
            $height_small = $dimensions["thumb_height"];
          }
          else { // immagine DF (dimensioni fisse)
            $width_big = POST_WIDTH_IMG_DF_BIG;
            $height_big = POST_HEIGHT_IMG_DF_BIG;
            $width_small = POST_WIDTH_IMG_DF_SMALL;
            $height_small = POST_HEIGHT_IMG_DF_SMALL;
          }
          
          $img->new_proportionate_image(POST_DIR_IMG . $file_big, POST_QUAL_IMG, $width_big, $height_big);    
          $img->new_proportionate_image(POST_DIR_IMG . $file_small, POST_QUAL_IMG_SMALL, $width_small, $height_small);  
          $img->free();
        }
        
        $up->delete_file();
        
        if ($flag_tipo == 1) {
          try {
            $query = "UPDATE " . MYSQL_TAB_POST_IMG . " SET flag_tipo = '0' WHERE id_post = '$id_post'";
            $db->beginTransaction();
            $db->exec($query);
            $db->commit();
          } catch (PDOException $e) {
          }
        }

        $query = "INSERT INTO " . MYSQL_TAB_POST_IMG . " (id_post, file_big, file_small, width_big, height_big, width_small, height_small, flag_tipo)";
        $query .= "VALUES ('$id_post', '$file_big', '$file_small', '$width_big', '$height_big', '$width_small', '$height_small', '$flag_tipo')";
        $db->beginTransaction();
        $num = $db->exec($query);
        $db->commit();

        if ($num < 1) $popup = "<script language='javascript' type='text/javascript'>alert('errore caricamento immagine');</script>";
      }
    }
    else {
      $popup = "<script language='javascript' type='text/javascript'>alert('immagine non caricata (è possibile caricare fino a " . $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] . " immagini per ogni post)');</script>";
    }
  }  
  else if (isset($_POST["azione"]) && $_POST["azione"] == 2) { // eliminazione immagine    
    $id_immagine = $_POST["id_immagine"];
    
    $query = "SELECT file_big, file_small, flag_tipo FROM " . MYSQL_TAB_POST_IMG . " WHERE id = '$id_immagine' AND id_post = '$id_post'";
    $res = $db->query($query)->fetchAll();
    $file_big = $res[0]["file_big"];
    $file_small = $res[0]["file_small"];
    $flag_tipo = $res[0]["flag_tipo"];

    $query = "DELETE FROM " . MYSQL_TAB_POST_IMG . " WHERE id = '$id_immagine' AND id_post = '$id_post'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();
    
    if ($num > 0) {
      unlink(POST_DIR_IMG . $file_big);
      unlink(POST_DIR_IMG . $file_small);
    }
    else
      $popup = "<script language='javascript' type='text/javascript'>alert('errore eliminazione immagine');</script>";
  }
  else if (!$POST_IMG_DF[$_SESSION["ksglobo_categoria_post"]] && isset($_POST["azione"]) && $_POST["azione"] == 3) { // impostazione immagine come principale    
    $id_immagine = $_POST["id_immagine"];
    
    try {
      $query = "UPDATE " . MYSQL_TAB_POST_IMG . " SET flag_tipo = '0' WHERE id_post = '$id_post'";
      $db->beginTransaction();
      $db->exec($query);
      $db->commit();
    } catch (PDOException $e) {
    }
    
    $query = "UPDATE " . MYSQL_TAB_POST_IMG . " SET flag_tipo = '1' WHERE id = '$id_immagine' AND id_post = '$id_post'";
    $db->beginTransaction();
    $num = $db->exec($query);
    $db->commit();
  }

  $query = "SELECT titolo_it FROM " . MYSQL_TAB_POST . " WHERE id = '$id_post'";
  $res = $db->query($query)->fetchAll();
  $titolo_it = $res[0]["titolo_it"];

  try { 
    $query_img = "SELECT * FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post' ORDER BY flag_tipo DESC, id";
    $res_img = $db->query($query_img)->fetchAll();
    $num_img = count($res_img);
  } catch (PDOException $e) {
    $num_img = 0;
  }
  
  try { 
    $query_img_princ = "SELECT id FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post' AND flag_tipo = '1'";
    $res_img_princ = $db->query($query_img_princ)->fetchAll();
    $num_img_princ = count($res_img_princ);
  } catch (PDOException $e) {
    $num_img_princ = 0;
  }
  
  try { 
    $query_img_non_princ = "SELECT * FROM " . MYSQL_TAB_POST_IMG . " WHERE id_post = '$id_post' AND flag_tipo = '0'";
    $res_img_non_princ = $db->query($query_img_non_princ)->fetchAll();
    $num_img_non_princ = count($res_img_non_princ);
  } catch (PDOException $e) {
    $num_img_non_princ = 0;
  }
  
  echo "<form name='globo' action='adhoc_post_img.php' method='post' enctype='multipart/form-data' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";   
  echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>IMMAGINI POST</b></td></tr>";
  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>titolo post</b></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'>$titolo_it</td></tr>";
  
  if ($POST_IMG_DF[$_SESSION["ksglobo_categoria_post"]] && ($num_img < $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]])) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>tipo immagine</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_tipo'>";
    if ($num_img_princ < 1) echo "<option value='1' selected='selected'>immagine principale (" . POST_WIDTH_IMG_DF_BIG . " x " . POST_HEIGHT_IMG_DF_BIG . ")</option>";
    if ($num_img_non_princ < ($POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] - 1)) echo "<option value='0'>immagine photogallery</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
  }
  else {
    echo "<input type='hidden' name='flag_tipo' value='0'>";
  }
    
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>file</b> <font color='red'>jpg gif png</font></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<input type='file' class='input_text' size='45' maxlength='250' name='file' value=''"; 
  if ($num_img >= $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
  echo " />";     
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='hidden' name='id_post' value='$id_post' />";
  echo "<input type='hidden' name='azione' value='1' />";
  echo "<input type='submit' value='carica' class='input_bottone'"; 
  if ($num_img >= $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]]) echo " disabled='disabled'";
  echo " />"; 
  echo "</form>";
  echo "<table class='tab_int margin_top_blocchi' cellspacing='3' width='100%'>";   
  echo "<tr>";
  echo "<td class='tab_int_td_1' align='center'>";
  echo "<b>immagini caricate ($num_img)</b>";
  if ($num_img >= $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]]) echo "<div style='margin-top: 2px'>raggiunto il massimo numero di immagini caricabili</div>";
  echo "</td>";
  echo "</tr>";
      
  if ($num_img > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_3' align='center' valign='middle'>";
    
    echo "<table cellpadding='0' cellspacing='0' border='0'>";
    echo "<tr>";
    
    $i = 0;
      
    foreach ($res_img as $r_img) { 
      $id_immagine = $r_img["id"];
      $file_big = $r_img["file_big"];
      $file_small = $r_img["file_small"];
      $width_big = $r_img["width_big"];
      $height_big = $r_img["height_big"];
      $width_small = $r_img["width_small"];
      $height_small = $r_img["height_small"];
      $flag_tipo = $r_img["flag_tipo"];
      
      echo "<td align='center'>";
      echo "<table cellpadding='0' cellspacing='0' border='0'";
      if ($flag_tipo == 1) echo " style='border: 1px solid #ff0000'>";
      else echo " style='border: 1px solid #828282'>";
      echo "<tr>";
      /*
      echo "<td height='" . (LIM_HEIGHT_IMG_SMALL_VE + 10) . "' align='center' valign='middle' style='padding: 0px 10px 0px 10px; background-color: #ffffff;'>";
      echo "<img src='" . POST_URL_IMG . $file_small . "' border='0' />";
      */
      echo "<td align='center' valign='middle' style='background-color: #ffffff;'>";
      echo "<img src='" . POST_URL_IMG . $file_small . "' width='140' border='0' style='margin: 5px 5px 5px 5px;' />";
      echo "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td align='center' valign='middle' style='padding-bottom: 5px; background-color: #ffffff;'>";
      echo "<table cellpadding='0' cellspacing='0' border='0'>";
      echo "<tr>";
      echo "<td><a href='" . POST_URL_IMG . $file_big . "' rel='shadowbox' title=''><img src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='zoom' title='zoom' /></a></td>";
      
      if (!$POST_IMG_DF[$_SESSION["ksglobo_categoria_post"]] && $flag_tipo == 0 && $POST_NUM_IMG[$_SESSION["ksglobo_categoria_post"]] > 1) {
        echo "<td><img src='immagini/spacer.gif' width='5' height='1' border='0' /></td>";
        echo "<td>";
        echo "<form action='adhoc_post_img.php' method='post'>";
        echo "<input type='hidden' name='id_post' value='$id_post' />";
        echo "<input type='hidden' name='id_immagine' value='$id_immagine' />";
        echo "<input type='hidden' name='azione' value='3' />";
        echo "<input type='image' src='immagini/ico_stella.gif' width='16' height='16' border='0' alt='imposta come principale' title='imposta come principale' />";
        echo "</form>";
        echo "</td>";
      }
      
      echo "<td><img src='immagini/spacer.gif' width='5' height='1' border='0' /></td>";
      echo "<td>";
      echo "<form action='adhoc_post_img.php' method='post' onsubmit='return confermaEliminazione();'>";
      echo "<input type='hidden' name='id_post' value='$id_post' />";
      echo "<input type='hidden' name='id_immagine' value='$id_immagine' />";
      echo "<input type='hidden' name='azione' value='2' />";
      echo "<input type='image' src='immagini/ico_x.gif' width='16' height='16' border='0' alt='elimina' title='elimina' />";
      echo "</form>";
      echo "</td>";
      
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      
      if ($i > 0 && (($i + 1) % 5 == 0) && (($i + 1) < $num_img))
        echo "</tr></table><div><img src='immagini/spacer.gif' width='1' height='6' border='0'></div><table cellpadding='0' cellspacing='0' border='0'><tr>";
      else if ($i < $num_img - 1) 
        echo "<td><img src='immagini/spacer.gif' width='6' height='1' border='0'></td>";
      
      $i++;
    }
      
    echo "</tr>";
    echo "</table>";

    echo "</td>";
    echo "</tr>";
  }
  else
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessuna immagine</b></td></tr>";    
  echo "</table>";
  
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