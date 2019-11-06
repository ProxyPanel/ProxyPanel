(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/owl-carousel", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginOwlCarousel = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'owlCarousel';

  var OwlCarousel =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(OwlCarousel, _Plugin);

    function OwlCarousel() {
      babelHelpers.classCallCheck(this, OwlCarousel);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OwlCarousel).apply(this, arguments));
    }

    babelHelpers.createClass(OwlCarousel, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          // autoWidth: true,
          loop: true,
          nav: true,
          dots: false,
          dotsClass: 'owl-dots owl-dots-fall',
          responsive: {
            0: {
              items: 1
            },
            600: {
              items: 3
            }
          }
        };
      }
    }]);
    return OwlCarousel;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, OwlCarousel);

  var _default = OwlCarousel;
  _exports.default = _default;
});