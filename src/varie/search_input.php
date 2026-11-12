<?php
/*
 * search_input.php — Campo testo di ricerca con pulsante "azzera" (X).
 *
 * Imposta PRIMA dell'include:
 *   $si_name   (obbligatorio) — name dell'input
 *   $si_value  (obbligatorio) — valore corrente
 *   $si_label  (opzionale)    — etichetta, default "Testo"
 *
 * La X compare solo quando il campo è popolato; al click svuota il campo e
 * re-invia il form, azzerando la ricerca salvata in sessione. Funziona su
 * desktop e mobile. Da usare dentro il widget di ricerca (search_open/close).
 */
$_si_label = $si_label ?? 'Testo';
?>
            <div class="form__row">
              <div class="form__label"><span class="form__label-name"><?= htmlspecialchars($_si_label) ?></span></div>
              <div class="form__control">
                <div class="search-input">
                  <?php /* value grezzo: il termine è salvato codificato (per i LIKE sulle colonne
                           codificate); il browser decodifica nell'attributo → mostra il testo vero e,
                           al re-invio, la ri-codifica del ricevitore lo ricostruisce. */ ?>
                  <input type="text" class="input" maxlength="100" name="<?= htmlspecialchars($si_name) ?>" value="<?= $si_value ?>" />
                  <?php if (trim($si_value) !== ''): ?>
                  <button type="button" class="search-input__clear" title="Azzera ricerca" aria-label="Azzera ricerca"
                          onclick="this.previousElementSibling.value=''; this.previousElementSibling.form.submit();">
                    <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
                  </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
<?php unset($si_label); ?>
