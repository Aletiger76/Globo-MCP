<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $data_oggi = date("Y-m-d");

  $query_utenti = "SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente";
  $res_utenti = $db->query($query_utenti)->fetchAll();
  $num_utenti = count($res_utenti);

  $query_templ = "SELECT * FROM progetto_template ORDER BY descrizione";
  $res_templ = $db->query($query_templ)->fetchAll();
  $num_templ = count($res_templ);

  $query_proc = "SELECT * FROM progetto_procedura ORDER BY descrizione";
  $res_proc = $db->query($query_proc)->fetchAll();
  $num_proc = count($res_proc);
  require("varie/head.php");
?>
<script>
function cambiaTipoProgetto() {
  var tipo    = document.globo.tipo.value;
  var selT    = document.globo.id_progetto_template;
  var selP    = document.globo.id_progetto_procedura;
  var obbT    = document.getElementById("template_obb");
  var obbP    = document.getElementById("procedura_obb");
  if (tipo == 0) {
    if (selT != null) { selT.selectedIndex = 0; selT.disabled = true; selT.required = false; document.globo.responsabile_template.disabled = true; }
    if (selP != null) { selP.selectedIndex = 0; selP.disabled = true; selP.required = false; }
    if (obbT != null) obbT.style.visibility = "hidden";
    if (obbP != null) obbP.style.visibility = "hidden";
  }
  if (tipo == 1) {
    if (selT != null) { selT.selectedIndex = 0; selT.disabled = false; selT.required = true; document.globo.responsabile_template.disabled = false; }
    if (selP != null) { selP.disabled = true; selP.required = false; }
    if (obbT != null) obbT.style.visibility = "visible";
    if (obbP != null) obbP.style.visibility = "hidden";
  }
  if (tipo == 2) {
    if (selT != null) { selT.disabled = true; selT.required = false; document.globo.responsabile_template.disabled = true; }
    if (selP != null) { selP.selectedIndex = 0; selP.disabled = false; selP.required = true; }
    if (obbT != null) obbT.style.visibility = "hidden";
    if (obbP != null) obbP.style.visibility = "visible";
  }
}
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuovo progetto</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="prog_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco progetti
        </a>
      </div>
    </div>

    <form name="globo" action="prog_ins_2.php" method="post">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati progetto</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label">Visibilità</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1">amministratori</option>
                    <option value="2" selected>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="visibilita" value="2" />
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_creazione" required
                  value="<?= globo_converti_data_in_formato_ita($data_oggi) ?>"
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Nome <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="nome_progetto" value="" required />
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_progetto" class="textarea" rows="6" required></textarea>
              </div>
            </div>

            <?php if ($num_templ > 0 || $num_proc > 0): ?>
            <div class="form__row">
              <div class="form__label">Tipo</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="tipo" onchange="cambiaTipoProgetto()">
                    <option value="0">progetto generico</option>
                    <?php if ($num_templ > 0): ?><option value="1">progetto con template</option><?php endif; ?>
                    <?php if ($num_proc > 0): ?><option value="2">procedura (progetto con scadenze annuali)</option><?php endif; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="tipo" value="0" />
            <?php endif; ?>

            <?php if ($num_templ > 0): ?>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Template <span id="template_obb" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control" style="gap:16px;flex-wrap:wrap">
                <div class="select-wrap">
                  <select name="id_progetto_template" disabled>
                    <option value="0">—</option>
                    <?php foreach ($res_templ as $r_templ): ?>
                    <option value="<?= $r_templ['id'] ?>"><?= htmlspecialchars($r_templ['descrizione']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <span style="font-size:13px;font-weight:600;color:var(--fg-2)">Resp. scad.</span>
                <div class="select-wrap">
                  <select name="responsabile_template" disabled>
                    <?php foreach ($res_utenti as $r_utenti):
                      $nu = $r_utenti['nome_utente'];
                    ?>
                    <option value="<?= htmlspecialchars($nu) ?>"<?= $nu == $_SESSION["ksglobo_nome_utente"] ? ' selected' : '' ?>><?= htmlspecialchars($nu) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="id_progetto_template" value="0" />
            <?php endif; ?>

            <?php if ($num_proc > 0): ?>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Procedura <span id="procedura_obb" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_progetto_procedura" disabled>
                    <option value="0">—</option>
                    <?php foreach ($res_proc as $r_proc): ?>
                    <option value="<?= $r_proc['id'] ?>"><?= htmlspecialchars($r_proc['descrizione']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="id_progetto_procedura" value="0" />
            <?php endif; ?>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
        <div class="card__foot">
          <a class="btn btn--ghost" href="prog_lista.php">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
