<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);
  $nl_visible = MOD_NEWSLETTER && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_NEWSLETTER;
  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Contatti</h1>
      </div>
      <?php if (!$nl_visible): ?>
      <div class="page__actions">
        <a class="btn btn--ghost" href="cont_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco contatti
        </a>
      </div>
      <?php endif; ?>
    </div>

    <form name="globo" action="cont_ins_2.php" method="post">

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo contatto</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Azienda</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="azienda" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="nome" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Cognome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cognome" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Qualifica</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="qualifica" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Telefono</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="telefono" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Cellulare</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cellulare" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Fax</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="fax" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control"><input type="email" class="input" maxlength="200" name="email" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Sito web <span class="form__hint form__hint--inline">es. www.kosmosol.it</span></span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="sito" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Indirizzo</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="indirizzo" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">CAP</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cap" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Città</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="citta" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Provincia</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="provincia" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Regione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="regione" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Area</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="area" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nazione</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="nazione" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Partita IVA</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="pi" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Codice fiscale</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="200" name="cf" value="" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Note</span></div>
              <div class="form__control form__control--multi">
                <textarea name="note" class="textarea" rows="7"></textarea>
              </div>
            </div>
          </div>
        </div>
        <?php if (!$nl_visible): ?>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($nl_visible):
        $res_nl = $db->query("SELECT * FROM nl_lista WHERE flag_stato = '1' ORDER BY nome ASC")->fetchAll();
      ?>
      <div class="card" style="margin-top:16px">
        <div class="card__head">
          <span class="card__head-eyebrow">Liste di distribuzione</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <?php foreach ($res_nl as $r_nl): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name"><?= htmlspecialchars($r_nl["nome"]) ?></span></div>
              <div class="form__control"><input type="checkbox" name="id_lista[]" value="<?= (int)$r_nl["id"] ?>" /></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card__foot">
          <a class="btn btn--ghost" href="cont_lista.php">Elenco contatti</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
      <?php endif; ?>

    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
