(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/datepair", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginDatepair = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'datepair';

  var Datepair =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Datepair, _Plugin);

    function Datepair() {
      babelHelpers.classCallCheck(this, Datepair);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Datepair).apply(this, arguments));
    }

    babelHelpers.createClass(Datepair, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          startClass: 'datepair-start',
          endClass: 'datepair-end',
          timeClass: 'datepair-time',
          dateClass: 'datepair-date'
        };
      }
    }]);
    return Datepair;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Datepair);

  var _default = Datepair;
  _exports.default = _default;
});