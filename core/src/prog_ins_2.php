<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $visibilita            = $_POST["visibilita"]            ?? '';
  $data_creazione        = globo_converti_data_in_formato_mysql($_POST["data_creazione"] ?? '');
  $nome_progetto         = globo_converti_in_html($_POST["nome_progetto"]         ?? '');
  $descrizione_progetto  = globo_converti_in_html($_POST["descrizione_progetto"]  ?? '');
  $id_progetto_template  = isset($_POST["id_progetto_template"])  ? $_POST["id_progetto_template"]  : 0;
  $responsabile_template = isset($_POST["responsabile_template"]) ? $_POST["responsabile_template"] : $_SESSION["ksglobo_nome_utente"];
  $id_progetto_procedura = isset($_POST["id_progetto_procedura"]) ? $_POST["id_progetto_procedura"] : 0;

  if (TUTTO_MINUSCOLO) {
    $nome_progetto        = strtolower($nome_progetto);
    $descrizione_progetto = strtolower($descrizione_progetto);
  }

  $stmtInsProg = $db->prepare("INSERT INTO progetto (id_progetto_procedura, visibilita, data_creazione, flag_archivio, data_archiviazione, nome, descrizione) VALUES (?, ?, ?, '0', '0000-00-00', ?, ?)");
  $db->beginTransaction();
  $stmtInsProg->execute([$id_progetto_procedura, $visibilita, $data_creazione, $nome_progetto, $descrizione_progetto]);
  $num = $stmtInsProg->rowCount();
  $id_progetto = $db->lastInsertId();
  $db->commit();

  if ($num == 1) {
    $text = 'Progetto inserito.';
    globo_log_attivita($db, 'crea', 'progetto', $id_progetto, $nome_progetto);
    $num_2 = 0; $num_4 = 0;

    if ($id_progetto_template > 0) {
      $stmtSelStep = $db->prepare("SELECT * FROM progetto_template_step WHERE id_progetto_template = ? ORDER BY giorni_scadenza, id");
      $stmtSelStep->execute([$id_progetto_template]);
      $res_1 = $stmtSelStep->fetchAll();

      $stmtInsNota = $db->prepare("INSERT INTO progetto_nota (id_progetto, tipo, visibilita, data_creazione, utente_creazione, descrizione, utente_ultima_modifica) VALUES (?, 'step', '2', ?, ?, ?, ?)");
      $stmtInsScad = $db->prepare("INSERT INTO scadenza (data, tipo, visibilita, id_bilancio, id_progetto, id_progetto_nota, descrizione, responsabile, utente_ultima_modifica) VALUES (?, 'progetto_step', '2', '0', ?, ?, ?, ?, ?)");

      foreach ($res_1 as $r_1) {
        $descrizione_step = $r_1["descrizione"];
        $giorni_scadenza  = $r_1["giorni_scadenza"];
        $db->beginTransaction();
        $stmtInsNota->execute([$id_progetto, $data_creazione, $_SESSION["ksglobo_nome_utente"], $descrizione_step, $_SESSION["ksglobo_nome_utente"]]);
        $num_2 += $stmtInsNota->rowCount();
        $id_nota = $db->lastInsertId();
        $db->commit();
        if ($giorni_scadenza >= 0) {
          $data_scadenza       = date("Y-m-d", (strtotime(date("Y-m-d")) + $giorni_scadenza * 86400));
          $descrizione_scadenza = "progetto: $nome_progetto<br />descrizione: $descrizione_progetto<br /><br />$descrizione_step";
          $db->beginTransaction();
          $stmtInsScad->execute([$data_scadenza, $id_progetto, $id_nota, $descrizione_scadenza, $responsabile_template, $_SESSION["ksglobo_nome_utente"]]);
          $num_4 += $stmtInsScad->rowCount();
          $db->commit();
        }
      }
    }

    if ($num_2 > 0) $text .= ' ' . ($num_2 == 1 ? '1 step inserito.' : "$num_2 step inseriti.");
    if ($num_4 > 0) $text .= ' ' . ($num_4 == 1 ? '1 scadenza inserita.' : "$num_4 scadenze inserite.");

    $_SESSION['flash'] = ['type' => 'ok', 'text' => $text];
    header("Location: prog_vis.php?id=" . (int)$id_progetto);
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Progetto non inserito.'];
    header("Location: prog_lista.php");
  }
  exit;
