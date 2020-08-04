(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-tagsinput", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapTagsinput = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tagsinput';

  var Tagsinput =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Tagsinput, _Plugin);

    function Tagsinput() {
      babelHelpers.classCallCheck(this, Tagsinput);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tagsinput).apply(this, arguments));
    }

    babelHelpers.createClass(Tagsinput, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          tagClass: 'badge badge-default'
        };
      }
    }]);
    return Tagsinput;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Tagsinput);

  var _default = Tagsinput;
  _exports.default = _default;
});