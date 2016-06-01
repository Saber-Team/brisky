__d("text.t",function(e,t,n){n.exports="Here is brisk demo!"});
kerneljs.exec("app",function(e,t,n){var c=e("text.t");document.getElementById("text").textContent=c,document.getElementById("invoke").addEventListener("click",function(){e.async(["hello.t"],function(e){alert(e)})},!1),document.getElementById("redirect").addEventListener("click",function(){return Quickling.fetch(),!1},!1)});
__d("hello.t",function(n,e,t){t.exports="Hello AceMood!"});
kerneljs.exec("+aIF5",function(n,e,t){"use strict";var i=e.exec=function(){alert(1e3)};i()});
