(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/tabs", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginTabs = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tabs';

  var Tabs =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Tabs, _Plugin);

    function Tabs() {
      babelHelpers.classCallCheck(this, Tabs);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tabs).apply(this, arguments));
    }

    babelHelpers.createClass(Tabs, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (this.$el.find('.nav-tabs-horizontal') && _jquery.default.fn.responsiveHorizontalTabs) {
          this.type = 'horizontal';
          this.$el.responsiveHorizontalTabs();
        } else if (this.$el.find('.nav-tabs-vertical')) {
          this.type = 'vertical';
          this.$el.children().matchHeight();
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return Tabs;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Tabs);

  var _default = Tabs;
  _exports.default = _default;
});