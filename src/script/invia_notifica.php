<?php
  $testo = $argv[1];
  
  mail("globo@kosmosol.it", $testo, $testo . "\n\n" . date("d/m/Y H:i:s"), "From: globo@kosmosol.it");
?>