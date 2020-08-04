(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/ladda", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginLadda = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'ladda';

  var LaddaPlugin =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(LaddaPlugin, _Plugin);

    function LaddaPlugin() {
      babelHelpers.classCallCheck(this, LaddaPlugin);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LaddaPlugin).apply(this, arguments));
    }

    babelHelpers.createClass(LaddaPlugin, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof Ladda === 'undefined') {
          return;
        }

        if (this.options.type === 'progress') {
          this.options.callback = function (instance) {
            var progress = 0;
            var interval = setInterval(function () {
              progress = Math.min(progress + Math.random() * 0.1, 1);
              instance.setProgress(progress);

              if (progress === 1) {
                instance.stop();
                clearInterval(interval);
              }
            }, 200);
          };
        }

        Ladda.bind(this.$el[0], this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          type: 'normal',
          timeout: 2000
        };
      }
    }]);
    return LaddaPlugin;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, LaddaPlugin);

  var _default = LaddaPlugin;
  _exports.default = _default;
});