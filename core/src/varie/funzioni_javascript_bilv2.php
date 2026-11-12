<script language="javascript" type="text/javascript">
  <!--
  function arrotonda(num, dec) {
    var res = Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
    res = res.toFixed(2);
    return res;
  }
  function aggiornaTot() {
    var totale = 0;
    for (i = 0; i < <?php echo BILV2_NUM_RIGHE; ?>; i++) {
      imponibile = parseFloat(document.getElementById("imponibile_" + i).value);
      iva = parseFloat(document.getElementById("iva_" + i).value);
      if (!isNaN(imponibile) && !isNaN(iva)) {
        totale = totale + imponibile + imponibile * (iva / 100);
      }
    }
    document.getElementById("totale").innerHTML = arrotonda(totale, 2) + " iva inclusa";
    <?php if ($tipo_bilancio == "entrata" && $num_ag > 0) { ?> 
      aggiornaTotProvvigione(); 
    <?php } ?>
  }
  function modificaFlagPagamento() {
    var flag_pagamento = document.globo.flag_pagamento.value;
    if (flag_pagamento == 2) document.getElementById("credito_debito_attuale").style.visibility = "visible";
    else document.getElementById("credito_debito_attuale").style.visibility = "hidden";
  }
  function modificaAgente() {
    if (document.getElementById("id_bilanciov2_agente").value == 0) {
      document.getElementById("perc_provvigione").value = 0;
      document.getElementById("totale_provvigione").innerHTML = "";
      document.getElementById("perc_provvigione").style.visibility = "hidden";
      document.getElementById("totale_provvigione").style.visibility = "hidden";
    }
    else {
      document.getElementById("perc_provvigione").style.visibility = "visible";
      document.getElementById("totale_provvigione").style.visibility = "visible";
      aggiornaTotProvvigione();
    }
  }
  function aggiornaTotProvvigione() {
    var id_bilanciov2_agente = document.getElementById("id_bilanciov2_agente").value;
    var perc_provvigione = parseFloat(document.getElementById("perc_provvigione").value);

    var totale_provvigione = 0;
    
    if (id_bilanciov2_agente == 0 || isNaN(perc_provvigione) || perc_provvigione <= 0) {
      document.getElementById("perc_provvigione").value = 0;
      document.getElementById("totale_provvigione").innerHTML = "";
    }
    else {
      for (i = 0; i < <?php echo BILV2_NUM_RIGHE; ?>; i++) {
        imponibile = parseFloat(document.getElementById("imponibile_" + i).value);
        if (!isNaN(imponibile)) {
          totale_provvigione = totale_provvigione + imponibile * (perc_provvigione / 100);
        }
      }
      document.getElementById("totale_provvigione").innerHTML = arrotonda(totale_provvigione, 2) + " iva esclusa";;
      document.getElementById("totale_provvigione").style.visibility = "visible";
    }
  }  
  function controllaForm() {
    var re = / /g;
    var data = document.globo.data.value.replace(re, "");
    var cliente_fornitore = document.globo.cliente_fornitore.value.replace(re, "");
    if (data == "") { alert("compilare il campo \"data\""); document.globo.data.focus(); return false; }
    <?php if ($tipo_bilancio == "entrata") { ?>
      if (cliente_fornitore == "") { alert("compilare il campo \"cliente\""); document.globo.cliente_fornitore.focus(); return false; }
    <?php } else { ?>
      if (cliente_fornitore == "") { alert("compilare il campo \"fornitore\""); document.globo.cliente_fornitore.focus(); return false; }
    <?php } ?>
    <?php if ($tipo_bilancio == "entrata" && $num_tipocli > 1) { ?>
      if (document.globo.id_bilanciov2_tipo_cliente.value == -1) { alert("compilare il campo \"tipo\""); document.globo.id_bilanciov2_tipo_cliente.focus(); return false; }
    <?php } ?>
    <?php if ($tipo_bilancio == "entrata") { ?>
      var fattura = document.globo.fattura.value;
      // if (isNaN(fattura)) { alert("compilare il campo \"fattura\" con un valore numerico"); document.globo.fattura.focus(); return false; }
    <?php } ?>
    var ok = false;
    <?php for ($i = 0; $i < BILV2_NUM_RIGHE; $i++) { ?>
      var descrizione_<?php echo $i; ?> = document.globo.descrizione_<?php echo $i; ?>.value.replace(re, "");
      if (descrizione_<?php echo $i; ?> != "") {
        ok = true;
        <?php if ($num_cat > 1) { ?>
          if (document.globo.id_bilanciov2_categoria_<?php echo $i; ?>.value == -1) { alert("compilare il campo \"categoria\""); document.globo.id_bilanciov2_categoria_<?php echo $i; ?>.focus(); return false; }
        <?php } ?>
        if (document.globo.flag_canone_<?php echo $i; ?>.value == -1) { alert("compilare il campo \"canone\""); document.globo.flag_canone_<?php echo $i; ?>.focus(); return false; }
        if (document.globo.imponibile_<?php echo $i; ?>.value == "") { alert("compilare il campo \"imponibile\""); document.globo.imponibile_<?php echo $i; ?>.focus(); return false; }
        if (isNaN(document.globo.imponibile_<?php echo $i; ?>.value)) { alert("inserire nel campo \"imponibile\" un valore numerico"); document.globo.imponibile_<?php echo $i; ?>.focus(); return false; }
        if (document.globo.iva_<?php echo $i; ?>.value == "") { alert("compilare il campo \"iva\""); document.globo.iva_<?php echo $i; ?>.focus(); return false; }
        if (isNaN(document.globo.iva_<?php echo $i; ?>.value)) { alert("inserire nel campo \"iva\" un valore numerico"); document.globo.iva_<?php echo $i; ?>.focus(); return false; }
      }
    <?php } ?>
    if (!ok) { alert("compilare almeno una riga"); document.globo.descrizione_0.focus(); return false; }
  }
  function confermaEliminazione() {
    return confirm("confermi eliminazione?");
  }
  //-->
</script>