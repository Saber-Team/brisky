!function(t){"use strict"}(this);
__d("text.t",function(t,i,s){s.exports="Here is brisk demo!"});
kerneljs.exec("app",function(t,e,n){var o=t("text.t");document.getElementById("text").textContent=o,document.getElementById("invoke").addEventListener("click",function(){t.async(["hello.t"],function(t){alert(t)})},!1)});
__d("hello.t",function(t,e,o){o.exports="Hello AceMood!"});
