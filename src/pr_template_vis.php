<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_template = $_POST["id_progetto_template"] ?? $_GET["id"] ?? '';

  $stmtTempl = $db->prepare("SELECT * FROM progetto_template WHERE id = ?");
  $stmtTempl->execute([$id_progetto_template]);
  $res = $stmtTempl->fetchAll();
  $descrizione = $res[0]["descrizione"];

  $stmtStep = $db->prepare("SELECT * FROM progetto_template_step WHERE id_progetto_template = ? ORDER BY giorni_scadenza, id");
  $stmtStep->execute([$id_progetto_template]);
  $res_step = $stmtStep->fetchAll();
  $num_step = count($res_step);

  $rows = [];
  foreach ($res_step as $r) {
    $rows[] = [
      'id' => $r["id"],
      'descrizione' => $r["descrizione"],
      'giorni_scadenza' => $r["giorni_scadenza"],
    ];
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Template progetto</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_template_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco template
        </a>
      </div>
    </div>

    <form name="globo" action="pr_template_agg.php" method="post">
      <input type="hidden" name="id_progetto_template" value="<?= (int)$id_progetto_template ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati template</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label">Id</div>
              <div class="form__control"><?= (int)$id_progetto_template ?></div>
            </div>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="descrizione" value="<?= $descrizione /* già HTML: grezzo */ ?>" required />
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_template_lista.php">Annulla</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <div class="card" style="margin-top:16px">
      <div class="card__head">
        <span class="card__head-eyebrow">Step template</span>
      </div>
      <?php if ($num_step == 0): ?>
      <p class="empty-state">Nessuno step.</p>
      <?php else: ?>
      <div class="list" style="--list-cols: 1fr 140px 58px">
        <div class="list__hdr">
          <span>Descrizione</span>
          <span>Giorni scadenza</span>
          <span style="text-align:center; white-space:nowrap">Dett.</span>
        </div>
        <?php foreach ($rows as $row): ?>
        <div class="list__row" onclick="window.location.href='pr_tem_step_vis.php?id_progetto_template=<?= (int)$id_progetto_template ?>&id_progetto_template_step=<?= (int)$row['id'] ?>'">
          <span data-label="Descrizione"><?= $row['descrizione'] /* già HTML: grezzo */ ?></span>
          <span data-label="Giorni scadenza"><?= htmlspecialchars($row['giorni_scadenza']) ?></span>
          <div class="row-acts">
            <a class="icon-btn" href="pr_tem_step_vis.php?id_progetto_template=<?= (int)$id_progetto_template ?>&id_progetto_template_step=<?= (int)$row['id'] ?>" onclick="event.stopPropagation()" title="Dettagli">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="card__foot" style="justify-content:flex-start">
        <a class="btn btn--ghost" href="pr_tem_step_ins_1.php?id=<?= (int)$id_progetto_template ?>">Nuovo step</a>
      </div>
    </div>

    <form action="pr_template_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto_template" value="<?= (int)$id_progetto_template ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo template?">Elimina template</button>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
