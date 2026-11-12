<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_CONTATTI') && MOD_CONTATTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CONTATTI);

  $id_contatto = $_POST["id_contatto"] ?? '';

  $stmt = $db->prepare("DELETE FROM contatto WHERE id = ?");
  $db->beginTransaction();
  $stmt->execute([$id_contatto]);
  $num = $stmt->rowCount();
  $db->commit();

  if ($num == 1) {
    globo_log_attivita($db, 'elimina', 'contatto', $id_contatto, '');
    $stmtNl = $db->prepare("DELETE FROM nl_lista_contatto WHERE id_contatto = ?");
    $db->beginTransaction();
    $stmtNl->execute([$id_contatto]);
    $db->commit();
    $_SESSION['flash'] = ['type' => 'ok', 'text' => 'Contatto eliminato.'];
  } else {
    $_SESSION['flash'] = ['type' => 'err', 'text' => 'Contatto non eliminato.'];
  }

  $has_p = isset($_GET["p"]) && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI;
  header("Location: cont_lista.php" . ($has_p ? '?p=' . (int)$_GET['p'] : ''));
  exit;
