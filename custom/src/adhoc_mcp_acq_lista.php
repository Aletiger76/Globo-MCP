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

if (isset($_POST["ricerca_pag"])) $ricerca_pag = (int)$_POST["ricerca_pag"];
else if (isset($_GET["pag"])) $ricerca_pag = (int)$_GET["pag"];
else if (isset($_SESSION["ksglobo_ricerca_mcp_acq"]["pag"])) $ricerca_pag = (int)$_SESSION["ksglobo_ricerca_mcp_acq"]["pag"];
else $ricerca_pag = 1;
if (isset($_POST["id_cliente"])) $ricerca_id_cliente = $_POST["id_cliente"];
else if (isset($_POST["ricerca_id_cliente"])) $ricerca_id_cliente = $_POST["ricerca_id_cliente"];
else if (isset($_GET["id_cliente"])) $ricerca_id_cliente = $_GET["id_cliente"];
else if (isset($_SESSION["ksglobo_ricerca_mcp_acq"]["id_cliente"])) $ricerca_id_cliente = $_SESSION["ksglobo_ricerca_mcp_acq"]["id_cliente"];
else $ricerca_id_cliente = -1;

unset($_SESSION["ksglobo_ricerca_mcp_acq"]);
$_SESSION["ksglobo_ricerca_mcp_acq"]["pag"] = $ricerca_pag;
$_SESSION["ksglobo_ricerca_mcp_acq"]["id_cliente"] = $ricerca_id_cliente;

$sql = "SELECT * FROM acquisto_servizio";
$params = [];
if ($ricerca_id_cliente != -1) { $sql .= " WHERE id_cliente = ?"; $params[] = $ricerca_id_cliente; }
$sql .= " ORDER BY data DESC, ora DESC, id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$res_tot = $stmt->fetchAll();
$num_tot = count($res_tot);

$num_pag = ceil($num_tot / 50);
if ($ricerca_pag > $num_pag) $ricerca_pag = 1;

$limit_1 = ($ricerca_pag - 1) * 50;
$limit_2 = 50;

// slice invece di ri-eseguire la query con LIMIT: stesso risultato, una sola query
$res = array_slice($res_tot, $limit_1, $limit_2);
$num = count($res);

$importo_tot = 0;
for ($i = 0; $i < $num; $i++)
  $importo_tot += $res[$i]["prezzo_servizio"];

$res_cli = $db->query("SELECT id FROM cliente ORDER BY id")->fetchAll();
$num_cli = count($res_cli);

$stmt_doc = $db->prepare("SELECT COUNT(*) FROM acquisto_servizio_documento WHERE id_acquisto_servizio = ?");
$stati_flag = [0 => 'non elaborato', 1 => 'in elaborazione', 2 => 'elaborato', 3 => 'nessun risultato'];
$stati_colore = [0 => 'var(--danger-500)', 1 => 'var(--warn-500, #d97706)', 2 => 'var(--ok-500)', 3 => 'var(--fg-4)'];

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Acquisti servizi</h1>
        <?php if ($num_tot > 0): ?>
        <p class="page__sub"><?= $num_tot ?> <?= $num_tot == 1 ? 'acquisto' : 'acquisti' ?> — importo totale pagina: <?= number_format($importo_tot, 2, ",", ".") ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($num_cli > 0): ?>
        <a class="btn btn--ghost" href="adhoc_mcp_acq_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo acquisto
        </a>
        <?php else: ?>
        <button type="button" class="btn btn--ghost" disabled title="Nessun cliente presente">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo acquisto
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php
      $search_action = 'adhoc_mcp_acq_lista.php';
      $search_form_attrs = 'name="globo"';
      require("varie/search_open.php");
    ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice cliente</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ricerca_id_cliente" onchange="this.form.submit()">
                    <option value="-1"<?= $ricerca_id_cliente == -1 ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_cli as $rc): ?>
                    <option value="<?= htmlspecialchars($rc["id"]) ?>"<?= $rc["id"] == $ricerca_id_cliente ? ' selected' : '' ?>><?= htmlspecialchars($rc["id"]) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
    <?php require("varie/search_close.php"); ?>

    <?php if ($ricerca_id_cliente != -1): ?>
    <div style="margin-top:12px">
      <a class="btn btn--ghost" href="adhoc_mcp_cli_vis.php?id=<?= urlencode($ricerca_id_cliente) ?>">Visualizza cliente</a>
    </div>
    <?php endif; ?>

    <?php if ($num <= 0): ?>
    <div class="card" style="margin-top:16px">
      <p class="empty-state">Nessun acquisto.</p>
    </div>
    <?php else: ?>
    <?php globo_render_paginazione($ricerca_pag, $num_pag, 'adhoc_mcp_acq_lista.php', 'ricerca_pag'); ?>
    <div class="list" style="--list-cols: 100px 90px 1fr 90px 1fr 90px 130px">
      <div class="list__hdr">
        <span>Data</span>
        <span>Cod. cliente</span>
        <span>Servizio</span>
        <span style="text-align:right">Prezzo</span>
        <span>Dati</span>
        <span style="text-align:center">Stato</span>
        <span style="text-align:center; white-space:nowrap">Azioni</span>
      </div>
      <?php foreach ($res as $r):
        $id_acquisto_servizio = $r["id"];
        $data = globo_converti_data_in_formato_ita($r["data"]);
        $flag_stato = $r["flag_stato"];
        $stmt_doc->execute([$id_acquisto_servizio]);
        $num_doc = $stmt_doc->fetchColumn();
      ?>
      <div class="list__row">
        <span data-label="Data" style="white-space:nowrap"><?= $data ?></span>
        <span data-label="Cod. cliente"><?= htmlspecialchars($r["id_cliente"]) ?></span>
        <span data-label="Servizio"><?= $r["nome_servizio"] ?></span>
        <span data-label="Prezzo" style="justify-content:flex-end"><?= number_format($r["prezzo_servizio"], 2, ",", ".") ?></span>
        <span data-label="Dati"><?= $r["dati"] ?></span>
        <span data-label="Stato" style="justify-content:center">
          <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= $stati_colore[$flag_stato] ?>;" title="<?= $stati_flag[$flag_stato] ?>"></span>
        </span>
        <div class="row-acts">
          <a class="icon-btn" href="adhoc_mcp_acq_vis.php?id=<?= (int)$id_acquisto_servizio ?>" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
          <a class="icon-btn" href="adhoc_mcp_acq_doc.php?id=<?= (int)$id_acquisto_servizio ?>" title="Documenti<?= $num_doc > 0 ? " ($num_doc)" : '' ?>">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg>
          </a>
          <form action="adhoc_mcp_acq_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_acquisto_servizio" value="<?= (int)$id_acquisto_servizio ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare questo acquisto servizio?" data-confirm-title="Elimina acquisto servizio">
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
