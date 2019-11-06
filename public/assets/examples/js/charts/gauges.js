(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/charts/gauges", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.chartsGauges = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Gauge Dynamic
  // ---------------------

  (0, _jquery.default)(document).ready(function ($$$1) {
    var dynamicGauge = $$$1("#exampleDynamicGauge").data('gauge');
    setInterval(function () {
      var random = Math.round(Math.random() * 1000);
      var options = {
        strokeColor: Config.colors("primary", 500)
      };

      if (random > 700) {
        options.strokeColor = Config.colors("pink", 500);
      } else if (random < 300) {
        options.strokeColor = Config.colors("green", 500);
      }

      dynamicGauge.setOptions(options).set(random);
    }, 1500);
  }); // Example Donut Dynamic
  // ---------------------

  (0, _jquery.default)(document).ready(function ($$$1) {
    var dynamicDonut = $$$1("#exampleDynamicDonut").data('donut');
    setInterval(function () {
      var random = Math.round(Math.random() * 1000);
      var options = {
        strokeColor: Config.colors("primary", 500)
      };

      if (random > 700) {
        options.strokeColor = Config.colors("pink", 500);
      } else if (random < 300) {
        options.strokeColor = Config.colors("green", 500);
      }

      dynamicDonut.setOptions(options).set(random);
    }, 1500);
  });
});