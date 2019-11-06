(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/faq", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesFaq = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function () {
    (0, _Site.run)();

    if ((0, _jquery.default)('.faq-list').length) {
      (0, _jquery.default)('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        (0, _jquery.default)(e.target).addClass('active').siblings().removeClass('active');
      });
    }
  });
});