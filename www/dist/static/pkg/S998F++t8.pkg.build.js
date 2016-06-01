!function(e){"use strict";var t={};t.fetch=function(e){var t=new XMLHttpRequest;t.open("GET",e,!0),t.addEventListener("load",function(e){}),t.send(null)},t.onLoad=function(){},e.Quickling=t}(this);
kerneljs.exec("+aIF5",function(e,t,o){"use strict";var n=t.exec=function(){alert(1e3)};n()});
__d("text.t",function(o,e,t){t.exports="Here is brisk demo!"});
kerneljs.exec("app",function(e,t,n){var c=e("text.t");document.getElementById("text").textContent=c,document.getElementById("invoke").addEventListener("click",function(){e.async(["hello.t"],function(e){alert(e)})},!1),document.getElementById("redirect").addEventListener("click",function(){return Quickling.fetch(),!1},!1)});
__d("hello.t",function(o,e,l){l.exports="Hello AceMood!"});
