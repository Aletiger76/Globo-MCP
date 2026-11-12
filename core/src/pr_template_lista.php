<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $query = "SELECT * FROM progetto_template ORDER BY descrizione";
  $res = $db->query($query)->fetchAll();
  $num = count($res);

  $stmtStep = $db->prepare("SELECT id FROM progetto_template_step WHERE id_progetto_template = ?");

  $rows = [];
  $num_step_tot = 0;

  foreach ($res as $r) {
    $stmtStep->execute([$r["id"]]);
    $ns = count($stmtStep->fetchAll());
    $num_step_tot += $ns;
    $rows[] = ['id' => $r["id"], 'descrizione' => $r["descrizione"], 'step' => $ns];
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Template progetti</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_template_ins_1.php">Nuovo template</a>
      </div>
    </div>

    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Elenco template</span>
      </div>
      <?php if ($num == 0): ?>
      <p class="empty-state">Nessun template.</p>
      <?php else: ?>
      <div class="list" style="--list-cols: 1fr 90px 58px">
        <div class="list__hdr">
          <span>Descrizione</span>
          <span>Step</span>
          <span style="text-align:center; white-space:nowrap">Dett.</span>
        </div>
        <?php foreach ($rows as $row): ?>
        <div class="list__row" onclick="window.location.href='pr_template_vis.php?id=<?= (int)$row['id'] ?>'">
          <span data-label="Descrizione"><?= $row['descrizione'] /* già HTML: grezzo */ ?></span>
          <span data-label="Step"><?= $row['step'] ?></span>
          <div class="row-acts">
            <a class="icon-btn" href="pr_template_vis.php?id=<?= (int)$row['id'] ?>" onclick="event.stopPropagation()" title="Dettagli">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($num > 0): ?>
      <div class="card__foot" style="justify-content:flex-start; color:var(--fg-2); font-size:13px">
        Totale: <?= $num_step_tot ?> step
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
