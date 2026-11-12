<?php  
    $dominio = "globo-ks.kosmosol.it";
    
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/config/globo-ks_kosmosol_it.php");
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/config/config_smtp.php");
    require("/var/www/vhosts/globo-ks.kosmosol.it/httpdocs/globo/varie/funzioni.php");
    
    if (MOD_CALENDARIO && date("w") != 0 && date("w") != 6) {
      globo_scrivi_log("| invia_scadenze $dominio | begin script");
      
      try {
        $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e) {
        mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
        exit();
      }

      $data_oggi = date("Y-m-d");
        
      /* inizio SCADENZE GENERICHE/PROGETTI */
      
      $query_1 = "SELECT * FROM utente WHERE livello > 0 ORDER BY livello, nome_utente";
      $res_1 = $db->query($query_1)->fetchAll();
      $num_1 = count($res_1);
      
      foreach ($res_1 as $r_1) { 
        $nome_utente = $r_1["nome_utente"];
        $livello = $r_1["livello"];
        
        $query_2 = "SELECT * FROM scadenza WHERE tipo <> 'bilancio_scad_pag' AND tipo <> 'bilancio_rinnovo'";
        $query_2 .= " AND data <= '$data_oggi' AND visibilita >= '$livello'";
        $query_2 .= " AND (responsabile = '$nome_utente' OR responsabile = 'tutti')";
        $query_2 .= " ORDER BY data DESC";
        $res_2 = $db->query($query_2)->fetchAll();
        $num_2 = count($res_2);
        
        if ($num_2 > 0) {
          $testo = "";
          
          foreach ($res_2 as $r_2) { 
            $data = $r_2["data"];
            $tipo = $r_2["tipo"];
            $visibilita = $r_2["visibilita"];
            $id_progetto = $r_2["id_progetto"];
            $descrizione = globo_elimina_spazi_multipli($r_2["descrizione"]);
            $responsabile = $r_2["responsabile"];
            
            $salta_scadenza = false;
            
            if ($tipo == "progetto_step") {
              $query_3 = "SELECT id FROM scadenza WHERE tipo = 'progetto_step' AND id_progetto = '$id_progetto' AND data < '$data'";
              $res_3 = $db->query($query_3)->fetchAll();
              $num_3 = count($res_3);
              
              if ($num_3 > 0) $salta_scadenza = true;
            }
            
            if (!$salta_scadenza) {
              if ($tipo == "progetto_step") $testo .= "scadenza step progetto";
              else if (substr_count($tipo, "progetto") > 0) $testo .= "scadenza progetto";
              else $testo .= "scadenza";
              
              $testo .= " del " . globo_converti_data_in_formato_ita($data);
              $testo .= "<br /><br />$descrizione";
              $testo .= "<br /><br />---<br /><br />";
            }
          }  
          
          if ($testo != "") {
            if ($livello == 1) {
              globo_invia_messaggio($db, 2, 1, 1, "scadenze - $nome_utente", $testo, 1, $nome_utente, "");
            }
            else { 
              //globo_invia_messaggio($db, 2, 1, 1, "scadenze - $nome_utente", $testo, 1, "", "");
              globo_invia_messaggio($db, 2, 1, 1, "scadenze - $nome_utente", $testo, 2, $nome_utente, "");
            }
          }
        }
      }
      
      /* fine SCADENZE GENERICHE/PROGETTI */

      /* inizio BILANCIO - SCADENZE PAGAMENTO */
      
      for ($i = 0; $i < 2; $i++) {
        if ($i == 0) $tmp = "entrata";
        else $tmp = "uscita"; 
        
        $query = "SELECT * FROM scadenza WHERE tipo = 'bilancio_scad_pag'";
        $query .= " AND descrizione LIKE '%scadenza pagamento " . $tmp . "%'";
        $query .= " AND data <= '$data_oggi'";
        $query .= " ORDER BY data DESC";
        $res = $db->query($query)->fetchAll();
        $num = count($res);

        if ($num > 0) {
          $testo = "";
          
          foreach ($res as $r) { 
            $data = $r["data"];
            $tipo = $r["tipo"];
            $descrizione = $r["descrizione"];
            
            if ($data != $data_oggi) $testo .= "vecchia scadenza bilancio";
            else $testo .= "scadenza bilancio";
            
            $testo .= "<br />data: " . globo_converti_data_in_formato_ita($data);
            $testo .= "<br /><br />$descrizione";
            $testo .= "<br /><br />---<br /><br />";
          }  
          
          if ($i == 0) globo_invia_messaggio($db, 2, 1, 1, "scadenze bilancio - pagamenti entrate", $testo, 1, "", "");
          else globo_invia_messaggio($db, 2, 1, 1, "scadenze bilancio - pagamenti uscite", $testo, 1, "", "");
        }
      }
      
      /* fine BILANCIO - SCADENZE PAGAMENTO */
      
      /* inizio BILANCIO - RINNOVI */
      
      for ($i = 0; $i < 2; $i++) {
        if ($i == 0) $tmp = "entrata";
        else $tmp = "uscita"; 
        
        $query = "SELECT * FROM scadenza WHERE tipo = 'bilancio_rinnovo'";
        $query .= " AND descrizione LIKE '%rinnovo " . $tmp . "%'";
        $query .= " AND data <= '$data_oggi'";
        $query .= " ORDER BY data DESC";
        $res = $db->query($query)->fetchAll();
        $num = count($res);

        if ($num > 0) {
          $testo = "";
          
          foreach ($res as $r) { 
            $data = $r["data"];
            $tipo = $r["tipo"];
            $descrizione = $r["descrizione"];
            
            if ($data != $data_oggi) $testo .= "vecchia scadenza bilancio";
            else $testo .= "scadenza bilancio";
            
            $testo .= "<br />data: " . globo_converti_data_in_formato_ita($data);
            $testo .= "<br /><br />$descrizione";
            $testo .= "<br /><br />---<br /><br />";
          }  
          
          if ($i == 0) globo_invia_messaggio($db, 2, 1, 1, "scadenze bilancio - rinnovi entrate", $testo, 1, "", "");
          else globo_invia_messaggio($db, 2, 1, 1, "scadenze bilancio - rinnovi uscite", $testo, 1, "", "");
        }
      }
      
      /* fine BILANCIO - RINNOVI */

      /* inizio SCADENZE PROCEDURE */
      
      $query_1 = "SELECT * FROM progetto_procedura ORDER BY descrizione";
      $res_1 = $db->query($query_1)->fetchAll();
      $num_1 = count($res_1);
      
      foreach ($res_1 as $r_1) { 
        $id_progetto_procedura = $r_1["id"];
        $descrizione_progetto_procedura = $r_1["descrizione"];
            
        $query_2 = "SELECT progetto.id AS id_progetto, progetto.nome AS nome_progetto,";
        $query_2 .= " progetto.descrizione AS descrizione_progetto, progetto_procedura_step.id AS id_progetto_procedura_step,";
        $query_2 .= " progetto_procedura_step.descrizione AS descrizione_progetto_procedura_step, progetto_procedura_step.data_scadenza AS data_scadenza_progetto_procedura_step";
        $query_2 .= " FROM progetto";
        $query_2 .= " JOIN progetto_procedura ON progetto_procedura.id = progetto.id_progetto_procedura";
        $query_2 .= " JOIN progetto_procedura_step ON progetto_procedura_step.id_progetto_procedura = progetto_procedura.id";
        $query_2 .= " WHERE progetto.flag_archivio = '0' AND progetto_procedura.id = '$id_progetto_procedura' AND progetto_procedura_step.data_scadenza <= '$data_oggi'";
        $query_2 .= " ORDER BY progetto_procedura_step.data_scadenza, progetto_procedura_step.descrizione";
        $res_2 = $db->query($query_2)->fetchAll();
        $num_2 = count($res_2);
        
        $testo = "";
        
        foreach ($res_2 as $r_2) { 
          $id_progetto = $r_2["id_progetto"];
          $nome_progetto = $r_2["nome_progetto"];
          $descrizione_progetto = $r_2["descrizione_progetto"];
          $id_progetto_procedura_step = $r_2["id_progetto_procedura_step"];
          $descrizione_progetto_procedura_step = $r_2["descrizione_progetto_procedura_step"];
          $data_scadenza_progetto_procedura_step = $r_2["data_scadenza_progetto_procedura_step"];
          
          $query_3 = "SELECT * FROM progetto_procedura_step_non_attivo WHERE id_progetto = '$id_progetto' AND id_progetto_procedura_step = '$id_progetto_procedura_step'";
          $res_3 = $db->query($query_3)->fetchAll();
          $num_3 = count($res_3);
          
          $query_4 = "SELECT * FROM progetto_procedura_step_chiuso WHERE id_progetto = '$id_progetto' AND id_progetto_procedura_step = '$id_progetto_procedura_step'";
          $res_4 = $db->query($query_4)->fetchAll();
          $num_4 = count($res_4);
        
          if ($num_3 <= 0 && $num_4 <= 0) {
            $testo .= "data: " . globo_converti_data_in_formato_ita($data_scadenza_progetto_procedura_step) . "<br />";
            $testo .= "progetto: $nome_progetto<br />descrizione: $descrizione_progetto<br />step: $descrizione_progetto_procedura_step";
            $testo .= "<br /><br />";
          }
        }
        
        $oggetto = "scadenze procedura - $descrizione_progetto_procedura";
        if (trim($testo) != "") globo_invia_messaggio($db, 2, 1, 1, $oggetto, $testo, 2, "", "");
      }

      /* fine SCADENZE PROCEDURE */
      
      $db = NULL;
      
      globo_scrivi_log("| invia_scadenze $dominio | end script");
    }
?>