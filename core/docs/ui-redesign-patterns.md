# UI Redesign Patterns — Design System Globo

Riferimento per replicare il redesign su altri cloni di Globo.
Ricavato durante il redesign del branch `ui-redesign` (maggio 2026).

---

## 1. Architettura CSS

Tre file in `src/css/`, da caricare nell'ordine:

```html
<link rel="stylesheet" href="css/globo.tokens.css" />
<link rel="stylesheet" href="css/globo.themes.css" />
<link rel="stylesheet" href="css/globo.app.css" />
```

Il tema è controllato dall'attributo `data-theme` sul `<body>`, il cui valore viene dal PHP:

```html
<body data-theme="<?= htmlspecialchars(GLOBO_THEME) ?>">
```

La costante `GLOBO_THEME` è definita nella config (es. `"blu-ks"`).

**Ritocchi grafici di un singolo clone: `globo.custom.css`** (dal 2026-09-28). Se il clone ha
`custom/src/css/globo.custom.css`, `head.php` lo carica **per ultimo**, dopo i tre file del design
system: le sue regole prevalgono. Il core non ha questo file, quindi nei cloni che non lo creano non
cambia nulla. Serve per ritocchi che valgono in tutto il clone e non si possono fare con un tema
(che cambia solo le variabili di colore), **senza** copiare in `custom/` un file del core
(`footer.php`, `globo.app.css`…), che smetterebbe così di ricevere le correzioni del core.
Primo uso: 5stelle, toast di successo verde con icona spunta/X (il `--brand` rosso del suo tema
rendeva il successo identico all'errore). Attenzione: le pagine con `<head>` proprio (§2) non
passano da `head.php` e quindi non lo caricano.

---

## 2. Due pattern per l'apertura pagina: `head.php` vs `<head>` custom

### 2a. Pagine senza calendario — usare `head.php`

`varie/head.php` chiude il tag `</head>`, scrive `<body data-theme="...">` e include lo sprite SVG.
Va incluso dopo tutto il PHP iniziale e prima del markup HTML.
Se la pagina ha JS da mettere in `<head>`, si aggiunge un `<script>` **dopo** il `require`, non prima.

```php
<?php require("varie/common_1.php");
  // ... logica PHP ...
  require("varie/head.php");
?>
<script>
function controllaForm() { ... }
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">
    ...
  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
```

### 2b. Pagine vecchio layout (Group B) — `<head>` manuale

Le pagine con layout tabellare XHTML legacy non usano `head.php`. Il `<head>` è scritto manualmente. Il CSS di Flatpickr va aggiunto esplicitamente:

```php
<?php require("varie/common_1.php");
  // ... logica PHP ...
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?= htmlspecialchars(TITLE) ?></title>
  <link rel="stylesheet" href="css/globo.tokens.css" />
  <link rel="stylesheet" href="css/globo.themes.css" />
  <link rel="stylesheet" href="css/globo.app.css" />
  <link rel="stylesheet" href="css/flatpickr.min.css" />
  <script>
  function controllaForm() { ... }
  </script>
</head>
<body data-theme="<?= htmlspecialchars(GLOBO_THEME) ?>">
<?php require("varie/icon-sprite.php"); ?>
<div class="shell">
  ...
```

Il JS di Flatpickr è caricato globalmente da `footer.php` (come per le pagine Group A). Per i widget data, vedi Pattern 8.

---

## 3. Shell layout

Struttura fissa di ogni pagina:

```html
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <!-- contenuto pagina -->

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
```

`footer.php` chiude `</div></main></div></body></html>`.

---

## 4. Intestazione pagina

```html
<div class="page__head">
  <div class="page__title-block">
    <h1 class="page__title">Titolo pagina</h1>
    <p class="page__sub">Sottotitolo opzionale</p>  <!-- es. nome progetto -->
  </div>
  <div class="page__actions">
    <a class="btn btn--ghost" href="...">Azione</a>
  </div>
</div>
```

---

## 5. Componente Card

```html
<div class="card">
  <div class="card__head">
    <span class="card__head-eyebrow">Titolo sezione</span>
    <span class="card__head-count">42</span>  <!-- contatore opzionale -->
  </div>
  <div class="card__body" style="padding:0">
    <!-- contenuto -->
  </div>
  <div class="card__foot">
    <a class="btn btn--ghost" href="...">Annulla</a>
    <button type="submit" class="btn btn--primary">Salva</button>
  </div>
</div>
```

Più card nella stessa pagina: `style="margin-top:16px"` sulle card successive.

---

## 6. Componente Form (dentro `card__body`)

```html
<div class="card__body" style="padding:0">
  <div class="form">

    <!-- campo semplice -->
    <div class="form__row">
      <div class="form__label">
        <span class="form__label-name">Etichetta <span class="form__hint form__hint--inline">obb</span></span>
      </div>
      <div class="form__control">
        <input type="text" class="input" maxlength="255" name="campo" value="..." />
      </div>
    </div>

    <!-- campo textarea -->
    <div class="form__row">
      <div class="form__label">...</div>
      <div class="form__control form__control--multi">
        <textarea name="campo" class="textarea" rows="7">...</textarea>
      </div>
    </div>

    <!-- campo select -->
    <div class="form__row">
      <div class="form__label">...</div>
      <div class="form__control">
        <div class="select-wrap">
          <select name="campo">
            <option value="0">...</option>
          </select>
        </div>
      </div>
    </div>

    <!-- valore read-only (non input) -->
    <div class="form__row">
      <div class="form__label">Etichetta</div>
      <div class="form__control">testo</div>
    </div>

  </div>
</div>
```

---

## 7. Componente List (tabella dati)

```html
<div class="list" style="--list-cols: 1fr auto auto 58px">
  <div class="list__hdr">
    <span>Colonna 1</span>
    <span>Colonna 2</span>
    <span>Colonna 3</span>
    <span style="text-align:center; white-space:nowrap">Dett.</span>  <!-- colonna occhio: etichettata e centrata -->
  </div>
  <?php foreach ($rows as $row): ?>
  <div class="list__row" onclick="window.location.href='pagina_vis.php?id=<?= (int)$row['id'] ?>'">
    <span><?= htmlspecialchars($row['campo1']) ?></span>
    <span><?= $row['campo2'] ?></span>
    <span><?= $row['campo3'] ?></span>
    <div class="row-acts">
      <a class="icon-btn" href="pagina_vis.php?id=<?= (int)$row['id'] ?>"
         onclick="event.stopPropagation()" title="Dettagli">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
```

**Regole:**
- `--list-cols` definisce le colonne come `grid-template-columns`. Aggiungere **`58px`** per ogni
  colonna-icona con header etichettato ("Dett."/"Doc."). **Non 36px**: quella misura basta solo
  per l'icona (`.row-acts` ha padding 0), ma l'header condivide il padding standard 16px+16px di
  `.list__hdr > *` — con 36px totali restano 4px per il testo, che trabocca e disallinea
  visivamente l'etichetta rispetto all'icona sotto (bug reale trovato e corretto il 2026-07-06 in
  10 liste core, larghezza presa dal precedente 5stelle `prog_lista.php`/Profili).
- La riga è clickable (`onclick`) **e** ha l'icona occhio come alternativa visiva.
- L'icona ha `event.stopPropagation()` per non attivare il click della riga.
- **Header della colonna occhio: etichettato "Dett." (o "Doc." per l'icona documenti), centrato
  (`text-align:center; white-space:nowrap`), MAI vuoto.** Prima del 2026-07-06 si usava un
  header vuoto (`<span></span>`) — cambiato perché senza etichetta la colonna risultava
  disallineata a vista (l'icona in `.row-acts` è `justify-content: flex-end`, non centrata via
  CSS; l'etichetta dà un riferimento visivo sufficiente senza dover toccare quel CSS condiviso).
- **Colonna con più icone eterogenee nella stessa cella** (es. occhio + elimina, o occhio + doc +
  elimina — pattern usato in moduli `adhoc_*` dove l'eliminazione è disponibile direttamente
  dalla lista, non solo dal dettaglio): etichetta **"Azioni"** invece di "Dett.", stesso centrato
  `text-align:center; white-space:nowrap`. Larghezza: `.icon-btn` è 36px con `gap: 6px` in
  `.row-acts` (vedi `globo.app.css`) — per *N* icone la larghezza minima strutturale è
  `N*36 + (N-1)*6`; aggiungere un margine di ~10px per l'header (stesso criterio del caso a
  singola icona, dove 58px = 36px + 22px di margine). Valori pratici verificati: **88px per 2
  icone**, **130px per 3 icone**. Se la colonna è condizionale (es. visibile solo per un livello
  utente), il condizionale va applicato **sia** all'header **sia** alla riga — un'asimmetria tra i
  due produce disallineamento (bug trovato il 2026-07-13 in `adhoc_mcp_serv_lista.php`).
  (Introdotto in Globo-MCP, 2026-07-13, propagato a `adhoc_ks_h5s_filtro_scadenze.php` 5stelle.)
- Righe con scadenza attiva: aggiungere classe `is-attention` alla `list__row`.
- Righe **in evidenza** (elementi che l'ordinamento tiene sempre in cima, a prescindere dalla
  data): classe `is-evidenza` sulla `list__row` → barra verticale + fondo **ambra** (`--warn-500`
  / `--warn-100`). È un registro cromatico deliberatamente diverso da `is-attention` (brand):
  i due stati **possono coesistere sulla stessa riga**, quindi non devono usare lo stesso colore
  o diventano indistinguibili. La barra è un `box-shadow: inset` e non un `border-left`, che
  sposterebbe il contenuto di 3px rispetto alle righe vicine. L'ordinamento è responsabilità
  della query (`ORDER BY <flag> DESC, ...`), non del CSS. (Introdotto in 5stelle, 2026-07-29,
  per le note in evidenza dei progetti.)
- Il totale in `card__foot`: `<div class="card__foot" style="justify-content:flex-start; color:var(--fg-2); font-size:13px">`.

**Allineamento colonne — usare `fr`/fisse, MAI `auto`:**
Header e ogni riga sono **grid separate**: una traccia `auto` (o `max-content`) si dimensiona sul contenuto della *singola* riga → le colonne non si allineano tra righe diverse né con l'header. Usare sempre unità `fr` o larghezze fisse (`px`), che dipendono solo dalla larghezza del contenitore (identica per tutte le righe). Fissa dove il contenuto ha larghezza nota (date, chip, icona `36px`), `fr` per il testo variabile (che va a capo, senza troncare). Es. `--list-cols: 104px 1.26fr 5.14fr 72px 36px`.

**Stile uniforme di default (dal 2026-06-12):** ogni `.list` ha **di default** allineamento a sinistra, separatori verticali tra le colonne (hairline `--border-1`, nessun colore) su header e righe, e celle a tutta altezza con contenuto centrato verticalmente. Non serve aggiungere classi: basta `<div class="list" style="--list-cols: …">` + `list__hdr` + `list__row`. Le classi `.list--left` e `.list--ruled` restano come **alias retro-compatibili** (no-op) — non aggiungerle alle nuove liste. Dettagli implementativi: gutter simmetrici 16px, `.row-acts` senza padding (traccia 36px = larghezza icon-btn), elementi `position:absolute` (es. `.attention-dot`) esonerati.

**Paginazione — contare con `COUNT(*)`, MAI `fetchAll` di tutto:**
Per il totale (numero pagine) usare una query `SELECT COUNT(*) FROM ... WHERE ...` lato DB, **non** `fetchAll()` dell'intero set per poi contare l'array in PHP. Caricare tutte le righe (tutte le colonne, incluse le descrizioni) solo per contarle è lento e spreca memoria: si materializza l'intero set, poi si ri-esegue la query con `LIMIT` per la pagina. Pattern corretto: una query `COUNT(*)` per il totale + una query `... ORDER BY ... LIMIT off, 100` per la pagina. **Da verificare/applicare ogni volta che si crea una nuova pagina di lista.** (Risolto in `prog_lista` e `cont_lista` 2026-06-12. `ut_lista` non pagina — carica tutti gli utenti, tabella piccola; `cal_lista` è per-giorno, non conta l'intera tabella.)

**Riga di raggruppamento (banner full-width dentro la `.list`):**
Quando le righe vanno raggruppate per un'entità (es. tutti gli step di una stessa struttura), inserire un **banner a tutta larghezza** prima della prima riga di ogni gruppo. Non esiste (ancora) una classe dedicata: si riusa `.list__row` forzando una sola colonna e disattivando l'aspetto cliccabile.

```html
<!-- banner di gruppo -->
<div class="list__row" style="grid-template-columns:1fr; cursor:default; background:var(--n-25)">
  <div style="gap:10px; flex-wrap:wrap; font-weight:700; text-transform:uppercase; color:var(--fg-1)">
    <span><?= $nome_gruppo ?></span>
    <span style="font-weight:600; color:var(--fg-3); text-transform:none">(<?= (int)$conteggio ?>)</span>
    <!-- eventuali chip di stato -->
  </div>
</div>
<!-- righe normali del gruppo: <div class="list__row" ...> con le colonne piene -->
```

Note: `grid-template-columns:1fr` annulla `--list-cols` solo per quella riga (il banner non rispetta le colonne); il `<div>` interno eredita `display:flex` da `.list__row > *` (quindi `gap` e chip si dispongono in linea); essendo unico figlio non prende il separatore verticale. Il raggruppamento è puramente visivo (run-length sul set già ordinato): un gruppo che cade a cavallo di due pagine appare spezzato — accettabile. Se il pattern verrà riusato, promuoverlo a una classe `.list__group`. (Introdotto in `adhoc_ks_h5s_filtro_scadenze`, 5stelle, 2026-06-15.)

**Tabella larga (molte colonne) → `.list` scrollabile orizzontalmente:**
Per liste operative dense (>~8 colonne, es. il gestionale H5S a 14 colonne) che non stanno in una
larghezza comoda, avvolgere la `.list` in un wrapper con scroll orizzontale e dare alla lista un
`min-width` così le colonne non si schiacciano:

```html
<div style="overflow-x:auto">
  <div class="list" style="--list-cols: 44px 140px minmax(180px,1.6fr) … 48px; min-width:1180px">
    <div class="list__hdr"> … </div>
    <!-- righe con data-label su ogni cella -->
  </div>
</div>
```

Su desktop/tablet stretti compare lo scroll orizzontale; **su mobile** (`max-width:640px`) interviene
comunque la regola del framework che impila `.list__row` in card "etichetta: valore" (richiede
`data-label` sulle celle), quindi il `min-width` non genera scroll lì. Per le icone d'azione specifiche
non presenti nello sprite (es. euro, copy) si può caricare Font Awesome con un `<link>` dopo `head.php`.
Foot di ricerca con **secondo bottone** (es. "Azzera ricerca" che invia `reset_ricerca`): non usare
`search_close.php` (ha solo "Cerca") ma chiudere a mano `</div><div class="search-card__foot">…</div>`
con i due `<button type="submit">`. (Introdotto in `adhoc_ks_h5s_lista`, 5stelle, 2026-06-15.)

**Allineamento colonne:** il default del design system è **a sinistra** per tutte le colonne
(`.list__hdr`/`.list__row`, vedi `globo.app.css`). Tenere centrato (`text-align:center` sull'header +
`justify-content:center` sulla cella) **solo** le colonne molto strette a singolo glifo: marcatori
booleani, conteggi e icone d'azione. Le colonne con testo/valori (anche brevi, es. sigle o date) vanno
a sinistra. Header e corpo della stessa colonna devono avere lo **stesso** allineamento.

**Colonne booleane "sì/no" → spunta verde, `globo_bool_icon()`:** per le colonne che esprimono un
booleano, non stampare il testo "sì"; usare l'helper core `globo_bool_icon($val, $title = "")`
(in `funzioni.php`): rende una **spunta verde** (`#i-check` + classe `.bool-si`, colore `--ok-500`) sul
"sì" e **nulla** sul "no" (meno rumore visivo; il "no" si deduce dall'assenza). Il secondo argomento è
un tooltip opzionale mostrato sul "sì" (es. elenco servizi, nome agente):

```php
<div data-label="PMS" style="justify-content:center"><?= globo_bool_icon($flag_pms) ?></div>
<div data-label="S/I" style="justify-content:center"><?= globo_bool_icon($popup_servizio != "", $popup_servizio) ?></div>
```

(Introdotto nel core + applicato a `adhoc_ks_h5s_lista` (PMS/S/I/Ag), 5stelle, 2026-06-17.)

**Scala tipografica delle righe lista.** Il testo-base di `.list__row` è **14px** (`--fs-14`,
impostato sul `.list__row` nel core), non i 15px del body: le tabelle dati sono dense e i 15px
risultavano troppo grandi. La scala completa, da usare per uniformità su tutte le liste:

| Elemento | Classe | Stile |
|---|---|---|
| valore primario / riga | (base `.list__row`) o `.list-name` | 14px · `.list-name` aggiunge 700 + `--fg-1` |
| descrizione / secondario | `.list-desc` | 13px · `--fg-3` |
| numeri, date, codici | `.list-meta` | 12px · **font mono** (JetBrains Mono) · `--fg-3` |

Essendo `font-size` sul `.list__row` del core, **tutte le pagine con liste si allineano
automaticamente** (core e cloni via subtree): non si tocca pagina per pagina. (Deciso 2026-06-17.)

**Checklist pre-chiusura, da passare su OGNI `.list` prima di dichiarare concluso un redesign**
(nata da bug reali trovati tutti nella stessa sessione su Globo-MCP, 2026-07-13 — pattern corretto
in alcune liste dello stesso batch, sbagliato in altre, perché riscritto a mano invece che copiato
da un template verificato):
1. Colonna azioni: header **mai vuoto**, etichettato e centrato ("Dett."/"Doc." per singola icona,
   "Azioni" per più icone eterogenee, "Elim." se l'unica azione è eliminare), larghezza corretta
   (58/88/130px per 1/2/3 icone — vedi sopra).
1b. **Cella con piu' elementi impilati → un solo figlio diretto.** Le celle di `.list__row` sono
   flex in riga su desktop (`globo.app.css`, `.list__row > *`) e griglia `etichetta | valore` su
   mobile (`.list__row > [data-label]`). Con due figli diretti (es. descrizione + riga "procedura:"),
   su desktop il secondo si attacca al primo anche con `display:block`, e su mobile finisce nella
   colonna stretta dell'etichetta. Racchiuderli in un `<div>` sistema entrambi i layout. Trovato in
   `prog_lista.php` nel collaudo di Studio CIA (2026-09-18), il primo cliente che usa le procedure
   abbastanza da far comparire quella riga in ogni progetto.
2. `--list-cols` impostato sul **contenitore `.list`**, mai solo con `grid-template-columns` diretto
   sull'header: `.list__row` legge sempre `var(--list-cols)`, se non definita le righe non hanno un
   layout valido e si disallineano dall'header.
3. Celle-dati nelle **righe**: allineamento con `justify-content` (`center`/`flex-end`), **mai
   `text-align`** — `.list__row > *` è `display:flex`, `text-align` non ha effetto lì. L'header invece
   non è flex sui suoi figli diretti, quindi `text-align` funziona **solo lì**: facile scrivere
   l'header giusto e la riga sbagliata perché sembrano la stessa regola ma non lo sono.
4. Colonne condizionali (es. per livello utente): il condizionale va replicato **identico** su header
   e riga — un'asimmetria produce un numero di colonne diverso tra i due.
5. Prima di scrivere una nuova lista, consultare **questo documento**, non imitare una pagina gemella
   nello stesso clone: potrebbe non essere allineata all'ultimo standard.

---

## 8. Campo calendario (input data) — Flatpickr

La libreria date picker è **Flatpickr 4.6.13** (file locali in `src/javascript/flatpickr/` e `src/css/`).  
L'inizializzazione è globale in `footer.php`; non serve codice JS per-pagina.

### Inizializzazione globale (`footer.php`)

```js
document.querySelectorAll('[data-flatpickr]').forEach(function(el) {
  flatpickr(el, { wrap: true, dateFormat: 'd/m/Y', locale: 'it', allowInput: true });
});
```

Il JS locale è caricato subito prima della chiusura di `</body>`:

```html
<script src="javascript/flatpickr/flatpickr.min.js"></script>
<script src="javascript/flatpickr/l10n/it.js"></script>
```

### Group A — pagine con `head.php` e icone SVG

Toggle only (senza pulsante cancella):

```html
<div class="form__control" data-flatpickr>
  <input type="text" class="input" maxlength="10" name="data"
    value="<?= htmlspecialchars(globo_converti_data_in_formato_ita($data)) ?>"
    data-input autocomplete="off" />
  <a class="icon-btn" data-toggle title="Apri calendario">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
  </a>
</div>
```

Toggle + clear (con pulsante cancella):

```html
<div class="form__control" data-flatpickr>
  <input type="text" class="input" maxlength="10" name="data_scadenza"
    value="<?= htmlspecialchars(globo_converti_data_in_formato_ita($data_scadenza)) ?>"
    data-input autocomplete="off" />
  <a class="icon-btn" data-toggle title="Apri calendario">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
  </a>
  <a class="icon-btn" data-clear title="Cancella data">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
  </a>
</div>
```

### Group B — pagine vecchio layout con icone `<img>`

Toggle only:

```html
<span data-flatpickr>
  <input type="text" class="input_text data" maxlength="10" name="data"
    value="<?php echo globo_converti_data_in_formato_ita($data); ?>" data-input autocomplete="off" />
  <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
</span>
```

Toggle + clear:

```html
<span data-flatpickr>
  <input type="text" class="input_text data" maxlength="10" name="data_scadenza"
    value="<?php echo globo_converti_data_in_formato_ita($data_scadenza); ?>" data-input autocomplete="off" />
  <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
  <a data-clear style="cursor:pointer"><img src="immagini/ico_x.gif" width="16" height="16" border="0" alt="" /></a>
</span>
```

### Campo inizialmente disabilitato (Group A)

Per campi abilitati/disabilitati via JS (es. checkbox condizionale):

```html
<div class="form__control" data-flatpickr>
  <input type="text" class="input" maxlength="10" name="data_scadenza"
    value="" data-input autocomplete="off" disabled />
  <a class="icon-btn" data-toggle title="Apri calendario">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
  </a>
</div>
```

Il JS che abilita/disabilita usa `document.globo.fieldname.disabled = true/false` sull'input — funziona perché `data-input` è sull'input stesso, non sul wrapper.

### Campo con readonly condizionale (Group B)

Quando solo alcuni ruoli possono modificare la data:

```php
<?php if ($readonly == ""): ?>
<span data-flatpickr>
  <input type="text" class="input_text data" maxlength="10" name="data_scadenza_canone"
    value="<?php echo $data_scadenza_canone; ?>" data-input autocomplete="off" />
  <a data-toggle style="cursor:pointer"><img src="immagini/ico_calendario.gif" width="16" height="16" border="0" alt="" /></a>
</span>
<?php else: ?>
<input type="text" class="input_text data" maxlength="10" name="data_scadenza_canone"
  value="<?php echo $data_scadenza_canone; ?>" readonly="readonly" />
<?php endif; ?>
```

### File locali Flatpickr

```
src/javascript/flatpickr/flatpickr.min.js
src/javascript/flatpickr/l10n/it.js
src/css/flatpickr.min.css
```

---

## 9. Esito operazione: toast + redirect (no pagina intermedia)

Le pagine handler (`*_ins_2.php`, `*_agg.php`, `*_el.php`) **non** mostrano una pagina
"Risultato operazione": eseguono la logica e **reindirizzano** lasciando un **toast** (flash di
sessione). Pattern adottato dai moduli redisegnati (Progetti, Profili H5S).

**Meccanismo flash.** `head.php` legge `$_SESSION['flash']` in `$_globo_flash` e lo cancella;
`footer.php` lo rende come toast. Quindi l'handler imposta il flash e fa `header(Location)`:

```php
<?php require("varie/common_1.php");

  // ... logica DB (nessun output HTML: header() dev'essere sicuro) ...

  $_SESSION['flash'] = $ok
    ? ['type' => 'ok',  'text' => 'Operazione eseguita.']
    : ['type' => 'err', 'text' => 'Operazione non eseguita.'];

  header("Location: pagina_vis.php?id=" . (int)$id);   // salvataggio → torna al dettaglio
  exit;
```

**Destinazione.**
- **Salvataggio** (`*_agg.php`, `*_ins_2.php`): torna al **dettaglio** (`*_ins_vis.php?id=`).
- **Eliminazione** (`*_el.php`): torna alla **lista**, **azzerando la ricerca persistita** in
  sessione (es. `unset($_SESSION["ksglobo_ricerca_<modulo>"])`), così la lista riparte dai default
  invece di ripristinare l'ultima ricerca.

**Attenzione `header()`.** Nessun output prima del redirect (niente `echo`/HTML; verificare che gli
helper chiamati prima — es. Mailchimp `getTags`/`addTags` — non stampino nulla).

**Legacy (deprecato).** La vecchia pagina-card "Risultato operazione" (eyebrow *Risultato
operazione* + bottoni "Torna al dettaglio"/"Elenco") è **da convertire** a toast+redirect quando la
si incontra. Vedi anche il § enforcement proattivo dei pattern.

---

## 10. Pattern navigazione GET

Le pagine `*_vis.php` devono accettare sia POST (navigazione legacy) sia GET (link diretti da result pages):

```php
$id = $_POST["id"] ?? $_GET["id"] ?? '';
// oppure con parametri multipli:
$id_padre = $_POST["id_padre"] ?? $_GET["id_padre"] ?? '';
$id_figlio = $_POST["id_figlio"] ?? $_GET["id_figlio"] ?? '';
```

Le result pages linkano alle vis con GET:

```html
<a class="btn btn--primary" href="pagina_vis.php?id=<?= (int)$id ?>">Visualizza</a>
```

---

## 11. Form eliminazione

Sempre fuori dal form principale (dopo la card). La conferma usa il **modale del design system**
via `data-confirm` (vedi pattern 22) — **mai** `confirm()` nativo. Il bottone apre la modale e, su
conferma, **invia il form** (footer.php fa `form.submit()`):

```html
<form action="pagina_el.php" method="post" style="margin-top:16px">
  <input type="hidden" name="id" value="<?= (int)$id ?>" />
  <button type="submit" class="btn btn--danger-ghost" data-confirm="Eliminare questo elemento?">Elimina</button>
</form>
```

Override opzionali del modale: `data-confirm-title`, `data-confirm-ok`. L'handler `pagina_el.php`
segue il **pattern 9** (toast + redirect alla lista, niente pagina "Risultato operazione").

---

## 12. Icone SVG

Le icone sono uno sprite inline incluso da `varie/icon-sprite.php`.
Uso:

```html
<svg class="ic-stroke" aria-hidden="true"><use href="#i-eye"/></svg>
<svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg>
<svg class="ic-stroke" aria-hidden="true"><use href="#i-x"/></svg>
<svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
<svg class="ic-stroke" aria-hidden="true"><use href="#i-file-text"/></svg>
```

Bottone icona standalone: `<a class="icon-btn" href="...">` oppure `<button class="icon-btn">`.

Icone disponibili nello sprite (elenco completo):
`i-home`, `i-calendar`, `i-contacts`, `i-folder`, `i-user`, `i-logout`,
`i-plus`, `i-search`, `i-edit`, `i-trash`, `i-eye`, `i-download`, `i-x`, `i-settings`,
`i-chev-left`, `i-chev-right`,
`i-mail`, `i-mail-check`, `i-bell`,
`i-arrow-right`, `i-arrow-right-circle`, `i-link-external`,
`i-file-text`, `i-tag`, `i-pin`,
`i-clock`, `i-shield`, `i-globe`.

---

## 13. Icone contestuali inline in campi form

Quando un campo ha un valore che permette un'azione contestuale (mailto, link esterno), si affianca all'input un `icon-btn` visibile solo se il valore è non vuoto:

```html
<!-- campo e-mail con link mailto -->
<div class="form__control">
  <input type="text" class="input" maxlength="200" name="email" value="..." />
  <?php if (trim($email) !== ''): ?>
  <a class="icon-btn" href="mailto:<?= htmlspecialchars(trim($email)) ?>" title="Scrivi e-mail">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-mail"/></svg>
  </a>
  <?php endif; ?>
</div>

<!-- campo sito web con link esterno -->
<div class="form__control">
  <input type="text" class="input" maxlength="200" name="sito" value="..." />
  <?php if (trim($sito) !== ''): ?>
  <a class="icon-btn" href="http://<?= htmlspecialchars(trim($sito)) ?>" target="_blank" title="Apri sito web">
    <svg class="ic-stroke" aria-hidden="true"><use href="#i-link-external"/></svg>
  </a>
  <?php endif; ?>
</div>
```

**Nota:** il campo `sito` viene salvato senza `http://` (viene rimosso in `*_ins_2.php` e `*_agg.php`), quindi il prefisso va aggiunto nell'href.

---

## 14. Navigazione POST verso moduli non ancora redesignati

Se una result page o vis page deve navigare verso un modulo ancora nel vecchio design (es. `nl_lis_vis.php`), usare un form POST nascosto con trigger via `onclick` su un `<button type="button">`, evitando form annidati:

```html
<!-- dentro la card__foot del form principale -->
<button type="button" class="btn btn--ghost"
        onclick="document.getElementById('frm_esterno').submit()">
  Ritorna alla lista di distribuzione
</button>

<!-- FUORI dal form principale (dopo </form>) -->
<form id="frm_esterno" action="nl_lis_vis.php" method="post" style="display:none">
  <input type="hidden" name="id_lista" value="<?= (int)$id_lista ?>" />
</form>
```

---

## 15. Più bottoni POST in `card__foot` o in aree navigazione

Quando una card ha più bottoni di navigazione POST verso destinazioni diverse (senza form nidificati), usare `form style="display:contents"` dentro `card__foot`. Ogni form è inline e non spezza il layout:

```html
<div class="card__foot">
  <!-- bottone che torna alla ricerca (solo se ?r=1) -->
  <?php if (isset($_GET["r"]) && $_GET["r"] == 1): ?>
  <form action="cal_ric.php" method="post" style="display:contents">
    <button type="submit" class="btn btn--ghost">Ritorna alla ricerca</button>
  </form>
  <?php endif; ?>

  <!-- bottone che porta alla lista del giorno -->
  <form action="cal_lista.php" method="post" style="display:contents">
    <input type="hidden" name="giorno"  value="<?= $giorno ?>" />
    <input type="hidden" name="mese"    value="<?= $mese ?>" />
    <input type="hidden" name="anno"    value="<?= $anno ?>" />
    <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
    <button type="submit" class="btn btn--ghost"><?= globo_converti_data_in_formato_ita($data) ?></button>
  </form>

  <!-- bottone principale -->
  <button type="submit" class="btn btn--primary">Aggiorna</button>
</div>
```

**Nota:** il form principale (quello del submit primario) deve essere il wrapper esterno della card; i form di navigazione stanno dentro `card__foot` come inline. Stessa tecnica funziona in `<div style="display:flex; gap:8px">` per righe di azioni esterne alla card.

---

## 16. Navigazione mese/giorno con `.cal-toolbar` e `.day-toolbar`

Il `page__head` porta il titolo contestuale e le azioni principali (Ricerca, Nuovo impegno, ecc.).
La navigazione prev/next sta in un componente separato: `.cal-toolbar` per il mese, `.day-toolbar` per il giorno.
Struttura: `icon-btn ← | label mese-prima | titolo centrato | label mese-dopo | icon-btn →`.

**Vista mensile (`cal-toolbar`):**
```html
<div class="cal-toolbar">
  <div class="cal-toolbar__nav">
    <form action="cal_home.php" method="post" style="display:contents">
      <input type="hidden" name="mese"    value="<?= $mese_prec ?>" />
      <input type="hidden" name="anno"    value="<?= $anno_prec ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
      <button type="submit" class="icon-btn" title="Mese precedente">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
      </button>
    </form>
    <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:capitalize;color:var(--fg-4)"><?= htmlspecialchars($mese_lettere_prec) ?></span>
  </div>
  <div class="cal-toolbar__title"><?= htmlspecialchars(strtolower($mese_lettere)) ?> <?= $anno ?></div>
  <div class="cal-toolbar__nav">
    <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:capitalize;color:var(--fg-4)"><?= htmlspecialchars($mese_lettere_succ) ?></span>
    <form action="cal_home.php" method="post" style="display:contents">
      <input type="hidden" name="mese"    value="<?= $mese_succ ?>" />
      <input type="hidden" name="anno"    value="<?= $anno_succ ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
      <button type="submit" class="icon-btn" title="Mese successivo">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
      </button>
    </form>
  </div>
</div>
```

`$mese_lettere_prec` e `$mese_lettere_succ` si calcolano nel PHP con `globo_mese_in_lettere($mese_prec)` ecc.

**Vista giornaliera (`day-toolbar`):**
```html
<div class="day-toolbar">
  <div class="cal-toolbar__nav">
    <form action="cal_lista.php" method="post" style="display:contents">
      <!-- campi prev day -->
      <button type="submit" class="icon-btn" title="Giorno precedente">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-left"/></svg>
      </button>
    </form>
    <span style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--fg-4)"><?= htmlspecialchars($label_prec) ?></span>
  </div>
  <div class="day-toolbar__title">
    <small><?= htmlspecialchars(ucfirst($giorno_lettere)) ?></small>
    <?= (int)$giorno ?> <?= htmlspecialchars($mese_lettere) ?> <?= $anno ?>
  </div>
  <div class="cal-toolbar__nav">
    <span style="..."><?= htmlspecialchars($label_succ) ?></span>
    <form action="cal_lista.php" method="post" style="display:contents">
      <!-- campi next day -->
      <button type="submit" class="icon-btn" title="Giorno successivo">
        <svg class="ic-stroke" aria-hidden="true"><use href="#i-chev-right"/></svg>
      </button>
    </form>
  </div>
</div>
```

Le label `$label_prec`/`$label_succ` si calcolano con:
```php
$abbr_days  = ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'];
$label_prec = $abbr_days[(int)date("w", $ts_prev)] . " " . (int)$g_prev . "/" . $m_prev;
$label_succ = $abbr_days[(int)date("w", $ts_next)] . " " . (int)$g_next . "/" . $m_next;
```

---

## 17. Griglia calendario mensile `.cal-month`

Usato in `cal_home.php`. Usa il componente CSS `.cal-month`/`.cal-day` del design system.
Le celle vuote iniziali si generano con un ciclo PHP (non più `grid-column-start`).
Ogni cella giorno è un `button.cal-day` dentro un `form style="display:contents"`.
Il CSS `button.cal-day` in `globo.app.css` azzera il bordo del browser e ripristina `border-right` e `border-bottom`.

```php
// calcolo prima colonna (lun=1 … dom=7)
$days_map  = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
$first_col = $days_map[date('l', mktime(0, 0, 0, $mese, 1, $anno))];
```

```html
<div class="cal-month">
  <div class="cal-month__weekdays">
    <?php foreach (['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $h): ?>
    <div><?= $h ?></div>
    <?php endforeach; ?>
  </div>
  <div class="cal-month__grid">
    <!-- celle vuote per allineamento primo giorno -->
    <?php for ($i = 1; $i < $first_col; $i++): ?>
    <div class="cal-day cal-day--empty"></div>
    <?php endfor; ?>

    <?php foreach ($cal_days as $g => $day):
      $is_today    = ($g == $giorno_oggi && $mese == $mese_oggi && $anno == $anno_oggi);
      $has_events  = ($day['n_imp'] + $day['n_scad']) > 0;
      $counter_cls = $has_events ? 'cal-day__counter--alert' : 'cal-day__counter--empty';
    ?>
    <form action="cal_lista.php" method="post" style="display:contents">
      <input type="hidden" name="giorno"  value="<?= $day['pad'] ?>" />
      <input type="hidden" name="mese"    value="<?= $mese ?>" />
      <input type="hidden" name="anno"    value="<?= $anno ?>" />
      <input type="hidden" name="cal_vis" value="<?= htmlspecialchars($cal_vis) ?>" />
      <button type="submit" class="cal-day<?= $is_today ? ' cal-day--today' : '' ?>">
        <span class="cal-day__num"><?= $day['pad'] ?></span>
        <div class="cal-day__counters">
          <span class="cal-day__counter <?= $counter_cls ?>"><b><?= $day['n_imp'] ?></b> imp · <b><?= $day['n_scad'] ?></b> scad</span>
        </div>
      </button>
    </form>
    <?php endforeach; ?>
  </div>
</div>
```

**Classi badge contatori:**
- nessun evento: `cal-day__counter--empty` (verde)
- ≥1 evento: `cal-day__counter--alert` (punto brand + numeri brand)

---

## 18. Vista giornaliera — Timeline con `.timeline`

Usato in `cal_lista.php`. Gli slot sono `button.timeline__slot` dentro `form style="display:contents"`.
Il CSS `button.timeline__slot` in `globo.app.css` azzera il bordo del browser e imposta `width:100%`.

Colonne: `70px 80px 1fr 140px 60px` (ora, durata, descrizione, responsabile, azione).

```html
<div class="card">
  <div class="card__head">
    <span class="card__head-eyebrow">Impegni</span>
    <span class="card__head-count">3</span>
  </div>
  <div class="timeline__head">
    <div>Ora</div>
    <div>Durata</div>
    <div>Descrizione</div>
    <div>Responsabile</div>
    <div style="text-align:right">Dett. / Ins.</div>
  </div>
  <div class="timeline">

    <!-- slot occupato -->
    <form action="cal_imp_vis.php" method="post" style="display:contents">
      <input type="hidden" name="id_impegno" value="..." />
      <input type="hidden" name="cal_vis"    value="..." />
      <button type="submit" class="timeline__slot timeline__slot--busy">
        <span class="timeline__time timeline__time--hour">10:00</span>
        <span class="timeline__dur">02:30</span>
        <span class="timeline__desc">Riunione di progetto</span>
        <span class="timeline__resp">mario</span>
        <span class="timeline__act">
          <span class="icon-btn"><svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right-circle"/></svg></span>
        </span>
      </button>
    </form>

    <!-- slot libero -->
    <form action="cal_imp_ins_1.php" method="post" style="display:contents">
      <input type="hidden" name="data"    value="..." />
      <input type="hidden" name="ora"     value="10:30" />
      <input type="hidden" name="cal_vis" value="..." />
      <button type="submit" class="timeline__slot timeline__slot--free">
        <span class="timeline__time">10:30</span>
        <span class="timeline__dur"></span>
        <span class="timeline__desc timeline__desc--free">slot libero</span>
        <span class="timeline__resp"></span>
        <span class="timeline__act">
          <span class="icon-btn icon-btn--ghost-dark"><svg class="ic-stroke" aria-hidden="true"><use href="#i-plus"/></svg></span>
        </span>
      </button>
    </form>

  </div>
</div>
```

**Classi ora:**
- `.timeline__time--hour` sugli slot con minuti `==00` (ore piene)

**Slot "tutti gli utenti" (vista piatta, senza slot liberi):**
Stessa struttura `.timeline__slot--busy`, senza i form per inserimento.
Aggiungere `is-attention` al button se l'impegno appartiene all'utente loggato.

**Logica avanzamento slot in PHP:**
```php
$slot_adv = globo_converti_ora_in_dec($slot_durata);
if ($slot_adv <= 0) $slot_adv = 0.5;  // fallback mezzo-ora se durata non leggibile
$is_hour = (fmod($slot_dec, 1.0) == 0.0);
```

---

## 19. Responsive / mobile

Il design è **desktop-first**: il layout base (sopra i breakpoint) è quello già validato. L'adattamento mobile vive **solo** in override dentro `@media`, raccolti nella sezione `RESPONSIVE` in fondo a `globo.app.css`.

**Breakpoint** (le `@media` non accettano `var()`, sono valori convenzionali):
- tablet: `@media (max-width: 1024px)`
- mobile: `@media (max-width: 640px)`

**REGOLA CARDINALE**: non modificare **mai** una regola base (desktop) per ottenere il mobile. Si **aggiungono** solo override dentro `@media`, oppure righe invisibili su desktop (es. `.topbar__burger { display: none }`). Così il desktop resta byte-per-byte invariato e non va ri-testato. Se un componente richiede *davvero* di cambiare una regola base condivisa, fermarsi e valutare l'impatto desktop prima.

**Cache-busting** (`varie/head.php`): i CSS sono linkati con `?v=<filemtime>` → cambia a ogni modifica del file, niente hard refresh in dev e ricarico automatico dopo i deploy.

**Pattern per componente** (tutti in `@media (max-width: 640px)` salvo nota):
- **Topbar → hamburger**: bottone `.topbar__burger` (base `display:none`, mobile `inline-flex`); `.topbar__nav` diventa tendina assoluta sotto la barra, aperta con `.topbar.is-open` (toggle JS in `footer.php`); data e nome utente nascosti, avatar+logout+burger nella barra. Icona sprite `i-menu`.
- **Page header**: `.page__head { flex-direction: column }`, `.page__actions { flex-wrap: wrap }`.
- **Form**: `.form__row { grid-template-columns: 1fr }` (label sopra il campo), label senza sfondo, campo a tutta larghezza.
- **Liste → card impilate**: `.list__hdr` nascosto; `.list__row` a blocco; ogni cella con `data-label="…"` diventa una riga "etichetta: valore" via `.list__row > [data-label]` (grid `104px 1fr`) + `::before { content: attr(data-label) }`. `display:grid !important` per battere eventuali `display:flex` inline (es. cella visibilità in `prog_lista`). Aggiungere `data-label` alle celle di ogni nuova lista.
- **Home**: `.home-grid` a 2 colonne (tablet) e 1 colonna (mobile); tile orizzontali compatte (icona+testo a sinistra, freccia nascosta).
- **Calendario** (griglie ridotte): toolbar compatte, nomi mese/giorno prec-succ nascosti (`.cal-toolbar__nav span`), griglia mese con celle/font ridotti, vista giorno (`.cal-grid`) con asse più stretto ed eventi compatti.
- **Toast/Modale/Tabelle**: toast a tutta larghezza in basso; overlay modale con padding ridotto; tabelle di dettaglio con classe `.detail-table` + `min-width` → scorrono in orizzontale nel contenitore `overflow-x:auto` invece di schiacciarsi.

---

## 20. Multiselect a categorie: riepilogo attivi + picker modale

**Quando usarlo.** Campo che seleziona molti elementi da un elenco lungo (decine/centinaia)
dove di norma ne sono attivi pochi. Sostituisce la checklist piatta: si mostrano **solo gli
attivi**, raggruppati per categoria, e si aggiungono gli altri da un **picker** con ricerca.
Prima applicazione: "Servizi / interfacciamenti" nel dettaglio profilo
(`adhoc_ks_h5s_ins_vis.php`, custom 5stelle — 204 voci, ~poche attive per profilo).

**Struttura.**
- **Riepilogo attivi** `#…-attivi`: contenitore vuoto (`data-empty="…"`) popolato via JS dagli
  elementi *checked*. Ogni voce = `.serv-chip` (riusa l'estetica di `.chip`): etichetta +
  eventuale URL contestuale + bottone **×** che de-seleziona.
- Pulsante **"+ Aggiungi"** (`.btn--ghost`) che apre la modale.
- **Picker** = `.modal-overlay[hidden]` + `.modal` (allargata, es. `max-width:680px`,
  `max-height:82vh`, `display:flex; flex-direction:column`): campo **ricerca** in alto +
  elenco categorie come `<details class="serv-cat">` (accordion nativo, niente JS per
  aprire/chiudere); ogni `summary` mostra nome categoria + badge `.filter-badge` "selez/tot";
  dentro, una `<label>` per voce con la **checkbox vera**.

**Regola chiave (niente cambi al modello dati).** Le **checkbox vere vivono dentro il picker**
e mantengono il `name="campo[]"` originale: una checkbox spuntata **viene inviata col form anche
se la modale è `hidden`/`display:none`**. Quindi il salvataggio resta identico a prima (nessun
hidden input di sincronizzazione, nessun campo nuovo). Il riepilogo è solo una **vista** generata
da JS leggendo le checkbox *checked*.

**JS (vanilla, IIFE, nessuna dipendenza).** `renderSummary()` raggruppa i checked per
`data-cat` e costruisce i chip (con `data-label`, `data-url`); `updateBadges()` aggiorna
"selez/tot" ed evidenzia (`.has-active`) le categorie con selezioni; `applyFilter(q)` nasconde le
voci non corrispondenti e le categorie vuote (e fa `details.open=true` sui match durante la
ricerca). Apertura/chiusura modale come gli altri overlay: click-fuori + `Escape` (vedi §
Toast/Modale). Si richiamano `renderSummary()`+`updateBadges()` anche al load.

**Metadati sulle checkbox** (per disaccoppiare render e logica): `data-label` (testo),
`data-cat` (categoria, per il raggruppamento del riepilogo), `data-url` (URL contestuale
calcolato server-side; il chip mostra il link solo se valorizzato).

**Sola lettura** (utente senza permessi di modifica): NON si rende il picker; si stampa
server-side solo il riepilogo attivi (chip read-only + eventuale URL) e, se serve preservare il
valore in un eventuale submit, hidden input `name="campo[]"` per gli attivi.

**Sorgente dati / categorie.** Le categorie e lo stato attivo/obsoleto stanno a DB (tabella di
supporto categorie + colonne `id_categoria`/`ord`/`flag_attivo`): query con JOIN, `ORDER BY
ord, valore`, filtro `flag_attivo=1`. Importante: includere anche gli elementi **obsoleti già
selezionati** sul record corrente (`OR id IN (<correnti>)`) altrimenti sparirebbero al salvataggio.

## 21. Combobox con ricerca (select singolo filtrabile)

**Quando usarlo.** Campo a **selezione singola** da un elenco lungo (decine/centinaia di voci)
dove scorrere il `<select>` nativo è scomodo. Sostituisce il `<select>` con un campo testo che
filtra le voci per sottostringa, mantenendo l'invio del solo **id**. Prima applicazione: campo
"Progetto" nel dettaglio profilo (`adhoc_ks_h5s_ins_vis.php`, custom 5stelle).
Diverso dal Pattern 20: lì selezione **multipla** con picker modale; qui **una sola** scelta, inline.

**Struttura.** Wrapper `.combo` (`position:relative; width:100%`) che contiene:
- `input.combo__input` di tipo testo (`role="combobox"`, `autocomplete="off"`): mostra/cerca, **non**
  ha `name` (non viene inviato).
- `input[type=hidden]` con il `name` reale del campo (es. `globo_id_progetto`): porta l'**id** nel
  submit. Pre-valorizzato server-side; il testo del campo è pre-riempito col nome corrispondente.
- bottone **×** `.combo__clear` per rimuovere la scelta (vedi sotto).
- `ul.combo__panel[hidden]` (`role="listbox"`): una `li.combo__opt` per voce con `data-id`/`data-label`,
  più una voce "— Nessuno —" (`data-id="0"`) visibile solo a campo vuoto.

**Regola chiave (l'id cambia solo su selezione esplicita).** Digitare nel campo **filtra** ma NON
tocca l'hidden: l'id si aggiorna solo cliccando/confermando una voce. Su `blur`/clic fuori si
**ripristina l'etichetta** dell'opzione selezionata, così un testo digitato a metà non resta
"orfano" disallineato dal valore salvato. Le opzioni usano `mousedown` + `preventDefault` (non
`click`) per non far perdere il focus all'input prima della scelta.

**Rimozione con conferma.** La × non azzera subito: apre una **modale di conferma**
(`.modal-overlay` + `.modal`, vedi Pattern 11/Toast-Modale) "Rimuovere «…»?"; solo su conferma
l'hidden torna a `0` e la × si nasconde. Evita cancellazioni accidentali dell'associazione.

**Apertura su/giù (flip).** Il pannello si apre verso il basso (`top:calc(100% + 4px)`); in
`open()` si misura lo spazio sotto nel viewport e, se insufficiente e sopra ce n'è di più, si
aggiunge `.combo__panel--up` (`bottom:calc(100% + 4px); top:auto`). Necessario quando il campo è
l'ultimo della pagina. La direzione si ricalcola a ogni apertura (non durante lo scroll).

**Tastiera.** Frecce ↑/↓ scorrono le voci visibili (`.is-active` + `scrollIntoView`), Invio
seleziona l'evidenziata, Esc chiude e ripristina l'etichetta.

**JS (vanilla, IIFE, nessuna dipendenza).** `filter(q)` mostra/nasconde le `combo__opt` per
sottostringa; `choose(o)` scrive id+label e chiude; `selectedLabel()` rilegge dall'hidden per i
ripristini. Guard iniziale `if (!combo) return;` così lo stesso script convive col caso sola-lettura.

**Sola lettura** (utente senza permessi di modifica): NON si rende il combobox; si stampa il nome
selezionato come testo statico (`.input--ro`) + hidden input col valore corrente per preservarlo.

## 22. Dialoghi: `globoConfirm` / `globoAlert` (mai `alert()`/`confirm()` nativi)

**Regola.** In tutte le pagine col nuovo shell **non si usano `alert()`/`confirm()` nativi del
browser**: si usa la **modale del design system** esposta globalmente da `varie/footer.php`.
Ogni volta che si incontra un `alert()`/`confirm()` legacy durante la migrazione, va sostituito.
(Esclusi: moduli morti `bil*` e librerie di terze parti — ckeditor/elfinder/ecc.)

**API (definita in `footer.php`, disponibile su ogni pagina che include il footer).**
- `globoConfirm(opts) → Promise<bool>` — modale a 2 bottoni. `opts`: `{message, title, okLabel,
  cancelLabel, danger}`. Forma breve: `globoConfirm("Procedere?")`. Risolve `true` su OK/Invio,
  `false` su Annulla/Esc/click-fuori.
- `globoAlert(message, opts) → Promise<void>` — modale a 1 bottone (`opts`: `{title, okLabel}`).
- Markup unico `#confirm-modal` (titolo `#confirm-modal-title`, messaggio `#confirm-modal-msg`,
  bottoni `#confirm-ok`/`#confirm-cancel`); titolo/etichette/colore impostati a runtime.

**Bottoni `[data-confirm]` (retrocompat).** Un `<button data-confirm="messaggio">` dentro un form
apre la modale (default da eliminazione: titolo "Conferma eliminazione", bottone rosso "Elimina")
e **invia il form** su conferma. Override opzionali: `data-confirm-title`, `data-confirm-ok`.
Resta il modo consigliato per le **eliminazioni** (niente JS per-pagina). Internamente usa
`globoConfirm`.

**Conferme che NON sono eliminazioni** (archiviare, ripristinare, ...): `data-confirm` da solo
mostra titolo "Conferma eliminazione", pulsante "Elimina" e stile di pericolo (rosso), perche' nasce
per le eliminazioni. Per un'azione che non distrugge niente si personalizza, e il pulsante torna blu:

```html
<button type="submit" class="btn btn--ghost" data-confirm="Confermi archiviazione?"
        data-confirm-title="Conferma archiviazione" data-confirm-ok="Archivia"
        data-confirm-danger="0">Archivia</button>
```

`data-confirm-danger="0"` esiste dal 2026-09-18: prima lo stile di pericolo era fisso, e
l'archiviazione di un progetto chiedeva "Conferma eliminazione" con un pulsante rosso "Elimina"
(segnalato da Alessandro nel collaudo manuale di Studio CIA). Messaggi ripresi dal Globo originale
(`confirm("confermi archiviazione?")`).

**Conversione di `confirm()`/`alert()` (il punto delicato = async).** Le funzioni native sono
**bloccanti**; la modale è **asincrona** → il flusso va riscritto, non è un find-replace:
- `alert(msg); return false;` → `globoAlert(msg); return false;` (il submit è già fermato).
- `if (!confirm(msg)) return false;` (submit-o-no, es. su `onsubmit`) → l'handler fa
  `e.preventDefault()` **sempre**, poi `globoConfirm(msg).then(ok => { if (ok) form.submit(); })`.
- confirm che **imposta un flag e invia comunque** (es. "genera nuovo codice cliente?") →
  `globoConfirm(...).then(ok => { campo.value = ok ? 'si' : 'no'; form.submit(); })` (entrambi i
  rami inviano). Tipicamente si converte l'intera `controllaForm` in validazione **async** che
  `await`-a alert/confirm e chiama `form.submit()` solo alla fine.

**Codice di riferimento — validazione async completa.** Fino al 2026-09-18 l'unica pagina del
core che implementava questo schema era `prog_doc.php`, citata come modello anche nei commenti
dei cloni (Verdecasa, `adhoc_vc_*`). Quella pagina e' stata **eliminata** (funzione documenti
di progetto mai usata da nessun cliente, e con un download vulnerabile: vedi
`refactoring-notes.md` → FILE ELIMINATI): il modello vive ora qui. Tre pezzi, tutti necessari:

```html
<script>
  // 1. La validazione e' async: globoAlert restituisce una Promise (la modale non blocca il
  //    thread come alert()). Ogni errore: await del messaggio, focus, return false.
  async function controllaForm() {
    var nome = document.globo.nome.value.trim();
    if (nome == "") { await globoAlert("compilare il campo \"nome\""); document.globo.nome.focus(); return false; }
    // ... altri controlli, stesso schema ...
    return true;
  }
</script>

<form name="globo" method="post" action="pagina_ins_2.php">   <!-- NIENTE onsubmit="return controllaForm()" -->
  ...
</form>

<script>
  // 2. Il submit si intercetta SEMPRE e si rifa' via JS solo se la validazione passa.
  //    onsubmit="return controllaForm()" non funziona piu': una funzione async restituisce una
  //    Promise, che e' "truthy", quindi il form partirebbe comunque (vedi la trappola qui sotto).
  //    form.submit() chiamato dentro il listener NON riscatena l'evento 'submit': niente ricorsione.
  document.globo.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (await controllaForm()) document.globo.submit();
  });
</script>

<?php if (isset($popup)): ?>
<script>
  // 3. Un messaggio che arriva dal server va differito a DOMContentLoaded: globoAlert e' definita
  //    in footer.php, cioe' DOPO il contenuto della pagina. Chiamata subito, non esisterebbe ancora.
  document.addEventListener('DOMContentLoaded', function() { globoAlert(<?= json_encode($popup, JSON_UNESCAPED_UNICODE) ?>); });
</script>
<?php endif; ?>
```

**La trappola che questo schema evita**: lasciare `onsubmit="return controllaForm()"` dopo aver
reso `controllaForm` async. Il form parte **sempre**, la validazione non blocca niente e nessun
errore lo segnala.

**Sweep.** Conversione **incrementale, modulo per modulo** (agganciata al collaudo manuale), non un
big-bang sull'intero codebase.

## 23. Icone sui pulsanti azione principali (`.btn`)

**Regola.** I pulsanti azione principali (`.btn` in `page__actions`, `card__foot`, footer ricerca)
mostrano un'**icona SVG a sinistra dell'etichetta**, per coerenza visiva tra i moduli. Markup:
`<svg class="ic-stroke" aria-hidden="true"><use href="#i-…"/></svg>` seguito dall'etichetta.

**Mappa azione → icona** (sprite in `varie/head.php`):

| Azione | Icona |
|---|---|
| Navigazione / ritorno / vai a lista (Elenco X, Torna a…, Indietro, Calendario) | `#i-arrow-right` |
| Nuovo / crea (Nuovo impegno, Nuova scadenza, Nuovo elemento) | `#i-plus` |
| Cerca | `#i-search` |
| Scarica / export (.vcs, XLS) | `#i-download` |
| Email (Ricevi via e-mail) | `#i-mail` |

Note:
- Nello sprite **non esiste una freccia sinistra**: per "ritorno/indietro" si usa comunque
  `#i-arrow-right` (è la freccia di navigazione standard, come in "Elenco profili").
- I pulsanti di **salvataggio** (Inserisci / Aggiorna / Salva) restano **senza icona**.
- I pulsanti distruttivi (Elimina) seguono il Pattern 11/22 (`data-confirm`), non questo.
- Le frecce prev/next di mese/giorno usano `#i-chev-left`/`#i-chev-right` (Pattern 16), fuori da questa mappa.

**Rollout.** **Completato (2026-06-24).** Inizialmente incrementale (prima applicazione: Profili
"Elenco profili" e Calendario; poi `prog_ins_1` e calendario scadenze/ricerca), poi esteso a **tutti**
i pulsanti di navigazione/ritorno top-right: Progetti (`prog_vis`), Contatti (`cont_vis`,
`cont_ins_1` — ritorno spostato da `card__foot` a `page__actions`), Utenti (`ut_vis`, `ut_ins_1`),
Procedure e Template (`pr_proced_*`, `pr_template_*`), `cal_lista` ("Torna alla vista mese").
Nota override: dove un clone ha un override custom della pagina, la freccia va messa anche
nell'override (es. 5stelle `custom/src/ut_vis.php`), altrimenti resta ombreggiata.
