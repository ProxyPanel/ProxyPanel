(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/layouts/panel-transition", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.layoutsPanelTransition = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
    var $example = $$$1('#exampleTransition');
    $$$1(document).on('click.panel.transition', '[data-type]', function () {
      var type = $$$1(this).data('type');
      $example.data('animateList').run(type);
    });
    $$$1(document).on('close.uikit.panel', '[class*=blocks-] > li > .panel', function () {
      $$$1(this).parent().hide();
    });
  });
});