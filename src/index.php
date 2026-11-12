<?php
  session_start();

  header("X-Frame-Options: SAMEORIGIN");
  header("X-Content-Type-Options: nosniff");
  header("Referrer-Policy: same-origin");
  header("X-XSS-Protection: 1; mode=block");

  unset($_SESSION["ksglobo_login"]);
  unset($_SESSION["ksglobo_nome_utente"]);
  unset($_SESSION["ksglobo_livello_utente"]);
  
  require("config/config.php");
  require("varie/funzioni.php"); 
  
  if (isset($IP_OK) && count($IP_OK) > 0) { // globo è utilizzabile solo da determinati indirizzi ip
    if (!in_array($_SERVER["REMOTE_ADDR"], $IP_OK)) exit();
  }
  
  if (isset($IP_KO) && count($IP_KO) > 0) { // globo è disabilitato per determinati indirizzi ip
    if (in_array($_SERVER["REMOTE_ADDR"], $IP_KO)) exit();
  }
  
  if (isset($_POST["nome_utente"]) && isset($_POST["password"])) {
    
    try {
      $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO, MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
      globo_invia_email(EMAIL_KOSMOSOL, "Errore MySQL - Globo", "request uri: " . htmlspecialchars($_SERVER["REQUEST_URI"]) . "<br><br>" . htmlspecialchars($e->getMessage()));
      header("Location: index.php?e=0");
      exit();
    }
    
    $ip = $_SERVER["REMOTE_ADDR"]; 
    
    // se ci sono stati 5 o più errori di accesso da quell'indirizzo ip negli ultimi 10 minuti, l'accesso da quell'indirizzo ip viene inibito.
    // NB: la finestra temporale va calcolata da MySQL stesso (NOW() - INTERVAL),
    // NON con un orario calcolato in PHP: il campo login.dt è scritto da MySQL
    // (CURRENT_TIMESTAMP) e, se PHP e MySQL hanno timezone diversi, il confronto
    // con un $dt_lim lato PHP sballa e il blocco non scatta mai.
    $num = 0;

    try {
      $stmt = $db->prepare("SELECT * FROM login WHERE ip = ? AND accesso = '0' AND dt > (NOW() - INTERVAL 10 MINUTE)");
      $stmt->execute([$ip]);
      $res = $stmt->fetchAll();
    }
    catch (PDOException $e) {
      $num = -1;
    }
    
    if ($num == 0) { $num = count($res); }

    if($ip != "217.221.186.162" && $ip != "87.27.156.207") { // 87.27.156.207
      if ($num >= 5) {
      header("Location: index.php?e=2");
      exit();
      }   
    }
    //pulisco il nome utente da caratteri non validi
    $nome_utente_login = $_POST["nome_utente"];
    $nome_utente_clear = str_replace(array('!','#','$','%','&','\'','*','+',',','=','?','^','_','`','{','|','}','~','@','[',']','<','>','/','(',')'), '', $nome_utente_login);
    $nome_utente = strtolower($nome_utente_clear);
    $password = $_POST["password"];
    
    if ($nome_utente == "ks-globo" && md5($password) == "__PROD_KS_ADMIN_MD5__") { // accesso da super amministratore
      $num = 1;
    }
    else {
      // check se auth con token è abilitata
      if(AUTH_TOKEN) {
          //check se utente esiste
          $stmtCheckUtente = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
          $stmtCheckUtente->execute([$nome_utente]);
          $resCheckUtente = $stmtCheckUtente->fetchAll();
          $numCheckUtente = 0;
          if (count($resCheckUtente) == 1) {
            $stored_hash_ks = $resCheckUtente[0]['password'];
            if (str_starts_with($stored_hash_ks, '$2y$')) {
              $numCheckUtente = password_verify($password, $stored_hash_ks) ? 1 : 0;
            } else {
              $numCheckUtente = (md5($password) === $stored_hash_ks) ? 1 : 0;
              if ($numCheckUtente == 1) {
                $new_hash_ks = password_hash($password, PASSWORD_BCRYPT);
                $stmtMigrateKs = $db->prepare("UPDATE utente SET password = ? WHERE nome_utente = ?");
                $stmtMigrateKs->execute([$new_hash_ks, $nome_utente]);
              }
            }
          }
          //echo $numCheckUtente;
          $UserInfoEmail = $resCheckUtente[0]["email"];
          //echo $UserInfoEmail;
          //exit();
          if($numCheckUtente == 1) {
            //esite utente
            //get data di oggi
            $dtToday = date("Y-m-d");
            //guado se ha il token
            $stmtCheckToken = $db->prepare("SELECT auth_token_login FROM utente WHERE nome_utente = ? AND auth_token_expire > ?");
            $stmtCheckToken->execute([$nome_utente, $dtToday]);
            $resCheckTokenUtente = $stmtCheckToken->fetchAll();
            $numCheckTokenUtente = count($resCheckTokenUtente);
            //$UserInfoEmail = $resCheckTokenUtente[0]["email"];
            // se = "" non ha il token | se == 0 può avere il token ma è scaduto
            if($numCheckTokenUtente == 0 || $resCheckTokenUtente[0]["auth_token_login"] == NULL) {
              //genero il token
              $setToken = getToken(100);
              //set time creazione token - set time expire token con DATE_EXPIRE_TOKEN
              $timestamp = time() + (DATE_EXPIRE_TOKEN * 86400);
              $dtTokenCreate = date("Y-m-d");
              $dtTokenExpire = gmdate("Y-m-d", $timestamp);
              //inserisco il cookie del db legato all'utente
              $stmtSetToken = $db->prepare("UPDATE utente SET auth_token_login = ?, auth_token_create = ?, auth_token_expire = ? WHERE nome_utente = ?");
              $stmtSetToken->execute([$setToken, $dtTokenCreate, $dtTokenExpire, $nome_utente]);
              // invio mail per settare il cookie
              $sendToken = sendTokenMail($nome_utente, $UserInfoEmail, $setToken);
              globo_invia_email(EMAIL_KOSMOSOL, "Generato nuovo token di accesso", "utente: $nome_utente<br>ip: $ip");
              //redirect error, non hai il token
              header("Location: index.php?e=3");
              exit();
            } else {
               // l'user loggato ha il token
               // leggo il token da db
               $tokenCookieSaveDB = $resCheckTokenUtente[0]["auth_token_login"];
               //controllo che il token nel cookie ci sia
               if (isset($_POST["tokenCo"]) && $_POST["tokenCo"] != "" || (isset($_COOKIE["_authLogGloboH5s"]) && $_COOKIE["_authLogGloboH5s"] != "")) {
                  // leggo cookie save browser: preferisco il campo POST (popolato via JS
                  // dal cookie "_authLogGloboKs", nome usato da KosmoSol) ma se e' vuoto
                  // ricado sul cookie diretto "_authLogGloboH5s" - il JS su questa pagina
                  // cerca il nome sbagliato per H5S (bug trovato il 2026-07-07, mai preso
                  // perche' AUTH_TOKEN e' disattivato in dev), quindi per H5S tokenCo e'
                  // sempre vuoto anche col cookie giusto presente nel browser.
                  $tokenCookieSaveBrowser = ($_POST["tokenCo"] ?? "") !== "" ? $_POST["tokenCo"] : ($_COOKIE["_authLogGloboH5s"] ?? "");
                  //echo "tokenCookie browser ok " . $_POST["tokenCo"] . "<br>";
                  //controllo che il cookie save browser dia lo stesso del db
                  if($tokenCookieSaveBrowser == $tokenCookieSaveDB) {
                      //echo "tokenCookie Save match $tokenCookieSaveDB" . "<br>";
                      //entro in globo perchè num = 1
                      $num = 1;
                    } else {
                      //redirect error, token cookie e db sono diversi
                      $sendToken = sendTokenMail($nome_utente, $UserInfoEmail, $tokenCookieSaveDB);
                      globo_invia_email(EMAIL_KOSMOSOL, "Token cookie e token db differenti", "utente: $nome_utente<br>ip: $ip");
                      header("Location: index.php?e=3");
                      exit();
                    }
                  } else {
                    //redirect error, non hai più il token
                    $sendToken = sendTokenMail($nome_utente, $UserInfoEmail, $tokenCookieSaveDB);
                    globo_invia_email(EMAIL_KOSMOSOL, "Token non presente nel browser", "utente: $nome_utente<br>ip: $ip");
                    header("Location: index.php?e=3");
                    exit();
                  }
           }
          } else {
            //non esite utente
            $num = 0;
          }  
        
      } else {
        // else per auth token non attivo
        try {
            $stmtLogin = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
            $stmtLogin->execute([$nome_utente]);
            $res = $stmtLogin->fetchAll();
            if (count($res) == 1) {
              $stored_hash = $res[0]['password'];
              if (str_starts_with($stored_hash, '$2y$')) {
                $num = password_verify($password, $stored_hash) ? 1 : 0;
              } else {
                $num = (md5($password) === $stored_hash) ? 1 : 0;
                if ($num == 1) {
                  $new_hash = password_hash($password, PASSWORD_BCRYPT);
                  $stmtMigrate = $db->prepare("UPDATE utente SET password = ? WHERE nome_utente = ?");
                  $stmtMigrate->execute([$new_hash, $nome_utente]);
                }
              }
            } else {
              $num = 0;
            }
          } catch (PDOException $e) {
            $num = 0;
          }
      }
      
    }
    
    if ($num != 1) {
      try { // retro compatibilità
        $stmtBc = $db->prepare("SELECT * FROM utente WHERE nome_utente = ? AND password = PASSWORD(?)");
        $stmtBc->execute([$nome_utente, $password]);
        $res = $stmtBc->fetchAll();
        $num = count($res);
      } catch (PDOException $e) {
        $num = 0;
      }
    }

    if ($num != 1) {
      globo_invia_email(EMAIL_KOSMOSOL, "errore di accesso", "utente: $nome_utente<br>ip: $ip");
      
      $query = "CREATE TABLE IF NOT EXISTS `login` (";
      $query .= " `nome_utente` varchar(50) NOT NULL,";
      $query .= " `ip` varchar(50) NOT NULL,";
      $query .= " `accesso` tinyint(1) NOT NULL,";
      $query .= " `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
      $query .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
      $db->exec($query);
      
      $stmtLoginFail = $db->prepare("INSERT INTO login (nome_utente, ip, accesso) VALUES (?, ?, '0')");
      $db->beginTransaction();
      $stmtLoginFail->execute([$nome_utente, $ip]);
      $db->commit();
      
      $db = NULL;
      
      header("Location: index.php?e=1");
      exit();
    }
    else {
      if ($nome_utente == "ks-globo") { 
        $_SESSION["ksglobo_login"] = true;
        $_SESSION["ksglobo_nome_utente"] = $nome_utente;
        $_SESSION["ksglobo_livello_utente"] = 0;
      }
      else {
        $stmtUser = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
        $stmtUser->execute([$nome_utente]);
        $r = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $nome_utente = $r["nome_utente"];
        $livello = $r["livello"];
            
        $_SESSION["ksglobo_login"] = true;
        $_SESSION["ksglobo_nome_utente"] = $nome_utente;
        $_SESSION["ksglobo_livello_utente"] = $livello;
      }
      
      if (date("G") < 8 || date("G") > 19 || date("w") == 0) globo_invia_email(EMAIL_KOSMOSOL, "accesso effettuato", "utente: $nome_utente<br>ip: $ip");

      $query = "CREATE TABLE IF NOT EXISTS `login` (";
      $query .= " `nome_utente` varchar(50) NOT NULL,";
      $query .= " `ip` varchar(50) NOT NULL,";
      $query .= " `accesso` tinyint(1) NOT NULL,";
      $query .= " `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
      $query .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
      $db->exec($query);
      
      $stmtLoginOk = $db->prepare("INSERT INTO login (nome_utente, ip, accesso) VALUES (?, ?, '1')");
      $db->beginTransaction();
      $stmtLoginOk->execute([$nome_utente, $ip]);
      $db->commit();
      globo_log_attivita($db, 'login', 'utente', null, '');

      $db = NULL;
      
      header("Location: home.php");
      exit();
    }
  }

  // ── Messaggio di errore da query string (dopo redirect) ───────────────────
  $_globo_login_err = '';
  if (isset($_GET['e'])) {
    switch ((int)$_GET['e']) {
      case 0: $_globo_login_err = 'Errore di connessione al database.'; break;
      case 1: $_globo_login_err = 'Nome utente o password non corretti.'; break;
      case 2: $_globo_login_err = 'Accesso momentaneamente disabilitato. Riprovare più tardi.'; break;
      case 3: $_globo_login_err = 'Per garantire la sicurezza del tuo account devi confermare questo browser come dispositivo affidabile. Controlla la tua e-mail per il link di autorizzazione.'; break;
    }
  }

  // ── Data per la card login ─────────────────────────────────────────────────
  $_globo_login_date = ucfirst(strtolower(globo_giorno_in_lettere(date('w'))))
    . ' ' . date('d')
    . ' ' . strtolower(globo_mese_in_lettere(date('m')))
    . ' ' . date('Y');

  require('varie/head.php');
