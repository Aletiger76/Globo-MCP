<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? $_GET["id_progetto_template"] ?? '';
  $id_progetto_template_step = $_POST["id_progetto_template_step"] ?? $_GET["id_progetto_template_step"] ?? '';

  $stmtStep = $db->prepare("SELECT * FROM progetto_template_step WHERE id = ?");
  $stmtStep->execute([$id_progetto_template_step]);
  $res = $stmtStep->fetchAll();
  $descrizione = globo_converti_acapo_da_html($res[0]["descrizione"]);
  $giorni_scadenza = $res[0]["giorni_scadenza"];

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Step template</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_template_vis.php?id=<?= (int)$id_progetto_template ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza template
        </a>
      </div>
    </div>

    <form name="globo" action="pr_tem_step_agg.php" method="post">
      <input type="hidden" name="id_progetto_template" value="<?= (int)$id_progetto_template ?>" />
      <input type="hidden" name="id_progetto_template_step" value="<?= (int)$id_progetto_template_step ?>" />
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
              <div class="form__label">Giorni scadenza</div>
              <div class="form__control">
                <input type="number" class="input" style="max-width:80px" min="0" name="giorni_scadenza" value="<?= htmlspecialchars($giorni_scadenza) ?>" />
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_template_vis.php?id=<?= (int)$id_progetto_template ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <form action="pr_tem_step_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto_template" value="<?= (int)$id_progetto_template ?>" />
      <input type="hidden" name="id_progetto_template_step" value="<?= (int)$id_progetto_template_step ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo step?">Elimina step</button>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
