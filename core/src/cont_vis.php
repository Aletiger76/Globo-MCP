<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

  $sv     = isset($_GET["sv"]);
  $has_p  = isset($_GET["p"])  && MOD_PROGETTI  && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI;
  $has_ld = isset($_GET["ld"]) && MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER;

  $id_contatto = $_POST["id_contatto"] ?? ($_GET["id"] ?? '');

  $stmt = $db->prepare("SELECT * FROM contatto WHERE id = ?");
  $stmt->execute([$id_contatto]);
  $res = $stmt->fetchAll();

  $azienda   = globo_converti_virgolette_apici_in_html($res[0]["azienda"]);
  $nome      = globo_converti_virgolette_apici_in_html($res[0]["nome"]);
  $cognome   = globo_converti_virgolette_apici_in_html($res[0]["cognome"]);
  $qualifica = globo_converti_virgolette_apici_in_html($res[0]["qualifica"]);
  $telefono  = globo_converti_virgolette_apici_in_html($res[0]["telefono"]);
  $cellulare = globo_converti_virgolette_apici_in_html($res[0]["cellulare"]);
  $fax       = globo_converti_virgolette_apici_in_html($res[0]["fax"]);
  $email     = $res[0]["email"];
  $sito      = $res[0]["sito"];
  $indirizzo = globo_converti_virgolette_apici_in_html($res[0]["indirizzo"]);
  $cap       = globo_converti_virgolette_apici_in_html($res[0]["cap"]);
  $citta     = globo_converti_virgolette_apici_in_html($res[0]["citta"]);
  $provincia = globo_converti_virgolette_apici_in_html($res[0]["provincia"]);
  $regione   = globo_converti_virgolette_apici_in_html($res[0]["regione"]);
  $area      = globo_converti_virgolette_apici_in_html($res[0]["area"]);
  $nazione   = globo_converti_virgolette_apici_in_html($res[0]["nazione"]);
  $pi        = globo_converti_virgolette_apici_in_html($res[0]["pi"]);
  $cf        = globo_converti_virgolette_apici_in_html($res[0]["cf"]);
  $note      = globo_converti_acapo_da_html($res[0]["note"]);
  $nl_flag_confermato = $res[0]["nl_flag_confermato"];
  $dt_inserimento     = $res[0]["dt_inserimento"];
  $dt_ultima_modifica = $res[0]["dt_ultima_modifica"];

  $nl_visible = MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER;

  $show_info = globo_controlla_data_datetime($dt_inserimento)
            || globo_controlla_data_datetime($dt_ultima_modifica)
            || $nl_flag_confermato == 0;

  if ($has_p)      $form_action = 'cont_agg.php?p='  . (int)$_GET['p'];
  elseif ($has_ld) $form_action = 'cont_agg.php?ld=' . (int)$_GET['ld'];
  else             $form_action = 'cont_agg.php';

  $del_action = 'cont_el.php' . ($has_p ? '?p=' . (int)$_GET['p'] : ($has_ld ? '?ld=' . (int)$_GET['ld'] : ''));

  $ro = $sv ? ' readonly' : '';

  require("varie/head.php");
