var theImages = new Array();

theImages[0] = "shadowbox/shadowbox_3.0.3/close.png";
theImages[1] = "shadowbox/loading/loading.gif";

var i = 0;
var p = theImages.length;
var preBuffer = new Array();

for (i = 0; i < p; i++) {
  preBuffer[i] = new Image();
  preBuffer[i].src = theImages[i];
}