<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require(__DIR__ . "/../phpmailer/src/Exception.php");
require(__DIR__ . "/../phpmailer/src/PHPMailer.php");
require(__DIR__ . "/../phpmailer/src/SMTP.php");

/************************************************* CONTROLLO ACCESSI *************************************************/

  // Guardia d'accesso centralizzata: se $consentito è falso, reindirizza e
  // INTERROMPE l'esecuzione. L'exit() è il punto cruciale — senza, dopo header()
  // lo script proseguirebbe eseguendo letture/mutazioni nonostante il redirect
  // (è il bug storico delle vecchie guardie con "header senza exit").
  // La condizione va valutata dal chiamante in base al modulo/livello, es.:
  //   globo_richiedi_accesso(MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO);
  //   globo_richiedi_accesso($_SESSION["ksglobo_livello_utente"] == 0); // solo admin globale
  // $dest: destinazione del redirect (default index.php).
  function globo_richiedi_accesso(bool $consentito, string $dest = "index.php"): void {
    if (!$consentito) { header("Location: " . $dest); exit(); }
  }

/************************************************* INVIO NOTIFICHE *************************************************/

  function globo_crea_phpmailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->Host       = SMTP_ARUBA_SERVER;
    $mail->Username   = SMTP_ARUBA_USER;
    $mail->Password   = SMTP_ARUBA_PASSWORD;
    $mail->setFrom(EMAIL_GLOBO, EMAIL_GLOBO);
    $mail->Sender     = EMAIL_GLOBO;
    $mail->CharSet    = 'UTF-8';
    $mail->isHTML(true);
    return $mail;
  }

  function globo_invia_email(string $destinatario, string $oggetto, string $testo): bool {
    try {
      $mail = globo_crea_phpmailer();
      $mail->addAddress($destinatario);
      $mail->Subject = $oggetto;
      $mail->Body    = $testo;
      $mail->AltBody = strip_tags($testo);
      $mail->send();
      return true;
    } catch (Exception $e) {
      error_log("globo_invia_email: impossibile inviare a $destinatario — " . $e->getMessage());
      return false;
    }
  }

  function globo_invia_messaggio($db, $tipo, $invia_tramite_email, $invia_tramite_globomail, $oggetto, $testo, $visibilita, $utente_dest, $utente_mitt) {
    // $tipo = 2 --> notifica da script automatico
    // $tipo = 1 --> notifica
    // $tipo = 0 --> messaggio globomail
    
    $num_messaggi_email_inviati = 0;
    $num_messaggi_globomail_inviati = 0;
    $dt = date("Y-m-d H:i:s");

    if ($utente_mitt == "") $utente_mitt = "globo";
    
    if ($tipo == 1 || $tipo == 2) {
      $oggetto_email = globo_converti_da_html($oggetto);
      //$testo_email = globo_converti_da_html($testo);
	  $testo_email = $testo;
    }
    else {
      $oggetto_email = "nuovo messaggio globomail da $utente_mitt";
      $testo_email = "oggetto: " . globo_converti_da_html($oggetto) . "\n\n";
      $testo_email .= globo_converti_da_html($testo);
    }
  
    $oggetto_globomail = globo_converti_in_html($oggetto);
    $testo_globomail = globo_converti_in_html($testo);
    
    if ($utente_dest == "") {
      $stmt = $db->prepare("SELECT * FROM utente WHERE livello > 0 ORDER BY nome_utente");
      $stmt->execute([]);
    }
    else {
      $stmt = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
      $stmt->execute([$utente_dest]);
      $visibilita = 10;
    }

    $res = $stmt->fetchAll();
    $num = count($res);
    
    if ($tipo == 2) sleep(40); // negli script automatici imposto un timeout per evitare connessioni smtp troppo ravvicinate
    
    $phpmailer = globo_crea_phpmailer();
    $phpmailer->SMTPKeepAlive = true;

    if ($phpmailer->Host == SMTP_CRITSEND_SERVER) $oggetto_email = "[tag: " . MYSQL_DB_GLOBO . "_mess] " . $oggetto_email;

    $phpmailer->Subject = $oggetto_email;
    $phpmailer->Body    = $testo_email;

    $stmtGlobomail = $db->prepare("INSERT INTO globomail (dt, utente_mitt, utente_dest, oggetto, testo, flag_lettura) VALUES (?, ?, ?, ?, ?, '0')");

    foreach ($res as $r) {
      $livello = $r["livello"];
      $flag_notifiche_email = $r["flag_notifiche_email"];
      if ($tipo == 1 || $tipo == 2) $flag_notifiche_globomail = $r["flag_notifiche_globomail"];
      else $flag_notifiche_globomail = 1;

      if ($livello <= $visibilita && $flag_notifiche_email == 1 && $invia_tramite_email == 1) {
        $email = $r["email"];
        
        if (globo_controlla_email($email)) {
          try {
            $phpmailer->ClearAddresses();
            $phpmailer->AddAddress($email);
            $phpmailer->Send();
            $num_messaggi_email_inviati++;
            globo_scrivi_log("| " . MYSQL_DB_GLOBO . " | $oggetto_email | $email | ok", "invio_email");
          } catch (Exception $e) {
            globo_scrivi_log("| " . MYSQL_DB_GLOBO . " | $oggetto_email | $email | errore: " . $e->getMessage(), "invio_email");
          }
        }
      }

      if (defined("MOD_GLOBOMAIL") && MOD_GLOBOMAIL && $livello <= $visibilita && $flag_notifiche_globomail == 1 && $invia_tramite_globomail == 1) {
        $nome_utente = $r["nome_utente"];
        
        $db->beginTransaction();
        $stmtGlobomail->execute([$dt, $utente_mitt, $nome_utente, $oggetto_globomail, $testo_globomail]);
        if ($stmtGlobomail->rowCount() > 0) $num_messaggi_globomail_inviati++;
        $db->commit();
      }
    }
    
    $phpmailer->SmtpClose();
    
    $num_messaggi_inviati["email"] = $num_messaggi_email_inviati;
    $num_messaggi_inviati["globomail"] = $num_messaggi_globomail_inviati;
    
    return $num_messaggi_inviati;
  }

  function sendErrorEmail(string $errorMessage): void {
    $testo = 'Errore MySQL:<br><br>' . htmlspecialchars($errorMessage);
    globo_invia_email(EMAIL_KOSMOSOL, 'Errore MySQL - Globo', $testo);
  }

