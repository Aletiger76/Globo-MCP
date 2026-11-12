<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  if (isset($_POST["id_progetto"])) $id_progetto = $_POST["id_progetto"];
  else if (isset($_GET["id"])) $id_progetto = $_GET["id"];

/*** azioni per progetto con procedura ***/
  if (isset($_POST["azione"]) && ($_POST["azione"] == 1 || $_POST["azione"] == 2 || $_POST["azione"] == 3 || $_POST["azione"] == 4)) {
    $id_progetto_procedura_step = $_POST["id_progetto_procedura_step"] ?? '';
    $azione = $_POST["azione"];

    $db->beginTransaction();
    try {
      if ($azione == 1) {
        $stmt1 = $db->prepare("INSERT INTO progetto_procedura_step_chiuso (id_progetto, id_progetto_procedura_step, utente) VALUES (?, ?, ?)");
        $stmt1->execute([$id_progetto, $id_progetto_procedura_step, $_SESSION["ksglobo_nome_utente"]]);
        $num_1 = $stmt1->rowCount();
      }
      else if ($azione == 2) {
        $stmt1 = $db->prepare("DELETE FROM progetto_procedura_step_chiuso WHERE id_progetto = ? AND id_progetto_procedura_step = ?");
        $stmt1->execute([$id_progetto, $id_progetto_procedura_step]);
        $num_1 = $stmt1->rowCount();
      }
      else if ($azione == 3) {
        $stmt1 = $db->prepare("INSERT INTO progetto_procedura_step_non_attivo (id_progetto, id_progetto_procedura_step, utente) VALUES (?, ?, ?)");
        $stmt1->execute([$id_progetto, $id_progetto_procedura_step, $_SESSION["ksglobo_nome_utente"]]);
        $num_1 = $stmt1->rowCount();
      }
      else if ($azione == 4) {
        $stmt1 = $db->prepare("DELETE FROM progetto_procedura_step_non_attivo WHERE id_progetto = ? AND id_progetto_procedura_step = ?");
        $stmt1->execute([$id_progetto, $id_progetto_procedura_step]);
        $num_1 = $stmt1->rowCount();
      }
    } catch (PDOException $e) { $num_1 = 0; }
    $db->commit();

    if ($azione == 1 || $azione == 2) {
      if ($num_1 > 0) {
        $stmt2 = $db->prepare("SELECT * FROM progetto JOIN progetto_procedura ON progetto_procedura.id = progetto.id_progetto_procedura JOIN progetto_procedura_step ON progetto_procedura_step.id_progetto_procedura = progetto_procedura.id WHERE progetto.id = ? AND progetto_procedura_step.id = ?");
        $stmt2->execute([$id_progetto, $id_progetto_procedura_step]);
        $res_2 = $stmt2->fetchAll();
        $num_2 = count($res_2);

        if ($num_2 > 0) {
          $nome_progetto = $res_2[0]["nome"];
          $descrizione_progetto = $res_2[0]["descrizione"];
          $descrizione_progetto_procedura = $res_2[0]["descrizione"];
          $descrizione_progetto_procedura_step = $res_2[0]["descrizione"];

          if ($azione == 1) $oggetto = "chiusura step procedura - $descrizione_progetto_procedura";
          else $oggetto = "apertura step procedura - $descrizione_progetto_procedura";
          $testo = "progetto: $nome_progetto<br />";
          $testo .= "descrizione: $descrizione_progetto<br />";
          $testo .= "step: $descrizione_progetto_procedura_step<br />";
          $testo .= "utente: " . $_SESSION["ksglobo_nome_utente"];

          // globo_invia_messaggio($db, 1, 1, 1, $oggetto, $testo, 1, "", "");
        }
      }
    }
  }

/*** azioni per progetto con template ***/
  if (isset($_POST["azione"]) && $_POST["azione"] == 5) {
    $id_nota = $_POST["id_nota"] ?? '';

    $stmtDelScad = $db->prepare("DELETE FROM scadenza WHERE id_progetto_nota = ?");
    $db->beginTransaction();
    try { $stmtDelScad->execute([$id_nota]); } catch (PDOException $e) { }
    $db->commit();
  }

