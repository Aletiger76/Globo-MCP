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
$num_cli = count($res_cli);

$res_ser = $db->query("SELECT id, nome FROM servizio ORDER BY id")->fetchAll();

$stmt_rica = $db->prepare("SELECT SUM(importo) as tot_rica FROM ricarica_credito_cliente WHERE id_cliente = ?");
$stmt_acq = $db->prepare("SELECT SUM(prezzo_servizio) as tot_acq FROM acquisto_servizio WHERE id_cliente = ?");

$crediti_cliente = [];
foreach ($res_cli as $rc) {
  $stmt_rica->execute([$rc["id"]]);
  $tot_rica = $stmt_rica->fetchColumn();
  $stmt_acq->execute([$rc["id"]]);
  $tot_acq = $stmt_acq->fetchColumn();
  $crediti_cliente[$rc["id"]] = number_format($tot_rica - $tot_acq, 2, ".", "");
}

require("varie/head.php");
?>
<script>
  function controllaForm() {
    var f = document.globo;
    var prezzo_servizio = f.prezzo_servizio.value;
    var dati = f.dati.value.trim();
    if (isNaN(prezzo_servizio) || Number(prezzo_servizio) <= 0) {
      f.prezzo_servizio.setCustomValidity("Inserire un valore numerico positivo");
      f.prezzo_servizio.reportValidity();
      return false;
    }
    if (dati === "") {
      f.dati.setCustomValidity("Compilare il campo \"dati\"");
      f.dati.reportValidity();
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
        <h1 class="page__title">Nuovo acquisto servizio</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_acq_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco acquisti
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_acq_ins_2.php" method="post" onsubmit="return controllaForm();">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo acquisto servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Stato</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_stato">
                    <option value="0" selected>non elaborato</option>
                    <option value="1">in elaborazione</option>
                    <option value="2">elaborato</option>
                    <option value="3">nessun risultato</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice cliente</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_cliente">
                    <?php foreach ($res_cli as $rc): ?>
                    <option value="<?= htmlspecialchars($rc["id"]) ?>"><?= htmlspecialchars($rc["id"]) ?> (<?= $crediti_cliente[$rc["id"]] ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome servizio</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_servizio">
                    <?php foreach ($res_ser as $rs):
                      $nome_servizio = strlen($rs["nome"]) > 55 ? substr($rs["nome"], 0, 55) . "..." : $rs["nome"];
                    ?>
                    <option value="<?= (int)$rs["id"] ?>"><?= htmlspecialchars($nome_servizio) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Prezzo servizio</span></div>
              <div class="form__control"><input type="text" class="input" size="6" maxlength="10" name="prezzo_servizio" value="0.00" oninput="this.setCustomValidity('')" required /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Dati</span></div>
              <div class="form__control"><textarea class="input" name="dati" rows="4" oninput="this.setCustomValidity('')" required></textarea></div>
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
