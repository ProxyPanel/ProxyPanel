(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/asrange", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginAsrange = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'asRange';

  var AsRange =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(AsRange, _Plugin);

    function AsRange() {
      babelHelpers.classCallCheck(this, AsRange);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AsRange).apply(this, arguments));
    }

    babelHelpers.createClass(AsRange, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          tip: false,
          scale: false
        };
      }
    }]);
    return AsRange;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, AsRange);

  var _default = AsRange;
  _exports.default = _default;
});