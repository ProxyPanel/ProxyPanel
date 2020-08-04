(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-maxlength", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapMaxlength = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'maxlength';

  var Maxlength =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Maxlength, _Plugin);

    function Maxlength() {
      babelHelpers.classCallCheck(this, Maxlength);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Maxlength).apply(this, arguments));
    }

    babelHelpers.createClass(Maxlength, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          warningClass: 'badge badge-warning',
          limitReachedClass: 'badge badge-danger'
        };
      }
    }]);
    return Maxlength;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Maxlength);

  var _default = Maxlength;
  _exports.default = _default;
});