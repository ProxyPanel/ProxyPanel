(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/map-google", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesMapGoogle = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function () {
    (0, _Site.run)();
    var map = new GMaps({
      el: '#gmap',
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
  });
});