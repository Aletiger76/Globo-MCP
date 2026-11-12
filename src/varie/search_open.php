<?php
/*
 * search_open.php — Apertura del widget di ricerca standard (riusabile).
 *
 * Imposta PRIMA dell'include:
 *   $search_action      (obbligatorio) — action del <form>
 *   $search_eyebrow     (opzionale)    — titolo della card, default "Ricerca"
 *   $search_form_attrs  (opzionale)    — attributi extra del tag <form> (es. 'name="globo"')
 *
 * Tra apertura e chiusura la pagina inserisce i propri campi (.form__row …).
 * Chiudere con: require("varie/search_close.php");
 *
 * Lo stile (card, niente riga orizzontale prima del bottone, bottone "Cerca")
 * è centralizzato qui e in search_close.php → coerente su tutti i moduli.
 */
$_search_eyebrow = $search_eyebrow ?? 'Ricerca';
$_search_attrs   = isset($search_form_attrs) && $search_form_attrs !== '' ? ' ' . $search_form_attrs : '';
?>
    <div class="card search-card">
      <div class="card__head">
        <span class="card__head-eyebrow"><?= htmlspecialchars($_search_eyebrow) ?></span>
      </div>
      <div class="card__body" style="padding:0">
        <form action="<?= htmlspecialchars($search_action) ?>" method="post"<?= $_search_attrs ?>>
          <div class="form">
<?php
// Reset delle opzioni per non lasciarle a eventuali ricerche successive nella stessa pagina.
unset($search_eyebrow, $search_form_attrs);
?>
