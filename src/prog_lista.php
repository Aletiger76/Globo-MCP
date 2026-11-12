<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

if (!isset($_SESSION["ksglobo_ricerca_progetti"])) {
    $_SESSION["ksglobo_ricerca_progetti"] = [];
}

$id_progetto = 0;
if (isset($_POST["id_progetto"])) $id_progetto = $_POST["id_progetto"];
else if (isset($_GET["id"])) $id_progetto = $_GET["id"];

if (PROC_ARC && isset($_GET["a"]) && $_GET["a"] == 1) $flag_archivio = 1;
else $flag_archivio = 0;

if ($flag_archivio == 0) {
  if (isset($_POST["ricerca_pag"])) $ricerca_pag = $_POST["ricerca_pag"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["pag"])) $ricerca_pag = $_SESSION["ksglobo_ricerca_progetti"]["pag"];
  else $ricerca_pag = 1;
  if (isset($_POST["ricerca_pag_arc"])) $ricerca_pag_arc = $_POST["ricerca_pag_arc"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["pag_arc"])) $ricerca_pag_arc = $_SESSION["ksglobo_ricerca_progetti"]["pag_arc"];
  else $ricerca_pag_arc = 1;
  if (isset($_POST["ricerca_testo"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"]));
    else $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]);
  }
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_progetti"]["testo"];
  else $ricerca_testo = "";
  if (isset($_POST["ricerca_testo_arc"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo_arc = globo_converti_in_html(strtolower($_POST["ricerca_testo_arc"]));
    else $ricerca_testo_arc = globo_converti_in_html($_POST["ricerca_testo_arc"]);
  }
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["testo_arc"])) $ricerca_testo_arc = $_SESSION["ksglobo_ricerca_progetti"]["testo_arc"];
  else $ricerca_testo_arc = "";
  if (PROG_RIC_PROG_CON_NOTE) {
    if (isset($_POST["ricerca_solo_prog_con_note"])) $ricerca_solo_prog_con_note = $_POST["ricerca_solo_prog_con_note"];
    else if (isset($_SESSION["ksglobo_ricerca_progetti"]["solo_prog_con_note"])) $ricerca_solo_prog_con_note = $_SESSION["ksglobo_ricerca_progetti"]["solo_prog_con_note"];
    else $ricerca_solo_prog_con_note = 0;
  }
  else {
    $ricerca_solo_prog_con_note = 0;
  }
  if (isset($_POST["ricerca_procedura"])) $ricerca_procedura = $_POST["ricerca_procedura"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["procedura"])) $ricerca_procedura = $_SESSION["ksglobo_ricerca_progetti"]["procedura"];
  else $ricerca_procedura = 0;
  if (isset($_POST["ricerca_id_progetto_procedura"])) $ricerca_id_progetto_procedura = $_POST["ricerca_id_progetto_procedura"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura"])) $ricerca_id_progetto_procedura = $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura"];
  else $ricerca_id_progetto_procedura = 0;
  if (isset($_POST["ricerca_id_progetto_procedura_step_1"])) $ricerca_id_progetto_procedura_step_1 = $_POST["ricerca_id_progetto_procedura_step_1"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_1"])) $ricerca_id_progetto_procedura_step_1 = $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_1"];
  else $ricerca_id_progetto_procedura_step_1 = 0;
  if (isset($_POST["ricerca_stato_progetto_procedura_step_1"])) $ricerca_stato_progetto_procedura_step_1 = $_POST["ricerca_stato_progetto_procedura_step_1"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_1"])) $ricerca_stato_progetto_procedura_step_1 = $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_1"];
  else $ricerca_stato_progetto_procedura_step_1 = 0;
  if (isset($_POST["ricerca_progetto_procedura_step_chiuso_utente_1"])) $ricerca_progetto_procedura_step_chiuso_utente_1 = $_POST["ricerca_progetto_procedura_step_chiuso_utente_1"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_1"])) $ricerca_progetto_procedura_step_chiuso_utente_1 = $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_1"];
  else $ricerca_progetto_procedura_step_chiuso_utente_1 = "tutti";
  if (isset($_POST["ricerca_id_progetto_procedura_step_2"])) $ricerca_id_progetto_procedura_step_2 = $_POST["ricerca_id_progetto_procedura_step_2"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_2"])) $ricerca_id_progetto_procedura_step_2 = $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_2"];
  else $ricerca_id_progetto_procedura_step_2 = 0;
  if (isset($_POST["ricerca_stato_progetto_procedura_step_2"])) $ricerca_stato_progetto_procedura_step_2 = $_POST["ricerca_stato_progetto_procedura_step_2"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_2"])) $ricerca_stato_progetto_procedura_step_2 = $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_2"];
  else $ricerca_stato_progetto_procedura_step_2 = 0;
  if (isset($_POST["ricerca_progetto_procedura_step_chiuso_utente_2"])) $ricerca_progetto_procedura_step_chiuso_utente_2 = $_POST["ricerca_progetto_procedura_step_chiuso_utente_2"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_2"])) $ricerca_progetto_procedura_step_chiuso_utente_2 = $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_2"];
  else $ricerca_progetto_procedura_step_chiuso_utente_2 = "tutti";
  if (isset($_POST["ricerca_id_progetto_procedura_step_3"])) $ricerca_id_progetto_procedura_step_3 = $_POST["ricerca_id_progetto_procedura_step_3"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_3"])) $ricerca_id_progetto_procedura_step_3 = $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_3"];
  else $ricerca_id_progetto_procedura_step_3 = 0;
  if (isset($_POST["ricerca_stato_progetto_procedura_step_3"])) $ricerca_stato_progetto_procedura_step_3 = $_POST["ricerca_stato_progetto_procedura_step_3"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_3"])) $ricerca_stato_progetto_procedura_step_3 = $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_3"];
  else $ricerca_stato_progetto_procedura_step_3 = 0;
  if (isset($_POST["ricerca_progetto_procedura_step_chiuso_utente_3"])) $ricerca_progetto_procedura_step_chiuso_utente_3 = $_POST["ricerca_progetto_procedura_step_chiuso_utente_3"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_3"])) $ricerca_progetto_procedura_step_chiuso_utente_3 = $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_3"];
  else $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}
