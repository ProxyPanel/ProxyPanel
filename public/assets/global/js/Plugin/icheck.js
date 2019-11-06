(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/icheck", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginIcheck = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'iCheck';

  var ICheck =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(ICheck, _Plugin);

    function ICheck() {
      babelHelpers.classCallCheck(this, ICheck);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ICheck).apply(this, arguments));
    }

    babelHelpers.createClass(ICheck, [{
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
    return ICheck;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, ICheck);

  var _default = ICheck;
  _exports.default = _default;
});