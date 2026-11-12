<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Design system del core,
// stesso pattern di prog_lista.php (search_open/input/close, .list,
// paginazione). Logica DB invariata rispetto alla Fase A (PDO).
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

if (isset($_POST["id_cliente"]) && !isset($_POST["ricerca_testo"])) { // toggle flag_attivo
  $id_cliente = $_POST["id_cliente"];

  $stmt = $db->prepare("SELECT flag_attivo FROM cliente WHERE id = ?");
  $stmt->execute([$id_cliente]);
  $flag_attivo = $stmt->fetchColumn();
  $flag_attivo = $flag_attivo == 0 ? 1 : 0;

  $db->beginTransaction();
  $stmt = $db->prepare("UPDATE cliente SET flag_attivo = ? WHERE id = ?");
  $stmt->execute([$flag_attivo, $id_cliente]);
  $db->commit();
}

if (isset($_POST["ricerca_pag"])) $ricerca_pag = (int)$_POST["ricerca_pag"];
else if (isset($_SESSION["ksglobo_ricerca_mcp_cli"]["pag"])) $ricerca_pag = (int)$_SESSION["ksglobo_ricerca_mcp_cli"]["pag"];
else $ricerca_pag = 1;
if (isset($_POST["ricerca_testo"])) $ricerca_testo = globo_converti_in_html($_POST["ricerca_testo"]);
else if (isset($_SESSION["ksglobo_ricerca_mcp_cli"]["testo"])) $ricerca_testo = $_SESSION["ksglobo_ricerca_mcp_cli"]["testo"];
else $ricerca_testo = "";

unset($_SESSION["ksglobo_ricerca_mcp_cli"]);
$_SESSION["ksglobo_ricerca_mcp_cli"]["pag"] = $ricerca_pag;
$_SESSION["ksglobo_ricerca_mcp_cli"]["testo"] = $ricerca_testo;

$stmt = $db->prepare("SELECT * FROM cliente WHERE descrizione LIKE ? ORDER BY id");
$stmt->execute(["%$ricerca_testo%"]);
$res_tot = $stmt->fetchAll();
$num_tot = count($res_tot);

$num_pag = (int)ceil($num_tot / 50);
if ($ricerca_pag > $num_pag) $ricerca_pag = 1;
$res = array_slice($res_tot, ($ricerca_pag - 1) * 50, 50);
$num = count($res);

$num_lis = $db->query("SELECT COUNT(*) FROM listino")->fetchColumn();

require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Clienti</h1>
        <?php if ($num_tot > 0): ?>
        <p class="page__sub"><?= $num_tot ?> <?= $num_tot == 1 ? 'cliente' : 'clienti' ?></p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($num_lis <= 0): ?>
        <button type="button" class="btn btn--ghost" disabled title="Serve almeno un listino">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo cliente
        </button>
        <?php else: ?>
        <a class="btn btn--ghost" href="adhoc_mcp_cli_ins_1.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuovo cliente
        </a>
        <?php endif; ?>
      </div>
    </div>

    <?php
      $search_action = 'adhoc_mcp_cli_lista.php';
      require("varie/search_open.php");
      $si_name = 'ricerca_testo'; $si_value = $ricerca_testo; $si_label = 'Descrizione';
      require("varie/search_input.php");
      require("varie/search_close.php");
    ?>

    <?php if ($num <= 0): ?>
    <div class="card">
      <p class="empty-state">Nessun cliente trovato.</p>
    </div>
    <?php else: ?>
    <?php globo_render_paginazione($ricerca_pag, $num_pag, 'adhoc_mcp_cli_lista.php', 'ricerca_pag'); ?>
    <div class="list" style="--list-cols: 48px 140px 1fr 110px 88px">
      <div class="list__hdr">
        <div style="text-align:center">Stato</div>
        <div>Codice</div>
        <div>Descrizione</div>
        <div style="text-align:center">Credito</div>
        <div style="text-align:center; white-space:nowrap">Azioni</div>
      </div>
      <?php
        $stmt_rica = $db->prepare("SELECT SUM(importo) as tot_rica FROM ricarica_credito_cliente WHERE id_cliente = ?");
        $stmt_acq  = $db->prepare("SELECT SUM(prezzo_servizio) as tot_acq FROM acquisto_servizio WHERE id_cliente = ?");
      ?>
      <?php foreach ($res as $r):
        $id_cliente = $r["id"];
        $flag_attivo = $r["flag_attivo"];
        $descrizione = $r["descrizione"];

        $stmt_rica->execute([$id_cliente]);
        $tot_rica = $stmt_rica->fetchColumn();
        $stmt_acq->execute([$id_cliente]);
        $tot_acq = $stmt_acq->fetchColumn();
        $credito = number_format($tot_rica - $tot_acq, 2, ".", "");
      ?>
      <div class="list__row">
        <div data-label="Stato" style="justify-content:center">
          <form action="adhoc_mcp_cli_lista.php" method="post" style="display:contents">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente) ?>" />
            <button type="submit" class="icon-btn" title="<?= $flag_attivo == 0 ? 'Non attivo — click per attivare' : 'Attivo — click per disattivare' ?>">
              <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:<?= $flag_attivo == 0 ? 'var(--danger-500)' : 'var(--ok-500)' ?>"></span>
            </button>
          </form>
        </div>
        <div data-label="Codice"><span class="allCopy mono" style="font-weight:600"><?= htmlspecialchars($id_cliente) ?></span></div>
        <div data-label="Descrizione"><span class="list-name"><?= $descrizione ?></span></div>
        <div data-label="Credito" style="justify-content:center"><?= $credito ?></div>
        <div class="row-acts">
          <a class="icon-btn" href="adhoc_mcp_cli_vis.php?id=<?= urlencode($id_cliente) ?>" title="Dettagli">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
          </a>
          <form action="adhoc_mcp_cli_el.php" method="post" style="display:contents">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente) ?>" />
            <button type="submit" class="icon-btn icon-btn--danger" title="Elimina"
                    data-confirm="Eliminare il cliente «<?= htmlspecialchars($id_cliente) ?>»?" data-confirm-title="Elimina cliente">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.list -->
    <?php globo_render_paginazione($ricerca_pag, $num_pag, 'adhoc_mcp_cli_lista.php', 'ricerca_pag'); ?>
    <?php endif; ?>

  </div></main>
</div>
<?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
