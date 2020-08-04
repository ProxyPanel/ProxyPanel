(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/map-point", [], factory);
  } else if (typeof exports !== "undefined") {
    factory();
  } else {
    var mod = {
      exports: {}
    };
    factory();
    global.pagesMapPoint = mod.exports;
  }
})(this, function () {
  "use strict";

  var LocsA = [{
    lat: 45.9,
    lon: 10.9,
    title: 'Title A1',
    html: '<h3>Content A1</h3>',
    icon: 'http://maps.google.com/mapfiles/markerA.png',
    animation: google.maps.Animation.DROP
  }, {
    lat: 44.8,
    lon: 1.7,
    title: 'Title B1',
    html: '<h3>Content B1</h3>',
    icon: 'http://maps.google.com/mapfiles/markerB.png',
    show_infowindow: false
  }, {
    lat: 51.5,
    lon: -1.1,
    title: 'Title C1',
    html: ['<h3>Content C1</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8,
    icon: 'http://maps.google.com/mapfiles/markerC.png'
  }];
});