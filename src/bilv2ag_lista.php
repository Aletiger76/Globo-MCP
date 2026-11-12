<?php require("varie/common_1.php"); globo_richiedi_accesso(defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && defined('BILV2_AG') && BILV2_AG && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="css/globo.css" />
</head>
<body>
<table class="tab_est" cellspacing="0" align="center"><tr><td class="tab_est_td" align="center">
<div class="header"><?php require("varie/header.php"); ?></div>
<table class="tab_int" cellspacing="3" width="100%"><tr><td class="tab_int_td_titolo" align="center"><b>AGENTI</b></td></tr></table>
<?php  
  if (isset($_POST["ricerca_pag"])) $ricerca_pag = $_POST["ricerca_pag"]; 
  else if (isset($_SESSION["ksglobo_ricerca_agenti"]["pag"])) $ricerca_pag = $_SESSION["ksglobo_ricerca_agenti"]["pag"];
  else $ricerca_pag = 1;   
  if (isset($_POST["ricerca_testo"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"])); 
    else $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]); 
  }
  else if (isset($_SESSION["ksglobo_ricerca_agenti"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_agenti"]["testo"];
  else $ricerca_testo = ""; 
  if (isset($_POST["ricerca_vis_provvigioni"])) $ricerca_vis_provvigioni = $_POST["ricerca_vis_provvigioni"];  
  else if (isset($_SESSION["ksglobo_ricerca_agenti"]["vis_provvigioni"])) $ricerca_vis_provvigioni = $_SESSION["ksglobo_ricerca_agenti"]["vis_provvigioni"];
  else $ricerca_vis_provvigioni = 0;

  unset($_SESSION["ksglobo_ricerca_agenti"]);
  $_SESSION["ksglobo_ricerca_agenti"]["pag"] = $ricerca_pag;
  $_SESSION["ksglobo_ricerca_agenti"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_agenti"]["vis_provvigioni"] = $ricerca_vis_provvigioni;

  $query = "SELECT * FROM bilanciov2_agente";
  $query .= " WHERE nome LIKE '%$ricerca_testo%'";
  $query .= " ORDER BY TRIM(nome)";
  $res = $db->query($query)->fetchAll();
  $num_tot = count($res);

  $num_pag = ceil($num_tot / 50);
  if ($ricerca_pag > $num_pag) $ricerca_pag = 1;
  
  $limit_1 = ($ricerca_pag - 1) * 50;
  $limit_2 = 50;
  
  $query .= " LIMIT $limit_1, $limit_2";
  $res = $db->query($query)->fetchAll();
  $num = count($res);
  
  if ($num > 0 || trim($ricerca_testo) != "") {
    echo "<form action='bilv2ag_lista.php' method='post' class='margin_bottom_bottoni'>";
    echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
    echo "<tr><td class='tab_int_td_1' colspan='2' align='center'><b>RICERCA</b></td></tr>"; 
    
    if ($num_pag > 0) {
      echo "<tr>";
      echo "<td class='tab_int_td_2' width='50%' align='left'><b>pagina</b></td>";
      echo "<td class='tab_int_td_3' width='50%' align='left'>";
      echo "<select name='ricerca_pag' onchange='this.form.submit();'>";
      for ($i = 1; $i <= $num_pag; $i++) {
        if ($i == $ricerca_pag) echo "<option value='$i' selected='selected'>$i</option>";
        else echo "<option value='$i'>$i</option>";
      }
      
      echo "</select>";
      echo " di $num_pag</td>";
      echo "</tr>";
    }
    
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='50%' align='left'><b>testo</b></td>";
    echo "<td class='tab_int_td_3' width='50%' align='left'>";
    echo "<input type='text' class='input_text standard' maxlength='100' name='ricerca_testo' value='$ricerca_testo' />";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' align='left'><b>visualizza provvigioni</b></td>";
    echo "<td class='tab_int_td_3' align='left'>";
    echo "<select name='ricerca_vis_provvigioni' onchange='this.form.submit();'>";
  
    if ($ricerca_vis_provvigioni == 0) echo "<option value='0' selected='selected'>no</option>";
    else echo "<option value='0'>no</option>";
    if ($ricerca_vis_provvigioni == 1) echo "<option value='1' selected='selected'>sì - ordina per nome/azienda</option>";
    else echo "<option value='1'>sì - ordina per nome/azienda</option>";
    if ($ricerca_vis_provvigioni == 2) echo "<option value='2' selected='selected'>sì - ordina per totale provvigioni</option>";
    else echo "<option value='2'>sì - ordina per totale provvigioni</option>";
    if ($ricerca_vis_provvigioni == 3) echo "<option value='3' selected='selected'>sì - ordina per totale provvigioni pagate</option>";
    else echo "<option value='3'>sì - ordina per totale provvigioni pagate</option>";
    if ($ricerca_vis_provvigioni == 4) echo "<option value='4' selected='selected'>sì - ordina per totale provvigioni attualmente pagabili</option>";
    else echo "<option value='4'>sì - ordina per totale provvigioni attualmente pagabili</option>";
    
    echo "</select>";
    echo "</table>";
    echo "<input type='submit' value='ricerca' class='input_bottone' />";
    echo "</form>";
    
    echo "<form action='bilv2ag_ins_1.php' method='post' class='margin_bottom_blocchi'>";   
    echo "<input type='submit' value='nuovo agente' class='input_bottone' />";
    echo "</form>";
  }
  
  if ($num <= 0) {
    echo "<table class='tab_int' cellspacing='3' width='100%'>";   
    echo "<tr><td class='tab_int_td_1' align='center'><b>AGENTI</b></td></tr>";
    echo "<tr><td class='tab_int_td_3' align='center'><b>nessun agente</b></td></tr>";
    echo "</table>";
  }
  else {
    echo "<table class='tab_int' cellspacing='3' width='100%'>"; 
    
    if ($ricerca_vis_provvigioni != 0) $colspan = 6;
    else $colspan = 3;
    
    echo "<tr><td colspan='$colspan' class='tab_int_td_1' align='center'><b>AGENTI ($num_tot)</b></td></tr>";
    echo "<tr>";
    echo "<td class='tab_int_td_2' width='100%' align='left'><b>nome/azienda</b></td>";
    echo "<td class='tab_int_td_2 nobr' align='center'><b>em soll pag</b></td>";
    
    if ($ricerca_vis_provvigioni != 0) {
      echo "<td class='tab_int_td_2 nobr' align='right'><b>tot prov</b></td>";
      echo "<td class='tab_int_td_2 nobr' align='right'><b>tot prov<br />pagate</b></td>";
      echo "<td class='tab_int_td_2 nobr' align='right'><b>tot prov<br />attualmente<br />pagabili</b></td>";
    }
    
    echo "<td class='tab_int_td_2' align='center'><b>dett</b></td>";
    echo "</tr>";
    
    $bilanciov2_agenti = array();
    $i = 0;
    
    foreach ($res as $r) {
      $id_bilanciov2_agente = $r["id"]; 
      $nome = $r["nome"];
      $email = $r["email"];
      $fatture_provvigioni_pagate = $r["fatture_provvigioni_pagate"];
      $fatture_provvigioni_pagate_arr = explode(";", $fatture_provvigioni_pagate);
      $totale_provvigioni_pagate = $r["totale_provvigioni_pagate"];

      $bilanciov2_agente = array();
      $bilanciov2_agente["id"] = $id_bilanciov2_agente;
      $bilanciov2_agente["nome"] = $nome;
      $bilanciov2_agente["email"] = $email;
      $bilanciov2_agente["totale_provvigioni_pagate"] = $totale_provvigioni_pagate;
      
      if ($ricerca_vis_provvigioni != 0) {
        $query_1 = "SELECT * FROM bilanciov2 WHERE tipo = 'entrata' AND flag_esigibilita = '1' AND id_bilanciov2_agente = '$id_bilanciov2_agente' ORDER BY data ASC";
        $res_1 = $db->query($query_1)->fetchAll();
        $num_1 = count($res_1);
        
        $totale_provvigioni = 0;
        $totale_provvigioni_pagabili = 0;
  
        foreach ($res_1 as $r_1) { 
          $id_bilanciov2 = $r_1["id"];
          $data = globo_converti_data_in_formato_ita($r_1["data"]);
          $anno = date("Y", strtotime($r_1["data"]));
          $fattura = $r_1["fattura"];
          $flag_esigibilita = $r_1["flag_esigibilita"];
          $flag_pagamento = $r_1["flag_pagamento"];
          $perc_provvigione = $r_1["perc_provvigione"];
          $imponibile = 0;
          
          $query_2 = "SELECT * FROM bilanciov2_dettaglio WHERE id_bilanciov2 = '$id_bilanciov2'";
          $res_2 = $db->query($query_2)->fetchAll();
          $num_2 = count($res_2);
          
          foreach ($res_2 as $r_2) {
            $imponibile += $r_2["imponibile"];
          }
          
          $provvigione = $imponibile * $perc_provvigione / 100;
          
          $imponibile = number_format($imponibile, 2, '.', '');
          $provvigione = number_format($provvigione, 2, '.', '');
          
          $totale_provvigioni += $provvigione;
          if ($flag_pagamento == 1 && !in_array($fattura . "_" . $anno, $fatture_provvigioni_pagate_arr)) $totale_provvigioni_pagabili += $provvigione;
        }
  
        $bilanciov2_agente["totale_provvigioni"] = number_format($totale_provvigioni, 2, '.', '');
        $bilanciov2_agente["totale_provvigioni_pagabili"] = number_format($totale_provvigioni_pagabili, 2, '.', '');
      }
      else {
        $bilanciov2_agente["totale_provvigioni"] = 0;
        $bilanciov2_agente["totale_provvigioni_pagabili"] = 0;
      }
      
      $bilanciov2_agenti[$i] = $bilanciov2_agente;
      $i++;
    }
    
    /*********** funzioni di ordinamento dell'array ***********/
    function cmp_1($bilanciov2_agente_1, $bilanciov2_agente_2) {
      if (strtolower($bilanciov2_agente_1["nome"]) == strtolower($bilanciov2_agente_2["nome"])) return 0;
      return (strtolower($bilanciov2_agente_1["nome"]) < strtolower($bilanciov2_agente_2["nome"])) ? -1 : 1;
    } 
    
    function cmp_2($bilanciov2_agente_1, $bilanciov2_agente_2) {
      if (strtolower($bilanciov2_agente_1["totale_provvigioni"]) == strtolower($bilanciov2_agente_2["totale_provvigioni"])) return 0;
      return (strtolower($bilanciov2_agente_1["totale_provvigioni"]) < strtolower($bilanciov2_agente_2["totale_provvigioni"])) ? 1 : -1;
    } 
    
    function cmp_3($bilanciov2_agente_1, $bilanciov2_agente_2) {
      if (strtolower($bilanciov2_agente_1["totale_provvigioni_pagate"]) == strtolower($bilanciov2_agente_2["totale_provvigioni_pagate"])) return 0;
      return (strtolower($bilanciov2_agente_1["totale_provvigioni_pagate"]) < strtolower($bilanciov2_agente_2["totale_provvigioni_pagate"])) ? 1 : -1;
    }
    
    function cmp_4($bilanciov2_agente_1, $bilanciov2_agente_2) {
      if (strtolower($bilanciov2_agente_1["totale_provvigioni_pagabili"]) == strtolower($bilanciov2_agente_2["totale_provvigioni_pagabili"])) return 0;
      return (strtolower($bilanciov2_agente_1["totale_provvigioni_pagabili"]) < strtolower($bilanciov2_agente_2["totale_provvigioni_pagabili"])) ? 1 : -1;
    } 
    /**********************************************************/
    
    if ($ricerca_vis_provvigioni == 0 || $ricerca_vis_provvigioni == 1) uasort($bilanciov2_agenti, "cmp_1");
    else if ($ricerca_vis_provvigioni == 2) uasort($bilanciov2_agenti, "cmp_2");
    else if ($ricerca_vis_provvigioni == 3) uasort($bilanciov2_agenti, "cmp_3");
    else if ($ricerca_vis_provvigioni == 4) uasort($bilanciov2_agenti, "cmp_4");
    
    foreach ($bilanciov2_agenti as $bilanciov2_agente) {
      echo "<tr>";
      echo "<td class='tab_int_td_3' align='left'>" . $bilanciov2_agente["nome"] . "</td>";
      echo "<td class='tab_int_td_3' align='center'>";
      if (trim($bilanciov2_agente["email"]) != "") echo "<a href='mailto:" . $bilanciov2_agente["email"] . "' title='" . $bilanciov2_agente["email"] . "'>sì</a>";
      else echo "&nbsp;";
      echo "</td>";
      
      if ($ricerca_vis_provvigioni != 0) {
        echo "<td class='tab_int_td_3' align='right'>" . $bilanciov2_agente["totale_provvigioni"] . "</td>";
        echo "<td class='tab_int_td_3' align='right'>" . $bilanciov2_agente["totale_provvigioni_pagate"] . "</td>";
        echo "<td class='tab_int_td_3' align='right'>" . $bilanciov2_agente["totale_provvigioni_pagabili"] . "</td>";
      }
      
      echo "<td class='tab_int_td_3' align='center'>";
      echo "<form action='bilv2ag_vis.php' method='post'>"; 
      echo "<input type='hidden' name='id_bilanciov2_agente' value='" . $bilanciov2_agente["id"] . "' />";
      echo "<input type='image' src='immagini/ico_lente.gif' width='16' height='16' border='0' alt='visualizza dettagli' title='visualizza dettagli' />";
      echo "</form>";
      echo "</td>";
      echo "</tr>";
    }
    
    echo "</table>";
  }
  
  if (isset($_GET["p"]) && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI) {
    echo "<form action='prog_vis.php' method='post'>";
    echo "<input type='hidden' name='id_progetto' value='" . $_GET["p"] . "' />";
    echo "<input type='submit' value='ritorna al progetto' class='input_bottone' />";
    echo "</form>";
  }
  
  echo "<form action='bilv2ag_ins_1.php' method='post'>";
  echo "<input type='submit' value='nuovo agente' class='input_bottone' />";
  echo "</form>";
    
  echo "<form action='home.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='home' class='input_bottone' />";
  echo "</form>";
  
  echo "<form action='index.php' method='post' class='margin_top_bottoni'>";
  echo "<input type='submit' value='logout' class='input_bottone' />";
  echo "</form>";
?>
<div class="footer"><?php require("varie/footer.php"); ?></div>
</td></tr></table>
</body>
</html>
<?php require("varie/common_2.php"); ?>
