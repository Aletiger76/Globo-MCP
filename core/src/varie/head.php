<?php
/*
 * head.php — Shell di apertura comune a tutte le pagine Globo
 *
 * Emette DOCTYPE, <html>, <head> completo, tag <body> con il tema
 * attivo, e lo sprite SVG inline delle icone.
 *
 * DIPENDENZE: config.php deve essere già stato caricato dalla
 * pagina includente (fornisce TITLE e GLOBO_THEME).
 *
 * PERSONALIZZAZIONE PER CLONE:
 *   - Tema colore: cambiare GLOBO_THEME in config.php (es. "verde-querce").
 *     I temi disponibili sono definiti in css/globo.themes.css.
 *   - Titolo: cambiare la costante TITLE in config.php.
 *   - Foglio di stile aggiuntivo (solo per quel clone): aggiungere un
 *     <link> dopo questo include nella pagina che lo richiede.
 *
 * NOTA: questo file chiude </head> e apre <body>.
 * Le pagine devono chiudere </body></html> in autonomia.
 */
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?php echo htmlspecialchars(TITLE); ?></title>

  <!--
    Design system Globo — tre livelli separati:
      1. tokens  : palette, tipografia, spaziature (invariante tra cloni)
      2. themes  : variabili brand per cliente (solo colore principale)
      3. app     : componenti e layout (invariante tra cloni)
    Per cambiare tema: modificare GLOBO_THEME in config.php.
  -->
<?php
  // Cache-busting: ?v=<mtime> cambia a ogni modifica del file → niente più
  // hard refresh in dev e ricaricamento automatico dopo i deploy.
  $_globo_css_dir = __DIR__ . '/../css/';
  if (!function_exists('globo_css')) {
    function globo_css($file, $dir) { return $file . '?v=' . (@filemtime($dir . $file) ?: '1'); }
  }
?>
  <?php /* Webfont del design system: Inter (sans) + JetBrains Mono (codici/numeri).
           I token --font-sans/--font-mono li richiedono; senza caricamento il browser
           cade sui font di sistema (Segoe UI / Consolas). */ ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" />
  <link rel="stylesheet" href="css/<?= globo_css('globo.tokens.css', $_globo_css_dir) ?>" />
  <link rel="stylesheet" href="css/<?= globo_css('globo.themes.css', $_globo_css_dir) ?>" />
  <link rel="stylesheet" href="css/<?= globo_css('globo.app.css', $_globo_css_dir) ?>" />
  <link rel="stylesheet" href="css/<?= globo_css('flatpickr.min.css', $_globo_css_dir) ?>" />
<?php
  // Favicon parametrizzata per clone: costante FAVICON in config.php (es.
  // "immagini/favicon5.ico"); fallback al /favicon.ico nella webroot. Il ?v=mtime
  // forza l'aggiornamento quando il file cambia. FAVICON_TOUCH opzionale (iOS).
  $_globo_fav = (defined('FAVICON') && FAVICON !== '') ? FAVICON : 'favicon.ico';
?>
  <link rel="icon" href="<?= htmlspecialchars($_globo_fav) ?>?v=<?= @filemtime(__DIR__ . '/../' . $_globo_fav) ?: '1' ?>" />
<?php if (defined('FAVICON_TOUCH') && FAVICON_TOUCH !== ''): ?>
  <link rel="apple-touch-icon" href="<?= htmlspecialchars(FAVICON_TOUCH) ?>" />
<?php endif; ?>
</head>
<body data-theme="<?php echo htmlspecialchars(GLOBO_THEME); ?>">
<?php
/*
 * Sprite SVG inline: deve stare subito dopo <body> perché i simboli
 * devono essere nel DOM prima di essere referenziati con <use href="#i-...">.
 */
require(__DIR__ . "/icon-sprite.php");
// Legge e cancella il flash di sessione (usato dai toast)
$_globo_flash = null;
if (!empty($_SESSION['flash'])) {
  $_globo_flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
}
?>
