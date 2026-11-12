<?php
  // CUSTOM-MCP: override griglia home (hook previsto dal core, home.php
  // varie/home-tiles-custom.php) - riproduce le tile standard del core
  // invariate, aggiungendo in fondo le tile del modulo adhoc_mcp_* (assenti
  // dal core, che non conosce questo modulo specifico di MCP). Vedi
  // docs/analisi-mcp.md §5. Naviga tutte le pagine gia' convertite a PDO.
?>

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

<?php // CUSTOM-MCP: modulo adhoc_mcp_* - stesso gate (MOD_ADHOC_MCP + livello)
      // e stesso set di 6 voci del vecchio home.php ("categorie servizi" solo
      // per il livello 0, come nell'originale). ?>
<?php if (defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP): ?>
<a class="home-tile" href="adhoc_mcp_cli_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-contacts"/></svg></div>
  <div><div class="home-tile__lbl">Clienti MCP</div><div class="home-tile__sub">Anagrafica e credito</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
<a class="home-tile" href="adhoc_mcp_serv_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-shield"/></svg></div>
  <div><div class="home-tile__lbl">Servizi</div><div class="home-tile__sub">Catalogo servizi</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
<a class="home-tile" href="adhoc_mcp_lis_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg></div>
  <div><div class="home-tile__lbl">Listini</div><div class="home-tile__sub">Prezzi per cliente</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
<a class="home-tile" href="adhoc_mcp_acq_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-tag"/></svg></div>
  <div><div class="home-tile__lbl">Acquisti servizi</div><div class="home-tile__sub">Ordini e documenti</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
<a class="home-tile" href="adhoc_mcp_rica_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg></div>
  <div><div class="home-tile__lbl">Ricariche credito</div><div class="home-tile__sub">Log ricariche cliente</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
  <?php if ($_SESSION["ksglobo_livello_utente"] == 0): ?>
<a class="home-tile" href="adhoc_mcp_catserv_lista.php">
  <div class="home-tile__ic"><svg class="ic-stroke" aria-hidden="true"><use href="#i-folder"/></svg></div>
  <div><div class="home-tile__lbl">Categorie servizi</div><div class="home-tile__sub">Raggruppamento servizi</div></div>
  <svg class="home-tile__arrow ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
</a>
  <?php endif; ?>
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
