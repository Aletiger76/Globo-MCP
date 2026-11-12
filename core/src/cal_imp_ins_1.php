<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $data    = $_POST["data"]    ?? $_GET["data"]    ?? '';
  $cal_vis = $_POST["cal_vis"] ?? $_GET["cal_vis"] ?? $_SESSION["ksglobo_nome_utente"];

  // Legge e cancella il prefill di sessione (redirect da errore overlap)
  $prefill = null;
  if (!empty($_SESSION['form_prefill_imp'])) {
    $prefill = $_SESSION['form_prefill_imp'];
    unset($_SESSION['form_prefill_imp']);
  }

  $ora          = $prefill['ora']          ?? $_POST["ora"]         ?? -1;
  $durata       = $prefill['durata']       ?? $_POST["durata"]      ?? -1;
  $descrizione  = $prefill
    ? globo_converti_acapo_da_html($prefill['descrizione'])
    : (isset($_POST["descrizione"]) ? globo_converti_acapo_da_html($_POST["descrizione"]) : '');
  $responsabile = $prefill['responsabile'] ?? $_POST["responsabile"] ?? $cal_vis;

  $anno   = date("Y", strtotime($data));
  $mese   = date("m", strtotime($data));
  $giorno = date("d", strtotime($data));
  $giorno_lettere = strtolower(globo_giorno_in_lettere(date("w", strtotime($data))));

  $res_utenti      = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();
  $res_utenti_liv_1 = $db->query("SELECT nome_utente FROM utente WHERE livello = 1 ORDER BY nome_utente")->fetchAll();
  $num_utenti_liv_1 = count($res_utenti_liv_1);

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

    <form name="globo" action="cal_imp_ins_2.php" method="post">
      <input type="hidden" name="data"    value="<?= htmlspecialchars($data) ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo impegno</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Data</span></div>
              <div class="form__control"><?= htmlspecialchars($giorno_lettere) ?> <?= globo_converti_data_in_formato_ita($data) ?></div>
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
                    <option value="24:00"<?= "24:00" == $durata ? ' selected' : '' ?>>tutto il giorno</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione" class="textarea" rows="7" required><?= htmlspecialchars($descrizione) ?></textarea>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Responsabile</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="responsabile">
                    <option value="tutti"<?= $responsabile == 'tutti' ? ' selected' : '' ?>>tutti</option>
                    <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
                    <option value="amm"<?= $responsabile == 'amm' ? ' selected' : '' ?>>amministratori (<?= $num_utenti_liv_1 ?>)</option>
                    <?php endif; ?>
                    <?php foreach ($res_utenti as $r): ?>
                    <option value="<?= htmlspecialchars($r["nome_utente"]) ?>"<?= $responsabile == $r["nome_utente"] ? ' selected' : '' ?>><?= htmlspecialchars($r["nome_utente"]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Invia notifica</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifica">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_lista').submit()"><?= globo_converti_data_in_formato_ita($data) ?></button>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_home').submit()">Calendario</button>
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

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
