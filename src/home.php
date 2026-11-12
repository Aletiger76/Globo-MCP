<?php require("varie/common_1.php"); ?>
<?php
  // ── Saluto contestuale per ora del giorno ─────────────────────────────────
  $ora = (int)date("G");
  if ($ora < 12)     $_globo_saluto = "Buongiorno";
  elseif ($ora < 18) $_globo_saluto = "Buon pomeriggio";
  else               $_globo_saluto = "Buonasera";

  require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <!-- Saluto ──────────────────────────────────────────────────────────── -->
    <div class="greet">
      <div>
        <h1 class="greet__h"><?php echo htmlspecialchars($_globo_saluto . ', ' . ($_SESSION["ksglobo_nome_utente"] ?? '')); ?></h1>
        <p class="greet__sub">Cosa vuoi fare oggi?</p>
      </div>
    </div>

    <!-- Griglia moduli ──────────────────────────────────────────────────── -->
    <div class="home-grid">

      <?php if (file_exists(__DIR__ . '/varie/home-tiles-custom.php')): ?>
      <?php
      // Override griglia home (Fase 2 core/custom): se il clone fornisce
      // varie/home-tiles-custom.php, è lui a rendere l'INTERA griglia di tile
      // (ordine e voci controllati dal clone). Su Globo8 puro → default sotto.
      require(__DIR__ . '/varie/home-tiles-custom.php');
      ?>
      <?php else: ?>

      <?php if (defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO): ?>
      <a class="home-tile" href="cal_home.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg></div>
        <div><div class="home-tile__lbl">Calendario</div><div class="home-tile__sub">Impegni e scadenze</div></div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php if (defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI): ?>
      <a class="home-tile" href="cont_lista.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-contacts"/></svg></div>
        <div><div class="home-tile__lbl">Contatti</div><div class="home-tile__sub">Rubrica clienti e fornitori</div></div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php if (defined('MOD_BILANCIO') && MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO): ?>
      <a class="home-tile" href="bil_home.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg></div>
        <div>
          <div class="home-tile__lbl"><?php echo (defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2) ? 'Bilancio v1' : 'Bilancio'; ?></div>
          <div class="home-tile__sub">Entrate e uscite</div>
        </div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php
        $_globo_bilv2 = defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && (
          $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2 ||
          (isset($BILV2_UTENTE_RO) && in_array($_SESSION["ksglobo_nome_utente"], $BILV2_UTENTE_RO))
        );
      ?>
      <?php if ($_globo_bilv2): ?>
      <a class="home-tile" href="bilv2_home.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg></div>
        <div>
          <div class="home-tile__lbl"><?php echo (defined('MOD_BILANCIO') && MOD_BILANCIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIO) ? 'Bilancio v2' : 'Bilancio'; ?></div>
          <div class="home-tile__sub">Fatture e documenti</div>
        </div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php if (defined('MOD_BILANCIOV2') && MOD_BILANCIOV2 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_BILANCIOV2 && defined('BILV2_AG') && BILV2_AG): ?>
      <a class="home-tile" href="bilv2ag_lista.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-user"/></svg></div>
        <div><div class="home-tile__lbl">Agenti</div><div class="home-tile__sub">Gestione agenti commerciali</div></div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php if (defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI): ?>
      <a class="home-tile" href="prog_lista.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-folder"/></svg></div>
        <div><div class="home-tile__lbl">Progetti</div><div class="home-tile__sub">Gestione e monitoraggio</div></div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>

        <?php if (defined('PROC_ARC') && PROC_ARC): ?>
        <a class="home-tile" href="prog_lista.php?a=1">
          <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-folder"/></svg></div>
          <div><div class="home-tile__lbl">Archivio progetti</div><div class="home-tile__sub">Progetti archiviati</div></div>
          <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
        </a>
        <?php endif; ?>

        <?php if ($_SESSION["ksglobo_livello_utente"] == 0): ?>
        <a class="home-tile" href="pr_template_lista.php">
          <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg></div>
          <div><div class="home-tile__lbl">Template progetti</div><div class="home-tile__sub">Modelli riutilizzabili</div></div>
          <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
        </a>
        <a class="home-tile" href="pr_proced_lista.php">
          <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-settings"/></svg></div>
          <div><div class="home-tile__lbl">Procedure</div><div class="home-tile__sub">Flussi di lavoro standard</div></div>
          <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
        </a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (defined('MOD_GLOBOMAIL') && MOD_GLOBOMAIL && $_SESSION["ksglobo_livello_utente"] > 0 && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GLOBOMAIL): ?>
      <a class="home-tile" href="globomail_lista.php">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-mail"/></svg></div>
        <div>
          <div class="home-tile__lbl">GloboMail<?php if (!empty($globomail_nuovi_messaggi) && $globomail_nuovi_messaggi > 0): ?> (<?php echo (int)$globomail_nuovi_messaggi; ?>)<?php endif; ?></div>
          <div class="home-tile__sub">Messaggi interni</div>
        </div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php if ((defined('MOD_GESTIONE_UT') && MOD_GESTIONE_UT && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_GESTIONE_UT) || $_SESSION["ksglobo_livello_utente"] == 0): ?>
      <a class="home-tile" href="<?php echo $_SESSION["ksglobo_livello_utente"] == 0 ? 'ut_lista.php' : 'ut_vis.php'; ?>">
        <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-user"/></svg></div>
        <div>
          <div class="home-tile__lbl"><?php echo $_SESSION["ksglobo_livello_utente"] == 0 ? 'Utenti' : 'Il mio profilo'; ?></div>
          <div class="home-tile__sub"><?php echo $_SESSION["ksglobo_livello_utente"] == 0 ? 'Gestione accessi' : 'Impostazioni account'; ?></div>
        </div>
        <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </a>
      <?php endif; ?>

      <?php endif; /* fine override griglia home custom */ ?>

    </div><!-- /.home-grid -->

    <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
