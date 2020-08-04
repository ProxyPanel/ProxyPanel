(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/widgets/weather", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.widgetsWeather = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  // Widget Weather
  (0, _jquery.default)(document).ready(function () {
    (0, _Site.run)();
  }); // Example1 sunny
  // -----------------------------

  (function () {
    var ex1_sunny = new Skycons({
      "color": "white"
    });
    ex1_sunny.set(document.getElementById("ex4-rain"), "rain");
    ex1_sunny.play();
  })(); // Example5
  // -----------------------------


  (function () {
    var ex5 = new Skycons({
      "color": "white"
    }),
        ex5_list = ["ex5-partly-cloudy", "ex5-sunny", "ex5-cloudy", "ex5-rain"],
        ex5_type = ["partly-cloudy-day", "clear-day", "cloudy", "rain"],
        ex5_i;

    for (ex5_i = ex5_list.length; ex5_i--;) {
      ex5.set(ex5_list[ex5_i], ex5_type[ex5_i]);
    }

    ex5.play();
  })(); // Example6 Snow
  // -----------------------------


  (function () {
    var ex6_snow = new Skycons({
      "color": "white"
    });
    ex6_snow.set(document.getElementById("ex6-snow"), "snow");
    ex6_snow.play();
  })(); // Example7 Sleet
  // -----------------------------


  (function () {
    var ex7_sleet = new Skycons({
      "color": "#212121"
    });
    ex7_sleet.set(document.getElementById("ex7-sleet"), "sleet");
    ex7_sleet.play();
  })();
});