?>
<div class="shell">
  <?php if (!$sv): require("varie/header.php"); endif; ?>
  <main><div class="page">

    <?php if (!$sv): ?>
    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Contatti</h1>
      </div>
      <?php if (!$nl_visible): ?>
      <div class="page__actions">
        <a class="btn btn--ghost" href="cont_lista.php<?= $has_p ? '?p=' . (int)$_GET['p'] : '' ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco contatti
        </a>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!$sv): ?>
    <form name="globo" id="globo" action="<?= htmlspecialchars($form_action) ?>" method="post">
      <input type="hidden" name="id_contatto" value="<?= (int)$id_contatto ?>" />
    <?php endif; ?>

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli contatto</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Azienda</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="250" name="azienda" value='<?= $azienda ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="nome" value='<?= $nome ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Cognome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cognome" value='<?= $cognome ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Qualifica</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="qualifica" value='<?= $qualifica ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Telefono</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="telefono" value='<?= $telefono ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Cellulare</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cellulare" value='<?= $cellulare ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Fax</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="fax" value='<?= $fax ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control">
                <input type="email" class="input" maxlength="200" name="email" value="<?= htmlspecialchars($email) ?>"<?= $ro ?> />
                <?php if (trim($email) !== ''): ?>
                <a class="icon-btn" href="mailto:<?= htmlspecialchars(trim($email)) ?>" title="Scrivi e-mail">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-mail"/></svg>
                </a>
                <?php endif; ?>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Sito web <span class="form__hint form__hint--inline">es. www.kosmosol.it</span></span></div>
              <div class="form__control">
                <input type="text" class="input" maxlength="200" name="sito" value="<?= htmlspecialchars($sito) ?>"<?= $ro ?> />
                <?php if (trim($sito) !== ''): ?>
                <a class="icon-btn" href="http://<?= htmlspecialchars(trim($sito)) ?>" target="_blank" title="Apri sito web">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-link-external"/></svg>
                </a>
                <?php endif; ?>
              </div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Indirizzo</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="indirizzo" value='<?= $indirizzo ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">CAP</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cap" value='<?= $cap ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Città</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="citta" value='<?= $citta ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Provincia</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="provincia" value='<?= $provincia ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Regione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="regione" value='<?= $regione ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Area</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="area" value='<?= $area ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nazione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="nazione" value='<?= $nazione ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Partita IVA</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="pi" value='<?= $pi ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice fiscale</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cf" value='<?= $cf ?>'<?= $ro ?> /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Note</span></div>
              <div class="form__control form__control--multi">
                <textarea name="note" class="textarea" rows="7"<?= $ro ?>><?= $note ?></textarea>
              </div>
            </div>
            <?php if ($show_info): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Info</span></div>
              <div class="form__control form__control--multi">
                <?php if (globo_controlla_data_datetime($dt_inserimento)): ?>
                <div>Contatto inserito il <?= globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($dt_inserimento)) ?> alle ore <?= globo_ora_da_mysql_datetime($dt_inserimento) ?></div>
                <?php endif; ?>
                <?php if ($dt_inserimento != $dt_ultima_modifica && globo_controlla_data_datetime($dt_ultima_modifica)): ?>
                <div>Contatto modificato il <?= globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($dt_ultima_modifica)) ?> alle ore <?= globo_ora_da_mysql_datetime($dt_ultima_modifica) ?></div>
                <?php endif; ?>
                <?php if ($nl_flag_confermato == 0): ?>
                <div style="color:var(--err, red)">Contatto in attesa di conferma</div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!$sv && !$nl_visible): ?>
        <div class="card__foot">
          <?php if ($has_p): ?>
          <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$_GET['p'] ?>">Ritorna al progetto</a>
          <?php elseif ($has_ld): ?>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_nl_lis').submit()">Ritorna alla lista di distribuzione</button>
          <?php endif; ?>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($nl_visible):
        $res_nl   = $db->query("SELECT * FROM nl_lista WHERE flag_stato = '1' ORDER BY nome ASC")->fetchAll();
        $stmtIscr = $db->prepare("SELECT id FROM nl_lista_contatto WHERE id_lista = ? AND id_contatto = ?");
      ?>
      <div class="card" style="margin-top:16px">
        <div class="card__head">
          <span class="card__head-eyebrow">Liste di distribuzione</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <?php foreach ($res_nl as $r_nl):
              $stmtIscr->execute([(int)$r_nl["id"], (int)$id_contatto]);
              $is_iscritto = count($stmtIscr->fetchAll()) > 0;
            ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name"><?= htmlspecialchars($r_nl["nome"]) ?></span></div>
              <div class="form__control">
                <input type="checkbox" name="id_lista[]" value="<?= (int)$r_nl["id"] ?>"<?= $is_iscritto ? ' checked' : '' ?><?= $sv ? ' disabled' : '' ?> />
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if (!$sv): ?>
        <div class="card__foot">
          <a class="btn btn--ghost" href="cont_lista.php<?= $has_p ? '?p=' . (int)$_GET['p'] : '' ?>">Elenco contatti</a>
          <?php if ($has_p): ?>
          <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$_GET['p'] ?>">Ritorna al progetto</a>
          <?php elseif ($has_ld): ?>
          <button type="button" class="btn btn--ghost" onclick="document.getElementById('frm_nl_lis').submit()">Ritorna alla lista di distribuzione</button>
          <?php endif; ?>
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    <?php if (!$sv): ?>
    </form>

    <?php if ($has_ld): ?>
    <form id="frm_nl_lis" action="nl_lis_vis.php" method="post" style="display:none">
      <input type="hidden" name="id_lista" value="<?= (int)$_GET['ld'] ?>" />
    </form>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($del_action) ?>" method="post" style="margin-top:16px">
      <input type="hidden" name="id_contatto" value="<?= (int)$id_contatto ?>" />
      <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo contatto?">Elimina</button>
    </form>
    <?php endif; ?>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
