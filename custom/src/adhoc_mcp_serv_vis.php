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

if (isset($_POST["id_servizio"])) $id_servizio = $_POST["id_servizio"];
else $id_servizio = $_GET["id"];

$stmt = $db->prepare("SELECT * FROM servizio WHERE id = ?");
$stmt->execute([$id_servizio]);
$row = $stmt->fetch();

$id_categoria_servizio = $row["id_categoria_servizio"];
$flag_attivo = $row["flag_attivo"];
$nome = $row["nome"];
$livello = $row["livello"];
$flag_acq_dir = $row["flag_acq_dir"];

$res_cat = $db->query("SELECT * FROM categoria_servizio ORDER BY id")->fetchAll();

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $nome ?></h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_serv_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco servizi
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_serv_agg.php" method="post">
      <input type="hidden" name="id_servizio" value="<?= (int)$id_servizio ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Stato</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_attivo">
                    <option value="0"<?= $flag_attivo == 0 ? ' selected' : '' ?>>non attivo</option>
                    <option value="1"<?= $flag_attivo == 1 ? ' selected' : '' ?>>attivo</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Categoria</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_categoria_servizio">
                    <?php foreach ($res_cat as $rc): ?>
                    <option value="<?= (int)$rc["id"] ?>"<?= $rc["id"] == $id_categoria_servizio ? ' selected' : '' ?>><?= $rc["nome"] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="nome" value="<?= $nome ?>" required /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Livello</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="livello">
                    <?php for ($l = 0; $l <= 3; $l++): ?>
                    <option value="<?= $l ?>"<?= $livello == $l ? ' selected' : '' ?>><?= $l ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Acquistabile direttamente</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_acq_dir">
                    <option value="0"<?= $flag_acq_dir == 0 ? ' selected' : '' ?>>no</option>
                    <option value="1"<?= $flag_acq_dir == 1 ? ' selected' : '' ?>>sì</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
