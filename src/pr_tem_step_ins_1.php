<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? $_GET["id"] ?? '';

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuovo step template</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_template_vis.php?id=<?= (int)$id_progetto_template ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza template
        </a>
      </div>
    </div>

    <form name="globo" action="pr_tem_step_ins_2.php" method="post">
      <input type="hidden" name="id_progetto_template" value="<?= (int)$id_progetto_template ?>" />
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
              <div class="form__label">Giorni scadenza</div>
              <div class="form__control">
                <input type="number" class="input" style="max-width:80px" min="0" name="giorni_scadenza" value="" />
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_template_vis.php?id=<?= (int)$id_progetto_template ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
