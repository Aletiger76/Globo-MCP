<?php
  require("varie/common_1.php");
  if (!MOD_ADHOC_KSH5S || $_SESSION["ksglobo_livello_utente"] > LIV_MOD_ADHOC_KSH5S) { header("Location: index.php"); exit(); }

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

  if (isset($_POST["id"]) && $_POST["id"] > 0) $id = $_POST["id"];
  else $id = 0;

  if ($_SESSION["ksglobo_livello_utente"] > 1) {
    $readonly = "readonly='readonly'";
    $disabled = "disabled='disabled' style='color: #000; background-color: #fff;'";
  }
  else {
    $readonly = "";
    $disabled = "";
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php echo TITLE; ?></title>
<script language="javascript" type="text/javascript">
  <!--
  function controllaForm() {
    var re = / /g;
    var re_email = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
    var array_nome_db = new Array();
	
    <?php
      $i = 0;

      $query = "SELECT nome_db FROM h5s WHERE id <> '$id'";
      $res = $db->query($query)->fetchAll();

      foreach ($res as $r) {
        echo "array_nome_db[$i] = \"" . $r["nome_db"] . "\";\n";
        $i++;
      }
    ?>

    var nome = document.globo.nome.value.replace(re, "");
    // var location = document.globo.location.value.replace(re, "");
    var email = document.globo.email.value;
    //var email_2 = document.globo.email_2.value;
    var nome_db = document.globo.nome_db.value.replace(re, "");
    var id_h5s_server = document.globo.id_h5s_server.value;
    // var utente = document.globo.utente.value.replace(re, "");
    var flag_pms = document.globo.flag_pms.value;
    var flag_booking = document.globo.flag_booking.value;
    var id_booking = document.globo.id_booking.value.replace(re, "");
    var id_h5s_channel_manager = document.globo.id_h5s_channel_manager.value;
    var provinciaStruttura = document.globo.provinciaStruttura.value;

    if (nome == "") { alert("compilare il campo \"nome struttura\""); document.globo.nome.focus(); return false; }
    
    if (document.getElementById("statoStruttura").value == 123) {
      if (provinciaStruttura == -1) { alert("compilare il campo \"provincia\""); document.globo.provinciaStruttura.focus(); return false; }
    }
    // if (location == "") { alert("compilare il campo \"location\""); document.globo.location.focus(); return false; }
    if (email != "" && !re_email.test(email)) { alert("indirizzo e-mail non corretto"); document.globo.email.focus(); return false; }
    //if (email_2 != "" && !re_email.test(email_2)) { alert("indirizzo e-mail non corretto"); document.globo.email_2.focus(); return false; }
    if (nome_db == "") { alert("compilare il campo \"chiave/database\""); document.globo.nome_db.focus(); return false; }
    if (id_h5s_server == -1) { alert("compilare il campo \"server\""); document.globo.id_h5s_server.focus(); return false; }
    //if (utente == "") { alert("compilare il campo \"utente\""); document.globo.utente.focus(); return false; }
    if (flag_pms == -1) { alert("compilare il campo \"pms\""); document.globo.flag_pms.focus(); return false; }
    if (flag_booking == -1) { alert("compilare il campo \"booking online\""); document.globo.flag_booking.focus(); return false; }
    // if (flag_booking == 1 && id_booking == "") { alert("compilare il campo \"id booking online\""); document.globo.id_booking.focus(); return false; }
    if (id_h5s_channel_manager == -1) { alert("compilare il campo \"channel manager\""); document.globo.id_h5s_channel_manager.focus(); return false; }
    <?php if ($id == 0) { ?> if (array_nome_db.indexOf(nome_db) > -1) { return confirm("chiave/database gi� presente, procedere?"); } <?php } ?>
  }

  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }

  function confermaBloccoEmergenza() {
    return confirm("il blocco di emergenza disattiva immediatamente il booking online e il check-in online, confermi?");
  }
  //-->
