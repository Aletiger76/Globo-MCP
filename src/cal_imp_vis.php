<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $id_impegno = $_POST["id_impegno"] ?? $_GET["id_impegno"] ?? '';
  $cal_vis    = $_POST["cal_vis"]    ?? $_GET["cal_vis"]    ?? $_SESSION["ksglobo_nome_utente"];

  $res_utenti = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();

  $stmtImp = $db->prepare("SELECT * FROM impegno WHERE id = ?");
  $stmtImp->execute([$id_impegno]);
  $res = $stmtImp->fetchAll();

  $data       = $res[0]["data"];
  $ora        = $res[0]["ora"];
  $durata     = $res[0]["durata"];
  if ($durata == "24:00") $ora = globo_converti_ora_da_dec(CAL_ORA_INIZIO_GIO);
  $descrizione = globo_converti_acapo_da_html($res[0]["descrizione"]);
  $responsabile = $res[0]["responsabile"];
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];
  $dt_ultima_modifica = $res[0]["dt_ultima_modifica"];

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));

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

    <form name="globo" action="cal_imp_agg.php" method="post">
      <input type="hidden" name="id_impegno" value="<?= (int)$id_impegno ?>" />
      <input type="hidden" name="cal_vis"    value="<?= htmlspecialchars($cal_vis) ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli impegno</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
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
              <div class="form__label"><span class="form__label-name">Ora</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ora">
                    <?php $ora_dec = CAL_ORA_INIZIO_GIO; while ($ora_dec <= CAL_ORA_FINE_GIO): $ora_vis = globo_converti_ora_da_dec($ora_dec); ?>
                    <option value="<?= $ora_vis ?>"<?= $ora_vis == $ora ? ' selected' : '' ?>><?= $ora_vis ?></option>
                    <?php $ora_dec += 0.5; endwhile; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Durata</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="durata">
                    <?php $dur_dec = 0.5; while ($dur_dec <= CAL_MAX_DURATA_IMP): $dur_vis = globo_converti_ora_da_dec($dur_dec); ?>
                    <option value="<?= $dur_vis ?>"<?= $dur_vis == $durata ? ' selected' : '' ?>><?= $dur_vis ?></option>
                    <?php $dur_dec += 0.5; endwhile; ?>
                    <option value="24:00"<?= $durata == "24:00" ? ' selected' : '' ?>>tutto il giorno</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione" class="textarea" rows="7" required><?= $descrizione /* già HTML (entità + \n): grezzo */ ?></textarea>
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
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_ric').submit()">Ritorna alla ricerca</button>
          <?php endif; ?>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_lista').submit()"><?= globo_converti_data_in_formato_ita($data) ?></button>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_home').submit()">Calendario</button>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <form action="cal_imp_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_impegno" value="<?= (int)$id_impegno ?>" />
      <input type="hidden" name="data"        value="<?= htmlspecialchars($data) ?>" />
      <input type="hidden" name="cal_vis"     value="<?= htmlspecialchars($cal_vis) ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo impegno?">Elimina</button>
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

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
