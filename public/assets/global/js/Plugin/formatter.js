(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/formatter", [], factory);
  } else if (typeof exports !== "undefined") {
    factory();
  } else {
    var mod = {
      exports: {}
    };
    factory();
    global.PluginFormatter = mod.exports;
  }
})(this, function () {
  "use strict";
});