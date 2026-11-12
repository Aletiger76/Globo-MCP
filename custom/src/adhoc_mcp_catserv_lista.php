<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Design system del core,
// stesso pattern di adhoc_mcp_cli_lista.php (senza ricerca: lista corta).
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

$res = $db->query("SELECT * FROM categoria_servizio ORDER BY id")->fetchAll();
$num = count($res);

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Categorie servizi</h1>
        <?php if ($num > 0): ?>
        <p class="page__sub"><?= $num ?> <?= $num == 1 ? 'categoria' : 'categorie' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_catserv_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuova categoria
        </a>
      </div>
    </div>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessuna categoria servizio.</p>
    </div>
    <?php else: ?>
    <div class="list" style="--list-cols: 64px 1fr 88px">
      <div class="list__hdr">
        <div style="text-align:center">Id</div>
        <div>Nome</div>
        <div style="text-align:center; white-space:nowrap">Azioni</div>
      </div>
      <?php foreach ($res as $r): ?>
      <div class="list__row">
        <div data-label="Id" style="justify-content:center"><span class="list-meta"><?= $r["id"] ?></span></div>
        <div data-label="Nome"><span class="list-name"><?= $r["nome"] ?></span></div>
        <div class="row-acts">
          <a class="icon-btn" href="adhoc_mcp_catserv_vis.php?id=<?= (int)$r["id"] ?>" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
          <form action="adhoc_mcp_catserv_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_categoria_servizio" value="<?= (int)$r["id"] ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare la categoria «<?= htmlspecialchars($r["nome"]) ?>»?" data-confirm-title="Elimina categoria servizio">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.list -->
    <?php endif; ?>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
