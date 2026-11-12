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

if (isset($_POST["id_listino"])) $id_listino = $_POST["id_listino"];
else $id_listino = $_GET["id"];

$stmt = $db->prepare("SELECT * FROM listino WHERE id = ?");
$stmt->execute([$id_listino]);
$row = $stmt->fetch();
$nome = $row["nome"];

$stmt_cli = $db->prepare("SELECT COUNT(*) FROM cliente WHERE id_listino = ?");
$stmt_cli->execute([$id_listino]);
$num_cli = $stmt_cli->fetchColumn();

// Alias espliciti per servizio.nome/categoria_servizio.nome: con SELECT *
// le due colonne "nome" si sovrascriverebbero nell'array associativo di PDO
// (il vecchio mysql_result() le risolveva per nome-tabella, PDO no).
$res_serv = $db->query("SELECT servizio.id, servizio.nome AS nome_servizio, servizio.flag_acq_dir, categoria_servizio.nome AS nome_categoria FROM servizio JOIN categoria_servizio ON servizio.id_categoria_servizio = categoria_servizio.id ORDER BY servizio.id_categoria_servizio, servizio.id")->fetchAll();
$num_serv = count($res_serv);

$mostra_id = $_SESSION["ksglobo_livello_utente"] == 0;

$stmt_pr = $db->prepare("SELECT prezzo FROM listino_servizio WHERE id_listino = ? AND id_servizio = ?");
$prezzi = [];
foreach ($res_serv as $rs) {
  $stmt_pr->execute([$id_listino, $rs["id"]]);
  $p = $stmt_pr->fetchColumn();
  $prezzi[$rs["id"]] = $p === false ? "0.00" : $p;
}

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $nome ?></h1>
        <p class="page__sub"><?= $num_cli ?> <?= $num_cli == 1 ? 'cliente associato' : 'clienti associati' ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_lis_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco listini
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_lis_agg.php" method="post">
      <input type="hidden" name="id_listino" value="<?= (int)$id_listino ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli listino</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="nome" value="<?= $nome ?>" required /></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="card__head">
          <span class="card__head-eyebrow">Prezzi servizi</span>
        </div>
        <?php if ($num_serv == 0): ?>
        <p class="empty-state">Nessun servizio.</p>
        <?php else: ?>
        <div class="list" style="--list-cols: <?= $mostra_id ? '70px' : '' ?> 1fr 1fr 90px 130px">
          <div class="list__hdr">
            <?php if ($mostra_id): ?><span>Id</span><?php endif; ?>
            <span>Categoria</span>
            <span>Nome</span>
            <span style="text-align:center">Acq. dir.</span>
            <span>Prezzo</span>
          </div>
          <?php foreach ($res_serv as $rs):
            $id_servizio = $rs["id"];
          ?>
          <div class="list__row">
            <?php if ($mostra_id): ?><span data-label="Id"><?= (int)$id_servizio ?></span><?php endif; ?>
            <span data-label="Categoria"><?= $rs["nome_categoria"] ?></span>
            <span data-label="Nome"><?= $rs["nome_servizio"] ?></span>
            <span data-label="Acq. dir." style="justify-content:center"><?= $rs["flag_acq_dir"] == 0 ? 'no' : 'sì' ?></span>
            <span data-label="Prezzo"><input type="text" class="input" size="6" maxlength="10" name="prezzo[<?= (int)$id_servizio ?>]" value="<?= htmlspecialchars($prezzi[$id_servizio]) ?>" /></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
