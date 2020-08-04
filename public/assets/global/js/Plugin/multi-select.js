(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/multi-select", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginMultiSelect = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'multiSelect';

  var MultiSelect =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(MultiSelect, _Plugin);

    function MultiSelect() {
      babelHelpers.classCallCheck(this, MultiSelect);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MultiSelect).apply(this, arguments));
    }

    babelHelpers.createClass(MultiSelect, [{
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
    return MultiSelect;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, MultiSelect);

  var _default = MultiSelect;
  _exports.default = _default;
});