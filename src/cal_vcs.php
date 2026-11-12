<?php
  session_start();

  header("X-Frame-Options: SAMEORIGIN");
  header("X-Content-Type-Options: nosniff");
  header("Referrer-Policy: same-origin");
  header("X-XSS-Protection: 1; mode=block");

  if (!isset($_SESSION["ksglobo_login"]) || !$_SESSION["ksglobo_login"]) {
    exit();
  }

  $anno = $_POST["anno"] ?? '';
  $mese = $_POST["mese"] ?? '';

  require("config/config.php");
  require("varie/funzioni.php");
  
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);;
    exit();
  }

  if (strlen($mese) == 1) $mese = "0" . $mese;

  $nome_file = PREFISSO_FILE . "_impegni_" . $_SESSION["ksglobo_nome_utente"] . "_" . $anno . $mese . ".vcs";
  header("Content-Type: text/plain");
  header("Content-Disposition: attachment; filename=$nome_file");
  
  $stmtImp = $db->prepare("SELECT * FROM impegno WHERE data LIKE ? AND (responsabile = ? OR responsabile = 'tutti') ORDER BY data, ora");
  $stmtImp->execute(["$anno-$mese-%", $_SESSION["ksglobo_nome_utente"]]);
  $res = $stmtImp->fetchAll();
  $num = count($res);
  
  $codice_vcs = "";

  if ($num > 0) {
    for ($i = 0; $i < $num; $i++) { 
      $r = $res[$i];
      
      $data = $r["data"];
      $ora = $r["ora"];
      $durata = $r["durata"];
      $descrizione = $r["descrizione"];
      
      if ($i == 0) $codice_vcs = "BEGIN:VCALENDAR\nPRODID:-//Microsoft Corporation//Outlook MIMEDIR//EN\nVERSION:1.0\n\n";
      
      $data_inizio_vcs = date("Y", strtotime($data)) . date("m", strtotime($data)) . date("d", strtotime($data));
      $data_fine_vcs = $data_inizio_vcs;
      $ora_inizio_vcs = $ora[0] . $ora[1];
      $ora_inizio_vcs = $ora_inizio_vcs - 2; // GMT + ORA LEGALE
      $minuto_inizio_vcs = $ora[3] . $ora[4];
      
      if ($ora_inizio_vcs < 0) {
        $ora_inizio_vcs = 07;
        $minuto_inizio_vcs = 00;
      }
      
      $durata_ore_vcs = $durata[0] . $durata[1];
      $durata_minuti_vcs = $durata[3] . $durata[4];
      
      $minuto_fine_vcs = $minuto_inizio_vcs + $durata_minuti_vcs;
      $ora_fine_vcs = $ora_inizio_vcs + $durata_ore_vcs;
      
      if ($minuto_fine_vcs >= 60) {
        $minuto_fine_vcs = $minuto_fine_vcs - 60;
        $ora_fine_vcs = $ora_fine_vcs + 1;
      }  
      
      if ($ora_fine_vcs >= 24) {
        $ora_fine_vcs = 17;
        $minuto_fine_vcs = 00;
      }  
      
      if (strlen($ora_inizio_vcs) == 1) $ora_inizio_vcs = "0" . $ora_inizio_vcs;
      if (strlen($minuto_inizio_vcs) == 1) $minuto_inizio_vcs = "0" . $minuto_inizio_vcs;
      if (strlen($ora_fine_vcs) == 1) $ora_fine_vcs = "0" . $ora_fine_vcs;
      if (strlen($minuto_fine_vcs) == 1) $minuto_fine_vcs = "0" . $minuto_fine_vcs;
            
      $codice_vcs .= "BEGIN:VEVENT\n";
      $codice_vcs .= "DTSTART:" . $data_inizio_vcs . "T" . $ora_inizio_vcs . $minuto_inizio_vcs . "00Z\n";
      $codice_vcs .= "DTEND:" . $data_fine_vcs . "T" . $ora_fine_vcs . $minuto_fine_vcs . "00Z\n";
      $codice_vcs .= "LOCATION:\n";
      $codice_vcs .= "DESCRIPTION:" . $descrizione . "\n";
      $codice_vcs .= "SUMMARY:GLOBO\n";
      $codice_vcs .= "PRIORITY:1\n";
      $codice_vcs .= "END:VEVENT\n\n";
      
      if ($i == ($num - 1)) $codice_vcs .= "END:VCALENDAR";
    }    
    
    $codice_vcs = $stringa = str_replace("<br /><br /><br /><br />", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br /><br /><br />", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br /><br />", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br />", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br><br><br><br>", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br><br><br>", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br><br>", " - ", $codice_vcs);
    $codice_vcs = $stringa = str_replace("<br>", " - ", $codice_vcs);
    $codice_vcs = globo_converti_da_html($codice_vcs);
    
    echo $codice_vcs;
  }  
    
  $db = NULL;
  
  exit();
?>