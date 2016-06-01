!function(t){"use strict";var n=this.Quickling={};n.fetch=function(t){var n=new XMLHttpRequest;n.open("GET",t,!0),n.addEventListener("load",function(t){}),n.send(null)},n.onLoad=function(){}}(this);
kerneljs.exec("+aIF5",function(e,t,n){"use strict";var o=t.exec=function(){alert(1e3)};o()});
__d("text.t",function(o,e,t){t.exports="Here is brisk demo!"});
kerneljs.exec("app",function(e,t,n){var c=e("text.t");document.getElementById("text").textContent=c,document.getElementById("invoke").addEventListener("click",function(){e.async(["hello.t"],function(e){alert(e)})},!1),document.getElementById("redirect").addEventListener("click",function(){return Quickling.fetch(),!1},!1)});
__d("hello.t",function(o,e,l){l.exports="Hello AceMood!"});
