(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/highlight", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginHighlight = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'highlight';

  var Highlight =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Highlight, _Plugin);

    function Highlight() {
      babelHelpers.classCallCheck(this, Highlight);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Highlight).apply(this, arguments));
    }

    babelHelpers.createClass(Highlight, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        hljs.initHighlightingOnLoad();
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return Highlight;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Highlight);

  var _default = Highlight;
  _exports.default = _default;
});