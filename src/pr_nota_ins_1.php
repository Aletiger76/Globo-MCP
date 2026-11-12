<?php require("varie/common_1.php");
  globo_richiedi_accesso(defined('MOD_PROGETTI') && MOD_PROGETTI && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_PROGETTI);

  $id_progetto = $_POST["id_progetto"] ?? $_GET["id_progetto"] ?? '';

  $query_utenti = "SELECT nome_utente FROM utente WHERE livello > 0 ORDER BY nome_utente";
  $res_utenti = $db->query($query_utenti)->fetchAll();

  $query_utenti_liv_1 = "SELECT nome_utente FROM utente WHERE livello = 1 ORDER BY nome_utente";
  $res_utenti_liv_1 = $db->query($query_utenti_liv_1)->fetchAll();
  $num_utenti_liv_1 = count($res_utenti_liv_1);

  $stmtProg = $db->prepare("SELECT * FROM progetto WHERE id = ?");
  $stmtProg->execute([$id_progetto]);
  $res = $stmtProg->fetchAll();
  $nome_progetto = $res[0]["nome"];
  $descrizione_progetto = $res[0]["descrizione"];

  $data_oggi = date("Y-m-d");

  $query_tmp = "SELECT nome_utente FROM utente WHERE livello > 0 AND email <> ''";
  $res_tmp = $db->query($query_tmp)->fetchAll();
  $num_tmp = count($res_tmp);

  $has_calendario = MOD_CALENDARIO && $_SESSION["ksglobo_livello_utente"] <= LIV_MOD_CALENDARIO;
  require("varie/head.php");
