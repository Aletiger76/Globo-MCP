<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? $_GET["id"] ?? '';

  if (isset($_POST["azione"]) && $_POST["azione"] == 1) { // caricamento nuovo documento
    $nome = globo_converti_in_html($_POST["nome"]);
    $nome_file_documento_tmp = $_FILES["file"]["tmp_name"];
    $nome_file_documento_utente = $_FILES["file"]["name"];
    $tipo_file_documento = $_FILES["file"]["type"];
    $dimensione_file_documento = $_FILES["file"]["size"];
    $visibilita = $_POST["visibilita"];

    if (TUTTO_MINUSCOLO) $nome = strtolower($nome);

    $dt_upload = date("Y-m-d H:i:s");
    $dt = date("YmdHis");

    $tmp = explode(".", strtolower($nome_file_documento_utente));
    if (!in_array(end($tmp), $PROG_DOC_FORM_OK)) $stato_documento = 1;
    else if ($dimensione_file_documento > PROG_MAX_DIM_DOC_B) $stato_documento = 2;
    else {
      $tmp = explode(".", strtolower($nome_file_documento_utente));
      $estensione_file_documento = end($tmp);
      $nome_file_documento_server = $id_progetto . "_" . $dt . "." . $estensione_file_documento;
      $path_file_documento_server = PROG_DIR_DOC . $nome_file_documento_server;
      if (move_uploaded_file($nome_file_documento_tmp, $path_file_documento_server)) $stato_documento = 0;
      else $stato_documento = 3;
    }

    if ($stato_documento == 0) {
      $stmtIns = $db->prepare("INSERT INTO progetto_documento (id_progetto, visibilita, nome, file, dt_upload, utente_upload) VALUES (?, ?, ?, ?, ?, ?)");
      $db->beginTransaction();
      $stmtIns->execute([$id_progetto, $visibilita, $nome, $nome_file_documento_server, $dt_upload, $_SESSION["ksglobo_nome_utente"]]);
      $num = $stmtIns->rowCount();
      $db->commit();

      if ($num <= 0) {
        $popup = "documento non caricato - problemi legati al database";
        if (isset($stato_documento) && $stato_documento == 0) unlink(PROG_DIR_DOC . $nome_file_documento_server);
      }
    } else {
      if ($stato_documento == 1) $popup = "documento non caricato - problemi legati al tipo di file";
      if ($stato_documento == 2) $popup = "documento non caricato - dimensione file eccessiva (max " . PROG_MAX_DIM_DOC_MB . " mb)";
      if ($stato_documento == 3) $popup = "documento non caricato - problemi legati al trasferimento del file";
    }
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 2) { // eliminazione documento
    $id_progetto_documento = $_POST["id_progetto_documento"];

    $stmtSel = $db->prepare("SELECT file FROM progetto_documento WHERE progetto_documento.id = ?");
    $stmtSel->execute([$id_progetto_documento]);
    $res = $stmtSel->fetchAll();
    $file = $res[0]["file"];

    $stmtDel = $db->prepare("DELETE FROM progetto_documento WHERE id = ?");
    $db->beginTransaction();
    $stmtDel->execute([$id_progetto_documento]);
    $num = $stmtDel->rowCount();
    $db->commit();

    if ($num > 0) unlink(PROG_DIR_DOC . $file);
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 3) { // modifica nome documento
    $id_progetto_documento = $_POST["id_progetto_documento"];
    $nome = $_POST["nome"];
    if (TUTTO_MINUSCOLO) $nome = strtolower($nome);

    $stmtUpdNome = $db->prepare("UPDATE progetto_documento SET nome = ? WHERE id = ?");
    $db->beginTransaction();
    $stmtUpdNome->execute([$nome, $id_progetto_documento]);
    $db->commit();
  }

  if (isset($_POST["azione"]) && $_POST["azione"] == 4) { // modifica visibilità documento
    $id_progetto_documento = $_POST["id_progetto_documento"];
    $visibilita = $_POST["visibilita"];

    $stmtUpdVis = $db->prepare("UPDATE progetto_documento SET visibilita = ? WHERE id = ?");
    $db->beginTransaction();
    $stmtUpdVis->execute([$visibilita, $id_progetto_documento]);
    $db->commit();
  }

  $stmtProg = $db->prepare("SELECT nome, descrizione FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res = $stmtProg->fetchAll();
  $nome_progetto = $res[0]["nome"];
  $descrizione_progetto = $res[0]["descrizione"];

  if ($_SESSION["ksglobo_livello_utente"] > 1)
    $stmtDoc = $db->prepare("SELECT * FROM progetto_documento WHERE id_progetto = ? AND visibilita = '2' ORDER BY nome, dt_upload DESC");
  else
    $stmtDoc = $db->prepare("SELECT * FROM progetto_documento WHERE id_progetto = ? ORDER BY nome, dt_upload DESC");
  $stmtDoc->execute([$id_progetto]);
  $res_doc = $stmtDoc->fetchAll();
  $num_doc = count($res_doc);

  $_globo_can_upload = ($num_doc < PROG_MAX_NUM_DOC);
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
  <?php /* Shadowbox serve QUI solo per lo spinner di attesa durante l'upload (openLoadingShadowbox
           più sotto): è una comodità, non una dipendenza funzionale. Il require era incondizionato,
           e questa pagina è protetta da MOD_PROGETTI — non da MOD_PROGETTI_DOC — quindi resta
           raggiungibile per URL diretto anche dove il modulo Documenti è spento. Nei deploy che non
           spediscono più la libreria (5stelle esclude shadowbox/ dal pacchetto) bastava quell'URL
           per ottenere un errore fatale. Ora se la libreria non c'è la pagina si carica lo stesso
           e si perde solo lo spinner: vedi la guardia gemella su openLoadingShadowbox. */ ?>
  <?php if (file_exists(__DIR__ . "/shadowbox/shadowbox.php")) require("shadowbox/shadowbox.php"); ?>
  <script>
  <?php /* Pattern 22: globoAlert invece di alert() nativo. async perché globoAlert è basato su
           Promise (modale, non blocca il thread come alert()); il submit del form è quindi
           intercettato e rifatto via JS solo se la validazione passa (vedi sotto). */ ?>
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
      for ($i = 0; $i < count($PROG_DOC_FORM_OK); $i++) {
        echo "estensione != \"" . $PROG_DOC_FORM_OK[$i] . "\"";
        if ($i < (count($PROG_DOC_FORM_OK) - 1)) echo " && ";
      }
      echo ") { await globoAlert(\"formato file non valido\"); document.globo.file.value = \"\"; return false; }";
    ?>
    <?php /* Guardia gemella del require condizionale sopra: senza la libreria la funzione non
             esiste e una ReferenceError qui farebbe fallire controllaForm(), impedendo l'upload.
             Lo spinner è opzionale, il caricamento del documento no. */ ?>
    if (typeof openLoadingShadowbox === "function") openLoadingShadowbox();
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
        <h1 class="page__title"><?= $nome_progetto ?></h1>
        <p class="page__sub">Documenti del progetto</p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza progetto
        </a>
        <a class="btn btn--ghost" href="prog_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco progetti
        </a>
      </div>
    </div>

    <!-- ── Carica documento ─────────────────────────────────── -->
    <form name="globo" action="prog_doc.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
      <input type="hidden" name="azione" value="1" />
      <div class="card" style="margin-bottom: 20px;">
        <div class="card__head">
          <span class="card__head-eyebrow">Carica documento</span>
          <?php if (!$_globo_can_upload): ?>
          <span style="font-size:12px;color:var(--fg-3)">Raggiunto il massimo di <?= PROG_MAX_NUM_DOC ?> documenti</span>
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
                  <?php for ($i = 0; $i < count($PROG_DOC_FORM_OK); $i++) echo $PROG_DOC_FORM_OK[$i] . " "; ?>
                  — max <?= PROG_MAX_DIM_DOC_MB ?> MB
                </span>
              </div>
              <div class="form__control">
                <input type="file" name="file" <?= !$_globo_can_upload ? 'disabled' : '' ?> />
              </div>
            </div>

            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label">Visibilità</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita" <?= !$_globo_can_upload ? 'disabled' : '' ?>>
                    <option value="1">amministratori</option>
                    <option value="2" selected>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="visibilita" value="2" />
            <?php endif; ?>

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
        <span class="card__head-eyebrow">Documenti disponibili</span>
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
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Utente</th>
              <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?><th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center">Visib.</th><?php endif; ?>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center"></th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($res_doc as $r_doc):
            $id_progetto_documento = $r_doc["id"];
            $visibilita = $r_doc["visibilita"];
            $nome = $r_doc["nome"];
            $file = $r_doc["file"];
            $dt_upload = globo_converti_mysql_datetime($r_doc["dt_upload"]);
            $data_upload = globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($dt_upload));
            $ora_upload = globo_ora_da_mysql_datetime($dt_upload);
            $utente_upload = $r_doc["utente_upload"];
            $estensione = substr($file, strpos($file, ".") + 1);
          ?>
          <tr style="border-bottom:1px solid var(--border-1)">
            <td style="padding:8px 20px">
              <form action="prog_doc.php" method="post">
                <input type="text" class="input" style="min-width:260px" maxlength="200" name="nome"
                  value="<?= htmlspecialchars($nome) ?>" onblur="this.form.submit()" />
                <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                <input type="hidden" name="id_progetto_documento" value="<?= (int)$id_progetto_documento ?>" />
                <input type="hidden" name="azione" value="3" />
              </form>
            </td>
            <td style="padding:12px 20px;text-align:center;color:var(--fg-3);font-size:12px"><?= htmlspecialchars($estensione) ?></td>
            <td style="padding:12px 20px;text-align:center;white-space:nowrap;font-size:12px;color:var(--fg-3)"><?= $data_upload ?> <?= $ora_upload ?></td>
            <td style="padding:12px 20px;text-align:center;white-space:nowrap;font-size:12px;color:var(--fg-3)"><?= htmlspecialchars($utente_upload) ?></td>
            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <td style="padding:8px 20px;text-align:center">
              <form action="prog_doc.php" method="post">
                <div class="select-wrap" style="min-width:70px">
                  <select name="visibilita" onchange="this.form.submit()" style="font-size:12px">
                    <option value="1"<?= $visibilita == 1 ? ' selected' : '' ?>>amm</option>
                    <option value="2"<?= $visibilita == 2 ? ' selected' : '' ?>>tutti</option>
                  </select>
                </div>
                <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                <input type="hidden" name="id_progetto_documento" value="<?= (int)$id_progetto_documento ?>" />
                <input type="hidden" name="azione" value="4" />
              </form>
            </td>
            <?php endif; ?>
            <td style="padding:8px 20px;text-align:center">
              <a class="icon-btn" href="prog_file.php?file=<?= urlencode($file) ?>&tipo=1" target="_blank" title="Scarica">
                <svg class="ic-stroke" aria-hidden="true"><use href="#i-download"/></svg>
              </a>
            </td>
            <td style="padding:8px 20px;text-align:center">
              <form action="prog_doc.php" method="post">
                <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                <input type="hidden" name="id_progetto_documento" value="<?= (int)$id_progetto_documento ?>" />
                <input type="hidden" name="azione" value="2" />
                <!-- Pattern 22: conferma via modale del design system (globoConfirm) invece del confirm() nativo -->
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

    <?php /* Pattern 22: globoAlert invece di alert() nativo. Il messaggio è qui (prima di
             footer.php, che definisce window.globoAlert) — differito a DOMContentLoaded,
             che scatta dopo che tutto il documento (incluso lo script di footer.php) è
             stato interpretato. */ ?>
    <?php if (isset($popup)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { globoAlert(<?= json_encode($popup, JSON_UNESCAPED_UNICODE) ?>); });</script>
    <?php endif; ?>

    <?php /* controllaForm() è ora async (globoAlert) — non può più stare in onsubmit="return ...".
             form.submit() dentro il listener non ri-scatena 'submit' → niente ricorsione. */ ?>
    <script>
    document.globo.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (await controllaForm()) document.globo.submit();
    });
    </script>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
