(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/ascolorpicker", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginAscolorpicker = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'asColorPicker';

  var AsColorPicker =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(AsColorPicker, _Plugin);

    function AsColorPicker() {
      babelHelpers.classCallCheck(this, AsColorPicker);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AsColorPicker).apply(this, arguments));
    }

    babelHelpers.createClass(AsColorPicker, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          namespace: 'colorInputUi'
        };
      }
    }]);
    return AsColorPicker;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, AsColorPicker);

  var _default = AsColorPicker;
  _exports.default = _default;
});