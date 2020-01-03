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
  var LocsAv2 = [{
    lat: 45.9,
    lon: 10.9,
    title: 'Zone A1',
    html: '<h3>Content A1</h3>',
    type: 'circle',
    circle_options: {
      radius: 200000
    },
    draggable: true
  }, {
    lat: 44.8,
    lon: 1.7,
    title: 'Draggable',
    html: '<h3>Content B1</h3>',
    show_infowindow: false,
    visible: true,
    draggable: true
  }, {
    lat: 51.5,
    lon: -1.1,
    title: 'Title C1',
    html: ['<h3>Content C1</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8,
    visible: true
  }];
  var LocsB = [{
    lat: 52.1,
    lon: 11.3,
    title: 'Title A2',
    html: ['<h3>Content A2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8
  }, {
    lat: 51.2,
    lon: 22.2,
    title: 'Title B2',
    html: ['<h3>Content B2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8
  }, {
    lat: 49.4,
    lon: 35.9,
    title: 'Title C2',
    html: ['<h3>Content C2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 4
  }, {
    lat: 47.8,
    lon: 15.6,
    title: 'Title D2',
    html: ['<h3>Content D2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 6
  }];
  var LocsBv2 = [{
    lat: 52.1,
    lon: 11.3,
    title: 'Title A2',
    html: ['<h3>Content A2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8
  }, {
    lat: 51.2,
    lon: 22.2,
    title: 'Title B2',
    html: ['<h3>Content B2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 8,
    type: 'circle',
    circle_options: {
      radius: 100000
    }
  }, {
    lat: 49.4,
    lon: 35.9,
    title: 'Title C2',
    html: ['<h3>Content C2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 4
  }, {
    lat: 47.8,
    lon: 15.6,
    title: 'Title D2',
    html: ['<h3>Content D2</h3>', '<p>Lorem Ipsum..</p>'].join(''),
    zoom: 6
  }];
  var LocsAB = LocsA.concat(LocsB);
});