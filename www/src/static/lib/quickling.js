/**
 * @provides quickling
 */

;(function(global) {

  'use strict';

  var Quickling = {};

  Quickling.delegate = function(selector) {

  };

  Quickling.fetch = function(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        debugger;
        console.log(xhr.responseText);
      }
    };
    xhr.send(null);
  };

  Quickling.register = function(pattern, handler) {

  };

  global.Quickling = Quickling;

})(this);