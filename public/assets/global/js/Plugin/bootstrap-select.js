(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-select", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapSelect = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'selectpicker';

  var Selectpicker =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Selectpicker, _Plugin);

    function Selectpicker() {
      babelHelpers.classCallCheck(this, Selectpicker);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Selectpicker).apply(this, arguments));
    }

    babelHelpers.createClass(Selectpicker, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          style: 'btn-select',
          iconBase: 'icon',
          tickIcon: 'wb-check'
        };
      }
    }]);
    return Selectpicker;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Selectpicker);

  var _default = Selectpicker;
  _exports.default = _default;
});