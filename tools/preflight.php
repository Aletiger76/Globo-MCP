<?php
/*
 * preflight.php — Verifica dei requisiti server per Globo (pre-deploy).
 *
 * USO:
 *   1. Carica questo file nella web root dell'app (la cartella "globo/" sul server).
 *   2. Aprilo nel browser: https://globo-ks.kosmosol.it/globo/preflight.php
 *   3. Leggi l'elenco verde/rosso.
 *   4. >>> CANCELLA SUBITO QUESTO FILE DAL SERVER <<< (espone informazioni di sistema).
 *
 * Gira nello stesso runtime PHP del sito (non la CLI), quindi verifica la
 * versione e le estensioni REALI con cui gira l'applicazione.
 * NON è parte dell'app: tienilo in tools/, caricalo solo per la verifica.
 */

header('Content-Type: text/html; charset=UTF-8');

// Raccolta esiti: [ok(bool), etichetta, dettaglio]
$checks = [];

// ── 1. Versione PHP ─────────────────────────────────────────────────────────
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = [$phpOk, 'PHP ≥ 8.0', 'Rilevato: ' . PHP_VERSION];

// ── 2. Estensioni richieste ─────────────────────────────────────────────────
foreach (['pdo_mysql', 'gd', 'mbstring', 'openssl'] as $ext) {
  $loaded = extension_loaded($ext);
  $checks[] = [$loaded, "Estensione $ext", $loaded ? 'caricata' : 'MANCANTE'];
}

// ── 3. Config + connessione DB + colonne token ──────────────────────────────
$dbOk = false;
$cfgLoaded = false;
try {
  // Stesso meccanismo delle pagine dell'app (CWD = web root)
  require("config/config.php");
  $cfgLoaded = true;
} catch (Throwable $e) {
  $checks[] = [false, 'Caricamento config/config.php', 'ERRORE: ' . $e->getMessage()];
}

if ($cfgLoaded) {
  $checks[] = [true, 'Config caricata', 'host config selezionato in base a HTTP_HOST'];
  try {
    $pdo = new PDO(
      "mysql:host=" . MYSQL_HOST_GLOBO . ";dbname=" . MYSQL_DB_GLOBO . ";charset=utf8mb4",
      MYSQL_USER_GLOBO, MYSQL_PASSWORD_GLOBO
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbOk = true;
    $checks[] = [true, 'Connessione al DB', 'OK (database "' . MYSQL_DB_GLOBO . '")'];

    // Colonne token nella tabella utente: servono SOLO se il clone usa il sistema
    // di cookie "dispositivo fidato". Dove AUTH_TOKEN e' disattivato (MC Protection,
    // ML Benessere) le colonne non ci sono per scelta, non per dimenticanza:
    // segnarle in rosso darebbe tre falsi allarmi e un verdetto complessivo KO
    // proprio nel momento in cui di questa schermata ci si deve fidare.
    if (defined('AUTH_TOKEN') && AUTH_TOKEN) {
      $cols = $pdo->query("SHOW COLUMNS FROM utente LIKE 'auth_token%'")->fetchAll(PDO::FETCH_COLUMN);
      foreach (['auth_token_login', 'auth_token_create', 'auth_token_expire'] as $c) {
        $has = in_array($c, $cols, true);
        $checks[] = [$has, "Colonna utente.$c", $has ? 'presente' : 'MANCANTE (eseguire ALTER TABLE)'];
      }
    }
    else {
      $checks[] = [true, 'Colonne utente.auth_token_*',
        'non applicabile: AUTH_TOKEN disattivato in questa installazione, le colonne non servono', 'na'];
    }
  } catch (Throwable $e) {
    $checks[] = [false, 'Connessione al DB', 'ERRORE: ' . $e->getMessage()];
  }
}

// ── 4. Cartelle scrivibili ──────────────────────────────────────────────────
$uploadDir = __DIR__ . '/upload';
$uploadOk  = is_dir($uploadDir) && is_writable($uploadDir);
$checks[] = [$uploadOk, 'Cartella upload/ scrivibile', is_dir($uploadDir) ? ($uploadOk ? 'OK' : 'esiste ma NON scrivibile') : 'NON esiste'];

if ($cfgLoaded && defined('DIR_LOG')) {
  $logOk = is_dir(DIR_LOG) && is_writable(DIR_LOG);
  $checks[] = [$logOk, 'Cartella log (DIR_LOG) scrivibile', is_dir(DIR_LOG) ? ($logOk ? 'OK' : 'esiste ma NON scrivibile') : 'NON esiste: ' . DIR_LOG];
}

// ── Esito complessivo ───────────────────────────────────────────────────────
$allOk = array_reduce($checks, fn($acc, $c) => $acc && $c[0], true);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Globo — Preflight requisiti server</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 820px; margin: 2rem auto; padding: 0 1rem; color: #1a1a1a; }
    h1 { font-size: 1.4rem; }
    .banner { padding: 1rem; border-radius: 8px; font-weight: 700; margin: 1rem 0; }
    .banner.ok { background: #e6f6ed; color: #14672e; border: 1px solid #9fd8b4; }
    .banner.ko { background: #fdecee; color: #8a1220; border: 1px solid #f1b6bd; }
    .warn { background: #fff7e0; border: 1px solid #f0d27a; color: #6b4e00; padding: .9rem 1rem; border-radius: 8px; margin: 1rem 0; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    td { padding: .55rem .6rem; border-bottom: 1px solid #eee; vertical-align: top; }
    .st { width: 34px; font-size: 1.1rem; }
    .lbl { font-weight: 600; white-space: nowrap; }
    .det { color: #555; font-size: .92rem; }
    .ok-row .st { color: #18a957; } .ko-row .st { color: #d92b3f; } .na-row .st { color: #8a8a8a; }
    .na-row .lbl, .na-row .det { color: #777; }
  </style>
</head>
<body>
  <h1>Globo — Preflight requisiti server</h1>

  <div class="banner <?= $allOk ? 'ok' : 'ko' ?>">
    <?= $allOk ? '✓ Tutti i requisiti sono soddisfatti.' : '✗ Alcuni requisiti NON sono soddisfatti — vedi le righe rosse.' ?>
  </div>

  <div class="warn">⚠ Per sicurezza, CANCELLA questo file dal server subito dopo la verifica (espone versione PHP, estensioni e stato DB).</div>

  <table>
    <?php foreach ($checks as $c):
      // Terzo stato oltre a verde/rosso: "na" (non applicabile), grigio. Non e' un
      // problema da risolvere, ma nemmeno una verifica superata: dirlo com'e' evita
      // sia il falso allarme sia il falso via libera.
      $stato = $c[3] ?? ($c[0] ? 'ok' : 'ko');
      $simbolo = ['ok' => '✓', 'ko' => '✗', 'na' => '–'][$stato];
    ?>
    <tr class="<?= $stato ?>-row">
      <td class="st"><?= $simbolo ?></td>
      <td class="lbl"><?= htmlspecialchars($c[1]) ?></td>
      <td class="det"><?= htmlspecialchars($c[2]) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p class="det">PHP: <?= htmlspecialchars(PHP_VERSION) ?> — SAPI: <?= htmlspecialchars(PHP_SAPI) ?></p>
</body>
</html>