?>
<script>
function abilitaDisabilitaScadenza() {
  var inp = document.globo.data_scadenza;
  if (document.globo.flag_scadenza.value == 0) {
    inp.value = ""; inp.disabled = true; inp.required = false;
    document.globo.responsabile_scadenza.selectedIndex = 0;
    document.globo.responsabile_scadenza.disabled = true;
    document.getElementById("scadenza_obb_1").style.visibility = "hidden";
    document.getElementById("scadenza_obb_2").style.visibility = "hidden";
  } else {
    inp.disabled = false; inp.required = true;
    document.globo.responsabile_scadenza.disabled = false;
    document.getElementById("scadenza_obb_1").style.visibility = "visible";
    document.getElementById("scadenza_obb_2").style.visibility = "visible";
  }
}
function abilitaDisabilitaImpegno() {
  var dimp = document.globo.data_impegno;
  var ddesc = document.globo.descrizione_impegno;
  if (document.globo.flag_impegno.value == 0) {
    dimp.value = ""; dimp.disabled = true; dimp.required = false;
    ddesc.value = ""; ddesc.disabled = true; ddesc.required = false;
    document.globo.ora_impegno.selectedIndex = 0; document.globo.ora_impegno.disabled = true;
    document.globo.durata_impegno.selectedIndex = 0; document.globo.durata_impegno.disabled = true;
    document.globo.responsabile_impegno.selectedIndex = 0; document.globo.responsabile_impegno.disabled = true;
    document.globo.flag_notifica_impegno.selectedIndex = 0; document.globo.flag_notifica_impegno.disabled = true;
    ["impegno_obb_1","impegno_obb_2","impegno_obb_3","impegno_obb_4","impegno_obb_5"].forEach(function(id){ document.getElementById(id).style.visibility="hidden"; });
  } else {
    dimp.disabled = false; dimp.required = true;
    ddesc.disabled = false; ddesc.required = true;
    document.globo.ora_impegno.disabled = false;
    document.globo.durata_impegno.disabled = false;
    document.globo.responsabile_impegno.disabled = false;
    document.globo.flag_notifica_impegno.disabled = false;
    ["impegno_obb_1","impegno_obb_2","impegno_obb_3","impegno_obb_4","impegno_obb_5"].forEach(function(id){ document.getElementById(id).style.visibility="visible"; });
  }
}
// Stato iniziale applicato dopo l'init di Flatpickr (footer), così gli input
// data si agganciano da abilitati e restano apribili una volta riattivati.
document.addEventListener("DOMContentLoaded", function() {
  abilitaDisabilitaScadenza();
  abilitaDisabilitaImpegno();
});
</script>
<div class="shell">
  <?php require("varie/header.php"); ?>
  <main><div class="page">

    <div class="page__head">
      <div class="page__title-block">
        <h1 class="page__title">Nuova nota progetto</h1>
        <p class="page__sub"><?= $nome_progetto ?></p>
      </div>
      <div class="page__actions">
        <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">
          <svg class="ic-stroke" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          Visualizza progetto
        </a>
      </div>
    </div>

    <form name="globo" action="pr_nota_ins_2.php" method="post">
      <input type="hidden" name="id_progetto" value="<?= (int)$id_progetto ?>" />

      <div class="card">
        <div class="card__head">
          <span class="card__head-eyebrow">Dati nota</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
            <div class="form__row">
              <div class="form__label">Visibilità</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="visibilita">
                    <option value="1">amministratori</option>
                    <option value="2" selected>tutti</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="visibilita" value="2" />
            <?php endif; ?>

            <div class="form__row">
              <div class="form__label">Nome progetto</div>
              <div class="form__control"><?= $nome_progetto ?></div>
            </div>

            <div class="form__row">
              <div class="form__label">Descrizione progetto</div>
              <div class="form__control"><?= $descrizione_progetto /* HTML gia' codificato dal DB: grezzo, il browser rende br/entita' (htmlspecialchars raddoppierebbe -> &#39;) */ ?></div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_creazione"
                  value="<?= globo_converti_data_in_formato_ita($data_oggi) ?>"
                  data-input autocomplete="off" required />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Nota <span class="form__hint form__hint--inline">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_nota" class="textarea" rows="10" required></textarea>
              </div>
            </div>

            <?php if ($num_tmp > 0): ?>
            <div class="form__row">
              <div class="form__label">Invia notifica</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifica_nota">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="flag_notifica_nota" value="0" />
            <?php endif; ?>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
        <?php if (!$has_calendario): ?>
        <div class="card__foot">
          <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($has_calendario): ?>

      <div class="card" style="margin-top:16px">
        <div class="card__head">
          <span class="card__head-eyebrow">Scadenza</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <div class="form__row">
              <div class="form__label">Inserisci scadenza</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_scadenza" onchange="abilitaDisabilitaScadenza()">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data <span id="scadenza_obb_1" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_scadenza" value=""
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Responsabile <span id="scadenza_obb_2" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="responsabile_scadenza" disabled>
                    <option value="tutti" selected>tutti</option>
                    <?php foreach ($res_utenti as $r_utenti): ?>
                    <option value="<?= htmlspecialchars($r_utenti['nome_utente']) ?>"><?= htmlspecialchars($r_utenti['nome_utente']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
      </div>

      <div class="card" style="margin-top:16px">
        <div class="card__head">
          <span class="card__head-eyebrow">Impegno</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="form">

            <div class="form__row">
              <div class="form__label">Inserisci impegno</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_impegno" onchange="abilitaDisabilitaImpegno()">
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Data <span id="impegno_obb_1" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control" data-flatpickr>
                <input type="text" class="input" maxlength="10" name="data_impegno" value=""
                  data-input autocomplete="off" />
                <a class="icon-btn" data-toggle title="Apri calendario">
                  <svg class="ic-stroke" aria-hidden="true"><use href="#i-calendar"/></svg>
                </a>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Ora <span id="impegno_obb_2" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="ora_impegno" disabled>
                    <?php
                    $ora_dec = CAL_ORA_INIZIO_GIO;
                    while ($ora_dec <= CAL_ORA_FINE_GIO):
                      $ora_vis = globo_converti_ora_da_dec($ora_dec);
                    ?>
                    <option value="<?= htmlspecialchars($ora_vis) ?>"><?= htmlspecialchars($ora_vis) ?></option>
                    <?php $ora_dec += 0.5; endwhile; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Durata <span id="impegno_obb_3" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="durata_impegno" disabled>
                    <?php
                    $durata_dec = 0.5;
                    while ($durata_dec <= CAL_MAX_DURATA_IMP):
                      $durata_vis = globo_converti_ora_da_dec($durata_dec);
                    ?>
                    <option value="<?= htmlspecialchars($durata_vis) ?>"><?= htmlspecialchars($durata_vis) ?></option>
                    <?php $durata_dec += 0.5; endwhile; ?>
                    <option value="24:00">tutto il giorno</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Descrizione <span id="impegno_obb_4" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control form__control--multi">
                <textarea name="descrizione_impegno" class="textarea" rows="6" disabled></textarea>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">
                <span class="form__label-name">Responsabile <span id="impegno_obb_5" class="form__hint form__hint--inline" style="visibility:hidden">obb</span></span>
              </div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="responsabile_impegno" disabled>
                    <option value="tutti" selected>tutti</option>
                    <?php if ($_SESSION["ksglobo_livello_utente"] <= 1): ?>
                    <option value="amm">amministratori (<?= $num_utenti_liv_1 ?>)</option>
                    <?php endif; ?>
                    <?php foreach ($res_utenti as $r_utenti): ?>
                    <option value="<?= htmlspecialchars($r_utenti['nome_utente']) ?>"><?= htmlspecialchars($r_utenti['nome_utente']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form__row">
              <div class="form__label">Invia notifica</div>
              <div class="form__control">
                <div class="select-wrap">
                  <select name="flag_notifica_impegno" disabled>
                    <option value="0" selected>no</option>
                    <option value="1">sì</option>
                  </select>
                </div>
              </div>
            </div>

          </div><!-- /.form -->
        </div><!-- /.card__body -->
        <div class="card__foot">
          <a class="btn btn--ghost" href="prog_vis.php?id=<?= (int)$id_progetto ?>">Annulla</a>
          <button type="submit" class="btn btn--primary">Inserisci</button>
        </div>
      </div>

      <?php else: ?>
      <input type="hidden" name="flag_scadenza" value="0" />
      <input type="hidden" name="flag_impegno" value="0" />
      <?php endif; ?>

    </form>

  <?php require("varie/footer.php"); ?>
<?php require("varie/common_2.php"); ?>