/************************************************************ LOG SU FILE ************************************************************/

  function globo_scrivi_log($testo, $suff_file = "") {
    $log = date("d/m/Y H:i:s") . " " . $testo . "\n";
    if ($suff_file == "") $nome_file = date("Y-m-d") . ".txt";
    else $nome_file = date("Y-m-d") . "_" . $suff_file . ".txt";
    $file = fopen(DIR_LOG . $nome_file, "a");
    if ($file === false) return;
    fwrite($file, $log);
    fclose($file);
  }

  // Audit log delle attività principali (login, creazioni, eliminazioni) nella
  // tabella attivita_log. Snello e non invasivo: una riga per evento, l'utente e
  // l'ip vengono letti dalla sessione/richiesta. Un fallimento del log NON deve
  // mai rompere l'operazione dell'utente → tutto in try/catch silenzioso.
  // Retention: 12 mesi, gestita da src/script/purga_log_attivita.php (cron).
  //
  // L'audit log vive SEMPRE sul DB principale (MYSQL_DB_GLOBO). Alcuni handler
  // lavorano su un DB diverso (es. il verticale H5S che si connette a 'kosmosol'),
  // quindi NON si usa il $db del chiamante — che scriverebbe nel DB sbagliato e
  // fallirebbe in silenzio — ma una connessione dedicata al DB principale. Il
  // parametro $db resta per retro-compatibilità delle chiamate (ignorato).
  function globo_log_attivita($db, $azione, $entita, $entita_id = null, $dettaglio = "") {
    try {
      $logdb = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
      $st = $logdb->prepare("INSERT INTO attivita_log (nome_utente, ip, azione, entita, entita_id, dettaglio) VALUES (?, ?, ?, ?, ?, ?)");
      $st->execute([
        $_SESSION["ksglobo_nome_utente"] ?? "",
        $_SERVER["REMOTE_ADDR"] ?? "",
        $azione,
        $entita,
        $entita_id !== null && $entita_id !== "" ? (int)$entita_id : null,
        mb_substr((string)$dettaglio, 0, 255)
      ]);
    } catch (PDOException $e) {
      // log non disponibile: si prosegue senza interrompere l'operazione
    }
  }


