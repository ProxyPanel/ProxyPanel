(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/dashboard/v2", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.dashboardV2 = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)(); // Widget Linearea
    // ---------------

    (function () {
      var linearea = new Chartist.Line('#widgetLinearea .ct-chart', {
        labels: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
        series: [[0, 2.5, 2, 2.8, 2.6, 3.8, 0], [0, 1.4, 0.5, 2, 1.2, 0.9, 0]]
      }, {
        low: 0,
        showArea: true,
        showPoint: false,
        showLine: false,
        fullWidth: true,
        chartPadding: {
          top: 0,
          right: 10,
          bottom: 0,
          left: 0
        },
        axisX: {
          showGrid: false,
          labelOffset: {
            x: -14,
            y: 0
          }
        },
        axisY: {
          labelOffset: {
            x: -10,
            y: 0
          },
          labelInterpolationFnc: function labelInterpolationFnc(num) {
            return num % 1 === 0 ? num : false;
          }
        }
      });
    })(); // Widget Gmap
    // -----------


    (function () {
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
      map.addStyle({
        styledMapName: "Styled Map",
        styles: Plugin.getDefaults('gmaps').styles,
        mapTypeId: "map_style"
      });
      map.setStyle("map_style");
    })();
  });
});