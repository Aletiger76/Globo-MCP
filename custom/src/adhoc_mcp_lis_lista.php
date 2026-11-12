<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Design system del core.
$db = NULL;
try {
  $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_MCP . ";charset=utf8mb4", MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
  mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
  header("Location: index.php?e=0");
  exit();
}

$res = $db->query("SELECT * FROM listino ORDER BY id")->fetchAll();
$num = count($res);
$num_ser = $db->query("SELECT COUNT(*) FROM servizio")->fetchColumn();

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Listini</h1>
        <?php if ($num > 0): ?>
        <p class="page__sub"><?= $num ?> <?= $num == 1 ? 'listino' : 'listini' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($num_ser > 0): ?>
        <a class="btn btn--ghost" href="adhoc_mcp_lis_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo listino
        </a>
        <?php else: ?>
        <button type="button" class="btn btn--ghost" disabled title="Serve almeno un servizio">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo listino
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun listino.</p>
    </div>
    <?php else: ?>
    <div class="list" style="--list-cols: 1fr 110px 88px">
      <div class="list__hdr">
        <div>Nome</div>
        <div style="text-align:center">Clienti</div>
        <div style="text-align:center; white-space:nowrap">Azioni</div>
      </div>
      <?php
        $stmt_cli = $db->prepare("SELECT COUNT(*) FROM cliente WHERE id_listino = ?");
      ?>
      <?php foreach ($res as $r):
        $id_listino = $r["id"];
        $stmt_cli->execute([$id_listino]);
        $num_cli = $stmt_cli->fetchColumn();
      ?>
      <div class="list__row">
        <div data-label="Nome"><span class="list-name"><?= $r["nome"] ?></span></div>
        <div data-label="Clienti" style="justify-content:center"><?= $num_cli ?></div>
        <div class="row-acts">
          <?php if ($num_ser > 0): ?>
          <a class="icon-btn" href="adhoc_mcp_lis_vis.php?id=<?= (int)$id_listino ?>" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($id_listino > 1): ?>
          <form action="adhoc_mcp_lis_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_listino" value="<?= (int)$id_listino ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare il listino «<?= htmlspecialchars($r["nome"]) ?>»?" data-confirm-title="Elimina listino">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.list -->
    <?php endif; ?>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
