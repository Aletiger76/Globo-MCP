<?php require("varie/common_1.php");

  // Gestione template/procedure progetti riservata all'admin globale (livello 0).
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] == 0);

  $id_progetto_procedura = $_POST["id_progetto_procedura"] ?? $_GET["id"] ?? '';

  $stmtProced = $db->prepare("SELECT * FROM progetto_procedura WHERE id = ?");
  $stmtProced->execute([$id_progetto_procedura]);
  $res = $stmtProced->fetchAll();
  $descrizione = $res[0]["descrizione"];

  $stmtProg = $db->prepare("SELECT id FROM progetto WHERE id_progetto_procedura = ?");
  $stmtProg->execute([$id_progetto_procedura]);
  $num_prog = count($stmtProg->fetchAll());

  $stmtStep = $db->prepare("SELECT * FROM progetto_procedura_step WHERE id_progetto_procedura = ? ORDER BY data_scadenza, id");
  $stmtStep->execute([$id_progetto_procedura]);
  $res_step = $stmtStep->fetchAll();
  $num_step = count($res_step);

  $stmtChiusi = $db->prepare("SELECT COUNT(*) AS num_chiusi FROM progetto_procedura_step_chiuso WHERE id_progetto_procedura_step = ?");
  $stmtNonAttivi = $db->prepare("SELECT COUNT(*) AS num_non_attivi FROM progetto_procedura_step_non_attivo WHERE id_progetto_procedura_step = ?");

  $rows = [];
  foreach ($res_step as $r) {
    $stmtChiusi->execute([$r["id"]]);
    $rc = $stmtChiusi->fetchAll();
    $stmtNonAttivi->execute([$r["id"]]);
    $rna = $stmtNonAttivi->fetchAll();
    $rows[] = [
      'id' => $r["id"],
      'descrizione' => $r["descrizione"],
      'data_scadenza' => globo_converti_data_in_formato_ita($r["data_scadenza"]),
      'num_chiusi' => $rc[0]["num_chiusi"],
      'num_non_attivi' => $rna[0]["num_non_attivi"],
    ];
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Procedura progetto</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="pr_proced_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco procedure
        </a>
      </div>
    </div>

    <form name="globo" action="pr_proced_agg.php" method="post">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati procedura</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label">Id</div>
              <div class="form__control"><?= (int)$id_progetto_procedura ?></div>
            </div>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="descrizione" value="<?= $descrizione /* già HTML: grezzo, il browser decodifica nell'attributo */ ?>" required />
              </div>
            </div>
            <div class="form__row">
              <div class="form__label">Progetti</div>
              <div class="form__control"><?= $num_prog ?></div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="pr_proced_lista.php">Annulla</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <div class="card" style="margin-top:16px">
      <div class="card__head">
        <span class="card__head-eyebrow">Step procedura</span>
      </div>
      <?php if ($num_step == 0): ?>
      <p class="empty-state">Nessuno step.</p>
      <?php else: ?>
      <div class="list" style="--list-cols: 1fr 140px 90px 110px 58px">
        <div class="list__hdr">
          <span>Descrizione</span>
          <span>Data scadenza</span>
          <span>Chiusi</span>
          <span>Non attivi</span>
          <span style="text-align:center; white-space:nowrap">Dett.</span>
        </div>
        <?php foreach ($rows as $row): ?>
        <div class="list__row" onclick="window.location.href='pr_proc_step_vis.php?id_progetto_procedura=<?= (int)$id_progetto_procedura ?>&id_progetto_procedura_step=<?= (int)$row['id'] ?>'">
          <span data-label="Descrizione"><?= $row['descrizione'] /* già HTML: grezzo */ ?></span>
          <span data-label="Data scadenza"><?= htmlspecialchars($row['data_scadenza']) ?></span>
          <span data-label="Chiusi"><?= $row['num_chiusi'] ?></span>
          <span data-label="Non attivi"><?= $row['num_non_attivi'] ?></span>
          <div class="row-acts">
            <a class="icon-btn" href="pr_proc_step_vis.php?id_progetto_procedura=<?= (int)$id_progetto_procedura ?>&id_progetto_procedura_step=<?= (int)$row['id'] ?>" onclick="event.stopPropagation()" title="Dettagli">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="card__foot" style="justify-content:flex-start">
        <a class="btn btn--ghost" href="pr_proc_step_ins_1.php?id=<?= (int)$id_progetto_procedura ?>">Nuovo step</a>
      </div>
    </div>

    <form action="pr_proced_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto_procedura" value="<?= (int)$id_progetto_procedura ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questa procedura?">Elimina procedura</button>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
