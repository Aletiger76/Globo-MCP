<?php
/*
 * footer.php — Chiusura comune a tutte le pagine Globo
 *
 * Emette </div></main></div></body></html>.
 * Deve essere incluso come ultimo elemento della pagina, dopo tutto
 * il contenuto di <main><div class="page">.
 *
 * STRUTTURA DI PAGINA ATTESA:
 *   include head.php          → <!DOCTYPE html> ... <body>
 *   include header.php        → <header class="topbar">
 *   <div class="shell">
 *     <main><div class="page">
 *       ... contenuto pagina ...
 *     include footer.php      → </div></main></div></body></html>
 */
?>
    </div><!-- /.page -->
  </main>
</div><!-- /.shell -->

<!-- ── TOAST ────────────────────────────────────────────── -->
<div id="toast-container" aria-live="polite">
  <?php if (!empty($_globo_flash)): ?>
  <div class="toast<?= $_globo_flash['type'] === 'err' ? ' toast--err' : '' ?>" role="alert">
    <span class="toast__text"><?= htmlspecialchars($_globo_flash['text']) ?></span>
    <button type="button" class="icon-btn toast__close" aria-label="Chiudi">
      <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
    </button>
  </div>
  <?php endif; ?>
</div>

<!-- ── MODALE CONFERMA / AVVISO ──────────────────────────── -->
<!-- Titolo e bottoni sono impostati via JS (globoConfirm/globoAlert). -->
<div id="confirm-modal" class="modal-overlay" hidden>
  <div class="modal">
    <p class="modal__title" id="confirm-modal-title">Conferma</p>
    <p class="modal__msg" id="confirm-modal-msg"></p>
    <div class="modal__foot">
      <button type="button" class="btn btn--ghost" id="confirm-cancel">Annulla</button>
      <button type="button" class="btn btn--primary" id="confirm-ok">Conferma</button>
    </div>
  </div>
</div>

<script src="javascript/flatpickr/flatpickr.min.js"></script>
<script src="javascript/flatpickr/l10n/it.js"></script>
<script>
document.querySelectorAll('[data-flatpickr]').forEach(function(el) {
  // disableMobile: usa sempre il widget Flatpickr, anche su mobile (dove di
  // default verrebbe sostituito dal datepicker nativo del sistema).
  flatpickr(el, { wrap: true, dateFormat: 'd/m/Y', locale: 'it', allowInput: true, disableMobile: true });
});

// ── MODALE CONFERMA / AVVISO (API globale) ───────────────
// Sostituisce confirm()/alert() nativi con la modale del design system.
//   globoConfirm(opts) -> Promise<bool>   (2 bottoni; opts: {message,title,okLabel,cancelLabel,danger})
//   globoConfirm("testo") -> Promise<bool> (forma breve)
//   globoAlert(message, opts) -> Promise<void>  (1 bottone)
// I bottoni con [data-confirm] continuano a funzionare (delegano a globoConfirm).
(function() {
  var overlay  = document.getElementById('confirm-modal');
  var titleEl  = document.getElementById('confirm-modal-title');
  var msgEl    = document.getElementById('confirm-modal-msg');
  var btnOk    = document.getElementById('confirm-ok');
  var btnCancel= document.getElementById('confirm-cancel');
  var resolver = null;   // chiude la Promise della modale in corso
  var prevFocus= null;   // elemento da rifocalizzare alla chiusura

  function settle(value) {
    if (!resolver) return;
    var r = resolver; resolver = null;
    overlay.hidden = true;
    if (prevFocus && prevFocus.focus) prevFocus.focus();
    r(value);
  }

  // Apre la modale; opts: {message,title,okLabel,cancelLabel,danger,alert}
  function open(opts) {
    if (resolver) settle(false); // se una è già aperta, annullala prima
    titleEl.textContent   = opts.title   || (opts.alert ? 'Avviso' : 'Conferma');
    msgEl.textContent     = opts.message || '';
    btnOk.textContent     = opts.okLabel || (opts.alert ? 'OK' : 'Conferma');
    btnOk.className       = 'btn ' + (opts.danger ? 'btn--danger' : 'btn--primary');
    btnCancel.style.display = opts.alert ? 'none' : '';   // alert = un solo bottone (style: .btn ha un display esplicito, [hidden] non basta)
    btnCancel.textContent = opts.cancelLabel || 'Annulla';
    prevFocus = document.activeElement;
    overlay.hidden = false;
    btnOk.focus();
    return new Promise(function(res) { resolver = res; });
  }

  window.globoConfirm = function(opts) {
    if (typeof opts === 'string') opts = { message: opts };
    return open(opts || {});
  };
  window.globoAlert = function(message, opts) {
    opts = opts || {}; opts.message = message; opts.alert = true;
    return open(opts);
  };

  btnOk.addEventListener('click', function() { settle(true); });
  btnCancel.addEventListener('click', function() { settle(false); });
  overlay.addEventListener('click', function(e) { if (e.target === overlay) settle(false); });
  document.addEventListener('keydown', function(e) {
    if (overlay.hidden) return;
    if (e.key === 'Escape') settle(false);
    else if (e.key === 'Enter') { e.preventDefault(); settle(true); }
  });

  // Retrocompat: bottoni [data-confirm] (eliminazioni e simili) → modale + submit.
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('button[data-confirm]');
    if (!btn) return;
    e.preventDefault();
    var form = btn.form || btn.closest('form');
    window.globoConfirm({
      message: btn.dataset.confirm,
      title:   btn.dataset.confirmTitle || 'Conferma eliminazione',
      okLabel: btn.dataset.confirmOk    || 'Elimina',
      danger:  true
    }).then(function(ok) { if (ok && form) form.submit(); });
  });
})();

