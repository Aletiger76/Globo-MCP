<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $data    = $_POST["data"]    ?? '';
  $cal_vis = isset($_POST["cal_vis"]) ? $_POST["cal_vis"] : $_SESSION["ksglobo_nome_utente"];

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));
  $giorno_lettere = strtolower(globo_giorno_in_lettere(date("w", strtotime($data))));

  $res_utenti = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();

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

    <form name="globo" action="cal_scad_ins_2.php" method="post">
      <input type="hidden" name="data"    value="<?= htmlspecialchars($data) ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
      <?php if ($_SESSION["ksglobo_livello_utente"] > 1): ?>
      <input type="hidden" name="visibilita" value="2" />
      <?php endif; ?>

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuova scadenza</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Visibilità</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1" selected>amministratori</option>
                    <option value="2">tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Data</span></div>
              <div class="form__control"><?= htmlspecialchars($giorno_lettere) ?> <?= globo_converti_data_in_formato_ita($data) ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione" class="textarea" rows="7" required></textarea>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Responsabile</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <?php /* Default = utente corrente (non più "tutti" fisso): chi inserisce la
                           scadenza è normalmente anche il responsabile. L'utente loggato è
                           sempre tra $res_utenti (livello > 0, come chi ha fatto login). */ ?>
                  <select name="responsabile">
                    <option value="tutti">tutti</option>
                    <?php foreach ($res_utenti as $r): ?>
                    <option value="<?= htmlspecialchars($r["nome_utente"]) ?>"<?= $r["nome_utente"] == $_SESSION["ksglobo_nome_utente"] ? ' selected' : '' ?>><?= htmlspecialchars($r["nome_utente"]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ricorrenza</span></div>
              <div class="form__control form__control--multi" style="gap:6px; display:flex; flex-direction:column; padding:10px 0">
                <label style="display:flex; align-items:center; gap:8px"><input type="radio" name="flag_ricorrenza" value="1" checked /> unica</label>
                <label style="display:flex; align-items:center; gap:8px"><input type="radio" name="flag_ricorrenza" value="2" /> mensile *</label>
                <label style="display:flex; align-items:center; gap:8px"><input type="radio" name="flag_ricorrenza" value="3" /> annuale *</label>
                <div style="display:flex; align-items:center; gap:8px; color:var(--fg-2); font-size:13px">* numero di ricorrenze: <input type="text" class="input" style="width:60px" maxlength="2" name="num_ricorrenze" id="num_ricorrenze" /></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_lista').submit()">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            <?= globo_converti_data_in_formato_ita($data) ?>
          </button>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_home').submit()">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Calendario
          </button>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

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

  <script>
  (function() {
    var radios  = document.querySelectorAll('[name="flag_ricorrenza"]');
    var numInp  = document.getElementById('num_ricorrenze');
    function aggiornaRequired() {
      var val = document.querySelector('[name="flag_ricorrenza"]:checked').value;
      numInp.required = (val === '2' || val === '3');
    }
    radios.forEach(function(r) { r.addEventListener('change', aggiornaRequired); });
    aggiornaRequired();
  })();
  </script>
  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
