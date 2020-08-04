(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-tokenfield", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapTokenfield = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tokenfield';

  var Tokenfield =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Tokenfield, _Plugin);

    function Tokenfield() {
      babelHelpers.classCallCheck(this, Tokenfield);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tokenfield).apply(this, arguments));
    }

    babelHelpers.createClass(Tokenfield, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return Tokenfield;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Tokenfield);

  var _default = Tokenfield;
  _exports.default = _default;
});