<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? $_GET["id"] ?? '';
  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuovo step procedura</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_proced_vis.php?id=<?= (int)$id_progetto_procedura ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza procedura
        </a>
      </div>
    </div>

    <form name="globo" action="pr_proc_step_ins_2.php" method="post">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
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
                <textarea name="descrizione" class="textarea" rows="7" required></textarea>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data scadenza <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_scadenza" value=""
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_proced_vis.php?id=<?= (int)$id_progetto_procedura ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
