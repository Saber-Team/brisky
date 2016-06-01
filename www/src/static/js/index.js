/**
 * @entry
 * @provides app
 */

var text = require('./text');

document.getElementById('text').textContent = text;

document.getElementById('invoke').addEventListener('click', function () {

  require.async(['./hello'], function (hello) {
    alert(hello);
  });

}, false);

document.getElementById('redirect').addEventListener('click', function () {

  Quickling.fetch('/detail?pagelets=content&ajaxify=1');
  return false;

}, false);