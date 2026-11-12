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

$res_cli = $db->query("SELECT id FROM cliente ORDER BY id")->fetchAll();

require("varie/head.php");
?>
<script>
  function controllaForm() {
    var f = document.globo;
    var importo = f.importo.value;
    if (isNaN(importo) || Number(importo) <= 0) {
      f.importo.setCustomValidity("Inserire un importo numerico positivo");
      f.importo.reportValidity();
      return false;
    }
    return true;
  }
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuova ricarica credito</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_rica_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco ricariche
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_rica_ins_2.php" method="post" onsubmit="return controllaForm();">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuova ricarica credito</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice cliente</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_cliente">
                    <?php foreach ($res_cli as $rc): ?>
                    <option value="<?= htmlspecialchars($rc["id"]) ?>"><?= htmlspecialchars($rc["id"]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Importo</span></div>
              <div class="form__control"><input type="text" class="input" size="6" maxlength="10" name="importo" value="0.00" oninput="this.setCustomValidity('')" required /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Note</span></div>
              <div class="form__control"><textarea class="input" name="note" rows="4"></textarea></div>
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
