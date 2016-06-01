__d("text.t",function(e,t,o){o.exports="Here is brisk demo!"});
kerneljs.exec("app",function(e,t,n){var c=e("text.t");document.getElementById("text").textContent=c,document.getElementById("invoke").addEventListener("click",function(){e.async(["hello.t"],function(e){alert(e)})},!1),document.getElementById("redirect").addEventListener("click",function(){return Quickling.fetch(),!1},!1)});
__d("hello.t",function(e,t,o){o.exports="Hello AceMood!"});
kerneljs.exec("+aIF5",function(e,c,n){"use strict";var t=c.exec=function(){alert(1e3)};t()});
