<?php
  require("varie/common_1.php");
  // Export riservato: login garantito da common_1 + modulo Bilancio e livello.
  // (Prima questa pagina era raggiungibile SENZA autenticazione → fuga dati.)
  // NB: si mantiene la connessione $db propria qui sotto (charset legacy) per non
  // alterare l'encoding dell'Excel esportato.
  globo_richiedi_accesso(defined('MOD_BILANCIO') && MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO);

  $bgcolor_grigio = "#D4D0C8";

  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    exit();
  }
  
  $anno_bilancio = $_POST["anno_bilancio"] ?? '';
    
  $nome_file = PREFISSO_FILE . "_bilancio_" . date("YmdHis") . ".xls";
  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: inline; filename=$nome_file");

  echo "<table border='1'>"; 
  echo "<tr><td colspan='6' align='center' bgcolor='$bgcolor_grigio'><b>BILANCIO $anno_bilancio</b><br /><font size='2'>data creazione file: " . date("d/m/Y") . "</font></td></tr>"; 

  $utile = 0;
  
  for ($i_tipo = 0; $i_tipo < 2; $i_tipo++) {
    if ($i_tipo == 0) {
      $tipo_bilancio = "entrata";
      $col_sfondo = "#CCFFCC";
    }
    else if ($i_tipo == 1) {
      $tipo_bilancio = "uscita";
      $col_sfondo = "#FFCC99";
    }
    
    $stmt = $db->prepare("SELECT * FROM bilancio WHERE tipo = ? AND data LIKE ? ORDER BY data, id, fattura");
    $stmt->execute([$tipo_bilancio, $anno_bilancio . '%']);
    $res = $stmt->fetchAll();
    $num = count($res);
        
    if ($num == 0) {
      if ($i_tipo == 0) echo "<tr><td colspan='6' align='center'><b>NESSUNA ENTRATA</b></td></tr>"; 
      else if ($i_tipo == 1) echo "<tr><td colspan='6' align='center'><b>NESSUNA USCITA</b></td></tr>"; 
    }
    else {
      if ($i_tipo == 0) echo "<tr><td colspan='6' align='center' bgcolor='$col_sfondo'><b>ENTRATE</b></td></tr>"; 
      else if ($i_tipo == 1) echo "<tr><td colspan='6' align='center' bgcolor='$col_sfondo'><b>USCITE</b></td></tr>"; 
      
      echo "<tr>";
      echo "<td bgcolor='$bgcolor_grigio'><b>mese</b></td>";
      echo "<td bgcolor='$bgcolor_grigio'><b>data</b></td>";
      echo "<td bgcolor='$bgcolor_grigio'><b>descrizione</b></td>";
      echo "<td bgcolor='$bgcolor_grigio'><b>fattura</b></td>";
      echo "<td bgcolor='$bgcolor_grigio'><b>importo</b></td>";
      echo "<td bgcolor='$bgcolor_grigio'><b>pagamento</b></td>";
      echo "</tr>";
      
      $tot = 0;
      $mese = 0;
      $mese_precedente = 0;
      
      foreach ($res as $r) { 
        $id_bilancio = $r["id"];
        $data = $r["data"];
        $mese = date("m", strtotime($data));
        $descrizione = $r["descrizione"];
        $fattura = $r["fattura"];
        if ($fattura == "s") $fattura = "s&igrave;";
        if ($fattura == "n") $fattura = "no";
        $importo = $r["importo"];
        $tot = $tot + $importo;
        $pos_punto_importo = strpos($importo, ".");
        if ($pos_punto_importo != false) {
          $parte_intera_importo = substr($importo, 0, $pos_punto_importo);
          $parte_decimale_importo = substr($importo, $pos_punto_importo + 1, 2);
          $importo = $parte_intera_importo . "," . $parte_decimale_importo;
        }
        $flag_pagamento = $r["flag_pagamento"];
        if ($flag_pagamento == 0) $pagamento = "<font color='red'>no</font>";
        else if ($flag_pagamento == 1) $pagamento = "<font color='green'>s&igrave;</font>";
  
        if ($mese != $mese_precedente) {
          if ($mese == 1) echo "<tr><td><b>gennaio</b></td>";
          else if ($mese == 2) echo "<tr><td><b>febbraio</b></td>";
          else if ($mese == 3) echo "<tr><td><b>marzo</b></td>";
          else if ($mese == 4) echo "<tr><td><b>aprile</b></td>";
          else if ($mese == 5) echo "<tr><td><b>maggio</b></td>";
          else if ($mese == 6) echo "<tr><td><b>giugno</b></td>";
          else if ($mese == 7) echo "<tr><td><b>luglio</b></td>";
          else if ($mese == 8) echo "<tr><td><b>agosto</b></td>";
          else if ($mese == 9) echo "<tr><td><b>settembre</b></td>";
          else if ($mese == 10) echo "<tr><td><b>ottobre</b></td>";
          else if ($mese == 11) echo "<tr><td><b>novembre</b></td>";
          else if ($mese == 12) echo "<tr><td><b>dicembre</b></td>";
        }
        else {
          echo "<tr><td></td>";
        }
        
        echo "<td>$data</td><td>$descrizione</td><td align='left'>$fattura</td><td>$importo</td><td>$pagamento</td></tr>";
        
        $mese_precedente = $mese;
      }
    }
    
    if ($tipo_bilancio == "entrata") $utile = $utile + $tot;
    else if ($tipo_bilancio == "uscita") $utile = $utile - $tot;
    
    $pos_punto_tot = strpos($tot, ".");
    if ($pos_punto_tot != false) {
      $parte_intera_tot = substr($tot, 0, $pos_punto_tot);
      $parte_decimale_tot = substr($tot, $pos_punto_tot + 1, 2);
      $tot = $parte_intera_tot . "," . $parte_decimale_tot;
    }
    
    if ($i_tipo == 0) $tot_ent = $tot;
    else if ($i_tipo == 1) $tot_usc = $tot;
  }

  if ($utile > 0) $segno_utile = "+";
  else if ($utile < 0) $segno_utile = "-";
  
  $pos_punto_utile = strpos($utile, ".");
  if ($pos_punto_utile != false) {
    $parte_intera_utile = substr($utile, 0, $pos_punto_utile);
    $parte_decimale_utile = substr($utile, $pos_punto_utile + 1, 2);
    if ($segno_utile == "+") $utile = "<font color='green'>" . $parte_intera_utile . "," . $parte_decimale_utile . "</font>";
    else if ($segno_utile == "-") $utile = "<font color='red'>" . $parte_intera_utile . "," . $parte_decimale_utile . "</font>";
  }
  
  echo "<tr><td colspan='6' align='center' bgcolor='$bgcolor_grigio'><b>TOTALI</b></td></tr>"; 
  echo "<tr><td colspan='2' bgcolor='$bgcolor_grigio'><b>totale entrate</b></td><td colspan='4'><b>$tot_ent</b></td></tr>";
  echo "<tr><td colspan='2' bgcolor='$bgcolor_grigio'><b>totale uscite</b></td><td colspan='4'><b>$tot_usc</b></td></tr>";
  echo "<tr><td colspan='2' bgcolor='$bgcolor_grigio'><b>utile</b></td><td colspan='4'><b>$utile</b></td></tr>";
  echo "</table>";

  $db = NULL;
  
  exit();
?>