(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/formatter", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginFormatter = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'formatter';

  var Formatter =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Formatter, _Plugin);

    function Formatter() {
      babelHelpers.classCallCheck(this, Formatter);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Formatter).apply(this, arguments));
    }

    babelHelpers.createClass(Formatter, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.formatter) {
          return;
        }

        var browserName = navigator.userAgent.toLowerCase();

        if (/msie/i.test(browserName) && !/opera/.test(browserName)) {} else {}

        var $el = this.$el,
            options = this.options;

        if (options.pattern) {
          options.pattern = options.pattern.replace(/\[\[/g, '{{').replace(/\]\]/g, '}}');
        }

        $el.formatter(options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          persistent: true
        };
      }
    }]);
    return Formatter;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Formatter);

  var _default = Formatter;
  _exports.default = _default;
});