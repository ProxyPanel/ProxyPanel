(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/summernote", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginSummernote = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'summernote';

  var Summernote =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Summernote, _Plugin);

    function Summernote() {
      babelHelpers.classCallCheck(this, Summernote);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Summernote).apply(this, arguments));
    }

    babelHelpers.createClass(Summernote, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          height: 300
        };
      }
    }]);
    return Summernote;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Summernote);

  var _default = Summernote;
  _exports.default = _default;
});