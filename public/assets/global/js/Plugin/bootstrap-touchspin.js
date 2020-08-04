(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/bootstrap-touchspin", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginBootstrapTouchspin = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'TouchSpin';

  var TouchSpin =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(TouchSpin, _Plugin);

    function TouchSpin() {
      babelHelpers.classCallCheck(this, TouchSpin);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TouchSpin).apply(this, arguments));
    }

    babelHelpers.createClass(TouchSpin, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          // verticalupclass: 'wb-plus',
          // verticaldownclass: 'wb-minus',
          buttondown_class: 'btn btn-outline btn-default',
          buttonup_class: 'btn btn-outline btn-default'
        };
      }
    }]);
    return TouchSpin;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, TouchSpin);

  var _default = TouchSpin;
  _exports.default = _default;
});