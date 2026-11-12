<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO);

  $giorno  = $_POST["giorno"]  ?? $_GET["giorno"]  ?? '';
  $mese    = $_POST["mese"]    ?? $_GET["mese"]    ?? '';
  $anno    = $_POST["anno"]    ?? $_GET["anno"]    ?? '';
  $cal_vis = $_POST["cal_vis"] ?? $_GET["cal_vis"] ?? $_SESSION["ksglobo_nome_utente"];
  if ($cal_vis == "tutti") $cal_vis = $_SESSION["ksglobo_nome_utente"];

  if (strlen($mese) == 1) $mese = "0$mese";

  $data_mysql = "$anno-$mese-$giorno";
  $ts = mktime(0, 0, 0, (int)$mese, (int)$giorno, (int)$anno);
  $giorno_lettere = strtolower(globo_giorno_in_lettere(date("w", $ts)));
  $mese_lettere   = globo_mese_in_lettere((int)$mese);

  $ts_prev = $ts - 86400;
  $ts_next = $ts + 86400;
  $g_prev  = date("d", $ts_prev); $m_prev = date("m", $ts_prev); $a_prev = date("Y", $ts_prev);
  $g_next  = date("d", $ts_next); $m_next = date("m", $ts_next); $a_next = date("Y", $ts_next);

  $abbr_days  = ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'];
  $label_prec = $abbr_days[(int)date("w", $ts_prev)] . " " . (int)$g_prev . "/" . $m_prev;
  $label_succ = $abbr_days[(int)date("w", $ts_next)] . " " . (int)$g_next . "/" . $m_next;

  $res_utenti = $db->query("SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente")->fetchAll();

  // Pre-fetch impegni del giorno per la griglia oraria
  $stmtImp = $db->prepare("SELECT * FROM impegno WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti') ORDER BY ora");
  $stmtImp->execute([$data_mysql, $cal_vis]);
  $day_events = $stmtImp->fetchAll();

  // Mappa slot_index → evento (slot 0 = CAL_ORA_INIZIO_GIO, passo 0.5h)
  $slot_map = [];
  foreach ($day_events as $ev) {
    $start_dec = ($ev['ora'] == '00:00') ? (float)CAL_ORA_INIZIO_GIO : (float)globo_converti_ora_in_dec($ev['ora']);
    $dur_dec   = ($ev['durata'] == '24:00') ? (float)(CAL_ORA_FINE_GIO - CAL_ORA_INIZIO_GIO) : (float)globo_converti_ora_in_dec($ev['durata']);
    if ($dur_dec <= 0) $dur_dec = 0.5;
    $start_idx = (int)round(($start_dec - CAL_ORA_INIZIO_GIO) * 2);
    $span      = max(1, (int)round($dur_dec * 2));
    for ($i = 0; $i < $span; $i++) {
      $k = $start_idx + $i;
      if (!isset($slot_map[$k]))
        $slot_map[$k] = ['event' => $ev, 'is_start' => ($i == 0), 'start_idx' => $start_idx, 'span' => $span];
    }
  }

  if ($_SESSION["ksglobo_livello_utente"] <= 1) {
    $stmtScad = $db->prepare("SELECT * FROM scadenza WHERE data = ? AND (responsabile = ? OR responsabile = 'tutti') ORDER BY tipo, id");
    $stmtScad->execute([$data_mysql, $cal_vis]);
  } else {
    $stmtScad = $db->prepare("SELECT * FROM scadenza WHERE visibilita = '2' AND data = ? AND (responsabile = ? OR responsabile = 'tutti') ORDER BY tipo, id");
    $stmtScad->execute([$data_mysql, $cal_vis]);
  }
  $res_scad = $stmtScad->fetchAll();

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
        <form action="cal_home.php" method="post" style="display:contents">
          <input type="hidden" name="mese"    value="<?= htmlspecialchars($mese) ?>" />
          <input type="hidden" name="anno"    value="<?= htmlspecialchars($anno) ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="btn btn--ghost">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            Torna alla vista mese
          </button>
        </form>
        <form action="cal_imp_ins_1.php" method="post" style="display:contents">
          <input type="hidden" name="data"    value="<?= htmlspecialchars($data_mysql) ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="btn btn--ghost">Nuovo impegno</button>
        </form>
        <form action="cal_scad_ins_1.php" method="post" style="display:contents">
          <input type="hidden" name="data"    value="<?= htmlspecialchars($data_mysql) ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="btn btn--ghost">Nuova scadenza</button>
        </form>
      </div>
    </div>

    <div class="day-toolbar">
      <div class="cal-toolbar__nav">
        <form action="cal_lista.php" method="post" style="display:contents">
          <input type="hidden" name="giorno"  value="<?= htmlspecialchars($g_prev) ?>" />
          <input type="hidden" name="mese"    value="<?= htmlspecialchars($m_prev) ?>" />
          <input type="hidden" name="anno"    value="<?= htmlspecialchars($a_prev) ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="icon-btn" title="Giorno precedente">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
          </button>
          <button type="submit" class="cal-month-label" title="Vai a <?= htmlspecialchars($label_prec) ?>"><?= htmlspecialchars($label_prec) ?></button>
        </form>
      </div>
      <div class="day-toolbar__title">
        <small><?= htmlspecialchars(ucfirst($giorno_lettere)) ?></small>
        <?= (int)$giorno ?> <?= htmlspecialchars($mese_lettere) ?> <?= $anno ?>
      </div>
      <div class="cal-toolbar__nav">
        <form action="cal_lista.php" method="post" style="display:contents">
          <input type="hidden" name="giorno"  value="<?= htmlspecialchars($g_next) ?>" />
          <input type="hidden" name="mese"    value="<?= htmlspecialchars($m_next) ?>" />
          <input type="hidden" name="anno"    value="<?= htmlspecialchars($a_next) ?>" />
          <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
          <button type="submit" class="cal-month-label" title="Vai a <?= htmlspecialchars($label_succ) ?>"><?= htmlspecialchars($label_succ) ?></button>
          <button type="submit" class="icon-btn" title="Giorno successivo">
            <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
          </button>
        </form>
      </div>
      <div class="day-toolbar__foot">
        <form action="cal_lista.php" method="post" style="display:flex;justify-content:center;width:100%">
          <input type="hidden" name="giorno" value="<?= htmlspecialchars($giorno) ?>" />
          <input type="hidden" name="mese"   value="<?= htmlspecialchars($mese) ?>" />
          <input type="hidden" name="anno"   value="<?= htmlspecialchars($anno) ?>" />
          <div class="select-wrap">
            <select name="cal_vis" onchange="this.form.submit()">
