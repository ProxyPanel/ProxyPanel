(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/nestable", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginNestable = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'nestable';

  var Nestable =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Nestable, _Plugin);

    function Nestable() {
      babelHelpers.classCallCheck(this, Nestable);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Nestable).apply(this, arguments));
    }

    babelHelpers.createClass(Nestable, [{
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
    return Nestable;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Nestable);

  var _default = Nestable;
  _exports.default = _default;
});