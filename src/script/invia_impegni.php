<?php
    $dominio = "globo-ks.kosmosol.it";
    
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/config/globo-ks_kosmosol_it.php");
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/config/config_smtp.php");
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/varie/funzioni.php");
  
    if (MOD_CALENDARIO) {
      globo_scrivi_log("| invia_impegni $dominio | begin script");
      
      try {
        $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e) {
        mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
        exit();
      }

      for ($i = 0; $i < 2; $i++) {
        if ($i == 0) $data = date("Y-m-d"); 
        else $data = date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")) + 86400); // data domani
        
        // impegni degli amministratori
        $query = "SELECT * FROM impegno WHERE impegno.data = '$data'"; 
        $query .= " AND impegno.responsabile IN (SELECT nome_utente FROM utente WHERE utente.livello = '1')";
        $query .= " ORDER BY responsabile, ora";
        $res = $db->query($query)->fetchAll();
        $num_impegni_utenti_liv1 = count($res);

        $lista_impegni_utenti_liv1 = "";
          
        if ($num_impegni_utenti_liv1 > 0) {
          foreach ($res as $r) { 
            $lista_impegni_utenti_liv1 .= "responsabile: " . $r["responsabile"];
            $lista_impegni_utenti_liv1 .= "<br />ora: " . $r["ora"];
            if ($r["durata"] == "24:00") $durata = "tutto il giorno";
            else $durata = $r["durata"];
            $lista_impegni_utenti_liv1 .= "<br />durata: " . $durata;
            $lista_impegni_utenti_liv1 .= "<br />descrizione: " . globo_elimina_spazi_multipli(globo_converti_acapo_da_html_in_spazi($r["descrizione"]));
            $lista_impegni_utenti_liv1 .= "<br /><br />";
          }  
        }
        
        // impegni degli utenti
        $query = "SELECT * FROM impegno WHERE impegno.data = '$data'"; 
        $query .= " AND impegno.responsabile IN (SELECT nome_utente FROM utente WHERE utente.livello = '2')";
        $query .= " ORDER BY responsabile, ora";
        $res = $db->query($query)->fetchAll();
        $num_impegni_utenti_liv2 = count($res);
        
        $lista_impegni_utenti_liv2 = "";
          
        if ($num_impegni_utenti_liv2 > 0) {
          foreach ($res as $r) { 
            $lista_impegni_utenti_liv2 .= "responsabile: " . $r["responsabile"] . "<br />";
            $lista_impegni_utenti_liv2 .= "ora: " . $r["ora"];
            if ($r["durata"] == "24:00") $durata = "tutto il giorno";
            else $durata = $r["durata"];
            $lista_impegni_utenti_liv2 .= "<br />durata: " . $durata;
            $lista_impegni_utenti_liv2 .= "<br />descrizione: " . globo_elimina_spazi_multipli(globo_converti_acapo_da_html_in_spazi($r["descrizione"]));
            $lista_impegni_utenti_liv2 .= "<br /><br />";
          }  
        }
        
        // impegni di tutti
        $query = "SELECT * FROM impegno WHERE impegno.data = '$data'"; 
        $query .= " ORDER BY responsabile, ora";
        $res = $db->query($query)->fetchAll();
        $num_impegni_tutti = count($res);
        
        $lista_impegni_tutti = "";
          
        if ($num_impegni_tutti > 0) {
          foreach ($res as $r) { 
            $lista_impegni_tutti .= "responsabile: " . $r["responsabile"];
            $lista_impegni_tutti .= "<br />ora: " . $r["ora"];
            if ($r["durata"] == "24:00") $durata = "tutto il giorno";
            else $durata = $r["durata"];
            $lista_impegni_tutti .= "<br />durata: " . $durata;
            $lista_impegni_tutti .= "<br />descrizione: " . globo_elimina_spazi_multipli(globo_converti_acapo_da_html_in_spazi($r["descrizione"]));
            $lista_impegni_tutti .= "<br /><br />";
          }  
        }
        
        if (CAL_FLAG_INVIO_IMP == 1) { // tutti ricevono gli impegni di tutti
          if ($i == 0) $oggetto = "impegni di oggi - " . globo_converti_data_in_formato_ita($data);
          else $oggetto = "impegni di domani - " . globo_converti_data_in_formato_ita($data);
          
          if ($num_impegni_tutti > 0) globo_invia_messaggio($db, 2, 1, 1, $oggetto, $lista_impegni_tutti, 2, "", "");
        }
        else if (CAL_FLAG_INVIO_IMP == 2) { // gli amministratori ricevono gli impegni di tutti mentre gli utenti solo quelli degli utenti
          if ($i == 0) $oggetto = "impegni amministratori di oggi - " . globo_converti_data_in_formato_ita($data);
          else $oggetto = "impegni amministratori di domani - " . globo_converti_data_in_formato_ita($data);
          
          if ($num_impegni_utenti_liv1 > 0) globo_invia_messaggio($db, 2, 1, 1, $oggetto, $lista_impegni_utenti_liv1, 1, "", "");
          
          if ($i == 0) $oggetto = "impegni utenti di oggi - " . globo_converti_data_in_formato_ita($data);
          else $oggetto = "impegni utenti di domani - " . globo_converti_data_in_formato_ita($data);
          
          if ($num_impegni_utenti_liv2 > 0) globo_invia_messaggio($db, 2, 1, 1, $oggetto, $lista_impegni_utenti_liv2, 2, "", "");
        }
      }
    
      $db = NULL;
      
      globo_scrivi_log("| invia_impegni $dominio | end script");
    }
?>