// ── TOAST AUTO-DISMISS ───────────────────────────────────
(function() {
  function dismissToast(t) {
    t.classList.add('toast--out');
    setTimeout(function() { t.remove(); }, 260);
  }
  document.querySelectorAll('.toast').forEach(function(t) {
    var timer = setTimeout(function() { dismissToast(t); }, 4500);
    var close = t.querySelector('.toast__close');
    if (close) close.addEventListener('click', function() { clearTimeout(timer); dismissToast(t); });
  });
})();

// ── TOPBAR HAMBURGER (mobile) ────────────────────────────
(function() {
  var burger = document.getElementById('topbarBurger');
  var topbar = burger ? burger.closest('.topbar') : null;
  if (!burger || !topbar) return;
  function close() { topbar.classList.remove('is-open'); burger.setAttribute('aria-expanded', 'false'); }
  burger.addEventListener('click', function(e) {
    e.stopPropagation();
    var open = topbar.classList.toggle('is-open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  document.addEventListener('click', function(e) {
    if (topbar.classList.contains('is-open') && !topbar.contains(e.target)) close();
  });
  topbar.querySelectorAll('.topbar__nav a').forEach(function(a) { a.addEventListener('click', close); });
})();

// ── RIGHE LISTA CLICCABILI (solo mobile) ─────────────────
// Una riga .list__row con data-href diventa cliccabile SOLO sotto i 640px.
// Su desktop l'handler ritorna subito: nessun effetto (desktop invariato).
document.addEventListener('click', function(e) {
  if (!window.matchMedia('(max-width: 640px)').matches) return;
  var row = e.target.closest('.list__row[data-href]');
  if (!row || e.target.closest('.row-acts')) return;
  window.location.href = row.getAttribute('data-href');
});

// ── PANNELLO A SCOMPARSA (accordion) ─────────────────────
// Un bottone [data-toggle-panel] con aria-controls="<id>" mostra/nasconde il
// pannello con quell'id (attributo hidden) e aggiorna aria-expanded (per la
// rotazione del chevron via CSS). Riusabile (es. filtri avanzati nelle liste).
document.addEventListener('click', function(e) {
  var btn = e.target.closest('[data-toggle-panel]');
  if (!btn) return;
  var panel = document.getElementById(btn.getAttribute('aria-controls'));
  if (!panel) return;
  var open = panel.hasAttribute('hidden');
  if (open) panel.removeAttribute('hidden'); else panel.setAttribute('hidden', '');
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
});
</script>
</body>
</html>