<?php foreach ($res_utenti as $r): ?>
              <option value="<?= htmlspecialchars($r["nome_utente"]) ?>"<?= $cal_vis == $r["nome_utente"] ? ' selected' : '' ?>>impegni di <?= htmlspecialchars($r["nome_utente"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Impegni — <?= htmlspecialchars($cal_vis) ?></span>
      </div>
      <?php $n_body_slots = (CAL_ORA_FINE_GIO - CAL_ORA_INIZIO_GIO) * 2; ?>
      <div class="cal-grid" style="--cal-n-slots:<?= $n_body_slots ?>">
        <div class="cal-grid__axis">
          <?php for ($d = CAL_ORA_INIZIO_GIO; $d < CAL_ORA_FINE_GIO; $d += 0.5):
            $is_hour = (fmod($d, 1.0) == 0.0);
          ?>
          <div class="cal-grid__tick<?= $is_hour ? ' cal-grid__tick--hour' : '' ?>">
            <?= $is_hour ? htmlspecialchars(globo_converti_ora_da_dec($d)) : '' ?>
          </div>
          <?php endfor; ?>
          <div class="cal-grid__tick cal-grid__tick--hour cal-grid__tick--last"><?= htmlspecialchars(globo_converti_ora_da_dec(CAL_ORA_FINE_GIO)) ?></div>
        </div>
        <div class="cal-grid__body">
          <?php for ($idx = 0; $idx < $n_body_slots; $idx++):
            if (isset($slot_map[$idx]) && $slot_map[$idx]['is_start']):
              $entry      = $slot_map[$idx];
              $ev         = $entry['event'];
              $ev_dur_vis = ($ev['durata'] == '24:00') ? 'tutto il giorno' : htmlspecialchars($ev['durata']);
              $ev_is_mine = ($ev['responsabile'] == $_SESSION['ksglobo_nome_utente']);
          ?>
          <div class="cal-event<?= $ev_is_mine ? ' is-attention' : '' ?>" style="--ev-start:<?= $entry['start_idx'] ?>;--ev-span:<?= $entry['span'] ?>">
            <div class="cal-event__body">
              <?php
                $desc_raw  = str_replace(['<br />', '<br/>', '<br>'], "\n", $ev['descrizione']);
                $max_lines = max(2, $entry['span'] * 2 - 1);
                $lines     = explode("\n", $desc_raw);
                if (count($lines) > $max_lines)
                  $desc_raw = implode("\n", array_slice($lines, 0, $max_lines)) . '…';
              ?>
              <span class="cal-event__desc"><?= nl2br($desc_raw) /* desc già HTML; \n→<br>, entità decodificate dal browser */ ?></span>
              <span class="cal-event__meta"><?= htmlspecialchars($ev['responsabile']) ?> · <?= $ev_dur_vis ?></span>
            </div>
            <div class="cal-event__act">
              <form action="cal_imp_vis.php" method="post" style="display:contents">
                <input type="hidden" name="id_impegno" value="<?= (int)$ev['id'] ?>" />
                <input type="hidden" name="cal_vis"    value="<?= htmlspecialchars($cal_vis) ?>" />
                <button type="submit" class="icon-btn" title="Dettagli">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
                </button>
              </form>
              <form action="cal_imp_el.php" method="post" style="display:contents">
                <input type="hidden" name="id_impegno" value="<?= (int)$ev['id'] ?>" />
                <input type="hidden" name="data"       value="<?= htmlspecialchars($data_mysql) ?>" />
                <input type="hidden" name="cal_vis"    value="<?= htmlspecialchars($cal_vis) ?>" />
                <button type="submit" class="icon-btn icon-btn--danger" title="Elimina" data-confirm="Eliminare questo impegno?">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
                </button>
              </form>
            </div>
          </div>
          <?php elseif (!isset($slot_map[$idx])):
            $free_ora = globo_converti_ora_da_dec(CAL_ORA_INIZIO_GIO + $idx * 0.5);
          ?>
          <form action="cal_imp_ins_1.php" method="post" style="display:contents">
            <input type="hidden" name="data"    value="<?= htmlspecialchars($data_mysql) ?>" />
            <input type="hidden" name="ora"     value="<?= htmlspecialchars($free_ora) ?>" />
            <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
            <button type="submit" class="cal-free" style="--ev-start:<?= $idx ?>">
              <span class="cal-free__plus"><svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg></span>
            </button>
          </form>
          <?php endif; ?>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <?php $scad_cols = ($_SESSION["ksglobo_livello_utente"] <= 1) ? '1fr 130px 72px 78px' : '1fr 130px 78px'; ?>
    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Scadenze</span>
      </div>
      <?php if (count($res_scad) == 0): ?>
      <p class="empty-state">Nessuna scadenza.</p>
      <?php else: ?>
      <div class="card__body" style="padding:0">
        <div class="list" style="--list-cols: <?= $scad_cols ?>">
          <div class="list__hdr">
            <span>Descrizione</span>
            <span>Resp</span>
            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?><span>Vis</span><?php endif; ?>
            <span style="text-align:center; white-space:nowrap">Dett.</span>
          </div>
          <?php foreach ($res_scad as $r):
            $scad_is_mine   = ($r["responsabile"] == $_SESSION["ksglobo_nome_utente"] || $r["responsabile"] == "tutti");
            $scad_vis_label = ($r["visibilita"] == 1) ? "amm" : "tutti";
          ?>
          <div class="list__row<?= ($cal_vis == "tutti" && $scad_is_mine) ? ' is-attention' : '' ?>"
               data-href="cal_scad_vis.php?id_scadenza=<?= (int)$r['id'] ?>&cal_vis=<?= urlencode($cal_vis) ?>">
            <span data-label="Descrizione"><?= $r["descrizione"] /* già HTML (con <br>): grezzo */ ?></span>
            <span data-label="Resp"><?= htmlspecialchars($r["responsabile"]) ?></span>
            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?><span data-label="Vis"><span class="chip <?= $r["visibilita"] == 1 ? 'chip--amm' : 'chip--tutti' ?>"><?= htmlspecialchars($scad_vis_label) ?></span></span><?php endif; ?>
            <div class="row-acts">
              <form action="cal_scad_vis.php" method="post" style="display:contents">
                <input type="hidden" name="id_scadenza" value="<?= (int)$r["id"] ?>" />
                <input type="hidden" name="cal_vis"     value="<?= htmlspecialchars($cal_vis) ?>" />
                <button type="submit" class="icon-btn" title="Dettagli">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
                </button>
              </form>
              <form action="cal_scad_el.php" method="post" style="display:contents">
                <input type="hidden" name="id_scadenza" value="<?= (int)$r["id"] ?>" />
                <input type="hidden" name="data"        value="<?= htmlspecialchars($data_mysql) ?>" />
                <input type="hidden" name="cal_vis"     value="<?= htmlspecialchars($cal_vis) ?>" />
                <button type="submit" class="icon-btn icon-btn--danger" title="Elimina" data-confirm="Eliminare questa scadenza?">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-trash"/></svg>
                </button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
