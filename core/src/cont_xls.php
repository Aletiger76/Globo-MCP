<?php
  require("varie/common_1.php");
  // Export riservato: login garantito da common_1 + modulo Contatti e livello.
  // (Prima questa pagina era raggiungibile SENZA autenticazione → fuga PII.)
  // NB: si mantiene la connessione $db propria qui sotto (charset legacy) per non
  // alterare l'encoding dell'Excel esportato.
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

  $bgcolor_grigio = "#D4D0C8";
  
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    exit();
  }
  
  $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]);

  $p = '%' . $ricerca_testo . '%';
  $stmt = $db->prepare("SELECT * FROM contatto WHERE contatto.azienda LIKE ? OR contatto.nome LIKE ? OR contatto.cognome LIKE ? OR contatto.qualifica LIKE ? OR contatto.telefono LIKE ? OR contatto.cellulare LIKE ? OR contatto.fax LIKE ? OR contatto.email LIKE ? OR contatto.sito LIKE ? OR contatto.indirizzo LIKE ? OR contatto.cap LIKE ? OR contatto.citta LIKE ? OR contatto.provincia LIKE ? OR contatto.regione LIKE ? OR contatto.area LIKE ? OR contatto.nazione LIKE ? OR contatto.pi LIKE ? OR contatto.cf LIKE ? OR contatto.note LIKE ? ORDER BY TRIM(azienda), TRIM(cognome), TRIM(nome)");
  $stmt->execute(array_fill(0, 19, $p));
  $res = $stmt->fetchAll();
  $num = count($res);

  $nome_file = PREFISSO_FILE . "_contatti_" . date("YmdHis") . ".xls";
  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: inline; filename=$nome_file");

  echo "<table border='1'>"; 
  echo "<tr>";
  echo "<td colspan='18' align='center' bgcolor='$bgcolor_grigio'>";
  echo "<b>CONTATTI</b><br /><font size='2'>data creazione file: " . date("d/m/Y");
  if (trim($ricerca_testo) != "") echo " - testo ricercato: \"$ricerca_testo\"";
  echo "</font></td>";
  echo "</tr>"; 
  
  if ($num <= 0) {
    echo "<tr><td colspan='18' align='center'><b>nessun contatto</b></td></tr>"; 
  }
  else {
    echo "<tr>";
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>azienda</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>nome</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>cognome</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>qualifica</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>telefono</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>cellulare</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>fax</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>e-mail</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>sito web</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>indirizzo</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>cap</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>citt&agrave;</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>provincia</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>regione</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>area</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>partita iva</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>codice fiscale</b></td>"; 
    echo "<td align='left' bgcolor='$bgcolor_grigio'><b>note</b></td>";
    echo "</tr>"; 
    
    foreach ($res as $r) { 
      $azienda = globo_converti_da_html($r["azienda"]);
      $nome = globo_converti_da_html($r["nome"]);
      $cognome = globo_converti_da_html($r["cognome"]);
      $qualifica = globo_converti_da_html($r["qualifica"]);
      $telefono = globo_converti_da_html($r["telefono"]);
      $cellulare = globo_converti_da_html($r["cellulare"]);
      $fax = globo_converti_da_html($r["fax"]);
      $email = $r["email"];
      $sito = $r["sito"];
      $indirizzo = globo_converti_da_html($r["indirizzo"]);
      $cap = globo_converti_da_html($r["cap"]);
      $citta = globo_converti_da_html($r["citta"]);
      $provincia = globo_converti_da_html($r["provincia"]);
      $regione = globo_converti_da_html($r["regione"]);
      $area = globo_converti_da_html($r["area"]);
      $pi = globo_converti_da_html($r["pi"]);
      $cf = globo_converti_da_html($r["cf"]);
      $note = globo_converti_da_html(globo_converti_acapo_da_html_in_spazi($r["note"]));
        
      echo "<tr>";
      echo "<td align='left'>$azienda</td>";
      echo "<td align='left'>$nome</td>";
      echo "<td align='left'>$cognome</td>";
      echo "<td align='left'>$qualifica</td>";
      echo "<td align='left'>$telefono</td>";
      echo "<td align='left'>$cellulare</td>";
      echo "<td align='left'>$fax</td>";
      echo "<td align='left'>$email</td>";
      echo "<td align='left'>$sito</td>";
      echo "<td align='left'>$indirizzo</td>";
      echo "<td align='left'>$cap</td>";
      echo "<td align='left'>$citta</td>";
      echo "<td align='left'>$provincia</td>";
      echo "<td align='left'>$regione</td>";
      echo "<td align='left'>$area</td>";
      echo "<td align='left'>$pi</td>";
      echo "<td align='left'>$cf</td>";
      echo "<td align='left'>$note</td>";
      echo "</tr>"; 
    }
  }
    
  echo "</table>";

  $db = NULL;
  
  exit();
?>