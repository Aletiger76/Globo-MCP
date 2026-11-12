<?php
  session_start();

  header("X-Frame-Options: SAMEORIGIN");
  header("X-Content-Type-Options: nosniff");
  header("Referrer-Policy: same-origin");
  header("X-XSS-Protection: 1; mode=block");

  if (!isset($_SESSION["ksglobo_login"]) || (isset($_SESSION["ksglobo_login"]) && $_SESSION["ksglobo_login"] != true)) {
    header("Location: index.php");
    exit();
  }
  
  require("config/config.php");
  require("varie/funzioni.php");
  
  if (isset($IP_OK) && count($IP_OK) > 0) { // globo ? utilizzabile solo da determinati indirizzi ip
    if (!in_array($_SERVER["REMOTE_ADDR"], $IP_OK)) exit();
  }
  
  if (isset($IP_KO) && count($IP_KO) > 0) { // globo ? disabilitato per determinati indirizzi ip
    if (in_array($_SERVER["REMOTE_ADDR"], $IP_KO)) exit();
  }
  
  // PDO 
  try {
    $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO . ";charset=utf8mb4", MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch (PDOException $e) {
    $errorMessage = "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage();
    error_log($errorMessage, 0);
    sendErrorEmail($errorMessage);
	//mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
    header("Location: index.php?e=0");
    exit();
  }
?>