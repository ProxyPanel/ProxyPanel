(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/peity", ["exports", "jquery", "Plugin", "Config"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"), require("Config"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin, global.Config);
    global.PluginPeity = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin5, _Config) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.PeityPie = _exports.PeityDonut = _exports.PeityLine = _exports.PeityBar = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin5 = babelHelpers.interopRequireDefault(_Plugin5);

  var PeityBar =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(PeityBar, _Plugin);

    function PeityBar() {
      babelHelpers.classCallCheck(this, PeityBar);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PeityBar).apply(this, arguments));
    }

    babelHelpers.createClass(PeityBar, [{
      key: "getName",
      value: function getName() {
        return 'peityBar';
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.peity) {
          return;
        }

        var $el = this.$el;
        var options = this.options;

        if (options.skin) {
          var skinColors = (0, _Config.colors)(options.skin);

          if (skinColors) {
            options.fill = [skinColors[400]];
          }
        }

        $el.peity('bar', options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          delimiter: ',',
          fill: [(0, _Config.colors)('primary', 400)],
          height: 22,
          max: null,
          min: 0,
          padding: 0.1,
          width: 44
        };
      }
    }]);
    return PeityBar;
  }(_Plugin5.default);

  _exports.PeityBar = PeityBar;

  _Plugin5.default.register('peityBar', PeityBar);

  var PeityDonut =
  /*#__PURE__*/
  function (_Plugin2) {
    babelHelpers.inherits(PeityDonut, _Plugin2);

    function PeityDonut() {
      babelHelpers.classCallCheck(this, PeityDonut);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PeityDonut).apply(this, arguments));
    }

    babelHelpers.createClass(PeityDonut, [{
      key: "getName",
      value: function getName() {
        return 'peityDonut';
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.peity) {
          return;
        }

        var $el = this.$el;
        var options = this.options;

        if (options.skin) {
          var skinColors = (0, _Config.colors)(options.skin);

          if (skinColors) {
            options.fill = [skinColors[700], skinColors[400], skinColors[200]];
          }
        }

        $el.peity('donut', options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          delimiter: null,
          fill: [(0, _Config.colors)('primary', 700), (0, _Config.colors)('primary', 400), (0, _Config.colors)('primary', 200)],
          height: null,
          innerRadius: null,
          radius: 11,
          width: null
        };
      }
    }]);
    return PeityDonut;
  }(_Plugin5.default);

  _exports.PeityDonut = PeityDonut;

  _Plugin5.default.register('peityDonut', PeityDonut);

  var PeityLine =
  /*#__PURE__*/
  function (_Plugin3) {
    babelHelpers.inherits(PeityLine, _Plugin3);

    function PeityLine() {
      babelHelpers.classCallCheck(this, PeityLine);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PeityLine).apply(this, arguments));
    }

    babelHelpers.createClass(PeityLine, [{
      key: "getName",
      value: function getName() {
        return 'peityLine';
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.peity) {
          return;
        }

        var $el = this.$el;
        var options = this.options;

        if (options.skin) {
          var skinColors = (0, _Config.colors)(options.skin);

          if (skinColors) {
            options.fill = [skinColors[200]];
            options.stroke = skinColors[600];
          }
        }

        $el.peity('line', options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          delimiter: ',',
          fill: [(0, _Config.colors)('primary', 200)],
          height: 22,
          max: null,
          min: 0,
          stroke: (0, _Config.colors)('primary', 600),
          strokeWidth: 1,
          width: 44
        };
      }
    }]);
    return PeityLine;
  }(_Plugin5.default);

  _exports.PeityLine = PeityLine;

  _Plugin5.default.register('peityLine', PeityLine);

  var PeityPie =
  /*#__PURE__*/
  function (_Plugin4) {
    babelHelpers.inherits(PeityPie, _Plugin4);

    function PeityPie() {
      babelHelpers.classCallCheck(this, PeityPie);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PeityPie).apply(this, arguments));
    }

    babelHelpers.createClass(PeityPie, [{
      key: "getName",
      value: function getName() {
        return 'peityPie';
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.peity) {
          return;
        }

        var $el = this.$el;
        var options = this.options;

        if (options.skin) {
          var skinColors = (0, _Config.colors)(options.skin);

          if (skinColors) {
            options.fill = [skinColors[700], skinColors[400], skinColors[200]];
          }
        }

        $el.peity('pie', options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          delimiter: null,
          fill: [(0, _Config.colors)('primary', 700), (0, _Config.colors)('primary', 400), (0, _Config.colors)('primary', 200)],
          height: null,
          radius: 11,
          width: null
        };
      }
    }]);
    return PeityPie;
  }(_Plugin5.default);

  _exports.PeityPie = PeityPie;

  _Plugin5.default.register('peityPie', PeityPie);
});