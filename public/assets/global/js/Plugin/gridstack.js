(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/gridstack", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginGridstack = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'gridstack';

  var Gridstack =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Gridstack, _Plugin);

    function Gridstack() {
      babelHelpers.classCallCheck(this, Gridstack);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Gridstack).apply(this, arguments));
    }

    babelHelpers.createClass(Gridstack, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.gridstack) {
          return;
        }

        var $el = this.$el;
        $el.gridstack(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          cellHeight: 80,
          verticalMargin: 20
        };
      }
    }]);
    return Gridstack;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Gridstack);

  var _default = Gridstack;
  _exports.default = _default;
});