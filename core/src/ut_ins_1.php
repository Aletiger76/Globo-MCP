<?php require("varie/common_1.php");

  // Inserimento utenti riservato all'amministratore globale (livello 0 = ks-globo).
  globo_richiedi_accesso($_SESSION["ksglobo_livello_utente"] == 0);

  $show_email = MOD_CALENDARIO || MOD_PROGETTI;

  require("varie/head.php");
?>
<script>
function controllaForm() {
  var p = document.globo.password;
  p.setCustomValidity("");
  if (document.globo.email) document.globo.email.setCustomValidity("");
  if (parseInt(document.globo.strength.value || 0) <= 2) { p.setCustomValidity("Password troppo debole"); p.reportValidity(); return false; }
  if (document.globo.flag_notifiche_email && document.globo.email && document.globo.email.value.trim() === "" && document.globo.flag_notifiche_email.value == 1) {
    document.globo.email.setCustomValidity("Inserisci un indirizzo e-mail per ricevere le notifiche"); document.globo.email.reportValidity(); return false;
  }
  return true;
}
function validatePassword(password) {
  var msg = document.getElementById("msg");
  if (password.length === 0) { msg.innerHTML = ""; document.getElementById("strength").value = ""; return; }
  var patterns = ["[$@$!%*#?&]", "[A-Z]", "[0-9]", "[a-z]"];
  var ctr = 0;
  for (var i = 0; i < patterns.length; i++) if (new RegExp(patterns[i]).test(password)) ctr++;
  var label = "", color = "";
  if (ctr <= 2)      { label = "Debole"; color = "var(--danger-500)"; }
  else if (ctr == 3) { label = "Media";  color = "#c97a00"; }
  else               { label = "Forte";  color = "var(--brand)"; }
  msg.innerHTML = label; msg.style.color = color;
  document.getElementById("strength").value = ctr;
}
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Gestione utenti</h1>
      </div>
      <div class="page__actions">
        <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
        <a class="btn btn--ghost" href="ut_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco utenti
        </a>
        <?php endif; ?>
      </div>
    </div>

    <form name="globo" action="ut_ins_2.php" method="post" onsubmit="return controllaForm()">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuovo utente</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome utente <span class="form__hint form__hint--inline">obb</span></span></div>
              <div class="form__control"><input type="text" class="input" maxlength="50" name="nome_utente" value="" required /></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Password <span class="form__hint form__hint--inline">obb, min 8</span></span></div>
              <div class="form__control" style="gap:10px;flex-wrap:wrap">
                <input type="text" class="input" maxlength="30" minlength="8" name="password" value="" required autocomplete="off" onkeyup="validatePassword(this.value)" oninput="this.setCustomValidity('')" />
                <span id="msg" style="font-size:13px;font-weight:700"></span>
                <input type="hidden" name="strength" id="strength" value="" />
              </div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ruolo</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="livello">
                    <option value="1">amministratore</option>
                    <option value="2" selected>utente</option>
                  </select>
                </div>
              </div>
            </div>

            <?php if ($show_email): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control"><input type="email" class="input" maxlength="50" name="email" value="" oninput="this.setCustomValidity('')" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ricevi notifiche via e-mail</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifiche_email">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>
            <?php if (MOD_GLOBOMAIL): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ricevi notifiche via globomail</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifiche_globomail">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
