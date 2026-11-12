<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

  $id_contatto = $_POST["id_contatto"] ?? '';
  $azienda   = globo_converti_in_html($_POST["azienda"]   ?? '');
  $nome      = globo_converti_in_html($_POST["nome"]      ?? '');
  $cognome   = globo_converti_in_html($_POST["cognome"]   ?? '');
  $qualifica = globo_converti_in_html($_POST["qualifica"] ?? '');
  $telefono  = globo_converti_in_html($_POST["telefono"]  ?? '');
  $cellulare = globo_converti_in_html($_POST["cellulare"] ?? '');
  $fax       = globo_converti_in_html($_POST["fax"]       ?? '');
  $email     = trim($_POST["email"] ?? '');
  $sito      = trim($_POST["sito"]  ?? '');
  $sito      = str_replace("http://", "", $sito);
  $indirizzo = globo_converti_in_html($_POST["indirizzo"] ?? '');
  $cap       = globo_converti_in_html($_POST["cap"]       ?? '');
  $citta     = globo_converti_in_html($_POST["citta"]     ?? '');
  $provincia = globo_converti_in_html($_POST["provincia"] ?? '');
  $regione   = globo_converti_in_html($_POST["regione"]   ?? '');
  $area      = globo_converti_in_html($_POST["area"]      ?? '');
  $nazione   = globo_converti_in_html($_POST["nazione"]   ?? '');
  $pi        = globo_converti_in_html($_POST["pi"]        ?? '');
  $cf        = globo_converti_in_html($_POST["cf"]        ?? '');
  $note      = globo_converti_in_html($_POST["note"]      ?? '');

  if (TUTTO_MINUSCOLO) {
    $azienda = strtolower($azienda); $nome = strtolower($nome); $cognome = strtolower($cognome);
    $qualifica = strtolower($qualifica); $telefono = strtolower($telefono); $cellulare = strtolower($cellulare);
    $fax = strtolower($fax); $email = strtolower($email); $sito = strtolower($sito);
    $indirizzo = strtolower($indirizzo); $cap = strtolower($cap); $citta = strtolower($citta);
    $provincia = strtolower($provincia); $regione = strtolower($regione); $area = strtolower($area);
    $nazione = strtolower($nazione); $pi = strtolower($pi); $cf = strtolower($cf); $note = strtolower($note);
  }

  $id_lista = $_POST["id_lista"] ?? [];
  $dt = date("Y-m-d H:i:s");

  $stmt_1 = $db->prepare("UPDATE contatto SET azienda = ?, nome = ?, cognome = ?, qualifica = ?, telefono = ?, cellulare = ?, fax = ?, email = ?, sito = ?, indirizzo = ?, cap = ?, citta = ?, provincia = ?, regione = ?, area = ?, nazione = ?, pi = ?, cf = ?, note = ? WHERE id = ?");
  $db->beginTransaction();
  $stmt_1->execute([$azienda, $nome, $cognome, $qualifica, $telefono, $cellulare, $fax, $email, $sito, $indirizzo, $cap, $citta, $provincia, $regione, $area, $nazione, $pi, $cf, $note, $id_contatto]);
  $num_1 = $stmt_1->rowCount();
  $db->commit();

  $num_3 = 0;
  if (MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER) {
    if (globo_controlla_email($email)) {
      $res_2 = $db->query("SELECT id FROM nl_lista")->fetchAll();
      $stmtIns = $db->prepare("INSERT INTO nl_lista_contatto (id_lista, id_contatto, flag_stato, dt_inserimento) VALUES (?, ?, '1', ?)");
      $stmtDel = $db->prepare("DELETE FROM nl_lista_contatto WHERE id_lista = ? AND id_contatto = ?");
      foreach ($res_2 as $r2) {
        $id_lista_tmp = $r2["id"];
        $db->beginTransaction();
        try {
          if (in_array($id_lista_tmp, $id_lista)) { $stmtIns->execute([$id_lista_tmp, $id_contatto, $dt]); $num_3 += $stmtIns->rowCount(); }
          else { $stmtDel->execute([$id_lista_tmp, $id_contatto]); $num_3 += $stmtDel->rowCount(); }
        } catch (PDOException $e) {}
        $db->commit();
      }
    } else {
      $stmtDelAll = $db->prepare("DELETE FROM nl_lista_contatto WHERE id_contatto = ?");
      $db->beginTransaction();
      $stmtDelAll->execute([$id_contatto]);
      $num_3 += $stmtDelAll->rowCount();
      $db->commit();
    }
  }

  $text = ($num_1 > 0 || $num_3 > 0) ? 'Contatto aggiornato.' : 'Contatto non aggiornato.';
  $type = ($num_1 > 0 || $num_3 > 0) ? 'ok' : 'err';
  if ($num_3 > 0) {
    if (globo_controlla_email($email)) $text .= ' Liste di distribuzione aggiornate.';
    else $text .= ' Rimosso da tutte le liste di distribuzione.';
  }
  $_SESSION['flash'] = ['type' => $type, 'text' => $text];

  $has_p  = isset($_GET["p"])  && MOD_PROGETTI  && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI;
  $has_ld = isset($_GET["ld"]) && MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER;

  if ($has_p)       $url_vis = 'cont_vis.php?p=' . (int)$_GET['p'] . '&id=' . (int)$id_contatto;
  elseif ($has_ld)  $url_vis = 'cont_vis.php?ld=' . (int)$_GET['ld'] . '&id=' . (int)$id_contatto;
  else              $url_vis = 'cont_vis.php?id=' . (int)$id_contatto;

  header("Location: $url_vis");
  exit;