?>
<div class="login-page">
  <div class="login-wrap">
    <div class="login-card">

      <img class="login-logo"
           src="immagini/<?php echo htmlspecialchars(IMG_LOGO); ?>"
           alt="<?php echo htmlspecialchars(TITLE); ?>" />

      <p class="login-date"><?php echo htmlspecialchars($_globo_login_date); ?></p>

      <h1 class="login-title">Bentornato</h1>
      <p class="login-sub">Accedi al tuo spazio di lavoro</p>

      <?php if ($_globo_login_err): ?>
      <p class="login-err"><?php echo htmlspecialchars($_globo_login_err); ?></p>
      <?php endif; ?>

      <form name="globo" action="index.php" method="post">
        <div class="login-fields">
          <div class="login-field">
            <span class="eyebrow">Nome utente</span>
            <input class="input" type="text" name="nome_utente" maxlength="30"
                   autocomplete="username" autofocus required />
          </div>
          <div class="login-field">
            <span class="eyebrow">Password</span>
            <input class="input" type="password" name="password" maxlength="30"
                   autocomplete="current-password" required />
          </div>
        </div>
        <input type="hidden" id="tokenCo" name="tokenCo" value="">
        <button type="submit" class="btn btn--primary login-cta" onclick="GetCookie()">
          Accedi
        </button>
      </form>

      <p class="login-foot"><b><?php echo htmlspecialchars(TITLE); ?></b> - v.<?php echo htmlspecialchars(GLOBO_VERSION); ?></p>

    </div>
  </div>
</div>

<script>
// Campi vuoti del login: gestiti dalla validazione nativa del browser (required
// sugli input → tooltip standard). Nessun alert JS qui.
function GetCookie() {
  var v = document.cookie.replace(/(?:(?:^|.*;\s*)_authLogGloboKs\s*\=\s*([^;]*).*$)|^.*$/, "$1");
  document.getElementById('tokenCo').value = v;
}
</script>
</body>
</html>