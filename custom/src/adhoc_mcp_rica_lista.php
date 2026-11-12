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
else if (isset($_SESSION["ksglobo_ricerca_mcp_rica"]["pag"])) $ricerca_pag = (int)$_SESSION["ksglobo_ricerca_mcp_rica"]["pag"];
else $ricerca_pag = 1;
if (isset($_POST["id_cliente"])) $ricerca_id_cliente = $_POST["id_cliente"];
else if (isset($_POST["ricerca_id_cliente"])) $ricerca_id_cliente = $_POST["ricerca_id_cliente"];
else if (isset($_GET["id_cliente"])) $ricerca_id_cliente = $_GET["id_cliente"];
else if (isset($_SESSION["ksglobo_ricerca_mcp_rica"]["id_cliente"])) $ricerca_id_cliente = $_SESSION["ksglobo_ricerca_mcp_rica"]["id_cliente"];
else $ricerca_id_cliente = -1;

unset($_SESSION["ksglobo_ricerca_mcp_rica"]);
$_SESSION["ksglobo_ricerca_mcp_rica"]["pag"] = $ricerca_pag;
$_SESSION["ksglobo_ricerca_mcp_rica"]["id_cliente"] = $ricerca_id_cliente;

$sql = "SELECT ricarica_credito_cliente.*, cliente.descrizione FROM ricarica_credito_cliente JOIN cliente ON ricarica_credito_cliente.id_cliente = cliente.id";
$params = [];
if ($ricerca_id_cliente != -1) { $sql .= " WHERE ricarica_credito_cliente.id_cliente = ?"; $params[] = $ricerca_id_cliente; }
$sql .= " ORDER BY ricarica_credito_cliente.data DESC, ricarica_credito_cliente.ora DESC";

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
  $importo_tot += $res[$i]["importo"];

$res_cli = $db->query("SELECT id FROM cliente ORDER BY id")->fetchAll();
$num_cli = count($res_cli);

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Ricariche credito</h1>
        <?php if ($num_tot > 0): ?>
        <p class="page__sub"><?= $num_tot ?> <?= $num_tot == 1 ? 'ricarica' : 'ricariche' ?> — importo totale pagina: <?= number_format($importo_tot, 2, ",", ".") ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($num_cli > 0): ?>
        <a class="btn btn--ghost" href="adhoc_mcp_rica_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuova ricarica
        </a>
        <?php else: ?>
        <button type="button" class="btn btn--ghost" disabled title="Nessun cliente presente">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuova ricarica
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php
      $search_action = 'adhoc_mcp_rica_lista.php';
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
      <p class="empty-state">Nessuna ricarica.</p>
    </div>
    <?php else: ?>
    <?php globo_render_paginazione($ricerca_pag, $num_pag, 'adhoc_mcp_rica_lista.php', 'ricerca_pag'); ?>
    <div class="list" style="--list-cols: 100px 90px 1fr 90px 1fr 58px">
      <div class="list__hdr">
        <span>Data</span>
        <span>Cod. cliente</span>
        <span>Descrizione cliente</span>
        <span style="text-align:right">Importo</span>
        <span>Note</span>
        <span style="text-align:center; white-space:nowrap">Elim.</span>
      </div>
      <?php foreach ($res as $r):
        $id_ricarica_credito_cliente = $r["id"];
        $data = globo_converti_data_in_formato_ita($r["data"]);
      ?>
      <div class="list__row">
        <span data-label="Data" style="white-space:nowrap"><?= $data ?></span>
        <span data-label="Cod. cliente"><?= htmlspecialchars($r["id_cliente"]) ?></span>
        <span data-label="Descrizione cliente"><?= $r["descrizione"] ?></span>
        <span data-label="Importo" style="justify-content:flex-end"><?= number_format($r["importo"], 2, ",", ".") ?></span>
        <span data-label="Note"><?= $r["note"] ?></span>
        <div class="row-acts">
          <form action="adhoc_mcp_rica_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_ricarica_credito_cliente" value="<?= (int)$id_ricarica_credito_cliente ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare questa ricarica credito?" data-confirm-title="Elimina ricarica credito">
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
