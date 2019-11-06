(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/dropify", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginDropify = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'dropify';

  var Dropify =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Dropify, _Plugin);

    function Dropify() {
      babelHelpers.classCallCheck(this, Dropify);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Dropify).apply(this, arguments));
    }

    babelHelpers.createClass(Dropify, [{
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
    return Dropify;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Dropify);

  var _default = Dropify;
  _exports.default = _default;
});