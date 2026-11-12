<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_GLOBOMAIL') && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] > 0 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL); ?>
<?php 
  if (isset($_POST["azione"]) && $_POST["azione"] == 1) { // eliminazione dei messaggi selezionati
    if (isset($_POST["id_globomail_el"])) {
      $id_globomail_el = $_POST["id_globomail_el"];
      
      // Prepared statement + vincolo sul destinatario: si eliminano solo i PROPRI
      // messaggi (niente SQL injection, niente cancellazione di messaggi altrui).
      $stmt_el = $db->prepare("DELETE FROM globomail WHERE id = ? AND utente_dest = ?");
      foreach ($id_globomail_el as $id) {
        if (strlen($id) > 0) {
          $db->beginTransaction();
          $stmt_el->execute([$id, $_SESSION["ksglobo_nome_utente"]]);
          $db->commit();
        }
      }
    }
    else {
      $popup = "<script language='javascript' type='text/javascript'>alert('nessun messaggio selezionato');</script>";
    }
  }
  if (isset($_POST["azione"]) && $_POST["azione"] == 2) { // eliminazione di tutti i messaggi    
    $stmt_all = $db->prepare("DELETE FROM globomail WHERE utente_dest = ?");
    $db->beginTransaction();
    $stmt_all->execute([$_SESSION["ksglobo_nome_utente"]]);
    $db->commit();
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php 
  if ($_SESSION["ksglobo_livello_utente"] > 0 && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL) {
    echo "<meta http-equiv='Refresh' content='180; url=globomail_lista.php'>";
  }
?>
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function confermaEliminazione1() {
    return confirm("confermi eliminazione dei messaggi selezionati?");
  }
  function confermaEliminazione2() {
    return confirm("confermi eliminazione di tutti i messaggi?");
  }
  //-->
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>GLOBOMAIL</b></td></tr></table>
<?php  
  if (isset($_POST["ricerca_pag"])) $ricerca_pag = $_POST["ricerca_pag"]; 
  else if (isset($_SESSION["ksglobo_ricerca_globomail"]["pag"])) $ricerca_pag = $_SESSION["ksglobo_ricerca_globomail"]["pag"];
  else $ricerca_pag = 1;   
  if (isset($_POST["ricerca_testo"])) $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]); 
  else if (isset($_SESSION["ksglobo_ricerca_globomail"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_globomail"]["testo"];
  else $ricerca_testo = ""; 

  unset($_SESSION["ksglobo_ricerca_globomail"]);
  $_SESSION["ksglobo_ricerca_globomail"]["pag"] = $ricerca_pag;
  $_SESSION["ksglobo_ricerca_globomail"]["testo"] = $ricerca_testo;
  
  $query = "SELECT * FROM globomail";
  $query .= " WHERE utente_dest = '" . $_SESSION["ksglobo_nome_utente"] . "'";
  $query .= " AND (utente_mitt LIKE '%$ricerca_testo%' OR oggetto LIKE '%$ricerca_testo%')";
  $query .= " ORDER BY dt DESC, id DESC";
  $res = $db->query($query)->fetchAll();
  $num_tot = count($res);
  
  $num_pag = ceil($num_tot / 100);
  if ($ricerca_pag > $num_pag) $ricerca_pag = 1;
  
  $limit_1 = ($ricerca_pag - 1) * 100;
  $limit_2 = 100;
  
  $query .= " LIMIT $limit_1, $limit_2";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  if ($num > 0 || trim($ricerca_testo) != "") {
    echo "<form action='globomail_lista.php' method='post' class='margin_bottom_bottoni'>";
    echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
    echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>RICERCA</b></td></tr>"; 
    if ($num_pag > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' width='50%' align='left'><b>pagina</b></td>";
      echo "<td class='tab_int_td_3' width='50%' align='left'>";
      echo "<select name='ricerca_pag' onchange='this.form.submit();'>";
      for ($i = 1; $i <= $num_pag; $i++) {
        if ($i == $ricerca_pag) echo "<option value='$i' selected='selected'>$i</option>";
        else echo "<option value='$i'>$i</option>";
      }
      echo "</select>";
      echo " di $num_pag</td>";
      echo "</tr>";
    }
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='50%' align='left'><b>testo</b></td>";
    echo "<td class='tab_int_td_3' width='50%' align='left'>";
    echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "<input type='submit' value='ricerca' class='input_bottone' />";
    echo "</form>";
    
    echo "<form action='globomail_ins_1.php' method='post' class='margin_bottom_blocchi'>";   
    echo "<input type='submit' value='nuovo messaggio' class='input_bottone' />";
    echo "</form>";
  }
  
  if ($num <= 0) {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' align='center'><b>MESSAGGI</b></td></tr>";
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun messaggio</b></td></tr>";
    echo "</table>";
  }
  else {
    echo "<form action='globomail_lista.php' method='post' onsubmit='return confermaEliminazione1();' class='margin_bottom_bottoni'>";
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' colspan='6' align='center'><b>MESSAGGI ($num_tot)</b></td></tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='center'><b>mittente</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>data</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>ora</b></td>";
    echo "<td class='tab_int_td_2' align='left'><b>oggetto</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>sel</b></td>";
    echo "</tr>";
    
    foreach ($res as $r) { 
      $id_globomail = $r["id"];
      $dt = $r["dt"];
      $utente_mitt = $r["utente_mitt"];
      $oggetto = $r["oggetto"];
      $testo = $r["testo"];
      $flag_lettura = $r["flag_lettura"];
      
      $data = globo_data_da_mysql_datetime($dt);
      $ora = globo_ora_da_mysql_datetime($dt);
      if ($flag_lettura == 0) $colore = "red";
      else $colore = "black";

      echo "<tr>";
      echo "<td class='tab_int_td_3' align='center'><font color='$colore'><nobr>$utente_mitt</nobr></font></td>";
      echo "<td class='tab_int_td_3' align='center'><font color='$colore'><nobr>" . globo_converti_data_in_formato_ita($data) . "</nobr></font></td>";
      echo "<td class='tab_int_td_3' align='center'><font color='$colore'><nobr>$ora</nobr></font></td>";
      echo "<td class='tab_int_td_3' width='100%' align='left'><font color='$colore'>$oggetto</font></td>";
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<a href='globomail_vis.php?id=$id_globomail' title='" . globo_converti_acapo_da_html_in_spazi($testo) . "'><img src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='" . globo_converti_acapo_da_html_in_spazi($testo) . "'></a>";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='center'><input type='checkbox' name='id_globomail_el[]' value='$id_globomail'></td>";
      echo "</tr>";
    }
    
    echo "</table>";
    echo "<input type='hidden' name='azione' value='1' />";
    echo "<input type='submit' value='elimina selezionati' class='input_bottone' />";
    echo "</form>";
  }

  if ($num > 0) {
    echo "<form action='globomail_lista.php' method='post' onsubmit='return confermaEliminazione2();' class='margin_bottom_bottoni'>";
    echo "<input type='hidden' name='azione' value='2' />";
    echo "<input type='submit' value='elimina tutti' class='input_bottone' />";
    echo "</form>";
  }
  
  echo "<form action='globomail_ins_1.php' method='post' class='margin_bottom_bottoni'>";
  echo "<input type='submit' value='nuovo messaggio' class='input_bottone' />";
  echo "</form>";
    
  echo "<form action='home.php' method='post' class='margin_bottom_bottoni'>";
  echo "<input type='submit' value='home' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='index.php' method='post'>";
  echo "<input type='submit' value='logout' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
<?php if (isset($popup)) echo $popup; ?>
