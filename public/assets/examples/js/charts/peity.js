(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/charts/peity", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.chartsPeity = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Peity Default
  // ---------------------

  (function () {
    /* dynamic example */
    var dynamicChart = (0, _jquery.default)("#examplePeityDynamic").peity("line", {
      width: 64,
      fill: [Config.colors("primary", 200)],
      stroke: Config.colors("primary", 500),
      height: 22
    });
    setInterval(function () {
      var random = Math.round(Math.random() * 10);
      var values = dynamicChart.text().split(",");
      values.shift();
      values.push(random);
      dynamicChart.text(values.join(",")).change();
    }, 1000);
  })(); // Example Peity Red
  // -------------------


  (function () {
    /* dynamic example */
    var dynamicRedChart = (0, _jquery.default)("#examplePeityDynamicRed").peity("line", {
      width: 64,
      fill: [Config.colors("red", 200)],
      stroke: Config.colors("red", 500),
      height: 22
    });
    setInterval(function () {
      var random = Math.round(Math.random() * 10);
      var values = dynamicRedChart.text().split(",");
      values.shift();
      values.push(random);
      dynamicRedChart.text(values.join(",")).change();
    }, 1000);
  })(); // Example Peity Green
  // -------------------


  (function () {
    /* dynamic example */
    var dynamicGreenChart = (0, _jquery.default)("#examplePeityDynamicGreen").peity("line", {
      width: 64,
      fill: [Config.colors("green", 200)],
      stroke: Config.colors("green", 500),
      height: 22
    });
    setInterval(function () {
      var random = Math.round(Math.random() * 10);
      var values = dynamicGreenChart.text().split(",");
      values.shift();
      values.push(random);
      dynamicGreenChart.text(values.join(",")).change();
    }, 1000);
  })(); // Example Peity Orange
  // --------------------


  (function () {
    /* dynamic example */
    var dynamicOrangeChart = (0, _jquery.default)("#examplePeityDynamicOrange").peity("line", {
      width: 64,
      fill: [Config.colors("orange", 200)],
      stroke: Config.colors("orange", 500),
      height: 22
    });
    setInterval(function () {
      var random = Math.round(Math.random() * 10);
      var values = dynamicOrangeChart.text().split(",");
      values.shift();
      values.push(random);
      dynamicOrangeChart.text(values.join(",")).change();
    }, 1000);
  })();
});