(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/switchery", ["exports", "Plugin", "Config"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"), require("Config"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin, global.Config);
    global.PluginSwitchery = mod.exports;
  }
})(this, function (_exports, _Plugin2, _Config) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'switchery';

  var SwitcheryPlugin =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(SwitcheryPlugin, _Plugin);

    function SwitcheryPlugin() {
      babelHelpers.classCallCheck(this, SwitcheryPlugin);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SwitcheryPlugin).apply(this, arguments));
    }

    babelHelpers.createClass(SwitcheryPlugin, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof Switchery === 'undefined') {
          return;
        }

        new Switchery(this.$el[0], this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          color: (0, _Config.colors)('primary', 600)
        };
      }
    }]);
    return SwitcheryPlugin;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, SwitcheryPlugin);

  var _default = SwitcheryPlugin;
  _exports.default = _default;
});