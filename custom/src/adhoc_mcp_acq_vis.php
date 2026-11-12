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

if (isset($_POST["id_acquisto_servizio"])) $id_acquisto_servizio = $_POST["id_acquisto_servizio"];
else $id_acquisto_servizio = $_GET["id"];

// Alias esplicito per evitare l'ambiguita' sulla colonna "id" (presente sia
// in acquisto_servizio che in cliente) - vedi nota in adhoc_mcp_lis_vis.php.
$stmt = $db->prepare("SELECT acquisto_servizio.*, cliente.descrizione FROM acquisto_servizio JOIN cliente ON acquisto_servizio.id_cliente = cliente.id WHERE acquisto_servizio.id = ?");
$stmt->execute([$id_acquisto_servizio]);
$row = $stmt->fetch();

$data = globo_converti_data_in_formato_ita($row["data"]);
$ora = substr($row["ora"], 0, 5);
$id_cliente = $row["id_cliente"];
$descrizione_cliente = $row["descrizione"];
$nome_servizio = $row["nome_servizio"];
$prezzo_servizio = number_format($row["prezzo_servizio"], 2, ".", "");
$dati = $row["dati"];
$flag_stato = $row["flag_stato"];
$note = globo_converti_acapo_da_html($row["note"]);

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $nome_servizio ?></h1>
        <p class="page__sub"><?= htmlspecialchars($id_cliente) ?> — <?= $data ?> <?= $ora ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_acq_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco acquisti
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_acq_agg.php" method="post">
      <input type="hidden" name="id_acquisto_servizio" value="<?= (int)$id_acquisto_servizio ?>" />
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli acquisto servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Stato</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_stato">
                    <option value="0"<?= $flag_stato == 0 ? ' selected' : '' ?>>non elaborato</option>
                    <option value="1"<?= $flag_stato == 1 ? ' selected' : '' ?>>in elaborazione</option>
                    <option value="2"<?= $flag_stato == 2 ? ' selected' : '' ?>>elaborato</option>
                    <option value="3"<?= $flag_stato == 3 ? ' selected' : '' ?>>nessun risultato</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Data</span></div>
              <div class="form__control"><?= $data ?> <?= $ora ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice cliente</span></div>
              <div class="form__control"><?= htmlspecialchars($id_cliente) ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione cliente</span></div>
              <div class="form__control"><?= $descrizione_cliente ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome servizio</span></div>
              <div class="form__control"><?= $nome_servizio ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Prezzo servizio</span></div>
              <div class="form__control"><?= $prezzo_servizio ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Dati</span></div>
              <div class="form__control"><?= $dati ?></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Note</span></div>
              <div class="form__control"><textarea class="input" name="note" rows="5"><?= $note ?></textarea></div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="adhoc_mcp_acq_doc.php?id=<?= (int)$id_acquisto_servizio ?>">Documenti</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
