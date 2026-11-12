<?php require("varie/common_1.php");

  // Solo l'amministratore globale (livello 0 = ks-globo) può aprire il profilo di
  // un ALTRO utente. Un utente normale vede esclusivamente il PROPRIO profilo:
  // si ignora il nome_utente passato via GET/POST e si usa quello di sessione
  // (altrimenti con ?nome_utente=<altro> si leggerebbe il profilo altrui — IDOR).
  $is_admin = ($_SESSION["ksglobo_livello_utente"] == 0);
  $nome_utente = $is_admin
    ? ($_POST["nome_utente"] ?? $_GET["nome_utente"] ?? $_SESSION["ksglobo_nome_utente"])
    : $_SESSION["ksglobo_nome_utente"];

  $stmt = $db->prepare("SELECT * FROM utente WHERE nome_utente = ?");
  $stmt->execute([$nome_utente]);
  $res = $stmt->fetchAll();
  $livello                  = $res[0]["livello"];
  $email                    = $res[0]["email"];
  $flag_notifiche_globomail = $res[0]["flag_notifiche_globomail"];
  $flag_notifiche_email     = $res[0]["flag_notifiche_email"];
  // auth_token_login esiste solo se il clone usa il sistema di token cookie
  // (colonna aggiunta allo schema insieme ad AUTH_TOKEN, vedi index.php): con
  // AUTH_TOKEN disattivato la colonna puo' non esistere nel DB del clone (es.
  // Globo-MCP, rimasto su uno schema precedente) - stessa guardia usata in
  // index.php per lo stesso motivo.
  $sessionBrowser           = AUTH_TOKEN && ($res[0]["auth_token_login"] ?? null) !== null;

  try {
    $stmt = $db->prepare("SELECT dt FROM login WHERE nome_utente = ? AND accesso = '1' ORDER BY dt DESC LIMIT 1");
    $stmt->execute([$nome_utente]);
    $res_l = $stmt->fetchAll();
    $num_l = count($res_l);
  } catch (PDOException $e) {
    $num_l = 0;
  }
  if ($num_l > 0) $ultimo_accesso = globo_converti_data_in_formato_ita(globo_data_da_mysql_datetime($res_l[0]["dt"])) . " " . globo_ora_da_mysql_datetime($res_l[0]["dt"]);
  else            $ultimo_accesso = "non disponibile";

  $show_email = MOD_CALENDARIO || MOD_PROGETTI;
  $is_self    = ($_SESSION["ksglobo_nome_utente"] == $nome_utente);

  require("varie/head.php");
?>
<script>
function controllaForm() {
  var p1 = document.globo.password_1, p2 = document.globo.password_2;
  p1.setCustomValidity(""); p2.setCustomValidity("");
  if (document.globo.email) document.globo.email.setCustomValidity("");
  if (p1.value !== p2.value) { p2.setCustomValidity("Le password non coincidono"); p2.reportValidity(); return false; }
  if (p1.value !== "" && parseInt(document.globo.strength.value || 0) <= 2) { p1.setCustomValidity("Password troppo debole"); p1.reportValidity(); return false; }
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
        <p class="page__sub"><?= htmlspecialchars($nome_utente) ?></p>
      </div>
      <div class="page__actions">
        <?php if ($is_admin): ?>
        <a class="btn btn--ghost" href="ut_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco utenti
        </a>
        <?php endif; ?>
      </div>
    </div>

    <form name="globo" action="ut_agg.php" method="post" onsubmit="return controllaForm()">
      <input type="hidden" name="nome_utente" value="<?= htmlspecialchars($nome_utente) ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dettagli utente</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome utente</span></div>
              <div class="form__control"><?= htmlspecialchars($nome_utente) ?></div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nuova password <span class="form__hint form__hint--inline">min 8</span></span></div>
              <div class="form__control" style="gap:10px;flex-wrap:wrap">
                <input type="password" class="input" maxlength="30" minlength="8" name="password_1" value="" autocomplete="new-password" onkeyup="validatePassword(this.value)" oninput="this.setCustomValidity('')" />
                <span id="msg" style="font-size:13px;font-weight:700"></span>
                <input type="hidden" name="strength" id="strength" value="" />
              </div>
            </div>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nuova password (ripeti)</span></div>
              <div class="form__control"><input type="password" class="input" maxlength="30" minlength="8" name="password_2" value="" autocomplete="new-password" oninput="this.setCustomValidity('')" /></div>
            </div>

            <?php if ($is_admin): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ruolo</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="livello">
                    <option value="1"<?= $livello == 1 ? ' selected' : '' ?>>amministratore</option>
                    <option value="2"<?= $livello == 2 ? ' selected' : '' ?>>utente</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="livello" value="<?= (int)$livello ?>" />
            <?php endif; ?>

            <?php if ($show_email): ?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">E-mail</span></div>
              <div class="form__control"><input type="email" class="input" maxlength="50" name="email" value="<?= htmlspecialchars($email) ?>" oninput="this.setCustomValidity('')" /></div>
            </div>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ricevi notifiche via e-mail</span></div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifiche_email">
                    <option value="0"<?= $flag_notifiche_email == 0 ? ' selected' : '' ?>>no</option>
                    <option value="1"<?= $flag_notifiche_email == 1 ? ' selected' : '' ?>>sì</option>
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
                    <option value="0"<?= $flag_notifiche_globomail == 0 ? ' selected' : '' ?>>no</option>
                    <option value="1"<?= $flag_notifiche_globomail == 1 ? ' selected' : '' ?>>sì</option>
                  </select>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Ultimo accesso</span></div>
              <div class="form__control"><?= htmlspecialchars($ultimo_accesso) ?></div>
            </div>

          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Aggiorna</button>
        </div>
      </div>
    </form>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px">
      <?php /* Pulsante sessioni browser: pertinente solo se il clone usa il sistema
               di token cookie (AUTH_TOKEN) - a differenza di prima, con AUTH_TOKEN
               disattivato non si mostra ne' "elimina sessioni" ne' "nessuna sessione
               attiva" (sarebbe permanentemente vero, rumore visivo senza informazione). */ ?>
      <?php if (AUTH_TOKEN): ?>
      <?php if ($sessionBrowser): ?>
      <form action="ut_destroy_token.php" method="post" style="display:contents">
        <input type="hidden" name="nome_utente" value="<?= htmlspecialchars($nome_utente) ?>" />
        <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare le sessioni dei browser? Al prossimo accesso verrà inviata una mail per confermare l'identità.">Elimina sessioni dei browser</button>
      </form>
      <?php else: ?>
      <button type="button" class="btn btn--ghost" disabled>Nessuna sessione browser attiva</button>
      <?php endif; ?>
      <?php endif; ?>

      <?php if ($is_admin): ?>
      <form action="ut_el.php" method="post" style="display:contents">
        <input type="hidden" name="nome_utente" value="<?= htmlspecialchars($nome_utente) ?>" />
        <button type="submit" class="btn btn--danger-ghost"<?= $is_self ? ' disabled' : '' ?> data-confirm="Eliminare l'utente «<?= htmlspecialchars($nome_utente) ?>»?">Elimina utente</button>
      </form>
      <?php endif; ?>
    </div>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
