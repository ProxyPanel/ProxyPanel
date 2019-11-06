(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/asspinner", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginAsspinner = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'asSpinner';

  var AsSpinner =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(AsSpinner, _Plugin);

    function AsSpinner() {
      babelHelpers.classCallCheck(this, AsSpinner);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AsSpinner).apply(this, arguments));
    }

    babelHelpers.createClass(AsSpinner, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          namespace: 'spinnerUi',
          skin: null,
          min: '-10',
          max: 100,
          mousewheel: true
        };
      }
    }]);
    return AsSpinner;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, AsSpinner);

  var _default = AsSpinner;
  _exports.default = _default;
});