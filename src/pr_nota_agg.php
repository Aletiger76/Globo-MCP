<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto            = $_POST["id_progetto"]            ?? '';
  $id_nota                = $_POST["id_nota"]                ?? '';
  $tipo                   = $_POST["tipo"]                   ?? '';
  $visibilita             = $_POST["visibilita"]             ?? '';
  $data_creazione         = $_POST["data_creazione"]         ?? '';
  $descrizione_nota       = globo_converti_in_html($_POST["descrizione_nota"] ?? '');
  $descrizione_nota_old   = globo_converti_in_html($_POST["descrizione_nota"] ?? '');
  $data_scadenza          = globo_converti_data_in_formato_mysql($_POST["data_scadenza"] ?? '');
  $data_scadenza_old      = $_POST["data_scadenza_old"]      ?? '';
  $aggiorna_scadenze_step = $_POST["aggiorna_scadenze_step"] ?? '';
  $responsabile_scadenza  = $_POST["responsabile_scadenza"]  ?? '';
  $responsabile_scadenza_old = $_POST["responsabile_scadenza_old"] ?? '';
  $utente_creazione       = $_POST["utente_creazione"]       ?? '';
  $utente_ultima_modifica = $_POST["utente_ultima_modifica"] ?? '';

  if (TUTTO_MINUSCOLO) $descrizione_nota = strtolower($descrizione_nota);

  $agg = false;

  $stmtUpdNota = $db->prepare("UPDATE progetto_nota SET visibilita = ?, descrizione = ?, utente_ultima_modifica = ? WHERE id = ?");
  $db->beginTransaction();
  $stmtUpdNota->execute([$visibilita, $descrizione_nota, $_SESSION["ksglobo_nome_utente"], $id_nota]);
  $num_nota = $stmtUpdNota->rowCount();
  $db->commit();

  if ($num_nota == 1) {
    $agg = true;
    if ($visibilita == 2) {
      $stmtUpdProg = $db->prepare("UPDATE progetto SET visibilita = '2' WHERE id = ?");
      $db->beginTransaction(); $stmtUpdProg->execute([$id_progetto]); $db->commit();
    }
  }

  $stmtSelScad = $db->prepare("SELECT id FROM scadenza WHERE id_progetto_nota = ?");
  $stmtSelScad->execute([$id_nota]);
  $res        = $stmtSelScad->fetchAll();
  $id_scadenza = count($res) > 0 ? $res[0]["id"] : 0;

  if ($data_scadenza == "" && $id_scadenza > 0) {
    $stmtDelScad = $db->prepare("DELETE FROM scadenza WHERE id = ?");
    $db->beginTransaction(); $stmtDelScad->execute([$id_scadenza]); $db->commit();
    $agg = true;
  } elseif ($data_scadenza != "") {
    $stmtSelProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
    $stmtSelProg->execute([$id_progetto]);
    $res_prog         = $stmtSelProg->fetchAll();
    $nome_progetto    = $res_prog[0]["nome"];
    $desc_progetto    = globo_converti_acapo_da_html_in_spazi($res_prog[0]["descrizione"]);
    $desc_scadenza    = "progetto: $nome_progetto<br />descrizione: $desc_progetto<br /><br />$descrizione_nota";

    if ($id_scadenza == 0) {
      $tipo_prog = $tipo == "step" ? "progetto_step" : "progetto";
      $stmtInsScad = $db->prepare("INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica) VALUES (?, ?, ?, '0', ?, ?, ?, ?, ?)");
      $db->beginTransaction(); $stmtInsScad->execute([$data_scadenza, $tipo_prog, $visibilita, $id_progetto, $id_nota, $desc_scadenza, $responsabile_scadenza, $_SESSION["ksglobo_nome_utente"]]); $db->commit();
      $agg = true;
    } else {
      $stmtUpdScad = $db->prepare("UPDATE scadenza SET data = ?, visibilita = ?, descrizione = ?, responsabile = ?, utente_ultima_modifica = ? WHERE id = ?");
      $db->beginTransaction(); $stmtUpdScad->execute([$data_scadenza, $visibilita, $desc_scadenza, $responsabile_scadenza, $_SESSION["ksglobo_nome_utente"], $id_scadenza]); $db->commit();
      $agg = true;
    }
  }

  if ($aggiorna_scadenze_step && $data_scadenza != "" && $data_scadenza != $data_scadenza_old) {
    $diff = globo_differenza_date($data_scadenza, $data_scadenza_old);
    $stmtSelStep = $db->prepare("SELECT * FROM scadenza WHERE tipo = 'progetto_step' AND id_progetto = ? AND id_progetto_nota <> ?");
    $stmtSelStep->execute([$id_progetto, $id_nota]);
    $stmtUpdStep = $db->prepare("UPDATE scadenza SET data = ? WHERE id = ?");
    foreach ($stmtSelStep->fetchAll() as $r_1) {
      $db->beginTransaction(); $stmtUpdStep->execute([globo_calcola_data($r_1["data"], $diff), $r_1["id"]]); $db->commit();
    }
  }

  if (PROG_NOTIF && $agg && $tipo != "step") {
    if (!isset($nome_progetto)) {
      $stmtSelProgNotif = $db->prepare("SELECT * FROM progetto WHERE id = ?");
      $stmtSelProgNotif->execute([$id_progetto]);
      $res_prog      = $stmtSelProgNotif->fetchAll();
      $nome_progetto = $res_prog[0]["nome"];
      $desc_progetto = $res_prog[0]["descrizione"];
    }
    $oggetto = "nota progetto modificata";
    $testo   = "l'utente " . $_SESSION["ksglobo_nome_utente"] . " ha modificato la seguene nota, creata o precedentemente modificata da te (o con una scadenza associata a te)";
    $testo  .= "<br /><br />---<br /><br />nome progetto: $nome_progetto<br /><br />descrizione nota: " . globo_converti_acapo_da_html_in_spazi($descrizione_nota);
    if (trim($data_scadenza) != "" || trim($data_scadenza_old) != "") {
      $testo .= "<br /><br />data scadenza: " . globo_converti_data_in_formato_ita($data_scadenza);
      $testo .= "<br /><br />responsabile scadenza: $responsabile_scadenza";
    }
    $ut_inv = [];
    foreach ([$utente_creazione, $utente_ultima_modifica, $responsabile_scadenza, $responsabile_scadenza_old] as $nome_utente) {
      if (trim($nome_utente) == "") continue;
      $exclude = array_merge([$_SESSION["ksglobo_nome_utente"]], $ut_inv);
      $placeholders = implode(',', array_fill(0, count($exclude), '?'));
      $stmtNotif = $db->prepare("SELECT livello FROM utente WHERE nome_utente = ? AND nome_utente NOT IN ($placeholders)");
      $stmtNotif->execute(array_merge([$nome_utente], $exclude));
      $res_n = $stmtNotif->fetchAll();
      if (count($res_n) > 0 && $res_n[0]["livello"] <= $_SESSION["ksglobo_livello_utente"]) {
        globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, "", $nome_utente, "");
        $ut_inv[] = $nome_utente;
      }
    }
  }

  $label = $tipo != "step" ? 'Nota aggiornata.' : 'Step aggiornato.';
  $_SESSION['flash'] = ($agg || $num_nota == 1)
    ? ['type' => 'ok',  'text' => $label]
    : ['type' => 'err', 'text' => str_replace('aggiornata', 'non aggiornata', str_replace('aggiornato', 'non aggiornato', $label))];

  header("Location: prog_vis.php?id=" . (int)$id_progetto);
  exit;
