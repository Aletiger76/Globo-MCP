<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

  if (isset($_POST["ricerca_pag"])) $ricerca_pag = $_POST["ricerca_pag"];
  elseif (isset($_SESSION["ksglobo_ricerca_contatti"]["pag"])) $ricerca_pag = $_SESSION["ksglobo_ricerca_contatti"]["pag"];
  else $ricerca_pag = 1;

  if (isset($_POST["ricerca_testo"])) {
    if (TUTTO_MINUSCOLO) $ricerca_testo = globo_converti_in_html(strtolower($_POST["ricerca_testo"]));
    else $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]);
  } elseif (isset($_SESSION["ksglobo_ricerca_contatti"]["testo"])) {
    $ricerca_testo = $_SESSION["ksglobo_ricerca_contatti"]["testo"];
  } else {
    $ricerca_testo = "";
  }

  unset($_SESSION["ksglobo_ricerca_contatti"]);
  $_SESSION["ksglobo_ricerca_contatti"]["pag"]   = $ricerca_pag;
  $_SESSION["ksglobo_ricerca_contatti"]["testo"] = $ricerca_testo;

  $sql_order = " ORDER BY TRIM(azienda), TRIM(cognome), TRIM(nome)";

  if (isset($_GET["p"]) && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI) {
    $p = '%' . $ricerca_testo . '%';
    $stmtTest = $db->prepare("SELECT * FROM contatto WHERE azienda LIKE ? OR nome LIKE ? OR cognome LIKE ?");
    $stmtTest->execute([$p, $p, $p]);
    $res_test = $stmtTest->fetchAll();
    $num_test = count($res_test);

    if ($num_test <= 0) {
      if (strpos($ricerca_testo, " ") > 0) $ricerca_testo_1 = trim(substr($ricerca_testo, 0, strpos($ricerca_testo, " ")));
      else $ricerca_testo_1 = $ricerca_testo;
      if (strpos($ricerca_testo, " ") > 0) $ricerca_testo_2 = trim(substr($ricerca_testo, strpos($ricerca_testo, " ") + 1, strlen($ricerca_testo)));
      else $ricerca_testo_2 = "";

      $p1 = '%' . $ricerca_testo_1 . '%';
      if (strlen($ricerca_testo_2) > 0) {
        $p2 = '%' . $ricerca_testo_2 . '%';
        $sql_where = "azienda LIKE ? OR nome LIKE ? OR cognome LIKE ? OR azienda LIKE ? OR nome LIKE ? OR cognome LIKE ?";
        $params = [$p1, $p1, $p1, $p2, $p2, $p2];
      } else {
        $sql_where = "azienda LIKE ? OR nome LIKE ? OR cognome LIKE ?";
        $params = [$p1, $p1, $p1];
      }
    } else {
      $sql_where = "azienda LIKE ? OR nome LIKE ? OR cognome LIKE ?";
      $params = [$p, $p, $p];
    }
  } else {
    $p = '%' . $ricerca_testo . '%';
    $sql_where = "contatto.azienda LIKE ? OR contatto.nome LIKE ? OR contatto.cognome LIKE ? OR contatto.qualifica LIKE ? OR contatto.telefono LIKE ? OR contatto.cellulare LIKE ? OR contatto.fax LIKE ? OR contatto.email LIKE ? OR contatto.sito LIKE ? OR contatto.indirizzo LIKE ? OR contatto.cap LIKE ? OR contatto.citta LIKE ? OR contatto.provincia LIKE ? OR contatto.regione LIKE ? OR contatto.area LIKE ? OR contatto.nazione LIKE ? OR contatto.pi LIKE ? OR contatto.cf LIKE ? OR contatto.note LIKE ?";
    $params = array_fill(0, 19, $p);
  }

  // Conteggio totale via COUNT(*): conta lato DB senza materializzare in PHP
  // l'intero set (prima: SELECT * + fetchAll di tutti i contatti solo per
  // contarli). Niente ORDER BY nel conteggio.
  $stmtCount = $db->prepare("SELECT COUNT(*) FROM contatto WHERE " . $sql_where);
  $stmtCount->execute($params);
  $num_tot = (int)$stmtCount->fetchColumn();

  $num_pag = ceil($num_tot / 100);
  if ($num_pag == 0) $num_pag = 1;
  if ($ricerca_pag > $num_pag) $ricerca_pag = 1;

  $limit_1 = ($ricerca_pag - 1) * 100;

  $stmtPag = $db->prepare("SELECT * FROM contatto WHERE " . $sql_where . $sql_order . " LIMIT $limit_1, 100");
  $stmtPag->execute($params);
  $res = $stmtPag->fetchAll();
  $num = count($res);

  $mode_p   = isset($_GET["p"]) && MOD_PROGETTI  && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI;
  $show_email_col = MOD_NEWSLETTER && !$mode_p;
  $list_cols = $show_email_col ? '2fr 1fr 1fr 1.5fr 58px' : '2fr 1fr 1fr 58px';
  $form_action = $mode_p ? 'cont_lista.php?p=' . (int)$_GET['p'] : 'cont_lista.php';

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Contatti</h1>
        <?php if ($mode_p): ?>
        <p class="page__sub">Seleziona un contatto per il progetto</p>
        <?php elseif ($num_tot > 0): ?>
        <p class="page__sub"><?= $num_tot ?> contatt<?= $num_tot == 1 ? 'o' : 'i' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="cont_ins_1.php">Nuovo contatto</a>
        <form action="cont_xls.php" method="post" target="_blank" style="display:contents">
          <input type="hidden" name="ricerca_testo" value="<?= $ricerca_testo /* grezzo: cont_xls ri-codifica */ ?>" />
          <button type="submit" class="btn btn--ghost">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-download"/></svg>
            Scarica XLS
          </button>
        </form>
      </div>
    </div>

    <?php $search_action = $form_action; require("varie/search_open.php"); ?>
    <?php $si_name = 'ricerca_testo'; $si_value = $ricerca_testo; require("varie/search_input.php"); ?>
    <?php require("varie/search_close.php"); ?>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun contatto trovato.</p>
    </div>
    <?php else: ?>
    <?php globo_render_paginazione($ricerca_pag, $num_pag, $form_action, 'ricerca_pag'); ?>
    <div class="list list--left" style="--list-cols: <?= $list_cols ?>">
      <div class="list__hdr">
        <span>Azienda</span>
        <span>Nome</span>
        <span>Cognome</span>
        <?php if ($show_email_col): ?><span>E-mail</span><?php endif; ?>
        <span style="text-align:center; white-space:nowrap">Dett.</span>
      </div>
      <?php foreach ($res as $r):
        // azienda/nome/cognome sono salvati GIÀ codificati in HTML (globo_converti_in_html
        // in cont_agg/cont_ins): vanno stampati GREZZI, il browser decodifica le entità.
        // Applicare htmlspecialchars li ri-codificherebbe (a schermo "&#39;" ecc.).
        // email è invece salvata grezza → resta con htmlspecialchars.
        $r_id    = (int)$r["id"];
        $r_az    = $r["azienda"];
        $r_nome  = $r["nome"];
        $r_cogn  = $r["cognome"];
        $r_email = htmlspecialchars($r["email"]);
        $r_href  = $mode_p
          ? 'cont_vis.php?p=' . (int)$_GET['p'] . '&id=' . $r_id
          : 'cont_vis.php?id=' . $r_id;
      ?>
      <div class="list__row" onclick="window.location.href='<?= $r_href ?>'">
        <span data-label="Azienda"><?= $r_az ?></span>
        <span data-label="Nome"><?= $r_nome ?></span>
        <span data-label="Cognome"><?= $r_cogn ?></span>
        <?php if ($show_email_col): ?><span data-label="E-mail"><?= $r_email ?></span><?php endif; ?>
        <div class="row-acts">
          <a class="icon-btn" href="<?= $r_href ?>" onclick="event.stopPropagation()" title="Visualizza">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php globo_render_paginazione($ricerca_pag, $num_pag, $form_action, 'ricerca_pag'); ?>
    <?php endif; ?>

    <?php if ($mode_p): ?>
    <div style="margin-top:16px">
      <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$_GET['p'] ?>">Ritorna al progetto</a>
    </div>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
