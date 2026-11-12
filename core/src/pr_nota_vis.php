<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? $_GET["id_progetto"] ?? '';
  $id_nota = $_POST["id_nota"] ?? $_GET["id_nota"] ?? '';

  $query_utenti = "SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente";
  $res_utenti = $db->query($query_utenti)->fetchAll();

  $stmtProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res = $stmtProg->fetchAll();
  $nome_progetto = $res[0]["nome"];
  $descrizione_progetto = $res[0]["descrizione"];

  $stmtNota = $db->prepare("SELECT * FROM progetto_nota WHERE id = ?");
  $stmtNota->execute([$id_nota]);
  $res = $stmtNota->fetchAll();
  $tipo = $res[0]["tipo"];
  $visibilita = $res[0]["visibilita"];
  $data_creazione = $res[0]["data_creazione"];
  $utente_creazione = $res[0]["utente_creazione"];
  $descrizione_nota = globo_converti_acapo_da_html($res[0]["descrizione"]);
  $utente_ultima_modifica = $res[0]["utente_ultima_modifica"];
  $dt_ultima_modifica = $res[0]["dt_ultima_modifica"];

  $stmtScad = $db->prepare("SELECT data, responsabile FROM scadenza WHERE id_progetto_nota = ?");
  $stmtScad->execute([$id_nota]);
  $res = $stmtScad->fetchAll();

  if (count($res) > 0) {
    $data_scadenza = $res[0]["data"];
    $responsabile_scadenza = $res[0]["responsabile"];
  } else {
    $data_scadenza = "";
    $responsabile_scadenza = $_SESSION["ksglobo_nome_utente"];
  }

  $can_edit = $_SESSION["ksglobo_livello_utente"] <= 1 || $visibilita == 2;

  if ($tipo != "step" && $can_edit) $elim_ok = true;
  elseif ($tipo == "step" && $_SESSION["ksglobo_livello_utente"] <= 1) $elim_ok = true;
  else $elim_ok = false;

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title"><?= $tipo != "step" ? 'Dettagli nota progetto' : 'Dettagli step progetto' ?></h1>
        <p class="page__sub"><?= $nome_progetto /* già HTML: grezzo */ ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza progetto
        </a>
      </div>
    </div>

    <form name="globo" action="pr_nota_agg.php" method="post">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
      <input type="hidden" name="id_nota" value="<?= (int)$id_nota ?>" />
      <input type="hidden" name="data_creazione" value="<?= htmlspecialchars($data_creazione) ?>" />
      <input type="hidden" name="utente_creazione" value="<?= htmlspecialchars($utente_creazione) ?>" />
      <input type="hidden" name="utente_ultima_modifica" value="<?= htmlspecialchars($utente_ultima_modifica) ?>" />
      <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>" />
      <input type="hidden" name="descrizione_nota_old" value="<?= htmlspecialchars($descrizione_nota) ?>" />
      <input type="hidden" name="data_scadenza_old" value="<?= htmlspecialchars($data_scadenza) ?>" />
      <input type="hidden" name="responsabile_scadenza_old" value="<?= htmlspecialchars($responsabile_scadenza) ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow"><?= $tipo != "step" ? 'Nota' : 'Step' ?></span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label">Visibilità</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1"<?= $visibilita == 1 ? ' selected' : '' ?>>amministratori</option>
                    <option value="2"<?= $visibilita == 2 ? ' selected' : '' ?>>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="visibilita" value="2" />
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">Nome progetto</div>
              <div class="form__control"><?= $nome_progetto /* già HTML: grezzo */ ?></div>
            </div>

            <div class="form__row">
              <div class="form__label">Descrizione progetto</div>
              <div class="form__control"><?= $descrizione_progetto /* già HTML: grezzo */ ?></div>
            </div>

            <?php if ($tipo != "step"): ?>
            <div class="form__row">
              <div class="form__label">Data creazione nota</div>
              <div class="form__control"><?= htmlspecialchars(globo_converti_data_in_formato_ita($data_creazione)) ?></div>
            </div>
            <div class="form__row">
              <div class="form__label">Utente creazione nota</div>
              <div class="form__control"><?= htmlspecialchars($utente_creazione) ?></div>
            </div>
            <?php endif; ?>

            <?php if ($tipo != "step"): ?>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Nota <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_nota" class="textarea" rows="10" required><?= $descrizione_nota /* già HTML (entità + \n): grezzo, il browser decodifica */ ?></textarea>
              </div>
            </div>
            <?php elseif ($_SESSION["ksglobo_livello_utente"] == 0): ?>
            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_nota" class="textarea" rows="10" required><?= $descrizione_nota /* già HTML (entità + \n): grezzo, il browser decodifica */ ?></textarea>
              </div>
            </div>
            <?php else: ?>
            <div class="form__row">
              <div class="form__label">Descrizione</div>
              <div class="form__control"><?= $descrizione_nota /* già HTML: grezzo */ ?></div>
            </div>
            <?php /* grezzo: il browser decodifica nell'attributo → al submit invia il testo
                     vero, che pr_nota_agg ri-codifica. htmlspecialchars qui raddoppierebbe. */ ?>
            <input type="hidden" name="descrizione_nota" value="<?= $descrizione_nota ?>" />
            <?php endif; ?>

            <?php if ($tipo != "step"): ?>
            <div class="form__row">
              <div class="form__label">Ultima modifica</div>
              <div class="form__control"><?= htmlspecialchars($utente_ultima_modifica) ?> — <?= htmlspecialchars(globo_converti_datetime_in_formato_ita($dt_ultima_modifica)) ?></div>
            </div>
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">Data scadenza</div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_scadenza"
                  value="<?= htmlspecialchars(globo_converti_data_in_formato_ita($data_scadenza)) ?>"
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
                <a class="icon-btn" data-clear title="Cancella data">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
                </a>
              </div>
            </div>

            <?php if ($tipo == "step" && globo_controlla_data_datetime($data_scadenza)): ?>
            <div class="form__row">
              <div class="form__label">Aggiorna date scadenze degli altri step</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="aggiorna_scadenze_step">
                    <option value="1" selected>sì</option>
                    <option value="0">no</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="aggiorna_scadenze_step" value="0" />
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">Responsabile scadenza</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="responsabile_scadenza">
                    <option value="tutti"<?= $responsabile_scadenza == "tutti" ? ' selected' : '' ?>>tutti</option>
                    <?php foreach ($res_utenti as $r_utenti):
                      $nu = $r_utenti['nome_utente'];
                    ?>
                    <option value="<?= htmlspecialchars($nu) ?>"<?= $responsabile_scadenza == $nu ? ' selected' : '' ?>><?= htmlspecialchars($nu) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
        <?php if ($can_edit): ?>
        <div class="card__foot">
          <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
        <?php endif; ?>
      </div>

    </form>

    <?php if ($elim_ok): ?>
    <form action="pr_nota_el.php" method="post" style="margin-top:16px">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />
      <input type="hidden" name="id_nota" value="<?= (int)$id_nota ?>" />
      <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questa nota?">Elimina nota</button>
    </form>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
