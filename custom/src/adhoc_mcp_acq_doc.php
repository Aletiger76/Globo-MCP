<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

  // CUSTOM-MCP: Fase B (redesign UI), 2026-07-13. Pattern 22 (globoAlert/
  // globoConfirm invece di alert()/confirm() nativi) come core/src/prog_doc.php,
  // stesso riferimento gia' usato per il resto del modulo.
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

  $id_acquisto_servizio = $_POST["id_acquisto_servizio"] ?? $_GET["id"] ?? '';

  if (isset($_POST["azione"]) && $_POST["azione"] == 1) { // caricamento nuovo documento
    $nome = globo_converti_in_html($_POST["nome"]);
    $nome_file_documento_tmp = $_FILES["file"]["tmp_name"];
    $nome_file_documento_utente = $_FILES["file"]["name"];
    $dimensione_file_documento = $_FILES["file"]["size"];

    $dt_upload = date("Y-m-d H:i:s");
    $dt = date("YmdHis");

    $tmp = explode(".", strtolower($nome_file_documento_utente));
    if (!in_array(end($tmp), $MCP_DOC_FORM_OK)) $stato_documento = 1;
    else if ($dimensione_file_documento > MCP_MAX_DIM_DOC_B) $stato_documento = 2;
    else {
      $tmp = explode(".", strtolower($nome_file_documento_utente));
      $estensione_file_documento = end($tmp);
      $nome_file_documento_server = $id_acquisto_servizio . "_" . $dt . "." . $estensione_file_documento;
      $path_file_documento_server = MCP_DIR_DOC . $nome_file_documento_server;
      if (move_uploaded_file($nome_file_documento_tmp, $path_file_documento_server)) $stato_documento = 0;
      else $stato_documento = 3;
    }

    if ($stato_documento == 0) {
      $db->beginTransaction();
      $stmt = $db->prepare("INSERT INTO acquisto_servizio_documento (id_acquisto_servizio, nome, file, dt_upload) VALUES (?, ?, ?, ?)");
      $stmt->execute([$id_acquisto_servizio, $nome, $nome_file_documento_server, $dt_upload]);
      $num = $stmt->rowCount();
      $db->commit();

      if ($num <= 0) {
        $popup = "documento non caricato - problemi legati al database";
        unlink(MCP_DIR_DOC . $nome_file_documento_server);
      }
    }
    else {
      if ($stato_documento == 1) $popup = "documento non caricato - problemi legati al tipo di file";
      if ($stato_documento == 2) $popup = "documento non caricato - dimensione file eccessiva (max " . MCP_MAX_DIM_DOC_MB . " mb)";
      if ($stato_documento == 3) $popup = "documento non caricato - problemi legati al trasferimento del file";
    }
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 2) { // eliminazione documento
    $id_acquisto_servizio_documento = $_POST["id_acquisto_servizio_documento"];

    $stmt = $db->prepare("SELECT file FROM acquisto_servizio_documento WHERE id = ?");
    $stmt->execute([$id_acquisto_servizio_documento]);
    $file = $stmt->fetchColumn();

    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM acquisto_servizio_documento WHERE id = ?");
    $stmt->execute([$id_acquisto_servizio_documento]);
    $num = $stmt->rowCount();
    $db->commit();

    if ($num > 0) unlink(MCP_DIR_DOC . $file);
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 3) { // modifica nome documento
    $id_acquisto_servizio_documento = $_POST["id_acquisto_servizio_documento"];
    $nome = $_POST["nome"];

    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE acquisto_servizio_documento SET nome = ? WHERE id = ?");
    $stmt->execute([$nome, $id_acquisto_servizio_documento]);
    $db->commit();
  }

  $stmt_serv = $db->prepare("SELECT nome_servizio, id_cliente FROM acquisto_servizio WHERE id = ?");
  $stmt_serv->execute([$id_acquisto_servizio]);
  $row_serv = $stmt_serv->fetch();
  $nome_servizio = $row_serv["nome_servizio"];
  $id_cliente = $row_serv["id_cliente"];

  $stmt_doc = $db->prepare("SELECT * FROM acquisto_servizio_documento WHERE id_acquisto_servizio = ? ORDER BY nome, dt_upload DESC");
  $stmt_doc->execute([$id_acquisto_servizio]);
  $res_doc = $stmt_doc->fetchAll();
  $num_doc = count($res_doc);

  $_globo_can_upload = ($num_doc < MCP_MAX_NUM_DOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?= htmlspecialchars(TITLE) ?></title>
  <link rel="stylesheet" href="css/globo.tokens.css" />
  <link rel="stylesheet" href="css/globo.themes.css" />
  <link rel="stylesheet" href="css/globo.app.css" />
  <?php require("shadowbox/shadowbox.php"); ?>
  <script>
  async function controllaForm() {
    var re = / /g;
    var nome = document.globo.nome.value.replace(re, "");
    var file = document.globo.file.value.replace(re, "");
    var estensione = "";
    if (file.substring(file.length - 4, file.length - 3) == ".") estensione = file.substring(file.length - 3, file.length).toLowerCase();
    else if (file.substring(file.length - 5, file.length - 4) == ".") estensione = file.substring(file.length - 4, file.length).toLowerCase();
    if (nome == "") { await globoAlert("compilare il campo \"nome documento\""); document.globo.nome.focus(); return false; }
    if (file == "") { await globoAlert("compilare il campo \"file\""); document.globo.file.focus(); return false; }
    <?php
      echo "if (";
      for ($i = 0; $i < count($MCP_DOC_FORM_OK); $i++) {
        echo "estensione != \"" . $MCP_DOC_FORM_OK[$i] . "\"";
        if ($i < (count($MCP_DOC_FORM_OK) - 1)) echo " && ";
      }
      echo ") { await globoAlert(\"formato file non valido\"); document.globo.file.value = \"\"; return false; }";
    ?>
    openLoadingShadowbox();
    return true;
  }
  </script>
</head>
<body data-theme="<?= htmlspecialchars(GLOBO_THEME) ?>">
<?php require("varie/icon-sprite.php"); ?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $nome_servizio ?></h1>
        <p class="page__sub">Documenti dell'acquisto — <?= htmlspecialchars($id_cliente) ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_acq_vis.php?id=<?= (int)$id_acquisto_servizio ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza acquisto
        </a>
        <a class="btn btn--ghost" href="adhoc_mcp_acq_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco acquisti
        </a>
      </div>
    </div>

    <!-- ── Carica documento ─────────────────────────────────── -->
    <form name="globo" action="adhoc_mcp_acq_doc.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id_acquisto_servizio" value="<?= (int)$id_acquisto_servizio ?>" />
      <input type="hidden" name="azione" value="1" />
      <div class="card" style="margin-bottom: 20px;">
        <div class="card__head">
          <span class="card__head-eyebrow">Carica documento</span>
          <?php if (!$_globo_can_upload): ?>
          <span style="font-size:12px;color:var(--fg-3)">Raggiunto il massimo di <?= MCP_MAX_NUM_DOC ?> documenti</span>
          <?php endif; ?>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Nome documento <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="nome" value=""
                  <?= !$_globo_can_upload ? 'disabled' : '' ?> />
              </div>
            </div>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">File <span class="form__hint form__hint--inline">obb</span></span>
                <span class="form__hint">
                  <?php for ($i = 0; $i < count($MCP_DOC_FORM_OK); $i++) echo $MCP_DOC_FORM_OK[$i] . " "; ?>
                  — max <?= MCP_MAX_DIM_DOC_MB ?> MB
                </span>
              </div>
              <div class="form__control">
                <input type="file" name="file" <?= !$_globo_can_upload ? 'disabled' : '' ?> />
              </div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary" <?= !$_globo_can_upload ? 'disabled' : '' ?>>Carica</button>
        </div>
      </div>
    </form>

    <!-- ── Documenti disponibili ─────────────────────────────── -->
    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Documenti disponibili (<?= $num_doc ?>)</span>
      </div>
      <?php if ($num_doc <= 0): ?>
      <p class="empty-state">Nessun documento.</p>
      <?php else: ?>
      <div class="card__body" style="padding:0; overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
          <thead>
            <tr style="background:var(--n-25);border-bottom:1px solid var(--border-1)">
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:left">Nome</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center">Tipo</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Data</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center"></th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($res_doc as $r_doc):
            $id_acquisto_servizio_documento = $r_doc["id"];
            $nome = $r_doc["nome"];
            $file = $r_doc["file"];
            $dt_upload = globo_converti_mysql_datetime($r_doc["dt_upload"]);
            $data_upload = globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($dt_upload));
            $ora_upload = globo_ora_da_mysql_datetime($dt_upload);
            $estensione = substr($file, strpos($file, ".") + 1);
          ?>
          <tr style="border-bottom:1px solid var(--border-1)">
            <td style="padding:8px 20px">
              <form action="adhoc_mcp_acq_doc.php" method="post">
                <input type="text" class="input" style="min-width:260px" maxlength="200" name="nome"
                  value="<?= htmlspecialchars($nome) ?>" onblur="this.form.submit()" />
                <input type="hidden" name="id_acquisto_servizio" value="<?= (int)$id_acquisto_servizio ?>" />
                <input type="hidden" name="id_acquisto_servizio_documento" value="<?= (int)$id_acquisto_servizio_documento ?>" />
                <input type="hidden" name="azione" value="3" />
              </form>
            </td>
            <td style="padding:12px 20px;text-align:center;color:var(--fg-3);font-size:12px"><?= htmlspecialchars($estensione) ?></td>
            <td style="padding:12px 20px;text-align:center;white-space:nowrap;font-size:12px;color:var(--fg-3)"><?= $data_upload ?> <?= $ora_upload ?></td>
            <td style="padding:8px 20px;text-align:center">
              <a class="icon-btn" href="adhoc_mcp_file.php?file=<?= urlencode($file) ?>&tipo=1" target="_blank" title="Scarica">
                <svg class="ic-stroke" aria-hidden="true"><use href="#i-download"/></svg>
              </a>
            </td>
            <td style="padding:8px 20px;text-align:center">
              <form action="adhoc_mcp_acq_doc.php" method="post">
                <input type="hidden" name="id_acquisto_servizio" value="<?= (int)$id_acquisto_servizio ?>" />
                <input type="hidden" name="id_acquisto_servizio_documento" value="<?= (int)$id_acquisto_servizio_documento ?>" />
                <input type="hidden" name="azione" value="2" />
                <button type="submit" class="icon-btn" title="Elimina documento" data-confirm="Eliminare questo documento?">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <?php if (isset($popup)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { globoAlert(<?= json_encode($popup, JSON_UNESCAPED_UNICODE) ?>); });</script>
    <?php endif; ?>

    <script>
    document.globo.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (await controllaForm()) document.globo.submit();
    });
    </script>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
