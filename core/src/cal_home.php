<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $mese    = isset($_POST["mese"])    ? (int)$_POST["mese"]    : (int)date("m");
  $anno    = isset($_POST["anno"])    ? (int)$_POST["anno"]    : (int)date("Y");
  $cal_vis = isset($_POST["cal_vis"]) ? $_POST["cal_vis"]      : $_SESSION["ksglobo_nome_utente"];

  $mese_lettere = globo_mese_in_lettere($mese);

  $res_utenti = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();

  if ($mese == 1)       { $mese_prec = 12; $anno_prec = $anno - 1; $mese_succ = 2;  $anno_succ = $anno;     }
  elseif ($mese == 12)  { $mese_prec = 11; $anno_prec = $anno;     $mese_succ = 1;  $anno_succ = $anno + 1; }
  else                  { $mese_prec = $mese - 1; $anno_prec = $anno; $mese_succ = $mese + 1; $anno_succ = $anno; }

  $mese_lettere_prec = globo_mese_in_lettere($mese_prec);
  $mese_lettere_succ = globo_mese_in_lettere($mese_succ);

  $num_giorni_mese = 1;
  while (checkdate($mese, $num_giorni_mese, $anno)) $num_giorni_mese++;

  $giorno_oggi = (int)date("d");
  $mese_oggi   = (int)date("m");
  $anno_oggi   = (int)date("Y");

  $days_map  = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
  $first_col = $days_map[date('l', mktime(0, 0, 0, $mese, 1, $anno))];

  if ($cal_vis == "tutti") {
    $stmtImpAll    = $db->prepare("SELECT * FROM impegno WHERE data = ?");
    $stmtImpMine   = $db->prepare("SELECT * FROM impegno WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    if ($_SESSION["ksglobo_livello_utente"] <= 1) {
      $stmtScadAll  = $db->prepare("SELECT * FROM scadenza WHERE data = ?");
      $stmtScadMine = $db->prepare("SELECT * FROM scadenza WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    } else {
      $stmtScadAll  = $db->prepare("SELECT * FROM scadenza WHERE visibilita = '2' AND data = ?");
      $stmtScadMine = $db->prepare("SELECT * FROM scadenza WHERE visibilita = '2' AND data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    }
  } else {
    $stmtImpAll  = $db->prepare("SELECT * FROM impegno WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    if ($_SESSION["ksglobo_livello_utente"] <= 1) {
      $stmtScadAll = $db->prepare("SELECT * FROM scadenza WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    } else {
      $stmtScadAll = $db->prepare("SELECT * FROM scadenza WHERE visibilita = '2' AND data = ? AND (responsabile = ? OR responsabile = 'tutti')");
    }
  }

  $stmtUtente = $db->prepare("SELECT * FROM utente WHERE nome_utente = ? AND livello > 0 AND email <> ''");
  $stmtUtente->execute([$_SESSION["ksglobo_nome_utente"]]);
  $has_email = count($stmtUtente->fetchAll()) > 0;

  $cal_days = [];
  for ($g = 1; $g < $num_giorni_mese; $g++) {
    $g_pad  = str_pad($g, 2, "0", STR_PAD_LEFT);
    $data_g = "$anno-" . str_pad($mese, 2, "0", STR_PAD_LEFT) . "-$g_pad";
    if ($cal_vis == "tutti") {
      $stmtImpAll->execute([$data_g]);
      $n_imp  = count($stmtImpAll->fetchAll());
      $stmtImpMine->execute([$data_g, $_SESSION["ksglobo_nome_utente"]]);
      $n_imp_mine  = count($stmtImpMine->fetchAll());
      $stmtScadAll->execute([$data_g]);
      $n_scad = count($stmtScadAll->fetchAll());
      $stmtScadMine->execute([$data_g, $_SESSION["ksglobo_nome_utente"]]);
      $n_scad_mine = count($stmtScadMine->fetchAll());
    } else {
      $stmtImpAll->execute([$data_g, $cal_vis]);
      $n_imp       = count($stmtImpAll->fetchAll());
      $n_imp_mine  = 0;
      $stmtScadAll->execute([$data_g, $cal_vis]);
      $n_scad      = count($stmtScadAll->fetchAll());
      $n_scad_mine = 0;
    }
    if ($_SESSION["ksglobo_livello_utente"] == 0) { $n_imp_mine = 0; $n_scad_mine = 0; }
    $cal_days[$g] = ['pad'=>$g_pad, 'n_imp'=>$n_imp, 'imp_mine'=>$n_imp_mine, 'n_scad'=>$n_scad, 'scad_mine'=>$n_scad_mine];
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
      <div class="page__actions">
        <a href="cal_ric.php" class="btn btn--ghost">Ricerca</a>
        <form action="cal_email_imp_mese.php" method="post" style="display:contents">
          <input type="hidden" name="anno"         value="<?= $anno ?>" />
          <input type="hidden" name="mese"         value="<?= $mese ?>" />
          <input type="hidden" name="cal_vis"      value="<?= htmlspecialchars($cal_vis) ?>" />
          <input type="hidden" name="mese_lettere" value="<?= htmlspecialchars($mese_lettere) ?>" />
          <button type="submit" class="btn btn--ghost"<?= !$has_email ? ' disabled' : '' ?>>Ricevi via e-mail</button>
        </form>
        <form action="cal_vcs.php" method="post" target="_blank" style="display:contents">
          <input type="hidden" name="anno" value="<?= $anno ?>" />
          <input type="hidden" name="mese" value="<?= $mese ?>" />
          <button type="submit" class="btn btn--ghost"<?= $_SESSION["ksglobo_livello_utente"] == 0 ? ' disabled' : '' ?>>Scarica .vcs</button>
        </form>
      </div>
    </div>

    <div class="cal-toolbar">
      <div class="cal-toolbar__nav">
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese"    value="<?= $mese_prec ?>" />
          <input type="hidden" name="anno"    value="<?= $anno_prec ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="icon-btn" title="Mese precedente">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
          </button>
          <button type="submit" class="cal-month-label" title="Vai a <?= htmlspecialchars($mese_lettere_prec) ?>"><?= htmlspecialchars($mese_lettere_prec) ?></button>
        </form>
      </div>
      <div class="cal-month-picker">
        <button type="button" class="cal-month-picker__trigger" id="monthPickerTrigger" aria-expanded="false">
          <?= htmlspecialchars(strtolower($mese_lettere)) ?> <?= $anno ?>
        </button>
        <div class="cal-month-picker__dropdown" id="monthPickerDropdown" hidden>
          <div class="cal-month-picker__year-nav">
            <button type="button" class="icon-btn" id="pickerYearPrev" title="Anno precedente">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
            </button>
            <span class="cal-month-picker__year-label" id="pickerYearLabel"><?= $anno ?></span>
            <button type="button" class="icon-btn" id="pickerYearNext" title="Anno successivo">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
            </button>
          </div>
          <div class="cal-month-picker__grid">
            <?php
            $mesi_brevi = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
            foreach ($mesi_brevi as $i => $nome_mese_breve):
              $m_val = $i + 1;
            ?>
            <form action="cal_home.php" method="post" style="display:contents">
              <input type="hidden" name="mese"    value="<?= $m_val ?>" />
              <input type="hidden" name="anno"    value="<?= $anno ?>" class="picker-anno-input" />
              <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
              <button type="submit" class="cal-month-picker__btn<?= $m_val == $mese ? ' cal-month-picker__btn--active' : '' ?>">
                <?= $nome_mese_breve ?>
              </button>
            </form>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="cal-toolbar__nav">
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese"    value="<?= $mese_succ ?>" />
          <input type="hidden" name="anno"    value="<?= $anno_succ ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="cal-month-label" title="Vai a <?= htmlspecialchars($mese_lettere_succ) ?>"><?= htmlspecialchars($mese_lettere_succ) ?></button>
          <button type="submit" class="icon-btn" title="Mese successivo">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
          </button>
        </form>
      </div>
      <div class="cal-toolbar__foot">
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese" value="<?= $mese ?>" />
          <input type="hidden" name="anno" value="<?= $anno ?>" />
          <div class="select-wrap">
            <select name="cal_vis" onchange="this.form.submit()">
              <option value="tutti"<?= $cal_vis == "tutti" ? ' selected' : '' ?>>tutti gli utenti</option>
              <?php foreach ($res_utenti as $r): ?>
              <option value="<?= htmlspecialchars($r["nome_utente"]) ?>"<?= $cal_vis == $r["nome_utente"] ? ' selected' : '' ?>>impegni di <?= htmlspecialchars($r["nome_utente"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
    </div>
    <script>
    (function() {
      var trigger   = document.getElementById('monthPickerTrigger');
      var dropdown  = document.getElementById('monthPickerDropdown');
      var yearLabel = document.getElementById('pickerYearLabel');
      var yearPrev  = document.getElementById('pickerYearPrev');
      var yearNext  = document.getElementById('pickerYearNext');
      var pickerYear = <?= (int)$anno ?>;

      function setYear(y) {
        pickerYear = y;
        yearLabel.textContent = y;
        dropdown.querySelectorAll('.picker-anno-input').forEach(function(inp) { inp.value = y; });
      }

      trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        var opening = dropdown.hidden;
        dropdown.hidden = !opening;
        trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
      });

      yearPrev.addEventListener('click', function(e) { e.stopPropagation(); setYear(pickerYear - 1); });
      yearNext.addEventListener('click', function(e) { e.stopPropagation(); setYear(pickerYear + 1); });

      document.addEventListener('click', function(e) {
        if (!dropdown.hidden && !dropdown.contains(e.target) && e.target !== trigger) {
          dropdown.hidden = true;
          trigger.setAttribute('aria-expanded', 'false');
        }
      });
    })();
    </script>

    <div class="cal-month">
      <div class="cal-month__weekdays">
        <?php foreach (['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $h): ?>
        <div><?= $h ?></div>
        <?php endforeach; ?>
      </div>
      <div class="cal-month__grid">
        <?php for ($i = 1; $i < $first_col; $i++): ?>
        <div class="cal-day cal-day--empty"></div>
        <?php endfor; ?>
        <?php foreach ($cal_days as $g => $day):
          $is_today    = ($g == $giorno_oggi && $mese == $mese_oggi && $anno == $anno_oggi);
          $has_events  = ($day['n_imp'] + $day['n_scad']) > 0;
          $counter_cls = $has_events ? 'cal-day__counter--alert' : 'cal-day__counter--empty';
        ?>
        <form action="cal_lista.php" method="post" style="display:contents">
          <input type="hidden" name="giorno"  value="<?= $day['pad'] ?>" />
          <input type="hidden" name="mese"    value="<?= $mese ?>" />
          <input type="hidden" name="anno"    value="<?= $anno ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="cal-day<?= $is_today ? ' cal-day--today' : '' ?>">
            <span class="cal-day__num"><?= (int)$g ?></span>
            <div class="cal-day__counters">
              <span class="cal-day__counter <?= $counter_cls ?>"><b><?= $day['n_imp'] ?></b> imp · <b><?= $day['n_scad'] ?></b> scad</span>
            </div>
          </button>
        </form>
        <?php endforeach; ?>
      </div>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
