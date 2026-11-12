<?php require("varie/common_1.php");

  // Gestione utenti riservata all'amministratore globale (livello 0 = ks-globo).
  globo_richiedi_accesso($_SESSION["ksglobo_livello_utente"] == 0);

  $res = $db->query("SELECT * FROM utente WHERE livello > 0 ORDER BY livello, nome_utente")->fetchAll();
  $num = count($res);

  $show_email = MOD_CALENDARIO || MOD_PROGETTI;
  $list_cols  = $show_email ? '2fr 2fr 1fr 58px' : '2fr 1fr 58px';

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Gestione utenti</h1>
        <?php if ($num > 0): ?>
        <p class="page__sub"><?= $num ?> utent<?= $num == 1 ? 'e' : 'i' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="ut_ins_1.php">Nuovo utente</a>
      </div>
    </div>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun utente.</p>
    </div>
    <?php else: ?>
    <div class="list list--left" style="--list-cols: <?= $list_cols ?>">
      <div class="list__hdr">
        <span>Nome utente</span>
        <?php if ($show_email): ?><span>E-mail</span><?php endif; ?>
        <span>Ruolo</span>
        <span style="text-align:center; white-space:nowrap">Dett.</span>
      </div>
      <?php foreach ($res as $r):
        $u_nome  = htmlspecialchars($r["nome_utente"]);
        $u_email = htmlspecialchars($r["email"]);
        $u_ruolo = $r["livello"] == 1 ? 'amministratore' : 'utente';
        $u_href  = 'ut_vis.php?nome_utente=' . urlencode($r["nome_utente"]);
      ?>
      <div class="list__row" onclick="window.location.href='<?= $u_href ?>'">
        <span data-label="Nome utente"><?= $u_nome ?></span>
        <?php if ($show_email): ?><span data-label="E-mail"><?= $u_email ?></span><?php endif; ?>
        <span data-label="Ruolo"><?= $u_ruolo ?></span>
        <div class="row-acts">
          <a class="icon-btn" href="<?= $u_href ?>" onclick="event.stopPropagation()" title="Visualizza">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
