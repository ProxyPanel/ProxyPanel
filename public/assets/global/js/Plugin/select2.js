(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/select2", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginSelect2 = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'select2';

  var Select2 =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Select2, _Plugin);

    function Select2() {
      babelHelpers.classCallCheck(this, Select2);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Select2).apply(this, arguments));
    }

    babelHelpers.createClass(Select2, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          width: 'style'
        };
      }
    }]);
    return Select2;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Select2);

  var _default = Select2;
  _exports.default = _default;
});