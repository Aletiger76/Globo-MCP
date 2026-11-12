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

if (isset($_POST["id_categoria_servizio"])) $id_categoria_servizio = $_POST["id_categoria_servizio"];
else if (isset($_GET["id"])) $id_categoria_servizio = $_GET["id"];
else { header("Location: adhoc_mcp_catserv_lista.php"); exit(); }

$stmt = $db->prepare("SELECT * FROM categoria_servizio WHERE id = ?");
$stmt->execute([$id_categoria_servizio]);
$row = $stmt->fetch();
if (!$row) { header("Location: adhoc_mcp_catserv_lista.php"); exit(); }

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Dettagli categoria servizio</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_catserv_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco categorie
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_catserv_agg.php" method="post">
      <input type="hidden" name="id_categoria_servizio" value="<?= (int)$id_categoria_servizio ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli categoria servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="nome" value="<?= $row["nome"] ?>" required /></div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
