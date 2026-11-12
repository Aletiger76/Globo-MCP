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
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }
?>
<?php
  $id = $_POST["id"];

  $flag_stato = $_POST["flag_stato"];
  $flag_stato_old = $_POST["flag_stato_old"];
  $flag_allarme = $_POST["flag_allarme"];
  $nome = strtolower(globo_converti_in_html($_POST["nome"]));
  $nome_old = strtolower(globo_converti_in_html($_POST["nome_old"]));
  /* stato */
  $statoStruttura = $_POST["statoStruttura"];
  /* provincia */
  if (isset($_POST["provinciaStruttura"])) $provinciaStruttura = $_POST["provinciaStruttura"];
  else $provinciaStruttura = -1;
  //echo $provinciaStruttura;
  /* lingua template */
  $linguaStruttura = $_POST["linguaStruttura"];
  
  $location = strtolower(globo_converti_in_html($_POST["location"]));
  $location_old = strtolower(globo_converti_in_html($_POST["location_old"]));
  $camere = $_POST["camere"];
  $gruppo = strtolower(globo_converti_in_html($_POST["gruppo"]));
  $email = strtolower($_POST["email"]);
  $email_2 = strtolower($_POST["email_2"]);
  $nome_db = strtolower(globo_converti_in_html($_POST["nome_db"]));
  $id_h5s_server = $_POST["id_h5s_server"];
  //$utente = globo_converti_in_html($_POST["utente"]);
  $utente = "";
  $flag_pms = $_POST["flag_pms"];
  $flag_booking = $_POST["flag_booking"];
  $id_booking = strtolower($_POST["id_booking"]);
  $id_h5s_channel_manager = $_POST["id_h5s_channel_manager"];
  $dati_channel_manager = globo_converti_in_html($_POST["dati_channel_manager"]);

  if (isset($_POST["id_h5s_servizio"])) $id_h5s_servizio = $_POST["id_h5s_servizio"];
  else $id_h5s_servizio = array();
  $id_h5s_servizio_str = ";";

  for ($i = 0; $i < count($id_h5s_servizio); $i++) {
    $id_h5s_servizio_str .= $id_h5s_servizio[$i] . ";";
  }

  $modello_fattura = globo_converti_in_html($_POST["modello_fattura"]);
  $note = globo_converti_in_html($_POST["note"]);
  $data_scadenza_canone = globo_converti_data_in_formato_mysql($_POST["data_scadenza_canone"]);
  $data_scadenza_canone_old = globo_converti_data_in_formato_mysql($_POST["data_scadenza_canone_old"]);
  if (!globo_controlla_data_datetime($data_scadenza_canone)) $data_scadenza_canone = "0000-00-00";
  if (!globo_controlla_data_datetime($data_scadenza_canone_old)) $data_scadenza_canone_old = "0000-00-00";
  $globo_id_bilanciov2_agente = $_POST["globo_id_bilanciov2_agente"];
  $globo_id_progetto = $_POST["globo_id_progetto"];

  if ($flag_stato == 0) $flag_allarme = 0;
  if (trim($id_booking) == "") $id_booking = "";
	
  /* codice cliente */
  $queryCheckDbEsistente = "SELECT codice_cliente FROM h5s WHERE nome_db = '$nome_db'";
  $resqueryCheckDbEsistente = $db->query($queryCheckDbEsistente)->fetchAll();
  $numqueryCheckDbEsistente = count($resqueryCheckDbEsistente);
  
  if($numqueryCheckDbEsistente >= 1) {$codice_cliente = $resqueryCheckDbEsistente[0]["codice_cliente"];}
  else {$codice_cliente = tryGenerateHolelCode($db);}

  echo "<table class='tab_int' cellspacing='3' width='100%'>";
  echo "<tr><td class='tab_int_td_1' align='center'><b>RISULTATO OPERAZIONE</b></td></tr>";

  if ($id == 0) {
    $query = "INSERT INTO h5s (codice_cliente, flag_stato, flag_allarme, nome, lingua_struttura, stato_struttura, provincia_struttura, location, camere, gruppo, email, email_2, nome_db, id_h5s_server, utente, flag_pms,";
    $query .= " flag_booking, id_booking, id_h5s_channel_manager, dati_channel_manager, id_h5s_servizio, modello_fattura,";
    $query .= " note, data_scadenza_canone, globo_id_bilanciov2_agente, globo_id_progetto)";
    $query .= " VALUES ('$codice_cliente', '$flag_stato', '$flag_allarme', '$nome','$linguaStruttura', '$statoStruttura', '$provinciaStruttura', '$location', '$camere', '$gruppo', '$email','$email_2', '$nome_db', '$id_h5s_server', '$utente', '$flag_pms',";
    $query .= " '$flag_booking', '$id_booking', '$id_h5s_channel_manager', '$dati_channel_manager', '$id_h5s_servizio_str', '$modello_fattura',";
    $query .= " '$note', '$data_scadenza_canone', '$globo_id_bilanciov2_agente', '$globo_id_progetto')";
    $db->beginTransaction();
    $num = $db->exec($query);
    $id = $db->lastInsertId();
    $db->commit();

    if ($num == 1) {
      echo "<tr><td class='tab_int_td_3' align='center'><b>elemento inserito</b></td></tr>";

      $oggetto = "elemento h5s inserito";
      $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha inserito il seguente elemento h5s";
      $testo .= "\n\n---";
      $testo .= "\n\nid: " . $id;
	  $testo .= "\n\ncodice cliente: " . $codice_cliente;
      $testo .= "\n\nnome: " . globo_converti_da_html($nome);
      $testo .= "\n\nlocation: " . globo_converti_da_html($location);
      mail(EMAIL_KOSMOSOL, $oggetto, $testo, "From: " . EMAIL_GLOBO);
    }
    else echo "<tr><td class='tab_int_td_3' align='center'><b>elemento non inserito</b></td></tr>";
  }
  else {
    $query = "UPDATE h5s SET codice_cliente = '$codice_cliente', flag_stato = '$flag_stato', flag_allarme = '$flag_allarme', nome = '$nome', lingua_struttura = '$linguaStruttura', stato_struttura = '$statoStruttura', provincia_struttura = '$provinciaStruttura', location = '$location', camere = '$camere', gruppo = '$gruppo', email = '$email',email_2 = '$email_2',";
    $query .= " nome_db = '$nome_db', id_h5s_server = '$id_h5s_server', utente = '$utente', flag_pms = '$flag_pms',";
    $query .= " flag_booking = '$flag_booking', id_booking = '$id_booking', id_h5s_channel_manager = '$id_h5s_channel_manager', dati_channel_manager = '$dati_channel_manager',";
    $query .= " id_h5s_servizio = '$id_h5s_servizio_str', modello_fattura = '$modello_fattura', note = '$note', data_scadenza_canone = '$data_scadenza_canone',";
    $query .= " globo_id_bilanciov2_agente = '$globo_id_bilanciov2_agente', globo_id_progetto = '$globo_id_progetto'";
    $query .= " WHERE id = '$id'";
    $db->beginTransaction();
    try { $num = $db->exec($query); } catch (PDOException $e) { $num = 0; }
    $db->commit();

    if ($num == 1) {
      echo "<tr><td class='tab_int_td_3' align='center'><b>elemento aggiornato</b></td></tr>";

      $oggetto = "elemento h5s aggiornato";
      $testo = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha aggiornato il seguente elemento h5s";
      $testo .= "\n\n---";
      //$testo .= "\n\nid: " . $id;
	  $testo .= "\n\ncodice cliente: " . $codice_cliente;
      $testo .= "\n\nnome: " . globo_converti_da_html($nome);
      if ($nome_old != $nome && $nome_old != "") $testo .= "\n\nnome PRECEDENTE: " . globo_converti_da_html($nome_old);
      $testo .= "\n\nlocation: " . globo_converti_da_html($location);
	  $testo .= "\n\ndatabase: " . $nome_db;
      //if ($location_old != $location && $location_old != "") $testo .= "\n\nlocation PRECEDENTE: " . globo_converti_da_html($location_old);
      /*if ($data_scadenza_canone != $data_scadenza_canone_old) {
        $testo .= "\n\ndata scadenza canone: " . globo_converti_data_in_formato_ita($data_scadenza_canone);
        $testo .= "\n\ndata scadenza canone PRECEDENTE: " . globo_converti_data_in_formato_ita($data_scadenza_canone_old);
      }*/
      if ($flag_stato != $flag_stato_old) {
        $testo .= "\n\nflag_stato: " . $flag_stato;
        $testo .= "\n\nflag stato PRECEDENTE: " . $flag_stato_old;
		
		mail('development@hotelcinquestelle.cloud', 'Stato cliente 5stelle aggiornato', $testo, "From: " . EMAIL_GLOBO);
      }

      //mail('a.negri@kosmosol.it', $oggetto, $testo, "From: " . EMAIL_GLOBO);
    }
    else  echo "<tr><td class='tab_int_td_3' align='center'><b>elemento non aggiornato</b></td></tr>";
  }

  echo "</table>";

  echo "<form action='adhoc_ks_h5s_ins_vis.php' method='post'>";
  echo "<input type='hidden' name='id' value='$id' />";
  echo "<input type='submit' value='visualizza elemento' class='input_bottone' />";
  echo "</form>";

  echo "<form action='adhoc_ks_h5s_lista.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='elenco elementi' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>