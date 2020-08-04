(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jstree", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginJstree = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'jstree';

  var Jstree =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Jstree, _Plugin);

    function Jstree() {
      babelHelpers.classCallCheck(this, Jstree);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Jstree).apply(this, arguments));
    }

    babelHelpers.createClass(Jstree, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }]);
    return Jstree;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Jstree);

  var _default = Jstree;
  _exports.default = _default;
});