/************************************************************ FUNZIONI GENERICHE ************************************************************/
   
  function globo_controlla_email($email) {
    // return ereg("^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z])*\\.[a-zA-Z]{2,6}$", $email);
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }
  
  function globo_converti_in_html($stringa) {
    $stringa = str_replace("<", "&lt;", $stringa); $stringa = str_replace(">", "&gt;", $stringa);
    $stringa = str_replace("\n", "<br />", $stringa); $stringa = str_replace("\r", "", $stringa);
    $stringa = str_replace("\"", "&quot;", $stringa); $stringa = str_replace("?", "&quot;", $stringa); $stringa = str_replace("?", "&quot;", $stringa); $stringa = str_replace("?", "&quot;", $stringa);
    $stringa = str_replace("'", "&#39;", $stringa); $stringa = str_replace("?", "&#39;", $stringa); $stringa = str_replace("?", "&#39;", $stringa);
    $stringa = str_replace("?", "&agrave;", $stringa); $stringa = str_replace("?", "&Agrave;", $stringa);
    $stringa = str_replace("?", "&egrave;", $stringa); $stringa = str_replace("?", "&Egrave;", $stringa);
    $stringa = str_replace("?", "&eacute;", $stringa); $stringa = str_replace("?", "&Eacute;", $stringa);
    $stringa = str_replace("?", "&igrave;", $stringa); $stringa = str_replace("?", "&Igrave;", $stringa);
    $stringa = str_replace("?", "&ograve;", $stringa); $stringa = str_replace("?", "&Ograve;", $stringa);  
    $stringa = str_replace("?", "&ugrave;", $stringa); $stringa = str_replace("?", "&Ugrave;", $stringa);
    $stringa = str_replace("?", "&acirc;", $stringa); $stringa = str_replace("?", "&Acirc;", $stringa);
    $stringa = str_replace("?", "&ecirc;", $stringa); $stringa = str_replace("?", "&Ecirc;", $stringa);
    $stringa = str_replace("?", "&icirc;", $stringa); $stringa = str_replace("?", "&Icirc;", $stringa);
    $stringa = str_replace("?", "&ocirc;", $stringa); $stringa = str_replace("?", "&Ocirc;", $stringa);  
    $stringa = str_replace("?", "&ucirc;", $stringa); $stringa = str_replace("?", "&Ucirc;", $stringa);
    $stringa = str_replace("?", "&auml;", $stringa); $stringa = str_replace("?", "&Auml;", $stringa);
    $stringa = str_replace("?", "&euml;", $stringa); $stringa = str_replace("?", "&Euml;", $stringa);
    $stringa = str_replace("?", "&iuml;", $stringa); $stringa = str_replace("?", "&Iuml;", $stringa);
    $stringa = str_replace("?", "&ouml;", $stringa); $stringa = str_replace("?", "&Ouml;", $stringa);  
    $stringa = str_replace("?", "&uuml;", $stringa); $stringa = str_replace("?", "&Uuml;", $stringa);
    $stringa = str_replace("?", "&#382;", $stringa); $stringa = str_replace("?", "&#381;", $stringa);
    $stringa = str_replace("?", "&euro;", $stringa);
    $stringa = str_replace("?", "&deg;", $stringa);
    $stringa = str_replace("?", "&reg;", $stringa); $stringa = str_replace("?", "&trade;", $stringa); 
    $stringa = str_replace("\\", "", $stringa);
    $stringa = str_replace("?", "-", $stringa);
    return $stringa;
  }
  
  function globo_converti_da_html($stringa) {
    return htmlspecialchars_decode($stringa, ENT_QUOTES | ENT_HTML5);
  }

  // --- Helper di OUTPUT per il fix "doppia codifica" (decisione 2026-06-08) ---
  // Nel DB i campi di testo libero sono gia' HTML-di-presentazione (passati da
  // globo_converti_in_html in scrittura): in SOLA LETTURA vanno stampati GREZZI,
  // non con htmlspecialchars (che ricodificherebbe -> a schermo "d&#39;amico").
  // Questi due helper servono solo quando il dato esce in un contesto NON-HTML.

  // Da "HTML di presentazione" (come nel DB) a testo piano: scioglie i <br> in
  // a-capo e decodifica TUTTE le entita' (incluse le accentate nominate, &agrave; ...).
  function globo_to_text($v) {
    $v = str_ireplace(['<br />', '<br/>', '<br>'], "\n", $v);
    return html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }

  // Per popolare input/textarea in modifica: testo vero + escape corretto del contesto.
  // Round-trip: stored "l&#39;altra" -> globo_edit -> "l'altra" in textarea ->
  // al salvataggio globo_converti_in_html ri-produce l'originale.
  function globo_edit($v) {
    return htmlspecialchars(globo_to_text($v), ENT_QUOTES, 'UTF-8');
  }

  function globo_converti_in_html_da_editor($stringa) {
    // $stringa = stripslashes($stringa);
    $stringa = str_replace("'", "&#39;", $stringa);
    return $stringa;
  }
  
  function globo_converti_acapo_in_html($stringa) {
    if ($stringa != "") { 
      $stringa = str_replace("\n", "<br />", $stringa);
      $stringa = str_replace("\r", "<br />", $stringa);
    }
    return $stringa;
  }
  
  function globo_converti_acapo_da_html($stringa) {
    if ($stringa != "") { 
      $stringa = str_replace("<br />", "\n", $stringa);
      $stringa = str_replace("<br>", "\n", $stringa);
    }
    return $stringa;
  }
  
  function globo_converti_virgolette_apici_in_html($stringa) {
    $stringa = str_replace("\"", "&quot;", $stringa);
    $stringa = str_replace("'", "&#39;", $stringa);
    return $stringa;
  }
  
  function globo_converti_acapo_da_html_in_spazi($stringa) {
    if ($stringa != "") { 
      $stringa = str_replace("<br />", " ", $stringa);
      $stringa = str_replace("<br>", " ", $stringa);
    }
    return $stringa;
  }
  
  function globo_elimina_br_e_paragrafi($stringa) {
    if ($stringa != "") { 
      $stringa = str_replace("<br />", "", $stringa);
      $stringa = str_replace("<br>", "", $stringa);
      $stringa = str_replace("<p>", "", $stringa);
      $stringa = str_replace("</p>", "", $stringa);
    }
    return $stringa;
  }
  
  function globo_elimina_acapo($stringa) {
    if ($stringa != "") { 
      $stringa = str_replace("\n", "", $stringa);
      $stringa = str_replace("\r", "", $stringa);
    }
    return $stringa;
  }
  
  function globo_elimina_spazi_multipli($stringa) {
    $stringa = str_replace("\t", " ", $stringa);
    $stringa = preg_replace('/ {2,}/', ' ', $stringa);
    return $stringa;
  }
  
  function globo_2dec($numero) {
    return number_format($numero, 2, '.', '');
  }
  
  function globo_converti_num_fattura($num_fattura, $anno_fattura = 2012) {
    if (!is_numeric($num_fattura)) return $num_fattura;
    $lunghezza_num_fattura = strlen($num_fattura);
    if ($anno_fattura <= 2012) {
      if ($lunghezza_num_fattura == 1) $ret = "00" . $num_fattura;
      else if ($lunghezza_num_fattura == 2) $ret = "0" . $num_fattura;
      else $ret = $num_fattura;
    }
    else {
      if ($lunghezza_num_fattura == 1) $ret = "000" . $num_fattura;
      else if ($lunghezza_num_fattura == 2) $ret = "00" . $num_fattura;
      else if ($lunghezza_num_fattura == 3) $ret = "0" . $num_fattura;
      else $ret = $num_fattura;
    }
    return $ret;
  }
  
  function globo_converti_path_immagini_newsletter($stringa) {
    return str_replace(NL_OLDEDITOR_PATH_UPLOAD_IMG, NL_OLDEDITOR_URL_UPLOAD_IMG, $stringa);
  }
  
  function globo_tronca_stringa($stringa, $limite) {
    if (strlen($stringa) > $limite) {
      $stringa = substr($stringa, 0, ($limite - 3));
      for ($i = 0; $i < 5; $i++) {
        $ult_10_car = substr($stringa, strlen($stringa) - 9, 10);
        if (substr_count($ult_10_car, "&") > 0) $stringa = substr($stringa, 0, (strlen($stringa) - 8));
        else break;
      }
      $stringa .= "...";
    }
    return $stringa;
  }

  function globo_ckeditor_4_5_2($file_config, $num, $google_map = false) {
    echo "<script type=\"text/javascript\" src=\"ckeditor/ckeditor_4.5.2/ckeditor.js\"></script>\n";
    echo "<script type=\"text/javascript\">\n";
    echo "window.onload = function() {\n";
    for ($i = 1; $i <= $num; $i++) {
      echo "CKEDITOR.replace('ckeditor_" . $i . "',\n";
      echo "{\n";
      if ($_SERVER["HTTP_HOST"] == "localhost" || substr_count($_SERVER["HTTP_HOST"], "127.") > 0 || substr_count($_SERVER["HTTP_HOST"], "192.") > 0)
        echo "customConfig: '/KosmoSol/GLOBO/ckeditor/ckeditor_4.5.2/$file_config'\n";
      else
        echo "customConfig: '" . URL_GLOBO . "/ckeditor/ckeditor_4.5.2/$file_config'\n";
      echo "});\n";
    }
    if ($google_map) echo "loadMap();\n";
    echo "};\n";
    if ($google_map) {
      echo "window.onunload = function() {\n";
      echo "GUnload();\n";
      echo "};\n";
    }
    echo "</script>";
  }
  

