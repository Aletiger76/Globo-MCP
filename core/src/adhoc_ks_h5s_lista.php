<?php 
  require("varie/common_1.php"); 
  if (!MOD_ADHOC_KSH5S || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KSH5S) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
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
    
    $db_globo = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db_globo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	$db_bol = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO_H5S, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db_bol->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }
?>
<?php  
  if (isset($_POST["ricerca_pag"])) $ricerca_pag = $_POST["ricerca_pag"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["pag"])) $ricerca_pag = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["pag"];
  else $ricerca_pag = 1;
  if (isset($_POST["ricerca_flag_stato"])) $ricerca_flag_stato = $_POST["ricerca_flag_stato"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_stato"])) $ricerca_flag_stato = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_stato"];
  else $ricerca_flag_stato = 1;  
  if (isset($_POST["ricerca_flag_stato"])) $ricerca_flag_allarme = $_POST["ricerca_flag_allarme"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_allarme"])) $ricerca_flag_allarme = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_allarme"];
  else $ricerca_flag_allarme = -1;  
  if (isset($_POST["ricerca_testo"])) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"])); 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["testo"];
  else $ricerca_testo = "";  
  if (isset($_POST["ricerca_id_h5s_server"])) $ricerca_id_h5s_server = $_POST["ricerca_id_h5s_server"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_server"])) $ricerca_id_h5s_server = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_server"];
  else $ricerca_id_h5s_server = -1;  
  if (isset($_POST["ricerca_flag_booking"])) $ricerca_flag_booking = $_POST["ricerca_flag_booking"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_booking"])) $ricerca_flag_booking = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_booking"];
  else $ricerca_flag_booking = -1;   
  if (isset($_POST["ricerca_id_h5s_channel_manager"])) $ricerca_id_h5s_channel_manager = $_POST["ricerca_id_h5s_channel_manager"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_channel_manager"])) $ricerca_id_h5s_channel_manager = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_channel_manager"];
  else $ricerca_id_h5s_channel_manager = -1;  
  if (isset($_POST["ricerca_id_h5s_servizio"])) $ricerca_id_h5s_servizio = $_POST["ricerca_id_h5s_servizio"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_servizio"])) $ricerca_id_h5s_servizio = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_servizio"];
  else $ricerca_id_h5s_servizio = -1;  
  if (isset($_POST["ricerca_mese_scad"])) $ricerca_mese_scad = $_POST["ricerca_mese_scad"];
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["mese_scad"])) $ricerca_mese_scad = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["mese_scad"];
  else $ricerca_mese_scad = "";
  if (isset($_POST["ricerca_globo_id_bilanciov2_agente"])) $ricerca_globo_id_bilanciov2_agente = $_POST["ricerca_globo_id_bilanciov2_agente"]; 
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["globo_id_bilanciov2_agente"])) $ricerca_globo_id_bilanciov2_agente = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["globo_id_bilanciov2_agente"];
  else $ricerca_globo_id_bilanciov2_agente = -1;  
  if (isset($_POST["ricerca_ordinamento"])) $ricerca_ordinamento = $_POST["ricerca_ordinamento"];  
  else if (isset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["ordinamento"])) $ricerca_ordinamento = $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["ordinamento"];
  else $ricerca_ordinamento = "nome";
  
  if ($ricerca_flag_stato == 0) $ricerca_flag_allarme = -1;

  unset($_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]);
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["pag"] = $ricerca_pag;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_stato"] = $ricerca_flag_stato;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_allarme"] = $ricerca_flag_allarme;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_server"] = $ricerca_id_h5s_server;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["flag_booking"] = $ricerca_flag_booking;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_channel_manager"] = $ricerca_id_h5s_channel_manager;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["id_h5s_servizio"] = $ricerca_id_h5s_servizio;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["mese_scad"] = $ricerca_mese_scad;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["globo_id_bilanciov2_agente"] = $ricerca_globo_id_bilanciov2_agente;
  $_SESSION["ksglobo_ricerca_adhoc_ks_h5s"]["ordinamento"] = $ricerca_ordinamento;
	
  $ricerca_testo = trim($ricerca_testo);

  $query = "SELECT * FROM h5s";
  $query .= " WHERE (nome LIKE '%$ricerca_testo%' OR location LIKE '%$ricerca_testo%' OR gruppo LIKE '%$ricerca_testo%' OR email LIKE '%$ricerca_testo%' OR nome_db LIKE '%$ricerca_testo%' OR codice_cliente LIKE '%$ricerca_testo%')";
  $query .= " AND flag_stato = '$ricerca_flag_stato'";
  if ($ricerca_flag_allarme != -1) $query .= " AND flag_allarme = '$ricerca_flag_allarme'";
  if ($ricerca_id_h5s_server != -1) $query .= " AND id_h5s_server = '$ricerca_id_h5s_server'";
  if ($ricerca_flag_booking != -1) $query .= " AND flag_booking = '$ricerca_flag_booking'";
  if ($ricerca_id_h5s_channel_manager != -1) $query .= " AND id_h5s_channel_manager = '$ricerca_id_h5s_channel_manager'";
  if ($ricerca_id_h5s_servizio != -1) $query .= " AND id_h5s_servizio LIKE '%;" . $ricerca_id_h5s_servizio . ";%'";
  if ($ricerca_mese_scad != "") $query .= " AND data_scadenza_canone LIKE '%-$ricerca_mese_scad-%'";
  if ($ricerca_globo_id_bilanciov2_agente != -1) $query .= " AND globo_id_bilanciov2_agente = '$ricerca_globo_id_bilanciov2_agente'";
  if ($ricerca_ordinamento == "nome") $query .= " ORDER BY nome ASC, location ASC";
  else if ($ricerca_ordinamento == "id") $query .= " ORDER BY id DESC";
  else if ($ricerca_ordinamento == "data_scadenza_canone") $query .= " ORDER BY data_scadenza_canone ASC";
  $res = $db->query($query)->fetchAll();
  $num_tot = count($res);

  $num_pag = ceil($num_tot / 100);
  if ($ricerca_pag > $num_pag) $ricerca_pag = 1;
  
  $limit_1 = ($ricerca_pag - 1) * 100;
  $limit_2 = 100;
  
  $query .= " LIMIT $limit_1, $limit_2";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  $query_se = "SELECT * FROM h5s_server ORDER BY ord";
  $res_se = $db->query($query_se)->fetchAll();
  $num_se = count($res_se);
      
  $query_ch = "SELECT * FROM h5s_channel_manager WHERE id <> 0 AND id <> 100 ORDER BY valore";
  $res_ch = $db->query($query_ch)->fetchAll();
  $num_ch = count($res_ch);
  
  $query_serv = "SELECT * FROM h5s_servizio ORDER BY valore";
  $res_serv = $db->query($query_serv)->fetchAll();
  $num_serv = count($res_serv);

  $db_globo_ks = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO_KS, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
  $db_globo_ks->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $query_ag = "SELECT id, nome FROM bilanciov2_agente ORDER BY nome";
  $res_ag = $db_globo_ks->query($query_ag)->fetchAll();
  $num_ag = count($res_ag);
  
  echo "<form action='adhoc_ks_h5s_lista.php' method='post' class='margin_bottom_bottoni'>";
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
  echo "<td class='tab_int_td_2' align='left'><b>stato/allarme</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_flag_stato' onchange='this.form.submit();'>";

  if ($ricerca_flag_stato == 1) echo "<option value='1' selected='selected'>attivo</option>";
  else echo "<option value='1'>attivo</option>";
  if ($ricerca_flag_stato == 0) echo "<option value='0' selected='selected'>disattivato</option>";
  else echo "<option value='0'>disattivato</option>";

  echo "</select>";
  echo "&nbsp;&nbsp;";
  echo "<select name='ricerca_flag_allarme' onchange='this.form.submit();'>";
  
  if ($ricerca_flag_allarme == -1) echo "<option value='-1' selected='selected'>---</option>";
  else echo "<option value='-1'>---</option>";
  if ($ricerca_flag_allarme == 0) echo "<option value='0' selected='selected'>non in allarme</option>";
  else echo "<option value='0'>non in allarme</option>";
  if ($ricerca_flag_allarme == 1) echo "<option value='1' selected='selected'>in allarme</option>";
  else echo "<option value='1'>in allarme</option>";

  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left'><b>testo</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left'>";
  echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>server</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_id_h5s_server' onchange='this.form.submit();'>";
  echo "<option value='-1'>---</option>";
  
  foreach ($res_se as $r_se) { 
    if ($r_se["id"] == $ricerca_id_h5s_server) echo "<option value='" . $r_se["id"] . "' selected='selected'>" . $r_se["valore"] . "</option>";
    else echo "<option value='" . $r_se["id"] . "'>" . $r_se["valore"] . "</option>";
  }
  
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>booking online</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_flag_booking' onchange='this.form.submit();'>";
  if ($ricerca_flag_booking == -1) echo "<option value='-1' selected='selected'>---</option>";
  else echo "<option value='-1'>---</option>";
  if ($ricerca_flag_booking == 1) echo "<option value='1' selected='selected'>s�</option>";
  else echo "<option value='1'>s�</option>";
  if ($ricerca_flag_booking == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>channel manager</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_id_h5s_channel_manager' onchange='this.form.submit();'>";
  echo "<option value='-1'>---</option>";
  
  if ($ricerca_id_h5s_channel_manager == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";
  
  foreach ($res_ch as $r_ch) { 
    if ($r_ch["id"] == $ricerca_id_h5s_channel_manager) echo "<option value='" . $r_ch["id"] . "' selected='selected'>" . $r_ch["valore"] . "</option>";
    else echo "<option value='" . $r_ch["id"] . "'>" . $r_ch["valore"] . "</option>";
  }
  
  if ($ricerca_id_h5s_channel_manager == 100) echo "<option value='100' selected='selected'>altro / non specificato</option>";
  else echo "<option value='100'>altro / non specificato</option>";
  
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>servizi/interfacciamenti</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_id_h5s_servizio' onchange='this.form.submit();'>";
  echo "<option value='-1'>---</option>";
  
  foreach ($res_serv as $r_serv) { 
    if ($r_serv["id"] == $ricerca_id_h5s_servizio) echo "<option value='" . $r_serv["id"] . "' selected='selected'>" . $r_serv["valore"] . "</option>";
    else echo "<option value='" . $r_serv["id"] . "'>" . $r_serv["valore"] . "</option>";
  }
  
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>mese scadenza canone</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<select name='ricerca_mese_scad' onchange='this.form.submit();'>";
  if ($ricerca_mese_scad == "") echo "<option value='' selected='selected'>---</option>";
  else echo "<option value=''>---</option>";
  if ($ricerca_mese_scad == "01") echo "<option value='01' selected='selected'>gennaio</option>";
  else echo "<option value='01'>gennaio</option>";
  if ($ricerca_mese_scad == "02") echo "<option value='02' selected='selected'>febbraio</option>";
  else echo "<option value='02'>febbraio</option>";
  if ($ricerca_mese_scad == "03") echo "<option value='03' selected='selected'>marzo</option>";
  else echo "<option value='03'>marzo</option>";
  if ($ricerca_mese_scad == "04") echo "<option value='04' selected='selected'>aprile</option>";
  else echo "<option value='04'>aprile</option>";
  if ($ricerca_mese_scad == "05") echo "<option value='05' selected='selected'>maggio</option>";
  else echo "<option value='05'>maggio</option>";
  if ($ricerca_mese_scad == "06") echo "<option value='06' selected='selected'>giugno</option>";
  else echo "<option value='06'>giugno</option>";
  if ($ricerca_mese_scad == "07") echo "<option value='07' selected='selected'>luglio</option>";
  else echo "<option value='07'>luglio</option>";
  if ($ricerca_mese_scad == "08") echo "<option value='08' selected='selected'>agosto</option>";
  else echo "<option value='08'>agosto</option>";
  if ($ricerca_mese_scad == "09") echo "<option value='09' selected='selected'>settembre</option>";
  else echo "<option value='09'>settembre</option>";
  if ($ricerca_mese_scad == "10") echo "<option value='10' selected='selected'>ottobre</option>";
  else echo "<option value='10'>ottobre</option>";
  if ($ricerca_mese_scad == "11") echo "<option value='11' selected='selected'>novembre</option>";
  else echo "<option value='11'>novembre</option>";
  if ($ricerca_mese_scad == "12") echo "<option value='12' selected='selected'>dicembre</option>";
  else echo "<option value='12'>dicembre</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";  
  if ($num_ag > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left'><b>agente</b></td>";
    echo "<td class='tab_int_td_3' align='left'>";
    echo "<select name='ricerca_globo_id_bilanciov2_agente' onchange='this.form.submit();'>";
    if ($ricerca_globo_id_bilanciov2_agente == -1) echo "<option value='-1' selected='selected'>---</option>";
    else echo "<option value='-1'>---</option>";
    foreach ($res_ag as $r_ag) { 
      if ($ricerca_globo_id_bilanciov2_agente == $r_ag["id"])
        echo "<option value='" . $r_ag["id"] . "' selected='selected'>" . $r_ag["nome"] . "</option>";
      else
        echo "<option value='" . $r_ag["id"] . "'>" . $r_ag["nome"] . "</option>";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }
  else echo "<input type='hidden' name='ricerca_globo_id_bilanciov2_agente' value='-1' />";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left'><b>ordina per</b></td>";
  echo "<td class='tab_int_td_3' align='left'>";
  echo "<table cellpadding='0' cellspacing='0' border='0'>";
  echo "<tr>";
  if ($ricerca_ordinamento == "nome") echo "<td><input type='radio' name='ricerca_ordinamento' value='nome' checked='checked' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>nome</td>";
  else echo "<td><input type='radio' name='ricerca_ordinamento' value='nome' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>nome</td>";
  if ($ricerca_ordinamento == "id") echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='id' checked='checked' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>inserimento (inverso)</td>";
  else echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='id' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>inserimento (inverso)</td>";
  if ($ricerca_ordinamento == "data_scadenza_canone") echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='data_scadenza_canone' checked='checked' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>data scadenza canone</td>";
  else echo "<td style='padding-left: 12px;'><input type='radio' name='ricerca_ordinamento' value='data_scadenza_canone' onchange='this.form.submit();'></td><td style='padding-top: 1px;'>data scadenza canone</td>";
  echo "</tr>";
  echo "</table>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<input type='submit' value='ricerca' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='adhoc_ks_h5s_ins_vis.php' method='post' class='margin_bottom_blocchi'>";  
  echo "<input type='submit' value='nuovo elemento'";
  if ($_SESSION["ksglobo_livello_utente"] > 1) echo "class='input_bottone_dis' disabled='disabled'";
  else echo "class='input_bottone'";
  echo " />";
  echo "</form>";

  if ($num <= 0) {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' align='center'><b>LISTA ELEMENTI</b></td></tr>";
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun elemento</b></td></tr>";
    echo "</table>";
  }
  else {
    if (MYSQL_DB_GLOBO == MYSQL_DB_GLOBO_H5S) $colspan = 14;
    else $colspan = 12;
    
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' colspan='$colspan' align='center'><b>LISTA ELEMENTI ($num_tot)</b></td></tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2 nobr' align='left'><b>cod. cliente</b></td>";
    echo "<td class='tab_int_td_2 nobr' width='50%' align='left'><b>struttura</b></td>";
    echo "<td class='tab_int_td_2 nobr' width='45%' align='left'><b>gruppo</b></td>";
    /*echo "<td class='tab_int_td_2 nobr' align='center'><b>em</b></td>";*/
    echo "<td class='tab_int_td_2 nobr' align='left'><b>ch/db ser ut</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>pms</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>bol</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>cm</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>s/i</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>scad can</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>ag</b></td>";
    if (MYSQL_DB_GLOBO == MYSQL_DB_GLOBO_H5S) echo "<td colspan='2' class='tab_int_td_2' align='center'><b>prog</b></td>";
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    echo "</tr>";
    
    foreach ($res as $r) { 
      $id = $r["id"];
	  $codice_cliente = $r["codice_cliente"];
      $flag_allarme = $r["flag_allarme"];
      $nome = $r["nome"];
      $location = $r["location"];
      $gruppo = $r["gruppo"];
      $email = $r["email"];
      $nome_db = $r["nome_db"];
      $id_h5s_server = $r["id_h5s_server"];
      $utente = $r["utente"];
      $flag_pms = $r["flag_pms"];
      $flag_booking = $r["flag_booking"];
      $id_booking = $r["id_booking"];
      $id_h5s_channel_manager = $r["id_h5s_channel_manager"];
      $id_h5s_servizio = explode(";", $r["id_h5s_servizio"]);
      $data_scadenza_canone = $r["data_scadenza_canone"];
      $globo_id_bilanciov2_agente = $r["globo_id_bilanciov2_agente"];
      $globo_id_progetto = $r["globo_id_progetto"];
      
      $query_se = "SELECT valore FROM h5s_server WHERE id = '$id_h5s_server'";
      $res_se = $db->query($query_se)->fetchAll();
      $server = $res_se[0]["valore"];
      
      $query_ch = "SELECT valore_abbr FROM h5s_channel_manager WHERE id = '$id_h5s_channel_manager'";
      $res_ch = $db->query($query_ch)->fetchAll();
      $channel_manager = $res_ch[0]["valore_abbr"];
      
      $val_h5s_servizio = array();
      $popup_servizio = "";
      
      for ($i = 0; $i < count($id_h5s_servizio); $i++) {
        $query_serv = "SELECT valore FROM h5s_servizio WHERE id = '" . $id_h5s_servizio[$i] . "'";
        $res_serv = $db->query($query_serv)->fetchAll();
        if (count($res_serv) > 0) $val_h5s_servizio[count($val_h5s_servizio)] = $res_serv[0]["valore"];
      }
      
      if (count($val_h5s_servizio) > 0) {
        sort($val_h5s_servizio);
        
        for ($i = 0; $i < count($val_h5s_servizio); $i++) {
          $popup_servizio .= $val_h5s_servizio[$i] . "\n";
        }
      }

      echo "<tr>";
	  echo "<td class='tab_int_td_3' align='left'><span class='allCopy'>$codice_cliente</span></td>";
      echo "<td class='tab_int_td_3' align='left'>";
      if ($flag_allarme == 1) echo "<span style='color: red;'>";
      echo $nome . "<br />" . $location;
      if ($flag_allarme == 1) echo "</span>";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='left'>$gruppo</td>";
      /*echo "<td class='tab_int_td_3' align='center'>";
      if (trim($email) != "") echo "<a href='mailto:$email' title='$email'>s�</a>";
      else echo "&nbsp;";
      echo "</td>";*/
      echo "<td class='tab_int_td_3 nobr' align='left'><span class='allCopy'>$nome_db</span><br /><span class='allCopy'>$server</span><!--<br />$utente--></td>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($flag_pms) echo "s�"; 
      else echo "&nbsp;";
      echo "</td>";
      echo "<td class='tab_int_td_3 nobr' align='center'>";
      if ($flag_booking && trim($id_booking) == "") echo "<span style='color: red;'>s�</span>";
      else if ($flag_booking && trim($id_booking) != "") {
		/* ------- */
        $query_bol = "SELECT version FROM versione_bol WHERE id = '$id_booking'";
        $res_bol = $db_bol->query($query_bol)->fetchAll();
          echo "<a href='https://www.secure-reservation.cloud/booking/v2/check-availability.php?h=$id_booking' title='visualizza booking online' target='_blank'><img src='immagini/ico_lente.png' width='16' height='16' border='0' alt='' /></a>";
          echo " <small>[v". $res_bol[0]["version"] ."]</small>";
        /* ---------- */  
	  }
      else echo "&nbsp;";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($id_h5s_channel_manager != 0) echo $channel_manager;
      else echo "&nbsp;";
      echo "</td>";  
      echo "<td class='tab_int_td_3' align='center'>";
      if ($popup_servizio != "") echo "<a title='$popup_servizio'>s�</a>";
      else echo "&nbsp;";
      echo "</td>";
      echo "<td class='tab_int_td_3 nobr' align='center'>";
      if (strtotime($data_scadenza_canone) < strtotime(date("Y-m-d"))) echo "<span style='color: red;'>";
      if ($data_scadenza_canone != "0000-00-00") echo globo_converti_data_in_formato_ita($data_scadenza_canone);
      else echo "&nbsp;";
      if (strtotime($data_scadenza_canone) < strtotime(date("Y-m-d"))) echo "</span>";
      echo "</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      if ($globo_id_bilanciov2_agente > 0) {
        $query_agente = "SELECT nome FROM bilanciov2_agente WHERE id = '$globo_id_bilanciov2_agente'";
        $res_agente = $db_globo_ks->query($query_agente)->fetchAll();
        if (count($res_agente) > 0) {
          $agente = $res_agente[0]["nome"];
          echo "<a title='$agente'>s�</a>";
        }
        else echo "s�";
      }
      else echo "&nbsp;";
      echo "</td>";
      echo "</td>";
      
      $progetto_ok = false;
      
      if (MYSQL_DB_GLOBO == MYSQL_DB_GLOBO_H5S) {
        if ($globo_id_progetto > 0) {
          $query_pr = "SELECT id, nome FROM progetto WHERE id = '$globo_id_progetto'";
          $res_pr = $db_globo->query($query_pr)->fetchAll();
          
          if (count($res_pr) == 1) {  
            $progetto_ok = true;
            
            $num_step = 0;
            $num_step_aperti = 0;
            
            $query_pr_st = "SELECT id FROM progetto_nota WHERE id_progetto = '$globo_id_progetto' AND tipo = 'step'";
            $res_pr_st = $db_globo->query($query_pr_st)->fetchAll();
            $num_step = count($res_pr_st);
          
            for ($s = 0; $s < $num_step; $s++) {
              $id_pr_st = $res_pr_st[$s]["id"];
              
              $query_sc = "SELECT id FROM scadenza WHERE id_progetto_nota = '$id_pr_st'";
              $res_sc = $db_globo->query($query_sc)->fetchAll();
              $num_step_aperti += count($res_sc);
            }
            
            $num_step_chiusi = $num_step - $num_step_aperti;
            
            if ($num_step == 0) $perc_completamento = "";
            else $perc_completamento = number_format(($num_step_chiusi / $num_step) * 100);
          }
        }
        
        if ($progetto_ok) {
          echo "<td class='tab_int_td_3' align='center'>";
          if ($num_step > 0) {
            if ($num_step_aperti == 0) echo "<a title='nessuno step da completare'>$num_step_aperti</a>";
            else echo "<a title='$num_step_aperti step da completare'>$num_step_aperti</a>";
          }
          echo "</td>";
          /*
          echo "<td class='tab_int_td_3' align='center' style='font-size: 10px;'>";
          if ($perc_completamento > 0) {
            if ($perc_completamento > 80) echo "<span style='color: green;'>";
            else if ($perc_completamento > 40) echo "<span style='color: #ff6600;'>";
            else echo "<span style='color: red;'>";
            echo "$perc_completamento%";
            echo "<span>";
          }
          echo "</td>";
          */
          echo "<td class='tab_int_td_3' align='center'>";
          echo "<form action='prog_vis.php' method='post'>";
          echo "<input type='hidden' name='id_progetto' value='$globo_id_progetto' />";
          echo "<input type='image' src='immagini/ico_lente.png' width='16' height='16' border='0' alt='" . $res_pr[0]["nome"] . "' title='" . $res_pr[0]["nome"] . "' />";
          echo "</form>";
          echo "</td>";
        }
        else {
          echo "<td class='tab_int_td_3' align='center'>";
          echo "<span>";
          echo "<td class='tab_int_td_3' align='center'>";
          echo "<span>";
        }
      }
      
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='adhoc_ks_h5s_ins_vis.php' method='post'>";
      echo "<input type='hidden' name='id' value='$id' />";
      echo "<input type='image' src='immagini/ico_lente.png' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</tr>";
    }
    
    echo "</table>";
  }
  
  echo "<form action='adhoc_ks_h5s_ins_vis.php' method='post'>";
  echo "<input type='submit' value='nuovo elemento'";
  if ($_SESSION["ksglobo_livello_utente"] > 1) echo "class='input_bottone_dis' disabled='disabled'";
  else echo "class='input_bottone'";
  echo " />";
  echo "</form>";
  
  echo "<form action='http://www.kosmosol.it/5stelle/mappa.php' method='post' target='_blank' class='margin_top_bottoni'>";
  echo "<input type='submit' value='mappa' class='input_bottone' />";
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
<?php $db_globo_ks = NULL; $db_bol = NULL; require("varie/common_2.php"); ?>