</script>
<script type="text/javascript">
function select() {
  document.getElementById("provinciaStruttura").setAttribute("disabled", "disabled");
  if (document.getElementById("statoStruttura").value == 123) {
     document.getElementById("provinciaStruttura").removeAttribute("disabled");
  }
  else {
     document.getElementById("provinciaStruttura").value = -1;
     document.getElementById("provinciaStruttura").setAttribute("disabled", "disabled");
  }
};  
</script>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
<link rel="stylesheet" href="css/flatpickr.min.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>KOSMOSOL - 5stelle*</b></td></tr></table>
<?php
  if ($id > 0) {
    $query = "SELECT * FROM h5s WHERE id = '$id'";
    $res = $db->query($query)->fetchAll();
	/* codice cliente */
    $codice_cliente = $res[0]["codice_cliente"];

    $flag_stato = $res[0]["flag_stato"];
    $flag_allarme = $res[0]["flag_allarme"];
    $nome = $res[0]["nome"];
    /* stato */
    $statoStruttura = $res[0]["stato_struttura"];
    /* provincia */
    $provinciaStruttura = $res[0]["provincia_struttura"];
    /* lingua struttura */
    $linguaStruttura = $res[0]["lingua_struttura"];
    
    $location = $res[0]["location"];
    $camere = $res[0]["camere"];
    $gruppo = $res[0]["gruppo"];
    $email = $res[0]["email"];
    $email_2 = $res[0]["email_2"];
    $nome_db = $res[0]["nome_db"];
    $id_h5s_server = $res[0]["id_h5s_server"];
    $utente = $res[0]["utente"];
    $flag_pms = $res[0]["flag_pms"];
    $flag_booking = $res[0]["flag_booking"];
    $id_booking = $res[0]["id_booking"];
    $id_h5s_channel_manager = $res[0]["id_h5s_channel_manager"];
    $dati_channel_manager = globo_converti_acapo_da_html($res[0]["dati_channel_manager"]);
    $id_h5s_servizio = explode(";", $res[0]["id_h5s_servizio"]);
    $modello_fattura = $res[0]["modello_fattura"];
    $note = globo_converti_acapo_da_html($res[0]["note"]);
    $data_scadenza_canone = $res[0]["data_scadenza_canone"];
    $globo_id_bilanciov2_agente = $res[0]["globo_id_bilanciov2_agente"];
    $globo_id_progetto = $res[0]["globo_id_progetto"];
  }
  else {
	/* codice cliente */
    $codice_cliente = NULL;
    $flag_stato = 1;
    $flag_allarme = 0;
    $nome = "";
    $location = "";
    /* stato */
    $statoStruttura = 123;
    /* provinciaStruttura */
    $provinciaStruttura = -1;
    /* lingua struttura */
    $linguaStruttura = "";
    $linguaOption = "
    <option value='italiano' selected='selected'>italiano</option>
    <option value='inglese'>inglese</option>
    <option value='francese'>francese</option>
    <option value='spagnolo'>spagnolo</option>
    <option value='tedesco'>tedesco</option>
    ";
    
    $camere = "";
    $gruppo = "";
    $email = "";
    $email_2 = "";
    $nome_db = "";
    $id_h5s_server = -1;
    $utente = "";
    $flag_pms = -1;
    $flag_booking = -1;
    $id_booking = "";
    $id_h5s_channel_manager = -1;
    $dati_channel_manager = "";
    $id_h5s_servizio = array();
    $modello_fattura = "";
    $note = "";
    $data_scadenza_canone = "0000-00-00";
    $globo_id_bilanciov2_agente = 0;
    $globo_id_progetto = 0;
  }

  if ($camere == 0) $camere = "";
  if ($flag_booking == 0) $id_booking = "";

  $query_se = "SELECT * FROM h5s_server ORDER BY ord";
  $res_se = $db->query($query_se)->fetchAll();
  $num_se = count($res_se);
  
  /* stato */
  $query_statoStruttura = "SELECT * FROM h5s_state_it ORDER BY id";
  $res_statoStruttura = $db->query($query_statoStruttura)->fetchAll();
  $num_statoStruttura = count($res_statoStruttura);
  
  /* provincia */
  $query_provinciaStruttura = "SELECT * FROM h5s_province_it ORDER BY id";
  $res_provinciaStruttura = $db->query($query_provinciaStruttura)->fetchAll();
  $num_provinciaStruttura = count($res_provinciaStruttura);

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

  $db_globo_h5s = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO_H5S, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
  $db_globo_h5s->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $query_prog = "SELECT id, nome FROM progetto WHERE visibilita = '2' ORDER BY nome";
  $res_prog = $db_globo_h5s->query($query_prog)->fetchAll();
  $num_prog = count($res_prog);

  $query_prog_h5s = "SELECT globo_id_progetto FROM h5s WHERE id <> '$id'";
  $res_prog_h5s = $db->query($query_prog_h5s)->fetchAll();
  $id_prog_h5s = array();

  foreach ($res_prog_h5s as $r_prog_h5s) {
    $id_prog_h5s[count($id_prog_h5s)] = $r_prog_h5s["globo_id_progetto"];
  }

  echo "<form name='globo' action='adhoc_ks_h5s_ins_agg.php' method='post' onsubmit='return controllaForm();'>";
  echo "<table class='tab_int' cellspacing='3' width='100%'>";
  if ($id == 0) echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>NUOVO ELEMENTO</b></td></tr>";
  else echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>DETTAGLI ELEMENTO</b></td></tr>";
	
  /* codice cliente */
    if($id > 0) {
     echo "<tr>";
     echo "<td class='tab_int_td_2' align='left' valign='middle'><b>codice cliente</b></td>";
     echo "<td class='tab_int_td_3' align='left' valign='middle'><span style='font-weight: 600;font-size: 14px;' class='allCopy'>$codice_cliente</span></td>";
     echo "</tr>";
    }

  if ($id > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>stato</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_stato' id='flag_stato' $disabled>";

    if ($flag_stato == 1) echo "<option value='1' selected='selected'>attivo</option>";
    else echo "<option value='1'>attivo</option>";
    if ($flag_stato == 0) echo "<option value='0' selected='selected'>disattivato</option>";
    else echo "<option value='0'>disattivato</option>";

    echo "</select>";

    if ($disabled != "") echo "<input type='hidden' name='flag_stato' value='$flag_stato' />";

    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='flag_stato' value='$flag_stato' />";
  }

  echo "<input type='hidden' name='flag_stato_old' value='$flag_stato' />";

  if ($id > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>allarme</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='flag_allarme' id='flag_allarme'>";

    if ($flag_allarme == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    if ($flag_allarme == 1) echo "<option value='1' selected='selected'>s�</option>";
    else echo "<option value='1'>s�</option>";

    echo "</select>";
    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='flag_allarme' value='$flag_allarme' />";
  }

  echo "<tr><td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>nome struttura</b> <span style='color: red;'>obb</span></td><td class='tab_int_td_3' width='50%' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='200' name='nome' value='$nome' $readonly /></td></tr>";
  echo "<input type='hidden' name='nome_old' value='$nome'>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>camere</b></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text' style='width: 70px;' maxlength='20' name='camere' value='$camere' $readonly /></td></tr>";
  
  /* lingua predefinita struttura */
  switch ($linguaStruttura) {
    case "italiano":
        $linguaOption = "
        <option value='inglese'>inglese</option>
        <option value='francese'>francese</option>
        <option value='spagnolo'>spagnolo</option>
        <option value='tedesco'>tedesco</option>
        ";
        break;
    case "inglese":
        $linguaOption = "
        <option value='italiano'>italiano</option>
        <option value='francese'>francese</option>
        <option value='spagnolo'>spagnolo</option>
        <option value='tedesco'>tedesco</option>
        ";
        break;
    case "francese":
        $linguaOption = "
        <option value='italiano'>italiano</option>
        <option value='inglese'>inglese</option>
        <option value='spagnolo'>spagnolo</option>
        <option value='tedesco'>tedesco</option>
        ";
        break;
    case "tedesco":
        $linguaOption = "
        <option value='italiano'>italiano</option>
        <option value='inglese'>inglese</option>
        <option value='spagnolo'>spagnolo</option>
        <option value='francese'>francese</option>
        ";
        break;
	case "spagnolo":
        $linguaOption = "
        <option value='italiano'>italiano</option>
        <option value='inglese'>inglese</option>
        <option value='tedesco'>tedesco</option>
        <option value='francese'>francese</option>
        ";
        break;
  }
  
    echo "<tr>";
  echo "<td class='tab_int_td_2' width='50%' align='left' valign='middle'><b>lingua</b></td>";
  echo "<td class='tab_int_td_3' width='50%' align='left' valign='middle'>
  <select name='linguaStruttura'>
  <option selected='selected'>$linguaStruttura</option>
  $linguaOption
  </select>
  </td>";
  echo "</tr>";
  
  
  /* new tab statoStruttura */
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>stato</b></td>
  <td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='statoStruttura' id='statoStruttura' onchange='select();'>"; //onchange='showDiv();'

    if ($statoStruttura == -1) echo "<option value='-1'>---</option>";

    foreach ($res_statoStruttura as $r_statoStruttura) {
      if ($r_statoStruttura["id"] == $statoStruttura) echo "<option value='" . $r_statoStruttura["id"] . "' selected='selected'>" . $r_statoStruttura["name"] . "</option>";
      else echo "<option value='" . $r_statoStruttura["id"] . "'>" . $r_statoStruttura["name"] . "</option>";
    }

    echo "</select>";
  echo "</td>
  </tr>";
  //if ($disabled != "") echo "<input type='hidden' name='statoStruttura' value='$statoStruttura'>";
  
  $disabled_provincia = "disabled='disabled'";
  if($statoStruttura == 123) {$disabled_provincia = ""; }
  
  /* new tab provincia_struttura */
  echo "<tr id='tr_provincia'><td class='tab_int_td_2' align='left' valign='middle'><b>provincia</b> <span style='color: red;'>obb</span></td>
  <td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='provinciaStruttura' id='provinciaStruttura' $disabled_provincia>";

    if ($provinciaStruttura == -1) echo "<option value='-1'>---</option>";

    foreach ($res_provinciaStruttura as $r_provinciaStruttura) {
      if ($r_provinciaStruttura["id"] == $provinciaStruttura) echo "<option value='" . $r_provinciaStruttura["id"] . "' selected='selected'>" . $r_provinciaStruttura["name"] . "</option>";
      else echo "<option value='" . $r_provinciaStruttura["id"] . "'>" . $r_provinciaStruttura["name"] . "</option>";
    }

    echo "</select>";
  echo "</td>
  </tr>";
  //echo "<input type='hidden' name='provinciaStruttura' value='$provinciaStruttura'>";
  
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>location</b></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='200' name='location' value='$location'/></td></tr>"; //$readonly
  echo "<input type='hidden' name='location_old' value='$location'>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>gruppo</b></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='200' name='gruppo' value='$gruppo' $readonly /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>e-mail</b></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='90' name='email' value='$email' $readonly /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>e-mail aggiuntive</b> <em>(utilizzare il ; come separatore)</em></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='350' name='email_2' value='$email_2' $readonly /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>chiave/database</b> <span style='color: red;'>obb</span></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='80' name='nome_db' value='$nome_db' $readonly /></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>server</b> <span style='color: red;'>obb</span></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='id_h5s_server' id='id_h5s_server' $disabled>";

  if ($id_h5s_server == -1) echo "<option value='-1'>&nbsp;</option>";

  foreach ($res_se as $r_se) {
    if ($r_se["id"] == $id_h5s_server) echo "<option value='" . $r_se["id"] . "' selected='selected'>" . $r_se["valore"] . "</option>";
    else echo "<option value='" . $r_se["id"] . "'>" . $r_se["valore"] . "</option>";
  }

  echo "</select>";

  if ($disabled != "") echo "<input type='hidden' name='id_h5s_server' value='$id_h5s_server' />";

  echo "</td>";
  echo "</tr>";
  //echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>utente</b> <!--<span style='color: red;'>obb, maiusc/minusc</span>--></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='80' name='utente' value='$utente' $readonly /></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>pms</b> <span style='color: red;'>obb</span></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='flag_pms' id='flag_pms' $disabled>";

  if ($flag_pms == -1) echo "<option value='-1'>&nbsp;</option>";
  if ($flag_pms == 1) echo "<option value='1' selected='selected'>s�</option>";
  else echo "<option value='1'>s�</option>";
  if ($flag_pms == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";

  echo "</select>";

  if ($disabled != "") echo "<input type='hidden' name='flag_pms' value='$flag_pms' />";

  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>booking online</b> ";
	//per secure-reservation
	$query_bol = "SELECT version FROM versione_bol WHERE id = '$id_booking'";
    $res_bol = $db_bol->query($query_bol)->fetchAll();
  if ($flag_booking == 1) {
    echo " <small>[v". $res_bol[0]["version"] ."]</small> ";
  }
  echo "<span style='color: red;'>obb</span></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='flag_booking' id='flag_booking' $disabled>";

  if ($flag_booking == -1) echo "<option value='-1'>&nbsp;</option>";
  if ($flag_booking == 1) echo "<option value='1' selected='selected'>s�</option>";
  else echo "<option value='1'>s�</option>";
  if ($flag_booking == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";

  echo "</select>";

  if ($disabled != "") echo "<input type='hidden' name='flag_booking' value='$flag_booking' />";

  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>id booking online</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<input type='text' class='input_text standard' maxlength='200' name='id_booking' value='$id_booking' $readonly />";

  if (trim($id_booking) != "") {
      /* secure-reservation check */
      echo "<div style='margin-top: 5px;'>";
      echo "url booking online:<br /><a href='https://www.secure-reservation.cloud/booking/v2/check-availability.php?h=$id_booking' target='_blank' style='color: blue;'>";
      echo "https://www.secure-reservation.cloud/booking/v2/check-availability.php?<br />h=$id_booking";
      echo "</a>";
      echo "</div>";
      echo "<div style='margin-top: 3px;'>";
      echo "url offerte:<br /><a href='https://www.secure-reservation.cloud/booking/v2/offers.php?h=$id_booking' target='_blank' style='color: blue;'>";
      echo "https://www.secure-reservation.cloud/booking/v2/offers.php?<br />h=$id_booking";
      echo "</a>";
      echo "</div>";
  }

  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>channel manager</b> <span style='color: red;'>obb</span></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<select name='id_h5s_channel_manager' id='id_h5s_channel_manager' $disabled>";

  if ($id_h5s_channel_manager == -1) echo "<option value='-1'>&nbsp;</option>";

  if ($id_h5s_channel_manager == 0) echo "<option value='0' selected='selected'>no</option>";
  else echo "<option value='0'>no</option>";

  foreach ($res_ch as $r_ch) {
    if ($r_ch["id"] == $id_h5s_channel_manager) echo "<option value='" . $r_ch["id"] . "' selected='selected'>" . $r_ch["valore"] . "</option>";
    else echo "<option value='" . $r_ch["id"] . "'>" . $r_ch["valore"] . "</option>";
  }

  if ($id_h5s_channel_manager == 100) echo "<option value='100' selected='selected'>altro / non specificato</option>";
  else echo "<option value='100'>altro / non specificato</option>";

  echo "</select>";

  if ($disabled != "") echo "<input type='hidden' name='id_h5s_channel_manager' value='$id_h5s_channel_manager' />";

  echo "</td>";
  echo "</tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>dati channel manager</b> <span style='color: red;'>maiusc/minusc</span></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='dati_channel_manager' rows='4' cols='' class='standard'>$dati_channel_manager</textarea></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>servizi/interfacciamenti</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";
  echo "<table cellpadding='0' cellspacing='0' border='0'>";

  $i = 0;

  foreach ($res_serv as $r_serv) {
    if (in_array($r_serv["id"], $id_h5s_servizio)) {
      $checked = "checked='checked'";
      if ($disabled != "") echo "<input type='hidden' name='id_h5s_servizio[]' value='" . $r_serv["id"] . "' />";
    }
    else $checked = "";

    if ($i < (count($res_serv) - 1)) $border = "border-bottom: 1px solid #c5c5c5;";
    else $border = "";

    echo "<tr>";
    echo "<td align='left' valign='middle' style='$border'><input type='checkbox' name='id_h5s_servizio[]' value='" . $r_serv["id"] . "' $checked $disabled /></td>";
    echo "<td align='left' valign='middle' style='padding: 3px 4px 3px 4px; $border'>";
    if ($checked == "") echo "<span style='color: #7d7d7d;'>";
    echo $r_serv["valore"];

    if ($checked != "" && $r_serv["id"] == 11 && $id_h5s_server != 100) { // pms web
      $query_tmp = "SELECT url FROM h5s_server WHERE id = '$id_h5s_server'";
      $res_tmp = $db->query($query_tmp)->fetchAll();

      /*echo "<br />url: <a href='http://" . $res_tmp[0]["url"] . ":8080/receptionMobile/' target='_blank' style='color: blue;'>http://" . $res_tmp[0]["url"] . ":8080/receptionMobile/</a>";*/
	  // Nuovo link versione Pocket PMS - 18.02.2019 - NEGRI
	  echo "<br />url: <a href='https://" . $res_tmp[0]["url"] . "/receptionMobile/' target='_blank' style='color: blue;'>https://" . $res_tmp[0]["url"] . "/receptionMobile/</a>";
    }
    else if ($checked != "" && $r_serv["id"] == 17 && $id_h5s_server != 100) { // pos mobile
      $query_tmp = "SELECT url FROM h5s_server WHERE id = '$id_h5s_server'";
      $res_tmp = $db->query($query_tmp)->fetchAll();

      /*echo "<br />url: <a href='http://" . $res_tmp[0]["url"] . ":8080/hotelMobile' target='_blank' style='color: blue;'>http://" . $res_tmp[0]["url"] . ":8080/hotelMobile</a>";*/
	  // Nuovo link versione Pos Mobile - 18.02.2019 - NEGRI
	  echo "<br />url: <a href='https://" . $res_tmp[0]["url"] . "/hotelMobile/' target='_blank' style='color: blue;'>https://" . $res_tmp[0]["url"] . "/hotelMobile/</a>";
    }
    else if ($checked != "" && $r_serv["id"] == 23) { // check-in online
        /* secure-reservation check */
        echo "<br />url da riportare nella e-mail di pre check-in:<br /><span style='color: blue;' class='allCopy'>https://www.secure-reservation.cloud/booking/v2/check-in.php?k={key}&l={languageCode}&c={customerId}</span>";
    }

    if ($checked != "") echo "</span>";
    echo "</td>";
    echo "</tr>";

    $i++;
  }

  echo "</table>";
  echo "</td>";
  echo "</tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>modello fattura</b> <span style='color: red;'>maiusc/minusc</span></td><td class='tab_int_td_3' align='left' valign='middle'><input type='text' class='input_text standard' maxlength='80' name='modello_fattura' value='$modello_fattura' /></td></tr>";
  echo "<tr><td class='tab_int_td_2' align='left' valign='middle'><b>note</b> <span style='color: red;'>maiusc/minusc</span></td><td class='tab_int_td_3' align='left' valign='middle'><textarea name='note' rows='12' cols='' class='standard'>$note</textarea></td></tr>";
  echo "<tr>";
  echo "<td class='tab_int_td_2' align='left' valign='middle'><b>data scadenza canone</b></td>";
  echo "<td class='tab_int_td_3' align='left' valign='middle'>";

  if ($data_scadenza_canone == "0000-00-00") $data_scadenza_canone = "";
  else $data_scadenza_canone = globo_converti_data_in_formato_ita($data_scadenza_canone);
  ?>

  <?php if ($readonly == ""): ?>
  <span data-flatpickr>
    <input type="text" class="input_text data" maxlength="10" name="data_scadenza_canone"
      value="<?php echo $data_scadenza_canone; ?>" data-input autocomplete="off" />
    <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.png" width="24" height="24" border="0" alt="" /></a>
  </span>
  <?php else: ?>
  <input type="text" class="input_text data" maxlength="10" name="data_scadenza_canone"
    value="<?php echo $data_scadenza_canone; ?>" readonly="readonly" />
  <?php endif; ?>

  <?php
  echo "<input type='hidden' name='data_scadenza_canone_old' value='$data_scadenza_canone' />";

  if ($num_ag > 0) {
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left' valign='middle'><b>agente</b></td>";
    echo "<td class='tab_int_td_3' align='left' valign='middle'>";
    echo "<select name='globo_id_bilanciov2_agente' style='width: 380px;' $disabled>";

    if ($globo_id_bilanciov2_agente <= 0) echo "<option value='0' selected='selected'></option>";
    else echo "<option value='0'></option>";

    foreach ($res_ag as $r_ag) {
      if ($globo_id_bilanciov2_agente == $r_ag["id"])
        echo "<option value='" . $r_ag["id"] . "' selected='selected'>" . $r_ag["nome"] . "</option>";
      else
        echo "<option value='" . $r_ag["id"] . "'>" . $r_ag["nome"] . "</option>";
    }

    echo "</select>";

    if ($disabled != "") echo "<input type='hidden' name='globo_id_bilanciov2_agente' value='$globo_id_bilanciov2_agente' />";

    echo "</td>";
    echo "</tr>";
  }
  else {
    echo "<input type='hidden' name='globo_id_bilanciov2_agente' value='0' />";
  }

  if ($num_prog > 0) {
    if (MYSQL_DB_GLOBO == MYSQL_DB_GLOBO_H5S) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' align='left' valign='middle'><b>progetto</b></td>";
      echo "<td class='tab_int_td_3' align='left' valign='middle'>";
      echo "<select name='globo_id_progetto' style='width: 380px;' $disabled>";

      if ($globo_id_progetto <= 0) echo "<option value='0' selected='selected'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
      else echo "<option value='0'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";

      foreach ($res_prog as $r_prog) {
        // if (!in_array($r_prog["id"], $id_prog_h5s)) { // tolgo questo controllo perch� ci possono essere 2 profili h5s che fanno riferimento allo stesso progetto (2 strutture su 1 planner)
          if ($globo_id_progetto == $r_prog["id"])
            echo "<option value='" . $r_prog["id"] . "' selected='selected'>" . $r_prog["nome"] . "</option>";
          else
            echo "<option value='" . $r_prog["id"] . "'>" . $r_prog["nome"] . "</option>";
        // }
      }
      echo "</select>";

      if ($disabled != "") echo "<input type='hidden' name='globo_id_progetto' value='$globo_id_progetto' />";

      echo "</td>";
      echo "</tr>";
    }
    else {
      echo "<input type='hidden' name='globo_id_progetto' value='$globo_id_progetto' />";
    }
  }
  else {
    echo "<input type='hidden' name='globo_id_progetto' value='0' />";
  }

  echo "</table>";
  echo "<input type='hidden' name='id' value='$id'>";

  if ($id == 0) echo "<input type='submit' value='inserisci' class='input_bottone' />";
  else echo "<input type='submit' value='aggiorna' class='input_bottone' />";

  echo "</form>";

  if ($id > 0 && $_SESSION["ksglobo_livello_utente"] <= 1) {
    echo "<form action='adhoc_ks_h5s_el.php' method='post' onsubmit='return confermaEliminazione();' class='margin_top_bottoni'>";
    echo "<input type='hidden' name='id' value='$id' />";
    echo "<input type='submit' value='elimina' class='input_bottone' />";
    echo "</form>";
  }

  if ($id > 0 && trim($id_booking) != "" && $_SESSION["ksglobo_livello_utente"] <= 1) {
    echo "<form action='https://secure.kosmosol.it/booking/script/emergency-lock.php' method='post' target='_blank' onsubmit='return confermaBloccoEmergenza();' class='margin_top_bottoni'>";
    echo "<input type='hidden' name='h' value='$id_booking' />";
    echo "<input type='hidden' name='u' value='" . $_SESSION["ksglobo_nome_utente"] . "' />";
    echo "<input type='hidden' name='k' value='" . md5($id_booking . $_SESSION["ksglobo_nome_utente"] . date("Ymd")) . "' />";
    echo "<input type='submit' value='blocco di emergenza' class='input_bottone' />";
    echo "</form>";
  }

  echo "<form action='adhoc_ks_h5s_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco elementi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php $db_globo_ks = NULL; $db_bol = NULL; require("varie/common_2.php"); ?>