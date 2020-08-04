(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/gauge", ["exports", "Plugin", "Config"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"), require("Config"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin, global.Config);
    global.PluginGauge = mod.exports;
  }
})(this, function (_exports, _Plugin2, _Config) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'gauge';

  var GaugePlugin =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(GaugePlugin, _Plugin);

    function GaugePlugin() {
      babelHelpers.classCallCheck(this, GaugePlugin);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GaugePlugin).apply(this, arguments));
    }

    babelHelpers.createClass(GaugePlugin, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!Gauge) {
          return;
        }

        var $el = this.$el;
        var $canvas = $el.find('canvas');
        var $text = $el.find('.gauge-label');

        if ($canvas.length === 0) {
          return;
        }

        var gauge = new Gauge($canvas[0]).setOptions(this.options);
        $el.data('gauge', gauge);
        gauge.animationSpeed = 50;
        gauge.maxValue = $el.data('max-value');
        gauge.set($el.data('value'));

        if ($text.length > 0) {
          gauge.setTextField($text[0]);
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          lines: 12,
          angle: 0.2,
          lineWidth: 0.4,
          pointer: {
            length: 0.58,
            strokeWidth: 0.03,
            color: (0, _Config.colors)('blue-grey', 400)
          },
          limitMax: true,
          colorStart: (0, _Config.colors)('blue-grey', 200),
          colorStop: (0, _Config.colors)('blue-grey', 200),
          strokeColor: (0, _Config.colors)('primary', 500),
          generateGradient: true
        };
      }
    }]);
    return GaugePlugin;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, GaugePlugin);

  var _default = GaugePlugin;
  _exports.default = _default;
});