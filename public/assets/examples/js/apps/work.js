(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/apps/work", [], factory);
  } else if (typeof exports !== "undefined") {
    factory();
  } else {
    var mod = {
      exports: {}
    };
    factory();
    global.appsWork = mod.exports;
  }
})(this, function () {
  "use strict";

  $(document).ready(function () {
    AppWork.run();
  });
});