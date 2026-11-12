<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);
  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuovo template</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_template_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco template
        </a>
      </div>
    </div>

    <form name="globo" action="pr_template_ins_2.php" method="post">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati template</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="descrizione" value="" required />
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_template_lista.php">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