/*** dati progetto ***/
  $stmtProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res_1 = $stmtProg->fetchAll();

  $id_progetto_procedura = $res_1[0]["id_progetto_procedura"];
  $visibilita = $res_1[0]["visibilita"];
  $data_creazione = $res_1[0]["data_creazione"];
  $flag_archivio = $res_1[0]["flag_archivio"];
  $data_archiviazione = $res_1[0]["data_archiviazione"];
  $nome_progetto = $res_1[0]["nome"];
  $descrizione_progetto = globo_converti_acapo_da_html($res_1[0]["descrizione"]);

  $query_proc = "SELECT * FROM progetto_procedura ORDER BY descrizione";
  $res_proc = $db->query($query_proc)->fetchAll();
  $num_proc = count($res_proc);

  $_globo_can_edit = !($flag_archivio == 1 || (PROG_AGG_ARCH_ELIM_SOLO_ADMIN && $_SESSION["ksglobo_livello_utente"] > 1));

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $nome_progetto ?></h1>
        <?php if ($flag_archivio == 1): ?>
        <p class="page__sub">Progetto archiviato</p>
        <?php endif; ?>
      </div>
      <div class="page__actions">
        <?php if ($flag_archivio == 0): ?>
        <a class="btn btn--ghost" href="pr_nota_ins_1.php?id_progetto=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuova nota
        </a>
        <?php endif; ?>
        <?php if (defined('MOD_PROGETTI_DOC') && MOD_PROGETTI_DOC && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI_DOC): ?>
        <a class="btn btn--ghost" href="prog_doc.php?id=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg>
          Documenti
        </a>
        <?php endif; ?>
        <?php if ($flag_archivio == 1): ?>
        <a class="btn btn--ghost" href="prog_lista.php?a=1">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Archivio progetti
        </a>
        <?php else: ?>
        <a class="btn btn--ghost" href="prog_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco progetti
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Dettagli progetto ──────────────────────────────── -->
    <form name="globo" action="prog_agg.php" method="post">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
      <input type="hidden" name="nome_progetto_old" value="<?= $nome_progetto ?>" />
      <input type="hidden" name="descrizione_progetto_old" value="<?= $descrizione_progetto ?>" />
      <div class="card" style="margin-bottom: 12px;">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli progetto</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label">Visibilità</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1"<?= $visibilita == "1" ? ' selected' : '' ?>>amministratori</option>
                    <option value="2"<?= $visibilita == "2" ? ' selected' : '' ?>>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="visibilita" value="2" />
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">Data creazione</div>
              <div class="form__control form__control--ro"><?= globo_converti_data_in_formato_ita($data_creazione) ?></div>
            </div>

            <?php if ($flag_archivio == 1): ?>
            <div class="form__row">
              <div class="form__label">Data archiviazione</div>
              <div class="form__control form__control--ro"><?= globo_converti_data_in_formato_ita($data_archiviazione) ?></div>
            </div>
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">
                  Nome
                  <?php if ($_globo_can_edit): ?><span class="form__hint form__hint--inline">obb</span><?php endif; ?>
                </span>
              </div>
              <div class="form__control">
                <input type="text" class="input" maxlength="255" name="nome_progetto"
                  value="<?= $nome_progetto ?>"
                  <?= !$_globo_can_edit ? 'readonly' : 'required' ?> />
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">
                  Descrizione
                  <?php if ($_globo_can_edit): ?><span class="form__hint form__hint--inline">obb</span><?php endif; ?>
                </span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_progetto" class="textarea" rows="6"<?= $_globo_can_edit ? ' required' : '' ?>><?= $descrizione_progetto ?></textarea>
              </div>
            </div>

            <?php if ($num_proc > 0): ?>
            <div class="form__row">
              <div class="form__label">Procedura</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="id_progetto_procedura">
                    <option value="0"<?= $id_progetto_procedura == 0 ? ' selected' : '' ?>>—</option>
                    <?php foreach ($res_proc as $r_proc):
                      $id_proc_tmp = $r_proc["id"];
                      $desc_proc = $r_proc["descrizione"];
                    ?>
                    <option value="<?= $id_proc_tmp ?>"<?= $id_proc_tmp == $id_progetto_procedura ? ' selected' : '' ?>><?= $desc_proc /* descrizione procedura: già HTML, grezzo */ ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="id_progetto_procedura" value="0" />
            <?php endif; ?>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
        <div class="card__foot">
          <?php if ($_globo_can_edit): ?>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
          <?php else: ?>
          <button type="submit" class="btn btn--primary" disabled>Aggiorna</button>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <!-- Archivia / Ripristina / Ricerca contatto -->
    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom: 28px;">
      <?php if (defined('PROC_ARC') && PROC_ARC): ?>
      <?php if ($flag_archivio == 1): ?>
      <form action="prog_arc.php" method="post">
        <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
        <?php if (PROG_AGG_ARCH_ELIM_SOLO_ADMIN && $_SESSION["ksglobo_livello_utente"] > 1): ?>
        <button type="submit" class="btn btn--ghost" disabled>Ripristina</button>
        <?php else: ?>
        <button type="submit" class="btn btn--ghost" data-confirm="Ripristinare questo progetto?">Ripristina</button>
        <?php endif; ?>
      </form>
      <?php else: ?>
      <form action="prog_arc.php" method="post">
        <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
        <?php if (PROG_AGG_ARCH_ELIM_SOLO_ADMIN && $_SESSION["ksglobo_livello_utente"] > 1): ?>
        <button type="submit" class="btn btn--ghost" disabled>Archivia</button>
        <?php else: ?>
        <button type="submit" class="btn btn--ghost" data-confirm="Archiviare questo progetto?">Archivia</button>
        <?php endif; ?>
      </form>
      <?php endif; ?>
      <?php endif; ?>

      <?php if (defined('PROG_RIC_CONT') && PROG_RIC_CONT && defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI): ?>
      <form action="cont_lista.php?p=<?= (int)$id_progetto ?>" method="post">
        <input type="hidden" name="ricerca_testo" value="<?= $nome_progetto ?>" />
        <button type="submit" class="btn btn--ghost">Ricerca contatto</button>
      </form>
      <?php endif; ?>
    </div>

    <!-- ── Step procedura ──────────────────────────────────── -->
    <?php if ($id_progetto_procedura > 0):
      $stmtStepProc = $db->prepare("SELECT * FROM progetto_procedura_step WHERE id_progetto_procedura = ? ORDER BY data_scadenza, id");
      $stmtStepProc->execute([$id_progetto_procedura]);
      $res_2 = $stmtStepProc->fetchAll();
      $num_2 = count($res_2);
    ?>
    <?php if ($num_2 > 0): ?>
    <div class="card" style="margin-bottom: 20px;">
      <div class="card__head">
        <span class="card__head-eyebrow"><a name="procedura"></a>Step procedura</span>
      </div>
      <div class="card__body" style="padding:0; overflow-x:auto">
        <table class="detail-table" style="width:100%;border-collapse:collapse;font-size:14px">
          <thead>
            <tr style="background:var(--n-25);border-bottom:1px solid var(--border-1)">
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;width:44px">#</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:left">Descrizione</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Scadenza</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center">Stato</th>
              <?php if ($flag_archivio == 0): ?><th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center"></th><?php endif; ?>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Chiuso da</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $stmtNonAttivo = $db->prepare("SELECT * FROM progetto_procedura_step_non_attivo WHERE id_progetto = ? AND id_progetto_procedura_step = ?");
          $stmtChiuso = $db->prepare("SELECT * FROM progetto_procedura_step_chiuso WHERE id_progetto = ? AND id_progetto_procedura_step = ?");
          $i = 0;
          foreach ($res_2 as $r_2):
            $id_progetto_procedura_step = $r_2["id"];
            $descrizione = $r_2["descrizione"]; /* già HTML: niente ri-codifica (era doppia → "&#39;") */
            $data_scadenza = $r_2["data_scadenza"];

            $stmtNonAttivo->execute([$id_progetto, $id_progetto_procedura_step]);
            $num_3 = count($stmtNonAttivo->fetchAll());
            $flag_stato_1 = ($num_3 > 0) ? 0 : 1;
            $col = $num_3 > 0 ? 'var(--fg-4)' : 'var(--fg-1)';

            if ($flag_stato_1 == 1) {
              $stmtChiuso->execute([$id_progetto, $id_progetto_procedura_step]);
              $res_4 = $stmtChiuso->fetchAll();
              $num_4 = count($res_4);
              if ($num_4 > 0) {
                $flag_stato_2 = 0;
                $col = 'var(--fg-1)';
                $chiuso_da = $res_4[0]["utente"] . ' · ' . globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($res_4[0]["dt_ultima_modifica"]));
              } else {
                $flag_stato_2 = 1;
                $col = $flag_archivio == 0 ? 'var(--brand)' : 'var(--fg-1)';
                $chiuso_da = '';
              }
            } else {
              $flag_stato_2 = 0;
              $chiuso_da = '';
            }
          ?>
          <tr style="border-bottom:1px solid var(--border-1)">
            <td style="padding:12px 20px;text-align:center;color:<?= $col ?>"><?= ($i + 1) ?></td>
            <td style="padding:12px 20px;color:<?= $col ?>"><?= $descrizione ?></td>
            <td style="padding:12px 20px;text-align:center;white-space:nowrap;color:<?= $col ?>"><?= globo_converti_data_in_formato_ita($data_scadenza) ?></td>
            <td style="padding:8px 20px;text-align:center">
              <?php if ($flag_archivio == 0): ?>
              <form action="prog_vis.php#procedura" method="post" style="display:inline">
                <div class="select-wrap">
                  <select name="azione" onchange="this.form.submit()" style="font-size:12px">
                    <option value="4"<?= $flag_stato_1 == 1 ? ' selected' : '' ?>>attivo</option>
                    <option value="3"<?= $flag_stato_1 == 0 ? ' selected' : '' ?>>non attivo</option>
                  </select>
                </div>
                <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                <input type="hidden" name="id_progetto_procedura_step" value="<?= (int)$id_progetto_procedura_step ?>" />
              </form>
              <?php else: ?>
              <span style="font-size:12px;color:var(--fg-3)"><?= $flag_stato_1 == 1 ? 'attivo' : 'non attivo' ?></span>
              <?php endif; ?>
            </td>
            <?php if ($flag_archivio == 0): ?>
            <td style="padding:8px 20px;text-align:center">
              <?php if ($flag_stato_1 == 1): ?>
              <form action="prog_vis.php#procedura" method="post" style="display:inline">
                <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                <input type="hidden" name="id_progetto_procedura_step" value="<?= (int)$id_progetto_procedura_step ?>" />
                <?php if ($flag_stato_2 == 1): ?>
                <input type="hidden" name="azione" value="1" />
                <button type="submit" class="icon-btn" title="Chiudi step">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
                </button>
                <?php else: ?>
                <input type="hidden" name="azione" value="2" />
                <button type="submit" class="icon-btn" title="Riapri step">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
                </button>
                <?php endif; ?>
              </form>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="padding:12px 20px;text-align:center;white-space:nowrap;font-size:12px;color:var(--fg-3)"><?= htmlspecialchars($chiuso_da) ?></td>
          </tr>
          <?php $i++; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Step template ───────────────────────────────────── -->
    <?php if ($flag_archivio == 0):
      $stmtNoteStep = $db->prepare("SELECT * FROM progetto_nota WHERE id_progetto = ? AND tipo = 'step' ORDER BY id");
      $stmtNoteStep->execute([$id_progetto]);
      $res_2 = $stmtNoteStep->fetchAll();
      $num_2 = count($res_2);
    ?>
    <?php if ($num_2 > 0): ?>
    <div class="card" style="margin-bottom: 20px;">
      <div class="card__head">
        <span class="card__head-eyebrow"><a name="template"></a>Step progetto</span>
      </div>
      <div class="card__body" style="padding:0; overflow-x:auto">
        <table class="detail-table" style="width:100%;border-collapse:collapse;font-size:14px">
          <thead>
            <tr style="background:var(--n-25);border-bottom:1px solid var(--border-1)">
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;width:44px">#</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:left">Descrizione</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Data scadenza</th>
              <th style="padding:12px 20px;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--fg-4);text-align:center;white-space:nowrap">Responsabile</th>
              <th style="width:36px"></th>
            </tr>
          </thead>
          <tbody>
          <?php
          $stmtScadStep = $db->prepare("SELECT data, responsabile FROM scadenza WHERE id_progetto_nota = ?");
          $i = 0;
          foreach ($res_2 as $r_2):
            $id_nota = $r_2["id"];
            $descrizione = $r_2["descrizione"]; /* già HTML: niente ri-codifica (era doppia → "&#39;") */
            $stmtScadStep->execute([$id_nota]);
            $res_3 = $stmtScadStep->fetchAll();
            $num_3 = count($res_3);
            if ($num_3 > 0) {
              $col = $flag_archivio == 0 ? 'var(--brand)' : 'var(--fg-1)';
              $data_scadenza = $res_3[0]["data"];
              $responsabile_scadenza = $res_3[0]["responsabile"];
            } else {
              $col = 'var(--fg-1)';
              $data_scadenza = '';
              $responsabile_scadenza = '';
            }
          ?>
          <tr style="border-bottom:1px solid var(--border-1);cursor:pointer" onclick="window.location.href='pr_nota_vis.php?id_progetto=<?= (int)$id_progetto ?>&id_nota=<?= (int)$id_nota ?>'">
            <td style="padding:12px 20px;text-align:center;color:<?= $col ?>"><?= ($i + 1) ?></td>
            <td style="padding:12px 20px;color:<?= $col ?>"><?= $descrizione ?></td>
            <td style="padding:8px 20px;text-align:center;white-space:nowrap">
              <div style="display:inline-flex;align-items:center;gap:8px">
                <span style="color:<?= $col ?>"><?= globo_converti_data_in_formato_ita($data_scadenza) ?></span>
                <?php if ($data_scadenza != ''): ?>
                <form action="prog_vis.php#template" method="post" style="display:inline" onclick="event.stopPropagation()">
                  <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
                  <input type="hidden" name="id_nota" value="<?= (int)$id_nota ?>" />
                  <input type="hidden" name="azione" value="5" />
                  <button type="submit" class="icon-btn" title="Elimina scadenza">
                    <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
            <td style="padding:12px 20px;text-align:center;color:<?= $col ?>;font-size:12px"><?= htmlspecialchars($responsabile_scadenza) ?></td>
            <td style="padding:8px 20px;text-align:center">
              <a class="icon-btn" href="pr_nota_vis.php?id_progetto=<?= (int)$id_progetto ?>&id_nota=<?= (int)$id_nota ?>" onclick="event.stopPropagation()" title="Visualizza">
                <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
              </a>
            </td>
          </tr>
          <?php $i++; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Note progetto ───────────────────────────────────── -->
    <?php
    if ($_SESSION["ksglobo_livello_utente"] <= 1) {
      $stmtNote = $db->prepare("SELECT * FROM progetto_nota WHERE id_progetto = ? AND tipo <> 'step' ORDER BY data_creazione DESC, id DESC");
    } else {
      $stmtNote = $db->prepare("SELECT * FROM progetto_nota WHERE id_progetto = ? AND tipo <> 'step' AND visibilita = '2' ORDER BY data_creazione DESC, id DESC");
    }
    $stmtNote->execute([$id_progetto]);
    $res_2 = $stmtNote->fetchAll();
    $num_2 = count($res_2);
    ?>
    <div class="card">
      <div class="card__head">
        <span class="card__head-eyebrow">Note progetto</span>
      </div>
      <?php if ($num_2 <= 0): ?>
      <p class="empty-state">Nessuna nota.</p>
      <?php else: ?>
      <div class="list list--notes list--left list--ruled" style="border-radius:0; box-shadow:none; --list-cols: 104px 1.26fr 5.14fr <?= $_SESSION["ksglobo_livello_utente"] <= 1 ? '72px ' : '' ?>58px;">
        <div class="list__hdr">
          <span>Data</span>
          <span>Autore</span>
          <span>Descrizione</span>
          <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?><span>Visib.</span><?php endif; ?>
          <span style="text-align:center; white-space:nowrap">Dett.</span>
        </div>
        <?php
        $stmtScadNota = $db->prepare("SELECT id FROM scadenza WHERE id_progetto_nota = ?");
        foreach ($res_2 as $r_2):
          $id_nota = $r_2["id"];
          $visibilita = $r_2["visibilita"];
          $data_creazione = $r_2["data_creazione"];
          $utente_creazione = $r_2["utente_creazione"];
          $descrizione = $r_2["descrizione"];

          $stmtScadNota->execute([$id_nota]);
          $_globo_nota_has_scad = (count($stmtScadNota->fetchAll()) > 0);
        ?>
        <div class="list__row<?= $_globo_nota_has_scad ? ' is-attention' : '' ?>"
             style="cursor:pointer"
             onclick="window.location.href='pr_nota_vis.php?id_progetto=<?= (int)$id_progetto ?>&id_nota=<?= (int)$id_nota ?>'">
          <div class="list-meta" style="white-space:nowrap" data-label="Data"><?= globo_converti_data_in_formato_ita($data_creazione) ?></div>
          <div class="list-meta" data-label="Autore"><?= htmlspecialchars($utente_creazione) ?></div>
          <div class="list-desc" data-label="Descrizione"><?= $descrizione ?></div>
          <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
          <div data-label="Visib." style="display:flex;align-items:center;justify-content:flex-start">
            <span class="chip <?= $visibilita == 1 ? 'chip--amm' : 'chip--tutti' ?>"><?= $visibilita == 1 ? 'amm' : 'tutti' ?></span>
          </div>
          <?php endif; ?>
          <div class="row-acts">
            <a class="icon-btn" href="pr_nota_vis.php?id_progetto=<?= (int)$id_progetto ?>&id_nota=<?= (int)$id_nota ?>" onclick="event.stopPropagation()" title="Visualizza nota">
              <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
            </a>
          </div>
          <?php if ($_globo_nota_has_scad): ?><span class="attention-dot" aria-hidden="true"></span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ($flag_archivio == 0): ?>
      <div class="card__foot">
        <a class="btn btn--ghost" href="pr_nota_ins_1.php?id_progetto=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
          Nuova nota
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Elimina progetto ─────────────────────────────────
         Ripristinato (nel baseline era commentato). Handler: prog_el.php
         (cancella a cascata note, scadenze, documenti). Gating come Archivia:
         PROG_AGG_ARCH_ELIM_SOLO_ADMIN + livello. -->
    <?php if (PROG_AGG_ARCH_ELIM_SOLO_ADMIN && $_SESSION["ksglobo_livello_utente"] > 1): ?>
    <form method="post" style="margin-top:16px">
      <button type="submit" class="btn btn--danger-ghost" disabled>Elimina progetto</button>
    </form>
    <?php else: ?>
    <form action="prog_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare definitivamente questo progetto? Verranno rimossi anche note, scadenze e documenti. Operazione non reversibile.">Elimina progetto</button>
    </form>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
