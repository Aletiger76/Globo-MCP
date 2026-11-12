<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Stesso pattern di
// adhoc_mcp_cli_vis.php (card + form__row). La verifica "codice gia'
// esistente" (lato client, come l'originale) usa setCustomValidity invece
// di alert() nativo, coerente col resto del design system (vedi ut_vis.php).
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

$ids_cliente = $db->query("SELECT id FROM cliente ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$res_lis = $db->query("SELECT * FROM listino ORDER BY id")->fetchAll();
$res_cont = $db_globo->query("SELECT * FROM contatto ORDER BY id")->fetchAll();

require("varie/head.php");
?>
<script>
var _mcpIdsCliente = <?= json_encode(array_map('strval', $ids_cliente)) ?>;
function controllaForm() {
  var f = document.globo;
  f.id_cliente.setCustomValidity("");
  if (_mcpIdsCliente.indexOf(f.id_cliente.value.trim()) !== -1) {
    f.id_cliente.setCustomValidity("Codice cliente già presente");
    f.id_cliente.reportValidity();
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
        <h1 class="page__title">Nuovo cliente</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_cli_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco clienti
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_cli_ins_2.php" method="post" onsubmit="return controllaForm()">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo cliente</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

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
              <div class="form__label"><span class="form__label-name">Codice</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="id_cliente" value="" required oninput="this.setCustomValidity('')" /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Password</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="password" value="" required /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Descrizione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="150" name="descrizione" value="" required /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control"><input type="email" class="input" maxlength="150" name="email" value="" required /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Listino</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_listino">
                    <?php foreach ($res_lis as $l): ?>
                    <option value="<?= $l["id"] ?>"><?= $l["nome"] ?></option>
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
                    <option value="0" selected>—</option>
                    <?php foreach ($res_cont as $c):
                      $azienda = trim($c["azienda"]); $nome = trim($c["nome"]); $cognome = trim($c["cognome"]);
                      if ($azienda === '' && $nome === '' && $cognome === '') continue;
                      if ($azienda !== '') $opt = $azienda;
                      else if ($nome !== '' && $cognome !== '') $opt = $nome . ' ' . $cognome;
                      else $opt = $nome !== '' ? $nome : $cognome;
                    ?>
                    <option value="<?= $c["id"] ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
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

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
