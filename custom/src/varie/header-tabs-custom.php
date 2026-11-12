<?php
/*
 * header-tabs-custom.php — Navigazione topbar completa di MCP.
 *
 * Override completo (Fase 2): il core delega a questo file l'INTERA lista di
 * tab quando esiste (vedi core/varie/header.php). Riproduce i 3 tab core
 * standard invariati (calendario, contatti, progetti) e aggiunge le voci
 * "operative" del modulo adhoc_mcp_* — non tutte e 6: solo quelle di uso
 * quotidiano (clienti, ricariche, acquisti). Le altre (categorie servizi,
 * servizi, listini) sono anagrafica/catalogo consultata di rado, restano
 * raggiungibili solo dai box in home.php. Deciso con Alessandro 2026-07-13.
 */
$_p = $_globo_page ?? basename($_SERVER['PHP_SELF']);
$_liv = $_SESSION["ksglobo_livello_utente"];
$_globo_tab_mcp = '';
if (str_starts_with($_p, 'adhoc_mcp_cli_'))       $_globo_tab_mcp = 'mcp_cli';
elseif (str_starts_with($_p, 'adhoc_mcp_rica_'))  $_globo_tab_mcp = 'mcp_rica';
elseif (str_starts_with($_p, 'adhoc_mcp_acq_'))   $_globo_tab_mcp = 'mcp_acq';
?>
<?php if (defined('MOD_CALENDARIO') && MOD_CALENDARIO && $_liv <= LIV_MOD_CALENDARIO): ?>
<a class="topbar__tab<?= $_globo_tab === 'cal' ? ' is-active' : '' ?>" href="cal_home.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
  Calendario
</a>
<?php endif; ?>
<?php if (defined('MOD_CONTATTI') && MOD_CONTATTI && $_liv <= LIV_MOD_CONTATTI): ?>
<a class="topbar__tab<?= $_globo_tab === 'cont' ? ' is-active' : '' ?>" href="cont_lista.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-contacts"/></svg>
  Contatti
</a>
<?php endif; ?>
<?php if (defined('MOD_PROGETTI') && MOD_PROGETTI && $_liv <= LIV_MOD_PROGETTI): ?>
<a class="topbar__tab<?= $_globo_tab === 'prog' ? ' is-active' : '' ?>" href="prog_lista.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-folder"/></svg>
  Progetti
</a>
<?php endif; ?>
<?php if (defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_liv <= LIV_MOD_ADHOC_MCP): ?>
<a class="topbar__tab<?= $_globo_tab_mcp === 'mcp_cli' ? ' is-active' : '' ?>" href="adhoc_mcp_cli_lista.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-contacts"/></svg>
  Clienti
</a>
<a class="topbar__tab<?= $_globo_tab_mcp === 'mcp_rica' ? ' is-active' : '' ?>" href="adhoc_mcp_rica_lista.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
  Ricariche
</a>
<a class="topbar__tab<?= $_globo_tab_mcp === 'mcp_acq' ? ' is-active' : '' ?>" href="adhoc_mcp_acq_lista.php">
  <svg class="ic-stroke" aria-hidden="true"><use href="#i-tag"/></svg>
  Acquisti
</a>
<?php endif; ?>
