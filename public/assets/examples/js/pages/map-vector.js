(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/map-vector", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesMapVector = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function () {
    (0, _Site.run)();
    var defaults = Plugin.getDefaults('vectorMap');

    var options = _jquery.default.extend({}, defaults, {
      markers: [{
        latLng: [1.3, 103.8],
        name: '940 Visits'
      }, {
        latLng: [51.511214, -0.119824],
        name: '530 Visits'
      }, {
        latLng: [40.714353, -74.005973],
        name: '340 Visits'
      }, {
        latLng: [-22.913395, -43.200710],
        name: '1,800 Visits'
      }]
    }, true);

    (0, _jquery.default)('#world-map').vectorMap(options);
  });
});