<?php
/*
 * Sprite SVG inline — Globo CRM
 *
 * Contiene tutti i simboli SVG in stile Lucide usati nell'interfaccia.
 * Va incluso subito dopo <body> (i simboli devono essere nel DOM prima
 * di essere referenziati con <use href="#i-nome">).
 *
 * Per aggiungere una nuova icona: aggiungere un <symbol> con id "i-nome"
 * e viewBox="0 0 24 24". Le icone usano stroke, non fill.
 *
 * L'SVG è nascosto (width/height 0, position absolute) e non occupa spazio.
 */
?>
<svg width="0" height="0" style="position:absolute;overflow:hidden;" aria-hidden="true">
  <defs>
    <!-- Navigazione -->
    <symbol id="i-home"     viewBox="0 0 24 24"><path d="M3 11l9-8 9 8v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9z"/><path d="M9 22V12h6v10"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></symbol>
    <symbol id="i-contacts" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2.5"/><circle cx="12" cy="11" r="2.6"/><path d="M8 18c.4-2 2-3 4-3s3.6 1 4 3M16 2v4M8 2v4"/></symbol>
    <symbol id="i-folder"   viewBox="0 0 24 24"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h3.7l2 2.4h7.3A2.5 2.5 0 0 1 21 9.9V17a3 3 0 0 1-3 3H4a3 3 0 0 1-3-3V7.5Z"/></symbol>
    <symbol id="i-user"     viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4.5-6 8-6s7 2 8 6"/></symbol>
    <symbol id="i-logout"   viewBox="0 0 24 24"><path d="M15 5h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4M10 17l-5-5 5-5M5 12h12"/></symbol>

    <!-- Azioni comuni -->
    <symbol id="i-plus"               viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-search"             viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></symbol>
    <symbol id="i-edit"               viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></symbol>
    <symbol id="i-trash"              viewBox="0 0 24 24"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></symbol>
    <symbol id="i-eye"                viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></symbol>
    <symbol id="i-download"           viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></symbol>
    <symbol id="i-x"                  viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></symbol>
    <symbol id="i-check"              viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
    <symbol id="i-menu"               viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></symbol>
    <symbol id="i-settings"           viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 0 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 0 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 0 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v.1a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></symbol>

    <!-- Navigazione calendario -->
    <symbol id="i-chev-left"  viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></symbol>
    <symbol id="i-chev-right" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></symbol>
    <symbol id="i-chev-down"  viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></symbol>

    <!-- Comunicazione -->
    <symbol id="i-mail"       viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></symbol>
    <symbol id="i-mail-check" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6M16 19l2 2 4-4"/></symbol>
    <symbol id="i-bell"       viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></symbol>

    <!-- Frecce e link -->
    <symbol id="i-arrow-right"        viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></symbol>
    <symbol id="i-arrow-right-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9 8l4 4-4 4M8 12h7"/></symbol>
    <symbol id="i-link-external"      viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></symbol>

    <!-- Contenuto -->
    <symbol id="i-file-text" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></symbol>
    <symbol id="i-tag"       viewBox="0 0 24 24"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V4a2 2 0 0 1 2-2h8l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.2"/></symbol>
    <symbol id="i-pin"       viewBox="0 0 24 24"><path d="M12 2l3 6 6 1-4.5 4.5L18 20l-6-3-6 3 1.5-6.5L3 9l6-1z"/></symbol>

    <!-- Miscellanea -->
    <symbol id="i-clock"  viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6z"/></symbol>
    <symbol id="i-globe"  viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2c2.5 3 4 7 4 10s-1.5 7-4 10c-2.5-3-4-7-4-10s1.5-7 4-10z"/></symbol>
  </defs>
</svg>
