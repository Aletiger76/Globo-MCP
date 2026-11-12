<?php
/*
 * header.php — Topbar Globo CRM
 *
 * Emette <header class="topbar"> con brand, navigazione moduli,
 * data corrente, avatar utente e pulsante logout.
 *
 * DIPENDENZE:
 *   - sessione già avviata (session_start() nella pagina includente)
 *   - config.php già caricato (MOD_*, LIV_MOD_*, TITLE)
 *   - funzioni.php già caricato (globo_giorno_in_lettere, globo_mese_in_lettere)
 *
 * STRUTTURA DI PAGINA:
 *   Questo partial emette solo <header>. Le pagine devono avvolgerlo
 *   in <div class="shell"> e aggiungere <main><div class="page">
 *   per il contenuto. Vedi docs/refactoring-notes.md (Step 4).
 *
 * TAB ATTIVO:
 *   Rilevato automaticamente dal nome del file corrente (PHP_SELF).
 *   Convenzione: cal_* → calendario, cont_* → contatti, prog_* | pr_* → progetti.
 *
 * NOTA STORICA — navigazione calendario:
 *   Il vecchio header passava $mese/$anno/$cal_vis via POST form, mantenendo
 *   il mese aperto nella topbar. Il link ora punta a cal_home.php senza
 *   parametri (torna al mese corrente). Comportamento da riallineare quando
 *   il modulo calendario verrà riscritto (Step 7).
 */

// ── Rilevamento tab attivo ─────────────────────────────────────────────────
$_globo_page  = basename($_SERVER['PHP_SELF']);
$_globo_tab   = '';
if (str_starts_with($_globo_page, 'cal_'))                                           $_globo_tab = 'cal';
elseif (str_starts_with($_globo_page, 'cont_'))                                      $_globo_tab = 'cont';
elseif (str_starts_with($_globo_page, 'prog_') || str_starts_with($_globo_page, 'pr_')) $_globo_tab = 'prog';

// ── Iniziali avatar (es. "mario.rossi" → "MR", "admin" → "AD") ───────────
$_globo_raw_name = strtoupper($_SESSION["ksglobo_nome_utente"] ?? "");
preg_match('/^([A-Z])[._\-\s]([A-Z])/u', $_globo_raw_name, $_globo_m)
  ? $_globo_initials = $_globo_m[1] . $_globo_m[2]
  : $_globo_initials = substr($_globo_raw_name, 0, 2) ?: '??';

// ── Data corrente per la topbar ───────────────────────────────────────────
$_globo_giorno = strtolower(globo_giorno_in_lettere(date("w")));
$_globo_data_breve  = ucfirst($_globo_giorno) . ' ' . date("d") . ' ' . strtolower(globo_mese_in_lettere(date("m")));
$_globo_data_anno   = date("Y") . ' · ' . date("H:i");

// ── Link utente: admin (livello 0) → lista utenti, altri → profilo ────────
// TODO Step 4: ut_vis.php legge nome_utente da POST; da aggiornare per
//              accettare anche GET o lettura da sessione quando la pagina
//              verrà riscritta.
$_globo_user_href = ($_SESSION["ksglobo_livello_utente"] === 0)
  ? 'ut_lista.php'
  : 'ut_vis.php';
?>

<header class="topbar">

  <!-- Brand / home -->
  <a class="topbar__brand" href="home.php" title="Home">
    <img class="topbar__logo"
         src="immagini/<?php echo htmlspecialchars(IMG_LOGO); ?>"
         alt="<?php echo htmlspecialchars(TITLE); ?>" />
  </a>

  <!-- Navigazione moduli (solo quelli abilitati e accessibili all'utente) -->
  <nav class="topbar__nav" id="topbarNav" aria-label="Moduli">

    <?php if (file_exists(__DIR__ . '/header-tabs-custom.php')): ?>
    <?php
    // Override navigazione (Fase 2 core/custom): se il clone fornisce
    // varie/header-tabs-custom.php, è lui a rendere l'INTERA lista di tab
    // (ordine e voci interamente controllati dal clone — il core non sa
    // interlacciare i suoi moduli con quelli verticali del clone).
    // Su Globo8 puro il file non esiste → si rende il default qui sotto.
    require(__DIR__ . '/header-tabs-custom.php');
    ?>
    <?php else: ?>

    <?php if (defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO): ?>
    <a class="topbar__tab<?php echo $_globo_tab === 'cal'  ? ' is-active' : ''; ?>"
       href="cal_home.php">
      <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
      Calendario
    </a>
    <?php endif; ?>

    <?php if (defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI): ?>
    <a class="topbar__tab<?php echo $_globo_tab === 'cont' ? ' is-active' : ''; ?>"
       href="cont_lista.php">
      <svg class="ic-stroke" aria-hidden="true"><use href="#i-contacts"/></svg>
      Contatti
    </a>
    <?php endif; ?>

    <?php if (defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI): ?>
    <a class="topbar__tab<?php echo $_globo_tab === 'prog' ? ' is-active' : ''; ?>"
       href="prog_lista.php">
      <svg class="ic-stroke" aria-hidden="true"><use href="#i-folder"/></svg>
      Progetti
    </a>
    <?php endif; ?>

    <?php endif; ?>

  </nav>

  <!-- Destra: data, avatar utente, logout -->
  <div class="topbar__right">

    <div class="topbar__date" aria-label="Data corrente">
      <b><?php echo htmlspecialchars($_globo_data_breve); ?></b>
      <span><?php echo htmlspecialchars($_globo_data_anno); ?></span>
    </div>

    <a class="topbar__user" href="<?php echo $_globo_user_href; ?>"
       title="Profilo utente">
      <span class="topbar__avatar" aria-hidden="true">
        <?php echo htmlspecialchars($_globo_initials); ?>
      </span>
      <span class="topbar__user-name">
        <?php echo htmlspecialchars($_SESSION["ksglobo_nome_utente"] ?? ''); ?>
      </span>
    </a>

    <a class="icon-btn" href="index.php" title="Logout" aria-label="Logout">
      <svg class="ic-stroke" aria-hidden="true"><use href="#i-logout"/></svg>
    </a>

  </div>

  <!-- Hamburger (solo mobile) -->
  <button type="button" class="topbar__burger" id="topbarBurger"
          aria-label="Menu" aria-expanded="false" aria-controls="topbarNav">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-menu"/></svg>
  </button>

</header>
