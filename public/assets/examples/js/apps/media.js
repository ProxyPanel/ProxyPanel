(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/apps/media", ["jquery"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery);
    global.appsMedia = mod.exports;
  }
})(this, function (_jquery) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function () {
    AppMedia.run();
  });
});