<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

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

  $stmt_1 = $db->prepare("INSERT INTO contatto (azienda, nome, cognome, qualifica, telefono, cellulare, fax, email, sito, indirizzo, cap, citta, provincia, regione, area, nazione, pi, cf, note, nl_flag_confermato, nl_codice_conferma, dt_inserimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', '', ?)");
  $db->beginTransaction();
  $stmt_1->execute([$azienda, $nome, $cognome, $qualifica, $telefono, $cellulare, $fax, $email, $sito, $indirizzo, $cap, $citta, $provincia, $regione, $area, $nazione, $pi, $cf, $note, $dt]);
  $num_1 = $stmt_1->rowCount();
  $id_contatto = $db->lastInsertId();
  $db->commit();

  if ($num_1 == 1) {
    $text = 'Contatto inserito.';
    if (MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER) {
      if (globo_controlla_email($email)) {
        $num_2 = 0;
        $stmt_2 = $db->prepare("INSERT INTO nl_lista_contatto (id_lista, id_contatto, flag_stato, dt_inserimento) VALUES (?, ?, '1', ?)");
        foreach ($id_lista as $il) {
          $db->beginTransaction();
          try { $stmt_2->execute([$il, $id_contatto, $dt]); $num_2 += $stmt_2->rowCount(); } catch (PDOException $e) {}
          $db->commit();
        }
        if ($num_2 > 0) {
          $label = $num_2 === 1 ? '1 lista' : "$num_2 liste";
          $text .= " Associato a $label di distribuzione.";
        }
      }
    }
    $_SESSION['flash'] = ['type' => 'ok', 'text' => $text];
    header("Location: cont_vis.php?id=" . (int)$id_contatto);
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Contatto non inserito.'];
    header("Location: cont_lista.php");
  }
  exit;
