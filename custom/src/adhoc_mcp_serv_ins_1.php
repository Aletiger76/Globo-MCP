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

// Come nel sorgente originale: id calcolato come MAX(id)+1 (SELECT ... ORDER
// BY id DESC LIMIT 1) invece di auto-increment - servizio.id e' assegnato a
// mano, comportamento preservato identico.
$ultimo_id = $db->query("SELECT id FROM servizio ORDER BY id DESC LIMIT 1")->fetchColumn();
$id_servizio = ($ultimo_id === false) ? 1 : $ultimo_id + 1;

$res_cat = $db->query("SELECT * FROM categoria_servizio ORDER BY id")->fetchAll();

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuovo servizio</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_serv_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco servizi
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_serv_ins_2.php" method="post">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Id</span></div>
              <div class="form__control"><input type="text" class="input" size="3" maxlength="3" name="id" value="<?= (int)$id_servizio ?>" required /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Stato</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_attivo">
                    <option value="0">non attivo</option>
                    <option value="1">attivo</option>
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
                    <option value="<?= (int)$rc["id"] ?>"><?= $rc["nome"] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="nome" value="" required /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Livello</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="livello">
                    <option value="0" selected>0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Acquistabile direttamente</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_acq_dir">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
