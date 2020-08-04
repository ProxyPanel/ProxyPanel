(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/magnific-popup", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginMagnificPopup = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'magnificPopup';

  var MagnificPopup =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(MagnificPopup, _Plugin);

    function MagnificPopup() {
      babelHelpers.classCallCheck(this, MagnificPopup);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MagnificPopup).apply(this, arguments));
    }

    babelHelpers.createClass(MagnificPopup, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          type: 'image',
          closeOnContentClick: true,
          image: {
            verticalFit: true
          }
        };
      }
    }]);
    return MagnificPopup;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, MagnificPopup);

  var _default = MagnificPopup;
  _exports.default = _default;
});