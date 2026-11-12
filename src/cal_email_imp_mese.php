<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $anno        = $_POST["anno"]        ?? '';
  $mese        = $_POST["mese"]        ?? '';
  if (strlen($mese) == 1) $mese = "0$mese";
  $mese_lettere = strtolower($_POST["mese_lettere"] ?? '');

  $stmtImp = $db->prepare("SELECT * FROM impegno WHERE data LIKE ? AND (responsabile = ? OR responsabile = 'tutti') ORDER BY data, ora");
  $stmtImp->execute(["$anno-$mese-%", $_SESSION["ksglobo_nome_utente"]]);
  $res = $stmtImp->fetchAll();
  $num = count($res);

  $_globo_msgs = [];

  if ($num <= 0) {
    $_globo_msgs[] = ['type' => 'ok', 'text' => 'Nessun impegno.'];
  } else {
    $lista_impegni = "";
    foreach ($res as $r) {
      $data      = $r["data"];
      $ora       = $r["ora"];
      $durata    = $r["durata"];
      $descrizione = globo_converti_da_html($r["descrizione"]);
      $giorno    = "$data[8]" . "$data[9]";
      $giorno_sett = date('l', mktime(0, 0, 0, $mese, $giorno, $anno));
      $nomi_it = ['Monday'=>'lun','Tuesday'=>'mar','Wednesday'=>'mer','Thursday'=>'gio','Friday'=>'ven','Saturday'=>'sab','Sunday'=>'dom'];
      $giorno_sett = $nomi_it[$giorno_sett] ?? $giorno_sett;
      $lista_impegni .= "data: $giorno_sett $giorno<br />";
      $lista_impegni .= "ora: $ora<br />";
      $lista_impegni .= "durata: $durata<br />";
      $lista_impegni .= "descrizione: $descrizione<br /><br />";
    }
    $oggetto = "impegni " . $_SESSION["ksglobo_nome_utente"] . " $mese_lettere $anno";
    $esito = globo_invia_messaggio($db, 1, 1, 0, $oggetto, $lista_impegni, "", $_SESSION["ksglobo_nome_utente"], "");
    if ($esito["email"] > 0)
      $_globo_msgs[] = ['type' => 'ok',  'text' => 'Lista impegni inviata.'];
    else
      $_globo_msgs[] = ['type' => 'err', 'text' => 'Lista impegni non inviata.'];
  }

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Calendario</h1>
      </div>
    </div>

    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Risultato operazione</span>
      </div>
      <div class="card__body">
        <?php foreach ($_globo_msgs as $msg): ?>
        <p class="<?= $msg['type'] === 'ok' ? 'msg-ok' : 'msg-err' ?>"><?= htmlspecialchars($msg['text']) ?></p>
        <?php endforeach; ?>
      </div>
      <div class="card__foot">
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese" value="<?= $mese ?>" />
          <input type="hidden" name="anno" value="<?= $anno ?>" />
          <button type="submit" class="btn btn--primary">Calendario</button>
        </form>
      </div>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
