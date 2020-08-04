(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/ionrangeslider", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginIonrangeslider = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'ionRangeSlider';

  var IonRangeSlider =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(IonRangeSlider, _Plugin);

    function IonRangeSlider() {
      babelHelpers.classCallCheck(this, IonRangeSlider);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IonRangeSlider).apply(this, arguments));
    }

    babelHelpers.createClass(IonRangeSlider, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }]);
    return IonRangeSlider;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, IonRangeSlider);

  var _default = IonRangeSlider;
  _exports.default = _default;
});