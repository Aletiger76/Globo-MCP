<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Design system del core,
// stesso pattern di ut_vis.php (card + form__row, card__foot, riga di
// bottoni secondari sotto la card). Logica DB invariata rispetto alla
// Fase A (PDO). GET oltre a POST (come prog_vis.php), cosi' i link dalle
// liste possono essere <a href="...?id=..."> invece di un form.
$db = NULL;
$db_globo = NULL;
try {
  $db = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_MCP . ";charset=utf8mb4", MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $db_globo = new PDO("mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO . ";charset=utf8mb4", MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO);
  $db_globo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
  mail(EMAIL_KOSMOSOL, "errore mysql", "request uri: " . $_SERVER["REQUEST_URI"] . "\n\n" . $e->getMessage(), "From: " . EMAIL_GLOBO);
  header("Location: index.php?e=0");
  exit();
}

if (isset($_POST["id_cliente"])) $id_cliente = $_POST["id_cliente"];
else if (isset($_GET["id"])) $id_cliente = $_GET["id"];
else { header("Location: adhoc_mcp_cli_lista.php"); exit(); }

$stmt = $db->prepare("SELECT * FROM cliente WHERE id = ?");
$stmt->execute([$id_cliente]);
$row = $stmt->fetch();
if (!$row) { header("Location: adhoc_mcp_cli_lista.php"); exit(); }

$flag_attivo = $row["flag_attivo"];
$password = $row["password"];
$descrizione = $row["descrizione"];
$email = $row["email"];
$id_listino = $row["id_listino"];
$id_contatto_globo = $row["id_contatto_globo"];

$stmt_rica = $db->prepare("SELECT SUM(importo) as tot_rica FROM ricarica_credito_cliente WHERE id_cliente = ?");
$stmt_rica->execute([$id_cliente]);
$tot_rica = $stmt_rica->fetchColumn();
$stmt_acq = $db->prepare("SELECT SUM(prezzo_servizio) as tot_acq FROM acquisto_servizio WHERE id_cliente = ?");
$stmt_acq->execute([$id_cliente]);
$tot_acq = $stmt_acq->fetchColumn();
$credito = number_format($tot_rica - $tot_acq, 2, ".", "");

$res_lis = $db->query("SELECT * FROM listino ORDER BY id")->fetchAll();
$res_cont = $db_globo->query("SELECT * FROM contatto ORDER BY id")->fetchAll();

$stmt_acq2 = $db->prepare("SELECT id FROM acquisto_servizio WHERE id_cliente = ?");
$stmt_acq2->execute([$id_cliente]);
$num_acq = $stmt_acq2->rowCount();

$stmt_rica2 = $db->prepare("SELECT id FROM ricarica_credito_cliente WHERE id_cliente = ?");
$stmt_rica2->execute([$id_cliente]);
$num_rica = $stmt_rica2->rowCount();

require("varie/head.php");
?>
<script>
function controllaForm() {
  var f = document.globo;
  if (f.password.value.trim() === "") { alert('compilare il campo "password"'); f.password.focus(); return false; }
  if (f.descrizione.value.trim() === "") { alert('compilare il campo "descrizione"'); f.descrizione.focus(); return false; }
  var email = f.email.value.trim();
  var re_email = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
  if (email === "") { alert('compilare il campo "e-mail"'); f.email.focus(); return false; }
  if (!re_email.test(email)) { alert("indirizzo e-mail non corretto"); f.email.focus(); return false; }
  return true;
}
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Dettagli cliente</h1>
        <p class="page__sub"><?= htmlspecialchars($id_cliente) ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_cli_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco clienti
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_cli_agg.php" method="post" onsubmit="return controllaForm()">
      <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente) ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli cliente</span>
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
              <div class="form__label"><span class="form__label-name">Codice</span></div>
              <div class="form__control"><?= htmlspecialchars($id_cliente) ?></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Password</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="password" value="<?= $password ?>" /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="150" name="descrizione" value="<?= $descrizione ?>" /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control"><input type="email" class="input" maxlength="150" name="email" value="<?= htmlspecialchars($email) ?>" /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Listino</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_listino">
                    <?php foreach ($res_lis as $l): ?>
                    <option value="<?= $l["id"] ?>"<?= $l["id"] == $id_listino ? ' selected' : '' ?>><?= $l["nome"] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Contatto</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_contatto_globo">
                    <option value="0"<?= $id_contatto_globo == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_cont as $c):
                      $azienda = trim($c["azienda"]); $nome = trim($c["nome"]); $cognome = trim($c["cognome"]);
                      if ($azienda === '' && $nome === '' && $cognome === '') continue;
                      if ($azienda !== '') $opt = $azienda;
                      else if ($nome !== '' && $cognome !== '') $opt = $nome . ' ' . $cognome;
                      else $opt = $nome !== '' ? $nome : $cognome;
                      if (strlen($opt) > 55) $opt = substr($opt, 0, 55) . '...';
                    ?>
                    <option value="<?= $c["id"] ?>"<?= $c["id"] == $id_contatto_globo ? ' selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Credito</span></div>
              <div class="form__control"><?= $credito ?></div>
            </div>

          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px">
      <a class="btn btn--ghost" href="adhoc_mcp_acq_lista.php?id_cliente=<?= urlencode($id_cliente) ?>">Acquisti servizi (<?= $num_acq ?>)</a>
      <a class="btn btn--ghost" href="adhoc_mcp_rica_lista.php?id_cliente=<?= urlencode($id_cliente) ?>">Ricariche credito (<?= $num_rica ?>)</a>
      <?php if ($id_contatto_globo > 0): ?>
      <a class="btn btn--ghost" href="cont_vis.php?id=<?= (int)$id_contatto_globo ?>" target="_blank" rel="noopener">
        Visualizza contatto
      </a>
      <?php endif; ?>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
