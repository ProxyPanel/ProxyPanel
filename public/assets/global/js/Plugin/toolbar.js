(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/toolbar", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginToolbar = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'toolbar';

  var Toolbar =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Toolbar, _Plugin);

    function Toolbar() {
      babelHelpers.classCallCheck(this, Toolbar);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Toolbar).apply(this, arguments));
    }

    babelHelpers.createClass(Toolbar, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.toolbar) {
          return;
        }

        var $el = this.$el;
        var content = $el.data('toolbar');

        if (content) {
          this.options.content = content;
        }

        $el.toolbar(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          hideOnClick: true,
          event: 'hover'
        };
      }
    }]);
    return Toolbar;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Toolbar);

  var _default = Toolbar;
  _exports.default = _default;
});