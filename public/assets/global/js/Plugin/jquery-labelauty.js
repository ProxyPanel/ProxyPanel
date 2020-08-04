(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jquery-labelauty", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginJqueryLabelauty = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'labelauty';

  var Labelauty =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Labelauty, _Plugin);

    function Labelauty() {
      babelHelpers.classCallCheck(this, Labelauty);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Labelauty).apply(this, arguments));
    }

    babelHelpers.createClass(Labelauty, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          same_width: true
        };
      }
    }]);
    return Labelauty;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Labelauty);

  var _default = Labelauty;
  _exports.default = _default;
});