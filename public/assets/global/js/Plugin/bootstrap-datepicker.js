(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-datepicker", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapDatepicker = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'datepicker';

  var Datepicker =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Datepicker, _Plugin);

    function Datepicker() {
      babelHelpers.classCallCheck(this, Datepicker);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Datepicker).apply(this, arguments));
    }

    babelHelpers.createClass(Datepicker, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          autoclose: true
        };
      }
    }]);
    return Datepicker;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Datepicker);

  var _default = Datepicker;
  _exports.default = _default;
});