/********************************************************* DATA/ORA *********************************************************/
  
  function globo_controlla_data_datetime($d) { // controlla la correttezza di una data o di un datetime (data in formato "YYYY-MM-DD" e datetime in formato "YYYY-MM-DD HH:MM:SS")
    if (trim($d) == "") return false;
    else {
      $anno = substr($d, 0, 4);
      $mese = substr($d, 5, 2);
      $giorno = substr($d, 8, 2);
      if (!checkdate($mese, $giorno, $anno)) return false;
      else return true;
    }
  }

  function globo_converti_mysql_date($mysql_date) {
    if (is_numeric($mysql_date)) { // $mysql_date nel formato "YYYYMMDD"
      $ret = substr($mysql_date, 0, 4) . "-" . substr($mysql_date, 4, 2) . "-" . substr($mysql_date, 6, 2);
    } 
    else { // $mysql_date nel formato "YYYY-MM-DD"
      $ret = $mysql_date;
    }
    if (!globo_controlla_data_datetime($mysql_date)) $ret = "";
    return $ret;
  }
  
  function globo_converti_mysql_datetime($mysql_datetime) {
    if (is_numeric($mysql_datetime)) { // $mysql_datetime nel formato "YYYYMMDDHHMMSS"
      $ret = substr($mysql_datetime, 0, 4) . "-" . substr($mysql_datetime, 4, 2) . "-" . substr($mysql_datetime, 6, 2)
        . " " . substr($mysql_datetime, 8, 2) . ":" . substr($mysql_datetime, 10, 2) . ":" . substr($mysql_datetime, 12, 2);
    } 
    else { // $mysql_datetime nel formato "YYYY-MM-DD HH:MM:SS"
      $ret = $mysql_datetime;
    }
    return $ret;
  }
  
  function globo_data_da_mysql_datetime($mysql_datetime) {
    $mysql_datetime = globo_converti_mysql_datetime($mysql_datetime);
    $ret = substr($mysql_datetime, 0, 10);
    return $ret;
  }
  
  function globo_ora_da_mysql_datetime($mysql_datetime) {
    $mysql_datetime = globo_converti_mysql_datetime($mysql_datetime);
    $ret = substr($mysql_datetime, 11, 8);
    return $ret;
  }

  function globo_converti_data_in_formato_ita($data) { // converte la data dal formato "YYYY-MM-DD" al formato "DD/MM/YYYY"
    $data = globo_converti_mysql_date($data);
    if (trim($data) != "" && $data[4] == "-" && $data[7] == "-") {
      $anno = substr($data, 0, 4);
      $mese = substr($data, 5, 2);
      $giorno = substr($data, 8, 2);
      $data = $giorno . "/" . $mese . "/" . $anno;
    }
    return $data;
  }
  
  function globo_converti_data_in_formato_mysql($data) { // converte la data dal formato "DD/MM/YYYY" al formato "YYYY-MM-DD"
    if (trim($data) != "" && $data[2] == "/" && $data[5] == "/") {
      $giorno = substr($data, 0, 2);
      $mese = substr($data, 3, 2);
      $anno = substr($data, 6, 4);
      $data = $anno . "-" . $mese . "-" . $giorno;
    }
    return $data;
  }
  
  function globo_converti_datetime_in_formato_ita($datetime) {
    return globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($datetime)) . " " . globo_ora_da_mysql_datetime($datetime);
  }

  // riceve in ingresso $data_1 e $data_2 in formato "YYYY-MM-DD"
  // output = 0 in caso di date non valide
  // output = 1 se $data_1 < $data_2
  // output = 2 se $data_1 = $data_2
  // output = 3 se $data_1 > $data_2
  function globo_confronta_date($data_1, $data_2) {
    if (!globo_controlla_data_datetime($data_1) || !globo_controlla_data_datetime($data_2)) return 0;
    else if (strtotime($data_1) < strtotime($data_2)) return 1;
    else if (strtotime($data_1) == strtotime($data_2)) return 2;
    else if (strtotime($data_1) > strtotime($data_2)) return 3;
  }
  
  function globo_controlla_se_data_attuale_o_futura($data) {
    $data_oggi = date("Y-m-d");
    if (strtotime($data) >= strtotime($data_oggi)) return true;
    else return false;
  }
  
  // input = $data in formato "YYYY-MM-DD"
  // output = data + $giorni, in formato "YYYY-MM-DD"
  function globo_calcola_data($data, $giorni) { 
    if (!globo_controlla_data_datetime($data)) return "";
    else return date("Y-m-d", strtotime($data) + ($giorni * 86400) + 3600); // aggiungo 1 ora (3600 sec) per risolvere il problema del cambio ora solare/legale
  }

  // input: $data_1 e $data_2 in formato "YYYY-MM-DD"
  // output: differenza in giorni ($data_1 - $data_2)
  function globo_differenza_date($data_1, $data_2) {
    if (!globo_controlla_data_datetime($data_1) || !globo_controlla_data_datetime($data_2)) return "";
    else return number_format(round((strtotime($data_1) - strtotime($data_2)) / 86400), 0);
  }

  function globo_giorno_in_lettere($giorno) { // riceve in ingresso il giorno della settimana (da 0 a 6) e restituisce il giorno in lettere
    if ($giorno == 0) return "DOMENICA";
    else if ($giorno == 1) return "LUNEDÌ";
    else if ($giorno == 2) return "MARTEDÌ";
    else if ($giorno == 3) return "MERCOLEDÌ";
    else if ($giorno == 4) return "GIOVEDÌ";
    else if ($giorno == 5) return "VENERDÌ";
    else if ($giorno == 6) return "SABATO";
  }
  
  function globo_mese_in_lettere($mese) { // riceve in ingresso il mese (da 1 a 12) e restituisce il mese in lettere
    if ($mese == 1) return "GENNAIO";
    else if ($mese == 2) return "FEBBRAIO";
    else if ($mese == 3) return "MARZO";
    else if ($mese == 4) return "APRILE";
    else if ($mese == 5) return "MAGGIO";
    else if ($mese == 6) return "GIUGNO";
    else if ($mese == 7) return "LUGLIO";
    else if ($mese == 8) return "AGOSTO";
    else if ($mese == 9) return "SETTEMBRE";
    else if ($mese == 10) return "OTTOBRE";
    else if ($mese == 11) return "NOVEMBRE";
    else if ($mese == 12) return "DICEMBRE";
  }
  
  function globo_converti_ora_in_dec($ora) {
    $ore = substr($ora, 0, 2);
    $minuti = substr($ora, 3, 2);
    if ($minuti == "00") return ($ore);
    else return ($ore + 0.5);
  }
  
  function globo_converti_ora_da_dec($ora) {
    $ora_int = (int)$ora;
    $ora_dec = $ora - $ora_int;
    if (strlen($ora_int) == 1) $ora_int = "0" . $ora_int;
    if ($ora_dec > 0) return $ora_int . ":30";
    else return $ora_int . ":00";
  }
  
  function globo_calcola_ora_fine_impegno_dec($ora, $durata) {
    $ora_dec = globo_converti_ora_in_dec($ora);
    $durata_dec = globo_converti_ora_in_dec($durata);
    return ($ora_dec + $durata_dec);
  }

  // CORE: barra di paginazione standard riutilizzabile.
  // $pagina  = pagina corrente, $num_pag = numero totale pagine
  // $action  = url del form (può contenere query string, es. "prog_lista.php?a=1")
  // $param   = nome del parametro POST della pagina (es. "ricerca_pag")
  // Il testo cercato è preservato via sessione, quindi qui basta inviare la pagina.
  function globo_render_paginazione($pagina, $num_pag, $action, $param) {
    $num_pag = (int)$num_pag;
    if ($num_pag <= 1) return;
    $pagina = (int)$pagina;
    if ($pagina < 1) $pagina = 1;
    if ($pagina > $num_pag) $pagina = $num_pag;
    $action = htmlspecialchars($action);
    $param  = htmlspecialchars($param);
    ?>
    <form class="pager" action="<?= $action ?>" method="post">
      <button type="submit" class="icon-btn" name="<?= $param ?>" value="<?= $pagina - 1 ?>"<?= $pagina <= 1 ? ' disabled' : '' ?> title="Pagina precedente">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
      </button>
      <span class="pager__info">Pagina <?= $pagina ?> di <?= $num_pag ?></span>
      <button type="submit" class="icon-btn" name="<?= $param ?>" value="<?= $pagina + 1 ?>"<?= $pagina >= $num_pag ? ' disabled' : '' ?> title="Pagina successiva">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
      </button>
    </form>
    <?php
  }

  /*
   * globo_bool_icon — rende un valore booleano come "spunta verde" (sì) o niente (no).
   * Pattern delle liste: nelle colonne sì/no si mostra l'icona solo sul "sì"; il "no"
   * resta una cella vuota (meno rumore visivo). $title (opzionale) è il tooltip mostrato
   * sul sì (es. elenco servizi, nome agente).
   */
  function globo_bool_icon($val, $title = "") {
    if (!$val) return "";
    $svg = '<svg class="ic-stroke bool-si" aria-label="sì"><use href="#i-check"/></svg>';
    if ($title !== "") {
      return '<span title="' . htmlspecialchars($title, ENT_QUOTES, "UTF-8") . '" style="cursor:help">' . $svg . '</span>';
    }
    return $svg;
  }

  function globo_controlla_se_impegno_inseribile($db, $data, $ora, $durata, $responsabile, $id_impegno_da_escludere) {
    // $id_impegno_da_escludere ? impostato nel caso di aggiornamento dell'impegno
    $ora_inizio_impegno_dec = globo_converti_ora_in_dec($ora);
    $ora_fine_impegno_dec = globo_calcola_ora_fine_impegno_dec($ora, $durata);
    
    if ($responsabile == "tutti") $query = "SELECT * FROM impegno WHERE data = '$data'";
    else $query = "SELECT * FROM impegno WHERE data = '$data' AND (responsabile = '$responsabile' OR responsabile = 'tutti')";
    if ($id_impegno_da_escludere > 0) $query .= " AND id <> $id_impegno_da_escludere";
    $query .= " ORDER BY ora";
    
    $res = $db->query($query)->fetchAll();
    $num = count($res);
  
    foreach ($res as $r) { 
      $ora_tmp = $r["ora"];
      $durata_tmp = $r["durata"];
      
      $ora_inizio_impegno_tmp_dec = globo_converti_ora_in_dec($ora_tmp);
      $ora_fine_impegno_tmp_dec = globo_calcola_ora_fine_impegno_dec($ora_tmp, $durata_tmp);
      
      if (($ora_inizio_impegno_tmp_dec >= $ora_inizio_impegno_dec && $ora_inizio_impegno_tmp_dec < $ora_fine_impegno_dec) ||
        ($ora_fine_impegno_tmp_dec > $ora_inizio_impegno_dec && $ora_fine_impegno_tmp_dec <= $ora_fine_impegno_dec) ||
        ($ora_inizio_impegno_tmp_dec <= $ora_inizio_impegno_dec && $ora_fine_impegno_tmp_dec >= $ora_fine_impegno_dec))
        return false;
    }
    
    return true;
  }

  
