<?php
  require("config/config.php");

  header("X-Frame-Options: SAMEORIGIN");
  header("X-Content-Type-Options: nosniff");
  header("Referrer-Policy: same-origin");
  header("X-XSS-Protection: 1; mode=block");

  $time_UTC = gmdate("Y-m-d H:i:s");
  $token_ok = isset($_GET["token"]) && $_GET["token"] != "";
  if ($token_ok) {
    $tokenCookie = $_GET["token"];
    // DEV: domain="" e secure=0 per localhost. Produzione: domain="globo-ks.kosmosol.it", secure=1 — vedi docs/refactoring-notes.md
    setcookie("_authLogGloboKs", $tokenCookie, time() + (DATE_EXPIRE_TOKEN * 86400), "/", "", 0, 0);
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Riconoscimento dispositivo</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
  <style>
    body { font-family: sans-serif; background: #f5f5f5; }
    .container { max-width: 960px; margin: 3rem auto; padding: 0 15px; }
    .alert { padding: 1.25rem; border-radius: .25rem; box-shadow: 0 2px 5px rgba(0,0,0,.16); }
    .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .alert-danger  { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .alert h4 { margin-top: 0; }
    hr { border-color: rgba(0,0,0,.1); }
  </style>
</head>
<body>
<div class="container">
  <?php if ($token_ok) { ?>
    <div class="alert alert-success" role="alert">
      <h4><i class="fas fa-check-circle"></i> Accesso Autorizzato</h4>
      <p>Il tuo browser è stato autorizzato ad accedere a Globo KosmoSol Web.</p>
      <hr>
      <p style="margin-bottom:0; font-weight:bold;">
        <?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?><br>
        <?php echo $time_UTC; ?> UTC
      </p>
    </div>
  <?php } else { ?>
    <div class="alert alert-danger" role="alert">
      <h4><i class="fas fa-exclamation-circle"></i> Accesso Non Autorizzato</h4>
      <p>Il tuo browser non è stato autorizzato ad accedere a Globo KosmoSol Web.</p>
      <hr>
      <p style="margin-bottom:0; font-weight:bold;">
        <?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?><br>
        <?php echo $time_UTC; ?> UTC
      </p>
    </div>
  <?php } ?>
</div>
</body>
</html>
