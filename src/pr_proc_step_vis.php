<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? $_GET["id_progetto_procedura"] ?? '';
  $id_progetto_procedura_step = $_POST["id_progetto_procedura_step"] ?? $_GET["id_progetto_procedura_step"] ?? '';

  $stmtStep = $db->prepare("SELECT * FROM progetto_procedura_step WHERE id = ?");
  $stmtStep->execute([$id_progetto_procedura_step]);
  $res = $stmtStep->fetchAll();
  $descrizione = globo_converti_acapo_da_html($res[0]["descrizione"]);
  $data_scadenza = $res[0]["data_scadenza"];

  if (isset($_POST["agg_annuale"]) && $_POST["agg_annuale"] == 1) {
    $flag_rigenera_id = 1;
    $anno_scadenza = date("Y", strtotime($data_scadenza)) + 1;
    $mese_scadenza = date("m", strtotime($data_scadenza));
    $giorno_scadenza = date("d", strtotime($data_scadenza));
    $data_scadenza = $anno_scadenza . "-" . $mese_scadenza . "-" . $giorno_scadenza;
  } else {
    $flag_rigenera_id = 0;
  }
  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Step procedura</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_proced_vis.php?id=<?= (int)$id_progetto_procedura ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza procedura
        </a>
      </div>
    </div>

    <form name="globo" action="pr_proc_step_agg.php" method="post">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
      <input type="hidden" name="id_progetto_procedura_step" value="<?= (int)$id_progetto_procedura_step ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati step</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione" class="textarea" rows="7" required><?= $descrizione /* già HTML (entità + \n): grezzo */ ?></textarea>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data scadenza <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_scadenza"
                  value="<?= htmlspecialchars(globo_converti_data_in_formato_ita($data_scadenza)) ?>"
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label">Rigenera id</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_rigenera_id">
                    <option value="0"<?= $flag_rigenera_id == 0 ? ' selected' : '' ?>>no</option>
                    <option value="1"<?= $flag_rigenera_id == 1 ? ' selected' : '' ?>>sì</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_proced_vis.php?id=<?= (int)$id_progetto_procedura ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <?php if (!isset($_POST["agg_annuale"])): ?>
    <form action="pr_proc_step_vis.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
      <input type="hidden" name="id_progetto_procedura_step" value="<?= (int)$id_progetto_procedura_step ?>" />
      <input type="hidden" name="agg_annuale" value="1" />
      <button type="submit" class="btn btn--ghost">Modifica dati per agg annuale</button>
    </form>
    <?php endif; ?>

    <form action="pr_proc_step_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
      <input type="hidden" name="id_progetto_procedura_step" value="<?= (int)$id_progetto_procedura_step ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo step?">Elimina step</button>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
