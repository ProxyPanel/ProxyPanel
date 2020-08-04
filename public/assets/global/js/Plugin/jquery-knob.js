(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jquery-knob", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginJqueryKnob = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'knob';

  var Knob =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Knob, _Plugin);

    function Knob() {
      babelHelpers.classCallCheck(this, Knob);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Knob).apply(this, arguments));
    }

    babelHelpers.createClass(Knob, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          min: -50,
          max: 50,
          width: 120,
          height: 120,
          thickness: '.1'
        };
      }
    }]);
    return Knob;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Knob);

  var _default = Knob;
  _exports.default = _default;
});