<?php
require("varie/common_1.php");
globo_richiedi_accesso(defined('MOD_ADHOC_MCP') && MOD_ADHOC_MCP && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_ADHOC_MCP);

// CUSTOM-MCP: Fase B (redesign UI), 2026-07-10. Non serve la connessione DB
// (l'originale la apriva comunque per coerenza col resto del modulo, ma
// questa pagina non esegue query: qui la si omette, non serve nemmeno per
// coerenza essendo l'ultima del modulo a passare alla Fase B).
require("varie/head.php");
?>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuova categoria servizio</h1>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="adhoc_mcp_catserv_lista.php">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Elenco categorie
        </a>
      </div>
    </div>

    <form name="globo" action="adhoc_mcp_catserv_ins_2.php" method="post">
      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Nuova categoria servizio</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">
            <div class="form__row">
              <div class="form__label"><span class="form__label-name">Nome</span></div>
              <div class="form__control"><input type="text" class="input" maxlength="100" name="nome" value="" required /></div>
            </div>
          </div>
        </div>
        <div class="card__foot">
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>
    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
