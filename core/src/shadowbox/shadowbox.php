<script language="javascript" type="text/javascript" src="shadowbox/preload.js"></script>
<link rel="stylesheet" type="text/css" href="shadowbox/shadowbox_3.0.3/shadowbox.css">
<script language="javascript" type="text/javascript" src="shadowbox/shadowbox_3.0.3/shadowbox.js"></script>
<script language="javascript" type="text/javascript">
  Shadowbox.init({
    modal: true,
    initialHeight: 80,
    initialWidth: 168,
  });
  
  function openLoadingShadowbox() {    
    Shadowbox.open({
      content: "shadowbox/loading/loading.php",
      player: "iframe",
      title: "",
      height: 80,
      width: 168
    });
  }
</script>