// Genara il token per l'accesso tramite cookie restituisce una stringa codificata in sha512 e gli passi la lunghezza 100
  function getToken($length){
     $token = "";
     $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
     $codeAlphabet.= "0123456789-_";
     $max = strlen($codeAlphabet); // edited

     for ($i=0; $i < $length; $i++) {
      $token .= $codeAlphabet[random_int(0, $max-1)];
      $tokenFinal = hash('sha512', $token);
     }

    return $tokenFinal;
  }

 //invia mail all'utente con il token per settare il cookie 
 function sendTokenMail($nomeUtente, $mailUtente, $token) {
   
   $time_UTC = gmdate("Y-m-d H:i:s");
   $myIP = $_SERVER["REMOTE_ADDR"];
   $urlBase = URL_GLOBO;

   // Branding parametrico per-clone: ogni clone definisce queste costanti nel proprio config.
   // Fallback ai valori KosmoSol se non definite, così un clone che le dimentica non manda
   // in fatal questa email di sicurezza (vedi docs/refactoring-notes.md → HARDCODED).
   $brandName  = defined("BRAND_NAME")  ? BRAND_NAME  : "KosmoSol";
   $brandColor = defined("BRAND_COLOR") ? BRAND_COLOR : "#005da3";
   $brandLogo  = $urlBase . "/immagini/" . (defined("EMAIL_LOGO") ? EMAIL_LOGO : "kosmosol_dashwhite.png");

   $bodyMail = "
    <table style='width:100%'>
      <tr>
        <th colspan='2' bgcolor='$brandColor' style='padding: 5px;text-align: left;'>
         <img src='$brandLogo' width='80' alt='$brandName logo'/>
        </th>
      </tr>
      <tr>
        <td style='padding: 5px'>
        <br>Salve $nomeUtente,<br><br>

        Il tuo account è stato appena utilizzato per loggarsi da questo dispositivo:<br>
        Globo ID: <strong>Globo $brandName</strong><br>
        IP: <strong>$myIP</strong><br>
        <strong>$time_UTC UTC</strong><br><br>

        Non abbiamo potuto verificare se il tuo account è stato usato su questo dispositivo precedentemente.<br>
        Per garantire la sicurezza del tuo account è necessario aggiungere questo dispositivo alla lista dei dispositivi riconosciuti prima di poterti loggare nuovamente.<br><br> 

        Segui il link per aggiungere questo dispositivo alla lista dei dispositivi riconosciuti.<br>
        <a href='$urlBase/authorization.php?token=$token'>Aggiungi come dispositivo riconosciuto</a><br><br>

        Se non hai cercato di loggarti al tuo account da questo dispositivo o sei insicuro, ti consigliamo di cambiare la password del tuo account in modo da tenere al sicuro i tuoi dati.
        </td>
      </tr>
    </table>";

  try {
    $mail = globo_crea_phpmailer();
    $mail->addAddress($mailUtente);
    $mail->addBCC(EMAIL_KOSMOSOL);
    $mail->Subject = 'Richiesta riconoscimento browser';
    $mail->Body    = $bodyMail;
    $mail->AltBody = strip_tags($bodyMail);
    $mail->send();
    error_log('Email token inviata con successo');
    return "invio";
  } catch (Exception $e) {
    error_log('Impossibile inviare l\'email con il token. Errore: ' . $e->getMessage());
  }
 }

 function getCode($db) {
     
      $code = "";
      $codeNumber = "";
      $codeAlphabet = "";
      $Alphabet = "";
      $Number = "";
       
      $anno = date("Y");
      $query_code = "SELECT codice FROM codice_cliente where anno = '$anno'";
      $resquery_code = $db->query($query_code)->fetchAll();
      $numquery_code = count($resquery_code);
     
      $codeAlphabet = $resquery_code[0]["codice"];
     
      $Number = "0123456789";
      $maxNumber = strlen($Number); // edited
      
      for ($i=0; $i < 5; $i++) {
       $codeNumber .=  rand(0, $maxNumber-1);
      }
     return $codeAlphabet . $codeNumber;
      
    }

    function tryGenerateHolelCode($db) {

        $query = "SELECT codice_cliente FROM h5s";
        $resquery = $db->query($query)->fetchAll();
        $numquery = count($resquery);

        if($numquery > 0) {
           foreach ($resquery as $r_resquery) {
              $HotelCode[] = $r_resquery["codice_cliente"];
           }
        } else {
         $HotelCode[] = "0";
        }
       
        $checkLoop = 0;
        do {
          //$database = $h5s_database;
          $code = getCode($db);
          //$code = $testCode[$k];
          if (in_array($code, $HotelCode)) {
            //echo "codice già presente<br>";
            //$database = $h5s_database;
            $code = getCode($db);
            $checkLoop++;
            if($checkLoop < 10) $tryAgain = true; else $tryAgain = false;
          } else {
            $tryAgain = false;
            //echo $code;
          }
        } while ($tryAgain);
      
       return $code;

    }

  /* Hook clone (Fase 2): un clone può fornire funzioni proprie via overlay in
     varie/funzioni_custom.php. Nel core il file non esiste, quindi l'include è
     opzionale e non ha effetto. Vedi docs/fase2-core-custom.md §2. */
  if (file_exists(__DIR__ . "/funzioni_custom.php")) {
    require_once(__DIR__ . "/funzioni_custom.php");
  }
  // NB: la favicon NON va emessa qui (funzioni.php è incluso ovunque, prima del
  // <head>, e forzerebbe un'icona globale su tutti i cloni). È parametrizzata
  // in varie/head.php tramite la costante FAVICON. Vedi docs/fase2-core-custom.md.
?>