<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_scadenza = $_POST["id_scadenza"] ?? $_GET["id_scadenza"] ?? '';
  $cal_vis = $_POST["cal_vis"] ?? $_GET["cal_vis"] ?? $_SESSION["ksglobo_nome_utente"];

  $res_utenti = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();

  $stmtScad = $db->prepare("SELECT * FROM scadenza WHERE id = ?");
  $stmtScad->execute([$id_scadenza]);
  $res = $stmtScad->fetchAll();

  $data        = $res[0]["data"];
  $tipo        = $res[0]["tipo"];
  $visibilita  = $res[0]["visibilita"];
  $id_progetto = $res[0]["id_progetto"];
  $descrizione = $res[0]["descrizione"];
  $responsabile = $res[0]["responsabile"];
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];
  $dt_ultima_modifica = $res[0]["dt_ultima_modifica"];

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));

  $is_memo      = $tipo == "memo";
  $is_bilancio  = ($tipo == "bilancio_scad_pag" || $tipo == "bilancio_rinnovo");
  $is_progetto  = substr_count($tipo, "progetto") > 0;
  $is_readonly  = !$is_memo;

  if ($is_memo) {
    $descrizione_form = globo_converti_acapo_da_html($descrizione);
  } elseif ($is_bilancio) {
    $descrizione_display = "scadenza bilancio<br /><br />" . $descrizione;
  } elseif ($is_progetto) {
    $descrizione_display = "scadenza progetto<br /><br />" . $descrizione;
  }

  if ($is_bilancio)    $eyebrow = 'Scadenza bilancio';
  elseif ($is_progetto) $eyebrow = 'Scadenza progetto';
  else                  $eyebrow = 'Dettagli scadenza';

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Calendario</h1>
      </div>
    </div>

    <?php if ($is_memo): ?>

    <form name="globo" action="cal_scad_agg.php" method="post">
      <input type="hidden" name="id_scadenza" value="<?= (int)$id_scadenza ?>" />
      <input type="hidden" name="cal_vis"     value="<?= htmlspecialchars($cal_vis) ?>" />
      <?php if ($_SESSION["ksglobo_livello_utente"] > 1): ?>
      <input type="hidden" name="visibilita" value="2" />
      <?php endif; ?>

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow"><?= htmlspecialchars($eyebrow) ?></span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Visibilità</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1"<?= $visibilita == "1" ? ' selected' : '' ?>>amministratori</option>
                    <option value="2"<?= $visibilita == "2" ? ' selected' : '' ?>>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Data <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data"
                  value="<?= htmlspecialchars(globo_converti_data_in_formato_ita($data)) ?>"
                  data-input autocomplete="off" required />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione" class="textarea" rows="7" required><?= $descrizione_form /* già HTML (entità + \n): grezzo */ ?></textarea>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Responsabile</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="responsabile">
                    <option value="tutti"<?= $responsabile == "tutti" ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_utenti as $r): ?>
                    <option value="<?= htmlspecialchars($r["nome_utente"]) ?>"<?= $responsabile == $r["nome_utente"] ? ' selected' : '' ?>><?= htmlspecialchars($r["nome_utente"]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ultima modifica</span></div>
              <div class="form__control"><?= htmlspecialchars($utente_ultima_modifica) ?> — <?= globo_converti_datetime_in_formato_ita($dt_ultima_modifica) ?></div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <?php if (isset($_GET["r"]) && $_GET["r"] == 1): ?>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_ric').submit()">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Ritorna alla ricerca
          </button>
          <?php endif; ?>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_lista').submit()">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            <?= globo_converti_data_in_formato_ita($data) ?>
          </button>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_home').submit()">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Calendario
          </button>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <form action="cal_scad_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_scadenza" value="<?= (int)$id_scadenza ?>" />
      <input type="hidden" name="data"         value="<?= htmlspecialchars($data) ?>" />
      <input type="hidden" name="cal_vis"      value="<?= htmlspecialchars($cal_vis) ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questa scadenza?">Elimina</button>
    </form>

    <?php if (isset($_GET["r"]) && $_GET["r"] == 1): ?>
    <form id="frm_ric" action="cal_ric.php" method="post" style="display:none"></form>
    <?php endif; ?>
    <form id="frm_lista" action="cal_lista.php" method="post" style="display:none">
      <input type="hidden" name="giorno"  value="<?= $giorno ?>" />
      <input type="hidden" name="mese"    value="<?= $mese ?>" />
      <input type="hidden" name="anno"    value="<?= $anno ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
    </form>
    <form id="frm_home" action="cal_home.php" method="post" style="display:none">
      <input type="hidden" name="mese"    value="<?= $mese ?>" />
      <input type="hidden" name="anno"    value="<?= $anno ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
    </form>

    <?php else: /* ramo read-only (bilancio / progetto) */ ?>

    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow"><?= htmlspecialchars($eyebrow) ?></span>
      </div>
      <div class="card__body" style="padding:0">
        <div class="form">
          <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
          <div class="form__row">
            <div class="form__label"><span class="form__label-name">Visibilità</span></div>
            <div class="form__control"><?= $visibilita == "1" ? 'amministratori' : 'tutti' ?></div>
          </div>
          <?php endif; ?>
          <div class="form__row">
            <div class="form__label"><span class="form__label-name">Data</span></div>
            <div class="form__control"><?= globo_converti_data_in_formato_ita($data) ?></div>
          </div>
          <div class="form__row">
            <div class="form__label"><span class="form__label-name">Descrizione</span></div>
            <div class="form__control form__control--multi"><div><?= $descrizione_display ?></div></div>
          </div>
          <?php if ($is_progetto): ?>
          <div class="form__row">
            <div class="form__label"><span class="form__label-name">Responsabile</span></div>
            <div class="form__control"><?= htmlspecialchars($responsabile) ?></div>
          </div>
          <?php endif; ?>
          <div class="form__row">
            <div class="form__label"><span class="form__label-name">Ultima modifica</span></div>
            <div class="form__control"><?= htmlspecialchars($utente_ultima_modifica) ?> — <?= globo_converti_datetime_in_formato_ita($dt_ultima_modifica) ?></div>
          </div>
          <div class="form__row">
            <div class="form__label"></div>
            <div class="form__control" style="color:var(--err, red); font-size:13px">
              Da questa pagina è solamente possibile visualizzare la scadenza,
              <?php if ($is_bilancio): ?>
              per modificare o eliminare utilizzare la sezione bilancio.
              <?php elseif ($is_progetto): ?>
              per modificare o eliminare entrare nel relativo
              <?php if ($id_progetto > 0): ?>
              <a href="prog_vis.php?id=<?= (int)$id_progetto ?>" style="color:var(--err, red)"><strong>progetto</strong></a>.
              <?php else: ?>progetto.<?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="card__foot">
        <?php if (isset($_GET["r"]) && $_GET["r"] == 1): ?>
        <form action="cal_ric.php" method="post" style="display:contents">
          <button type="submit" class="btn btn--ghost">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Ritorna alla ricerca
          </button>
        </form>
        <?php endif; ?>
        <form action="cal_lista.php" method="post" style="display:contents">
          <input type="hidden" name="giorno"  value="<?= $giorno ?>" />
          <input type="hidden" name="mese"    value="<?= $mese ?>" />
          <input type="hidden" name="anno"    value="<?= $anno ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="btn btn--ghost">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            <?= globo_converti_data_in_formato_ita($data) ?>
          </button>
        </form>
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese"    value="<?= $mese ?>" />
          <input type="hidden" name="anno"    value="<?= $anno ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="btn btn--primary">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Calendario
          </button>
        </form>
      </div>
    </div>

    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
