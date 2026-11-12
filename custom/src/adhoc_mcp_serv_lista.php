<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-13. Design system del core.
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

if (isset($_POST["id_servizio"])) { // toggle flag_attivo del servizio
  $id_servizio = $_POST["id_servizio"];

  $stmt = $db->prepare("SELECT flag_attivo FROM servizio WHERE id = ?");
  $stmt->execute([$id_servizio]);
  $flag_attivo = $stmt->fetchColumn();

  $flag_attivo = $flag_attivo == 0 ? 1 : 0;

  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE servizio SET flag_attivo = ? WHERE id = ?");
  $stmt->execute([$flag_attivo, $id_servizio]);
  $db->commit();
}

// Alias espliciti per servizio.nome/categoria_servizio.nome: vedi nota in
// adhoc_mcp_lis_vis.php (SELECT * su JOIN con colonna "nome" ambigua).
$res = $db->query("SELECT servizio.id, servizio.flag_attivo, servizio.nome AS nome_servizio, servizio.livello, servizio.flag_acq_dir, categoria_servizio.nome AS nome_categoria FROM servizio JOIN categoria_servizio ON servizio.id_categoria_servizio = categoria_servizio.id ORDER BY servizio.id_categoria_servizio, servizio.id")->fetchAll();
$num = count($res);

$mostra_id = $_SESSION["ksglobo_livello_utente"] == 0;

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Servizi</h1>
        <?php if ($num > 0): ?>
        <p class="page__sub"><?= $num ?> <?= $num == 1 ? 'servizio' : 'servizi' ?></p>
        <?php endif; ?>
      </div>
      <?php if ($mostra_id): ?>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_serv_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo servizio
        </a>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun servizio.</p>
    </div>
    <?php else: ?>
    <div class="list" style="--list-cols: <?= $mostra_id ? '70px' : '' ?> 64px 1fr 1fr 60px 90px <?= $mostra_id ? '88px' : '' ?>">
      <div class="list__hdr">
        <?php if ($mostra_id): ?><span>Id</span><?php endif; ?>
        <span style="text-align:center">Stato</span>
        <span>Categoria</span>
        <span>Nome</span>
        <span style="text-align:center">Liv.</span>
        <span style="text-align:center">Acq. dir.</span>
        <?php if ($mostra_id): ?><span style="text-align:center; white-space:nowrap">Azioni</span><?php endif; ?>
      </div>
      <?php foreach ($res as $r):
        $id_servizio = $r["id"];
        $flag_attivo = $r["flag_attivo"];
      ?>
      <div class="list__row">
        <?php if ($mostra_id): ?><span data-label="Id"><?= (int)$id_servizio ?></span><?php endif; ?>
        <span data-label="Stato" style="justify-content:center">
          <form action="adhoc_mcp_serv_lista.php" method="post" style="display:contents">
            <input type="hidden" name="id_servizio" value="<?= (int)$id_servizio ?>" />
            <button type="submit" class="icon-btn" title="<?= $flag_attivo == 0 ? 'Non attivo (clic per attivare)' : 'Attivo (clic per disattivare)' ?>">
              <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= $flag_attivo == 0 ? 'var(--danger-500)' : 'var(--ok-500)' ?>;"></span>
            </button>
          </form>
        </span>
        <span data-label="Categoria"><?= $r["nome_categoria"] ?></span>
        <span data-label="Nome"><?= $r["nome_servizio"] ?></span>
        <span data-label="Liv." style="justify-content:center"><?= (int)$r["livello"] ?></span>
        <span data-label="Acq. dir." style="justify-content:center"><?= $r["flag_acq_dir"] == 0 ? 'no' : 'sì' ?></span>
        <?php if ($mostra_id): ?>
        <div class="row-acts">
          <a class="icon-btn" href="adhoc_mcp_serv_vis.php?id=<?= (int)$id_servizio ?>" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
          <form action="adhoc_mcp_serv_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_servizio" value="<?= (int)$id_servizio ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare il servizio «<?= htmlspecialchars($r["nome_servizio"]) ?>»?" data-confirm-title="Elimina servizio">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div><!-- /.list -->
    <?php endif; ?>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
