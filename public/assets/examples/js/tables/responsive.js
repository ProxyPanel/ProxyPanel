(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/responsive", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesResponsive = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Set tablesaw config
  // -------------------

  (function () {
    window.TablesawConfig = {
      i18n: {
        modeStack: 'Stack',
        modeSwipe: 'Swipe',
        modeToggle: 'Toggle',
        modeSwitchColumnsAbbreviated: 'Cols',
        modeSwitchColumns: 'Columns',
        columnToggleButton: 'Columns',
        columnToggleError: 'No eligible columns.',
        sort: 'Sort',
        swipePreviousColumn: '',
        swipeNextColumn: ''
      }
    };
  })();
});