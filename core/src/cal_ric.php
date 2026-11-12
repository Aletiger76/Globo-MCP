<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  if (isset($_POST["ricerca_testo"])) {
    $ricerca_testo = TUTTO_MINUSCOLO
      ? globo_converti_in_html(strtolower($_POST["ricerca_testo"]))
      : globo_converti_in_html($_POST["ricerca_testo"]);
  } elseif (isset($_SESSION["ksglobo_ricerca_calendario"]["testo"])) {
    $ricerca_testo = $_SESSION["ksglobo_ricerca_calendario"]["testo"];
  } else {
    $ricerca_testo = "";
  }

  unset($_SESSION["ksglobo_ricerca_calendario"]);
  $_SESSION["ksglobo_ricerca_calendario"]["testo"] = $ricerca_testo;

  $res_imp  = [];
  $res_scad = [];
  $num_imp  = 0;
  $num_scad = 0;
  $num      = 0;

  if (strlen(trim($ricerca_testo)) >= 3) {
    $stmtImp = $db->prepare("SELECT * FROM impegno WHERE descrizione LIKE ? ORDER BY data DESC");
    $stmtImp->execute(["%$ricerca_testo%"]);
    $res_imp  = $stmtImp->fetchAll();
    $num_imp  = count($res_imp);

    $stmtScad = $db->prepare("SELECT * FROM scadenza WHERE descrizione LIKE ? ORDER BY data DESC");
    $stmtScad->execute(["%$ricerca_testo%"]);
    $res_scad = $stmtScad->fetchAll();
    $num_scad = count($res_scad);

    $num = $num_imp + $num_scad;
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Calendario</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="cal_home.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Calendario
        </a>
      </div>
    </div>

    <?php $search_action = 'cal_ric.php'; $search_eyebrow = 'Ricerca impegni / scadenze'; require("varie/search_open.php"); ?>
    <?php $si_name = 'ricerca_testo'; $si_value = $ricerca_testo; require("varie/search_input.php"); ?>
    <?php require("varie/search_close.php"); ?>

    <?php if (strlen(trim($ricerca_testo)) < 3): ?>
    <div class="card">
      <div class="card__body">
        <p style="color:var(--fg-2)">Inserire il testo da ricercare (almeno 3 caratteri).</p>
      </div>
    </div>

    <?php elseif ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun risultato.</p>
    </div>

    <?php else: ?>
    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Risultati</span>
        <span class="card__head-count"><?= $num_imp ?> imp / <?= $num_scad ?> scad</span>
      </div>
      <div class="card__body" style="padding:0">
        <div class="list" style="--list-cols: 96px 110px 1fr 58px">
          <div class="list__hdr">
            <span>Tipo</span>
            <span>Data</span>
            <span>Descrizione</span>
            <span style="text-align:center; white-space:nowrap">Dett.</span>
          </div>
          <?php foreach ($res_imp as $r): ?>
          <div class="list__row">
            <span data-label="Tipo">imp</span>
            <span data-label="Data"><?= globo_converti_data_in_formato_ita($r["data"]) ?></span>
            <span data-label="Descrizione"><?= $r["descrizione"] /* già HTML: grezzo */ ?></span>
            <div class="row-acts">
              <form action="cal_imp_vis.php?r=1" method="post" style="display:contents">
                <input type="hidden" name="id_impegno" value="<?= (int)$r["id"] ?>" />
                <button type="submit" class="icon-btn" title="Dettagli">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
                </button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
          <?php foreach ($res_scad as $r): ?>
          <div class="list__row">
            <span data-label="Tipo">scad</span>
            <span data-label="Data"><?= globo_converti_data_in_formato_ita($r["data"]) ?></span>
            <span data-label="Descrizione"><?= $r["descrizione"] /* già HTML: grezzo */ ?></span>
            <div class="row-acts">
              <form action="cal_scad_vis.php?r=1" method="post" style="display:contents">
                <input type="hidden" name="id_scadenza" value="<?= (int)$r["id"] ?>" />
                <button type="submit" class="icon-btn" title="Dettagli">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
                </button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
