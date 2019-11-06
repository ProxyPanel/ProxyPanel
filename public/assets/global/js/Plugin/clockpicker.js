(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/clockpicker", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginClockpicker = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'clockpicker';

  var Clockpicker =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Clockpicker, _Plugin);

    function Clockpicker() {
      babelHelpers.classCallCheck(this, Clockpicker);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Clockpicker).apply(this, arguments));
    }

    babelHelpers.createClass(Clockpicker, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          donetext: 'Done'
        };
      }
    }]);
    return Clockpicker;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Clockpicker);

  var _default = Clockpicker;
  _exports.default = _default;
});