else {
  $ricerca_pag = 1;
  if (isset($_POST["ricerca_pag_arc"])) $ricerca_pag_arc = $_POST["ricerca_pag_arc"];
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["pag_arc"])) $ricerca_pag_arc = $_SESSION["ksglobo_ricerca_progetti"]["pag_arc"];
  else $ricerca_pag_arc = 1;
  $ricerca_solo_prog_con_note = 0;
  $ricerca_testo = "";
  if (isset($_POST["ricerca_testo_arc"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo_arc = globo_converti_in_html(strtolower($_POST["ricerca_testo_arc"]));
    else $ricerca_testo_arc = globo_converti_in_html($_POST["ricerca_testo_arc"]);
  }
  else if (isset($_SESSION["ksglobo_ricerca_progetti"]["testo_arc"])) $ricerca_testo_arc = $_SESSION["ksglobo_ricerca_progetti"]["testo_arc"];
  else $ricerca_testo_arc = "";
  $ricerca_procedura = 0;
  $ricerca_id_progetto_procedura = 0;
  $ricerca_id_progetto_procedura_step_1 = 0;
  $ricerca_stato_progetto_procedura_step_1 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_1 = "tutti";
  $ricerca_id_progetto_procedura_step_2 = 0;
  $ricerca_stato_progetto_procedura_step_2 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_2 = "tutti";
  $ricerca_id_progetto_procedura_step_3 = 0;
  $ricerca_stato_progetto_procedura_step_3 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}

if ($ricerca_procedura == 0 || $ricerca_id_progetto_procedura == 0) {
  $ricerca_id_progetto_procedura = 0;
  $ricerca_id_progetto_procedura_step_1 = 0;
  $ricerca_stato_progetto_procedura_step_1 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_1 = "tutti";
  $ricerca_id_progetto_procedura_step_2 = 0;
  $ricerca_stato_progetto_procedura_step_2 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_2 = "tutti";
  $ricerca_id_progetto_procedura_step_3 = 0;
  $ricerca_stato_progetto_procedura_step_3 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}

if ($ricerca_id_progetto_procedura != ($_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura"] ?? 0)) {
  $ricerca_id_progetto_procedura_step_1 = 0;
  $ricerca_stato_progetto_procedura_step_1 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_1 = "tutti";
  $ricerca_id_progetto_procedura_step_2 = 0;
  $ricerca_stato_progetto_procedura_step_2 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_2 = "tutti";
  $ricerca_id_progetto_procedura_step_3 = 0;
  $ricerca_stato_progetto_procedura_step_3 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}

if ($ricerca_id_progetto_procedura_step_1 == 0) {
  $ricerca_id_progetto_procedura_step_2 = 0;
  $ricerca_stato_progetto_procedura_step_2 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_2 = "tutti";
  $ricerca_id_progetto_procedura_step_3 = 0;
  $ricerca_stato_progetto_procedura_step_3 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}

if ($ricerca_id_progetto_procedura_step_2 == 0) {
  $ricerca_id_progetto_procedura_step_3 = 0;
  $ricerca_stato_progetto_procedura_step_3 = 0;
  $ricerca_progetto_procedura_step_chiuso_utente_3 = "tutti";
}

if ($flag_archivio == 0) {
  unset($_SESSION["ksglobo_ricerca_progetti"]);
  $_SESSION["ksglobo_ricerca_progetti"]["pag"] = $ricerca_pag;
  $_SESSION["ksglobo_ricerca_progetti"]["pag_arc"] = $ricerca_pag_arc;
  $_SESSION["ksglobo_ricerca_progetti"]["solo_prog_con_note"] = $ricerca_solo_prog_con_note;
  $_SESSION["ksglobo_ricerca_progetti"]["testo"] = $ricerca_testo;
  $_SESSION["ksglobo_ricerca_progetti"]["testo_arc"] = $ricerca_testo_arc;
  $_SESSION["ksglobo_ricerca_progetti"]["procedura"] = $ricerca_procedura;
  $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura"] = $ricerca_id_progetto_procedura;
  $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_1"] = $ricerca_id_progetto_procedura_step_1;
  $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_1"] = $ricerca_stato_progetto_procedura_step_1;
  $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_1"] = $ricerca_progetto_procedura_step_chiuso_utente_1;
  $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_2"] = $ricerca_id_progetto_procedura_step_2;
  $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_2"] = $ricerca_stato_progetto_procedura_step_2;
  $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_2"] = $ricerca_progetto_procedura_step_chiuso_utente_2;
  $_SESSION["ksglobo_ricerca_progetti"]["id_progetto_procedura_step_3"] = $ricerca_id_progetto_procedura_step_3;
  $_SESSION["ksglobo_ricerca_progetti"]["stato_progetto_procedura_step_3"] = $ricerca_stato_progetto_procedura_step_3;
  $_SESSION["ksglobo_ricerca_progetti"]["progetto_procedura_step_chiuso_utente_3"] = $ricerca_progetto_procedura_step_chiuso_utente_3;
}
else {
  $_SESSION["ksglobo_ricerca_progetti"]["pag_arc"] = $ricerca_pag_arc;
  $_SESSION["ksglobo_ricerca_progetti"]["testo_arc"] = $ricerca_testo_arc;
}

$params_prog = [];

if ($ricerca_id_progetto_procedura_step_1 > 0) {
  $query_prog = "SELECT * FROM progetto WHERE progetto.flag_archivio = '0'";
  $query_prog .= " AND progetto.id";
  if ($ricerca_stato_progetto_procedura_step_1 == 0) $query_prog .= " IN";
  else $query_prog .= " NOT IN";
  $query_prog .= " (SELECT id_progetto FROM progetto_procedura_step_chiuso WHERE progetto_procedura_step_chiuso.id_progetto_procedura_step = ?";
  $params_prog[] = $ricerca_id_progetto_procedura_step_1;
  if ($ricerca_stato_progetto_procedura_step_1 == 0 && $ricerca_progetto_procedura_step_chiuso_utente_1 != "tutti") {
    $query_prog .= " AND progetto_procedura_step_chiuso.utente = ?";
    $params_prog[] = $ricerca_progetto_procedura_step_chiuso_utente_1;
  }
  $query_prog .= ")";
  $query_prog .= " AND progetto.id NOT IN (SELECT id_progetto FROM progetto_procedura_step_non_attivo WHERE progetto_procedura_step_non_attivo.id_progetto_procedura_step = ?)";
  $params_prog[] = $ricerca_id_progetto_procedura_step_1;

  if ($ricerca_id_progetto_procedura_step_2 > 0) {
    $query_prog .= " AND progetto.id";
    if ($ricerca_stato_progetto_procedura_step_2 == 0) $query_prog .= " IN";
    else $query_prog .= " NOT IN";
    $query_prog .= " (SELECT id_progetto FROM progetto_procedura_step_chiuso WHERE progetto_procedura_step_chiuso.id_progetto_procedura_step = ?";
    $params_prog[] = $ricerca_id_progetto_procedura_step_2;
    if ($ricerca_stato_progetto_procedura_step_2 == 0 && $ricerca_progetto_procedura_step_chiuso_utente_2 != "tutti") {
      $query_prog .= " AND progetto_procedura_step_chiuso.utente = ?";
      $params_prog[] = $ricerca_progetto_procedura_step_chiuso_utente_2;
    }
    $query_prog .= ")";
    $query_prog .= " AND progetto.id NOT IN (SELECT id_progetto FROM progetto_procedura_step_non_attivo WHERE progetto_procedura_step_non_attivo.id_progetto_procedura_step = ?)";
    $params_prog[] = $ricerca_id_progetto_procedura_step_2;
  }

  if ($ricerca_id_progetto_procedura_step_3 > 0) {
    $query_prog .= " AND progetto.id";
    if ($ricerca_stato_progetto_procedura_step_3 == 0) $query_prog .= " IN";
    else $query_prog .= " NOT IN";
    $query_prog .= " (SELECT id_progetto FROM progetto_procedura_step_chiuso WHERE progetto_procedura_step_chiuso.id_progetto_procedura_step = ?";
    $params_prog[] = $ricerca_id_progetto_procedura_step_3;
    if ($ricerca_stato_progetto_procedura_step_3 == 0 && $ricerca_progetto_procedura_step_chiuso_utente_3 != "tutti") {
      $query_prog .= " AND progetto_procedura_step_chiuso.utente = ?";
      $params_prog[] = $ricerca_progetto_procedura_step_chiuso_utente_3;
    }
    $query_prog .= ")";
    $query_prog .= " AND progetto.id NOT IN (SELECT id_progetto FROM progetto_procedura_step_non_attivo WHERE progetto_procedura_step_non_attivo.id_progetto_procedura_step = ?)";
    $params_prog[] = $ricerca_id_progetto_procedura_step_3;
  }

  $query_prog .= " AND (nome LIKE ? OR descrizione LIKE ?)";
  $params_prog[] = "%$ricerca_testo%";
  $params_prog[] = "%$ricerca_testo%";
  if ($_SESSION["ksglobo_livello_utente"] > 1) $query_prog .= " AND visibilita = '2'";
  if ($ricerca_id_progetto_procedura > 0) {
    $query_prog .= " AND id_progetto_procedura = ?";
    $params_prog[] = $ricerca_id_progetto_procedura;
  }
}
else {
  $query_prog = "SELECT * FROM progetto WHERE flag_archivio = ?";
  $params_prog[] = $flag_archivio;
  if ($flag_archivio == 0) {
    $query_prog .= " AND (nome LIKE ? OR descrizione LIKE ?)";
    $params_prog[] = "%$ricerca_testo%";
    $params_prog[] = "%$ricerca_testo%";
  } else {
    $query_prog .= " AND (nome LIKE ? OR descrizione LIKE ?)";
    $params_prog[] = "%$ricerca_testo_arc%";
    $params_prog[] = "%$ricerca_testo_arc%";
  }
  if ($_SESSION["ksglobo_livello_utente"] > 1) $query_prog .= " AND visibilita = '2'";
  if ($ricerca_id_progetto_procedura > 0) {
    $query_prog .= " AND id_progetto_procedura = ?";
    $params_prog[] = $ricerca_id_progetto_procedura;
  }
}

// Conteggio totale via COUNT(*): conta lato DB senza materializzare in PHP
// l'intero set (prima si faceva fetchAll di TUTTI i progetti solo per contarli,
// e poi una seconda query con LIMIT per la pagina). $query_prog inizia sempre
// con "SELECT * FROM progetto ...": sostituiamo solo la SELECT-list. Niente
// ORDER BY nel conteggio (inutile).
$query_count = "SELECT COUNT(*) " . substr($query_prog, strlen("SELECT * "));
$stmtCount = $db->prepare($query_count);
$stmtCount->execute($params_prog);
$num_tot_prog = (int)$stmtCount->fetchColumn();

if ($flag_archivio == 0) {
  $num_pag = ceil($num_tot_prog / 100);
  if ($ricerca_pag > $num_pag) $ricerca_pag = 1;
  $limit_1 = ($ricerca_pag - 1) * 100;
  $limit_2 = 100;
}
else {
  $num_pag = ceil($num_tot_prog / 100);
  if ($ricerca_pag_arc > $num_pag) $ricerca_pag_arc = 1;
  $limit_1 = ($ricerca_pag_arc - 1) * 100;
  $limit_2 = 100;
}

// Query di pagina: ORDER BY + LIMIT, eseguita una sola volta.
$query_prog .= " ORDER BY nome, descrizione LIMIT $limit_1, $limit_2";
$stmtProg = $db->prepare($query_prog);
$stmtProg->execute($params_prog);
$res_prog = $stmtProg->fetchAll();
$num_prog = count($res_prog);

$query_proc = "SELECT * FROM progetto_procedura ORDER BY descrizione";
$res_proc = $db->query($query_proc)->fetchAll();
$num_proc = count($res_proc);

if ($ricerca_id_progetto_procedura > 0) {
  $stmtProcStep = $db->prepare("SELECT * FROM progetto_procedura_step WHERE id_progetto_procedura = ? ORDER BY data_scadenza, id");
  $stmtProcStep->execute([$ricerca_id_progetto_procedura]);
  $res_proc_step = $stmtProcStep->fetchAll();
  $num_proc_step = count($res_proc_step);
}

$stmtNoteCheck = $db->prepare("SELECT id FROM progetto_nota WHERE id_progetto = ? AND tipo = 'nota'");
$stmtProcDesc = $db->prepare("SELECT descrizione FROM progetto_procedura WHERE id = ?");
if ($_SESSION["ksglobo_livello_utente"] <= 1) {
  $stmtScadCheck = $db->prepare("SELECT id FROM scadenza WHERE id_progetto = ?");
} else {
  $stmtScadCheck = $db->prepare("SELECT id FROM scadenza WHERE id_progetto = ? AND visibilita = '2'");
}
if (MOD_PROGETTI_DOC && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI_DOC) {
  if ($_SESSION["ksglobo_livello_utente"] <= 1) {
    $stmtDocCheck = $db->prepare("SELECT * FROM progetto_documento WHERE id_progetto = ?");
  } else {
    $stmtDocCheck = $db->prepare("SELECT * FROM progetto_documento WHERE id_progetto = ? AND visibilita = '2'");
  }
}

if (PROG_RIC_PROG_CON_NOTE && $ricerca_solo_prog_con_note == 1) {
  foreach ($res_prog as $r_prog) {
    $id_progetto = $r_prog["id"];
    $stmtNoteCheck->execute([$id_progetto]);
    $res_note = $stmtNoteCheck->fetchAll();
    $num_note = count($res_note);
    if ($num_note <= 0) $num_tot_prog--;
  }
}

// Calcolo colonne lista
$_globo_show_vis = ($_SESSION["ksglobo_livello_utente"] <= 1);
$_globo_show_doc = (defined('MOD_PROGETTI_DOC') && MOD_PROGETTI_DOC && defined('LIV_MOD_PROGETTI_DOC') && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI_DOC);
$_globo_cols = '1fr 1fr';
if ($_globo_show_vis) $_globo_cols .= ' 72px';
$_globo_cols .= ' 58px';
if ($_globo_show_doc) $_globo_cols .= ' 58px';

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $flag_archivio == 0 ? 'Progetti' : 'Progetti archiviati' ?></h1>
        <?php if ($num_tot_prog > 0): ?>
        <p class="page__sub"><?= $num_tot_prog ?> <?= $num_tot_prog == 1 ? 'progetto' : 'progetti' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($flag_archivio == 0): ?>
        <a class="btn btn--ghost" href="prog_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo progetto
        </a>
        <?php else: ?>
        <a class="btn btn--ghost" href="prog_lista.php">Progetti attivi</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Ricerca ─────────────────────────────────────────── -->
    <?php
      $search_action = 'prog_lista.php' . ($flag_archivio == 1 ? "?a=$flag_archivio" : '');
      $search_form_attrs = 'name="globo"';
      require("varie/search_open.php");
    ?>

            <!-- Testo -->
            <?php
              $si_name  = $flag_archivio == 1 ? 'ricerca_testo_arc' : 'ricerca_testo';
              $si_value = $flag_archivio == 1 ? $ricerca_testo_arc : $ricerca_testo;
              require("varie/search_input.php");
            ?>

            <?php if (PROG_RIC_PROG_CON_NOTE && $flag_archivio == 0): ?>
            <!-- Solo progetti con note -->
            <div class="form__row">
              <div class="form__label">Solo progetti con note</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_solo_prog_con_note" onchange="this.form.submit()">
                    <option value="1"<?= $ricerca_solo_prog_con_note == 1 ? ' selected' : '' ?>>sì</option>
                    <option value="0"<?= $ricerca_solo_prog_con_note == 0 ? ' selected' : '' ?>>no</option>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($num_proc > 0 && $flag_archivio == 0): ?>
            <!-- Ricerca procedura -->
            <div class="form__row">
              <div class="form__label">Ricerca per procedura</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_procedura" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_procedura == 0 ? ' selected' : '' ?>>no</option>
                    <option value="1"<?= $ricerca_procedura == 1 ? ' selected' : '' ?>>sì</option>
                  </select>
                </div>
              </div>
            </div>

            <?php if ($ricerca_procedura == 1): ?>
            <div class="form__row">
              <div class="form__label">Procedura</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_id_progetto_procedura" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_id_progetto_procedura == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_proc as $r_proc): ?>
                    <option value="<?= $r_proc['id'] ?>"<?= $ricerca_id_progetto_procedura == $r_proc['id'] ? ' selected' : '' ?>><?= $r_proc['descrizione'] /* già HTML: grezzo */ ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($ricerca_procedura == 1 && $ricerca_id_progetto_procedura > 0): ?>
            <!-- Step #1 -->
            <div class="form__row">
              <div class="form__label">Step #1</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_id_progetto_procedura_step_1" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_id_progetto_procedura_step_1 == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_proc_step as $r_proc_step): ?>
                    <option value="<?= $r_proc_step['id'] ?>"<?= $ricerca_id_progetto_procedura_step_1 == $r_proc_step['id'] ? ' selected' : '' ?>><?= $r_proc_step['descrizione'] /* già HTML: grezzo */ ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <?php if ($ricerca_id_progetto_procedura_step_1 > 0): ?>
            <div class="form__row">
              <div class="form__label">Stato step #1</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_stato_progetto_procedura_step_1" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_stato_progetto_procedura_step_1 == 0 ? ' selected' : '' ?>>chiuso</option>
                    <option value="1"<?= $ricerca_stato_progetto_procedura_step_1 == 1 ? ' selected' : '' ?>>aperto</option>
                  </select>
                </div>
                <?php if ($ricerca_stato_progetto_procedura_step_1 == 0): ?>
                <?php $stmtU = $db->prepare("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente"); $stmtU->execute([]); $res_utenti = $stmtU->fetchAll(); ?>
                <span style="font-size:13px;color:var(--fg-3)">da</span>
                <div class="select-wrap">
                  <select name="ricerca_progetto_procedura_step_chiuso_utente_1" onchange="this.form.submit()">
                    <option value="tutti"<?= $ricerca_progetto_procedura_step_chiuso_utente_1 == 'tutti' ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_utenti as $u): ?><option value="<?= htmlspecialchars($u['nome_utente']) ?>"<?= $ricerca_progetto_procedura_step_chiuso_utente_1 == $u['nome_utente'] ? ' selected' : '' ?>><?= htmlspecialchars($u['nome_utente']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Step #2 -->
            <div class="form__row">
              <div class="form__label">Step #2</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_id_progetto_procedura_step_2" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_id_progetto_procedura_step_2 == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_proc_step as $r_proc_step): ?>
                    <option value="<?= $r_proc_step['id'] ?>"<?= $ricerca_id_progetto_procedura_step_2 == $r_proc_step['id'] ? ' selected' : '' ?>><?= $r_proc_step['descrizione'] /* già HTML: grezzo */ ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <?php if ($ricerca_id_progetto_procedura_step_2 > 0): ?>
            <div class="form__row">
              <div class="form__label">Stato step #2</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_stato_progetto_procedura_step_2" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_stato_progetto_procedura_step_2 == 0 ? ' selected' : '' ?>>chiuso</option>
                    <option value="1"<?= $ricerca_stato_progetto_procedura_step_2 == 1 ? ' selected' : '' ?>>aperto</option>
                  </select>
                </div>
                <?php if ($ricerca_stato_progetto_procedura_step_2 == 0): ?>
                <?php $stmtU = $db->prepare("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente"); $stmtU->execute([]); $res_utenti = $stmtU->fetchAll(); ?>
                <span style="font-size:13px;color:var(--fg-3)">da</span>
                <div class="select-wrap">
                  <select name="ricerca_progetto_procedura_step_chiuso_utente_2" onchange="this.form.submit()">
                    <option value="tutti"<?= $ricerca_progetto_procedura_step_chiuso_utente_2 == 'tutti' ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_utenti as $u): ?><option value="<?= htmlspecialchars($u['nome_utente']) ?>"<?= $ricerca_progetto_procedura_step_chiuso_utente_2 == $u['nome_utente'] ? ' selected' : '' ?>><?= htmlspecialchars($u['nome_utente']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Step #3 -->
            <div class="form__row">
              <div class="form__label">Step #3</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_id_progetto_procedura_step_3" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_id_progetto_procedura_step_3 == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_proc_step as $r_proc_step): ?>
                    <option value="<?= $r_proc_step['id'] ?>"<?= $ricerca_id_progetto_procedura_step_3 == $r_proc_step['id'] ? ' selected' : '' ?>><?= $r_proc_step['descrizione'] /* già HTML: grezzo */ ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <?php if ($ricerca_id_progetto_procedura_step_3 > 0): ?>
            <div class="form__row">
              <div class="form__label">Stato step #3</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_stato_progetto_procedura_step_3" onchange="this.form.submit()">
                    <option value="0"<?= $ricerca_stato_progetto_procedura_step_3 == 0 ? ' selected' : '' ?>>chiuso</option>
                    <option value="1"<?= $ricerca_stato_progetto_procedura_step_3 == 1 ? ' selected' : '' ?>>aperto</option>
                  </select>
                </div>
                <?php if ($ricerca_stato_progetto_procedura_step_3 == 0): ?>
                <?php $stmtU = $db->prepare("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente"); $stmtU->execute([]); $res_utenti = $stmtU->fetchAll(); ?>
                <span style="font-size:13px;color:var(--fg-3)">da</span>
                <div class="select-wrap">
                  <select name="ricerca_progetto_procedura_step_chiuso_utente_3" onchange="this.form.submit()">
                    <option value="tutti"<?= $ricerca_progetto_procedura_step_chiuso_utente_3 == 'tutti' ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_utenti as $u): ?><option value="<?= htmlspecialchars($u['nome_utente']) ?>"<?= $ricerca_progetto_procedura_step_chiuso_utente_3 == $u['nome_utente'] ? ' selected' : '' ?>><?= htmlspecialchars($u['nome_utente']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; /* step 3 > 0 */ ?>

            <?php endif; /* step 2 > 0 */ ?>
            <?php endif; /* step 1 > 0 */ ?>
            <?php endif; /* proc > 0 && step filter active */ ?>
            <?php endif; /* num_proc > 0 && !archived */ ?>

    <?php require("varie/search_close.php"); ?>

    <!-- ── Lista progetti ──────────────────────────────────── -->
    <?php if ($num_prog <= 0 || $num_tot_prog <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun progetto trovato.</p>
    </div>
    <?php else: ?>
    <?php
      $_pg_param  = $flag_archivio == 0 ? 'ricerca_pag' : 'ricerca_pag_arc';
      $_pg_cur    = $flag_archivio == 0 ? $ricerca_pag : $ricerca_pag_arc;
      $_pg_action = $flag_archivio == 1 ? 'prog_lista.php?a=1' : 'prog_lista.php';
      globo_render_paginazione($_pg_cur, $num_pag, $_pg_action, $_pg_param);
    ?>
    <div class="list">
      <div class="list__hdr" style="grid-template-columns: <?= $_globo_cols ?>">
        <div>Nome</div>
        <div>Descrizione</div>
        <?php if ($_globo_show_vis): ?><div style="text-align:center">Visib.</div><?php endif; ?>
        <div style="text-align:center; white-space:nowrap">Dett.</div>
        <?php if ($_globo_show_doc): ?><div style="text-align:center; white-space:nowrap">Doc.</div><?php endif; ?>
      </div>
      <?php foreach ($res_prog as $r_prog):
        $id_progetto     = $r_prog["id"];
        $id_progetto_procedura = $r_prog["id_progetto_procedura"];
        $visibilita      = $r_prog["visibilita"];
        $nome_progetto   = $r_prog["nome"];
        $descrizione_progetto = $r_prog["descrizione"];
        $descrizione_procedura = "";

        if ($id_progetto_procedura > 0) {
          $stmtProcDesc->execute([$id_progetto_procedura]);
          $res_proc_tmp = $stmtProcDesc->fetchAll();
          if (count($res_proc_tmp) > 0) $descrizione_procedura = $res_proc_tmp[0]["descrizione"];
        }

        if (PROG_RIC_PROG_CON_NOTE && $ricerca_solo_prog_con_note == 1) {
          $stmtNoteCheck->execute([$id_progetto]);
          if (count($stmtNoteCheck->fetchAll()) <= 0) continue;
        }

        $stmtScadCheck->execute([$id_progetto]);
        $_globo_has_scad = (count($stmtScadCheck->fetchAll()) > 0);
      ?>
      <div class="list__row<?= $_globo_has_scad ? ' is-attention' : '' ?>"
           style="grid-template-columns: <?= $_globo_cols ?>; cursor:pointer"
           onclick="window.location.href='prog_vis.php?id=<?= (int)$id_progetto ?>'">
        <?php /* nome/descrizione progetto e descrizione procedura sono salvati GIÀ
                 codificati in HTML (globo_converti_in_html): output GREZZO, il browser
                 decodifica le entità. htmlspecialchars qui ri-codificherebbe → "&#39;". */ ?>
        <div data-label="Nome">
          <?php if ($_globo_has_scad): ?><span class="attention-dot" aria-hidden="true"></span><?php endif; ?>
          <span class="list-name"><?= $nome_progetto ?></span>
        </div>
        <div data-label="Descrizione">
          <span class="list-desc"><?= $descrizione_progetto ?></span>
          <?php if ($ricerca_id_progetto_procedura == 0 && $descrizione_procedura != ''): ?>
          <span class="list-meta" style="display:block;margin-top:4px;color:var(--brand)">procedura: <?= $descrizione_procedura ?></span>
          <?php endif; ?>
        </div>
        <?php if ($_globo_show_vis): ?>
        <div data-label="Visib." style="display:flex;align-items:center;justify-content:center">
          <span class="chip <?= $visibilita == 1 ? 'chip--amm' : 'chip--tutti' ?>"><?= $visibilita == 1 ? 'amm' : 'tutti' ?></span>
        </div>
        <?php endif; ?>
        <div class="row-acts">
          <a class="icon-btn" href="prog_vis.php?id=<?= (int)$id_progetto ?>"
             onclick="event.stopPropagation()" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
        </div>
        <?php if ($_globo_show_doc):
          $stmtDocCheck->execute([$id_progetto]);
          $_globo_num_doc = count($stmtDocCheck->fetchAll());
        ?>
        <div class="row-acts">
          <a class="icon-btn<?= $_globo_num_doc > 0 ? ' icon-btn--ghost-dark' : '' ?>"
             href="prog_doc.php?id=<?= (int)$id_progetto ?>"
             onclick="event.stopPropagation()"
             title="Documenti<?= $_globo_num_doc > 0 ? " ($_globo_num_doc)" : '' ?>">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg>
          </a>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div><!-- /.list -->
    <?php globo_render_paginazione($_pg_cur, $num_pag, $_pg_action, $_pg_param); ?>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
