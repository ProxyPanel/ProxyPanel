(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/advanced/maps-google", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.advancedMapsGoogle = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function () {
    (0, _Site.run)(); // Simple
    // ------------------

    (function () {
      var simpleMap = new GMaps({
        el: '#simpleGmap',
        zoom: 8,
        center: {
          lat: -34.397,
          lng: 150.644
        }
      });
    })(); // Custom
    // ------------------


    (function () {
      var map = new GMaps({
        el: '#customGmap',
        lat: -12.043333,
        lng: -77.028333,
        zoomControl: true,
        zoomControlOpt: {
          style: "SMALL",
          position: "TOP_LEFT"
        },
        panControl: true,
        streetViewControl: false,
        mapTypeControl: false,
        overviewMapControl: false
      });
      map.drawOverlay({
        lat: -12.043333,
        lng: -77.028333,
        content: '<i class="wb-map" style="font-size:40px;color:' + Config.colors("red", 500) + ';"></i>'
      });
      map.drawOverlay({
        lat: -12.05449279282314,
        lng: -77.04333,
        content: '<i class="wb-map" style="font-size:32px;color:' + Config.colors("primary", 500) + ';"></i>'
      });
      map.addStyle({
        styledMapName: "Styled Map",
        styles: Plugin.getDefaults('gmaps', 'styles'),
        mapTypeId: "map_style"
      });
      map.setStyle("map_style");
      var path = [[-12.044012922866312, -77.02470665341184], [-12.05449279282314, -77.03024273281858], [-12.055122327623378, -77.03039293652341], [-12.075917129727586, -77.02764635449216], [-12.07635776902266, -77.02792530422971], [-12.076819390363665, -77.02893381481931], [-12.088527520066453, -77.0241058385925], [-12.090814532191756, -77.02271108990476]];
      map.drawPolyline({
        path: path,
        strokeColor: '#131540',
        strokeOpacity: 0.6,
        strokeWeight: 6
      });
    })(); // Markers
    // ------------------


    (function () {
      var map = new GMaps({
        div: '#markersGmap',
        lat: -12.043333,
        lng: -77.028333
      });
      map.addMarker({
        lat: -12.043333,
        lng: -77.03,
        title: 'Lima',
        details: {
          database_id: 42,
          author: 'HPNeo'
        },
        click: function click(e) {
          if (console.log) console.log(e);
          alert('You clicked in this marker');
        }
      });
      map.addMarker({
        lat: -12.042,
        lng: -77.028333,
        title: 'Marker with InfoWindow',
        infoWindow: {
          content: '<p>You clicked in this marker</p>'
        }
      });
    })(); // Polylines
    // ------------------


    (function () {
      var map = new GMaps({
        div: '#polylinesGmap',
        lat: -12.043333,
        lng: -77.028333,
        click: function click(e) {
          console.log(e);
        }
      });
      var path = [[-12.044012922866312, -77.02470665341184], [-12.05449279282314, -77.03024273281858], [-12.055122327623378, -77.03039293652341], [-12.075917129727586, -77.02764635449216], [-12.07635776902266, -77.02792530422971], [-12.076819390363665, -77.02893381481931], [-12.088527520066453, -77.0241058385925], [-12.090814532191756, -77.02271108990476]];
      map.drawPolyline({
        path: path,
        strokeColor: '#131540',
        strokeOpacity: 0.6,
        strokeWeight: 6
      });
    })(); // Polygons
    // ------------------


    (function () {
      var map = new GMaps({
        div: '#polygonsGmap',
        lat: -12.043333,
        lng: -77.028333
      });
      var path = [[-12.040397656836609, -77.03373871559225], [-12.040248585302038, -77.03993927003302], [-12.050047116528843, -77.02448169303511], [-12.044804866577001, -77.02154422636042]];
      map.drawPolygon({
        paths: path,
        strokeColor: '#BBD8E9',
        strokeOpacity: 1,
        strokeWeight: 3,
        fillColor: '#BBD8E9',
        fillOpacity: 0.6
      });
    })(); // Fusion Tables layers
    // ------------------


    (function () {
      var infoWindow = new google.maps.InfoWindow({}),
          map = new GMaps({
        div: '#FTLGmap',
        zoom: 11,
        lat: 41.850033,
        lng: -87.6500523
      });
      map.loadFromFusionTables({
        query: {
          select: '\'Geocodable address\'',
          from: '1mZ53Z70NsChnBMm-qEYmSDOvLXgrreLTkQUvvg'
        },
        suppressInfoWindows: true,
        events: {
          click: function click(point) {
            infoWindow.setContent('You clicked here!');
            infoWindow.setPosition(point.latLng);
            infoWindow.open(map.map);
          }
        }
      });
    })(); // Panoramas
    // ------------------


    (function () {
      var panorama = GMaps.createPanorama({
        el: '#panoramasGmap',
        lat: 42.3455,
        lng: -71.0983
      });
    })(); // Satellite
    // ------------------


    (function () {
      var simpleMap = new GMaps({
        div: "#satelliteGmap",
        lat: 0,
        lng: 0,
        zoom: 1,
        scrollwheel: !1
      }).setMapTypeId(google.maps.MapTypeId.SATELLITE);
    })();
  });
});