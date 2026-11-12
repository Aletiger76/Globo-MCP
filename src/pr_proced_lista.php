<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $query = "SELECT * FROM progetto_procedura ORDER BY descrizione";
  $res = $db->query($query)->fetchAll();
  $num = count($res);

  $stmtStep = $db->prepare("SELECT id FROM progetto_procedura_step WHERE id_progetto_procedura = ?");
  $stmtProg = $db->prepare("SELECT id FROM progetto WHERE id_progetto_procedura = ?");

  $rows = [];
  $num_step_tot = 0;
  $num_prog_tot = 0;

  foreach ($res as $r) {
    $stmtStep->execute([$r["id"]]);
    $ns = count($stmtStep->fetchAll());
    $stmtProg->execute([$r["id"]]);
    $np = count($stmtProg->fetchAll());
    $num_step_tot += $ns;
    $num_prog_tot += $np;
    $rows[] = ['id' => $r["id"], 'descrizione' => $r["descrizione"], 'step' => $ns, 'prog' => $np];
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Procedure progetti</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_proced_ins_1.php">Nuova procedura</a>
      </div>
    </div>

    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Elenco procedure</span>
      </div>
      <?php if ($num == 0): ?>
      <p class="empty-state">Nessuna procedura.</p>
      <?php else: ?>
      <div class="list" style="--list-cols: 1fr 90px 100px 58px">
        <div class="list__hdr">
          <span>Descrizione</span>
          <span>Step</span>
          <span>Progetti</span>
          <span style="text-align:center; white-space:nowrap">Dett.</span>
        </div>
        <?php foreach ($rows as $row): ?>
        <div class="list__row" onclick="window.location.href='pr_proced_vis.php?id=<?= (int)$row['id'] ?>'">
          <span data-label="Descrizione"><?= $row['descrizione'] /* già HTML: grezzo */ ?></span>
          <span data-label="Step"><?= $row['step'] ?></span>
          <span data-label="Progetti"><?= $row['prog'] ?></span>
          <div class="row-acts">
            <a class="icon-btn" href="pr_proced_vis.php?id=<?= (int)$row['id'] ?>" onclick="event.stopPropagation()" title="Dettagli">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="card__foot" style="justify-content:flex-start; color:var(--fg-2); font-size:13px">
        Totale: <?= $num_step_tot ?> step, <?= $num_prog_tot ?> progetti
      </div>
      <?php endif; ?>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
