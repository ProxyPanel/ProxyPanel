(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/isotope", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginIsotope = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'isotope';

  var Isotope =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Isotope, _Plugin);

    function Isotope() {
      babelHelpers.classCallCheck(this, Isotope);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Isotope).apply(this, arguments));
    }

    babelHelpers.createClass(Isotope, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        var _this = this;

        if (typeof _jquery.default.fn.isotope === 'undefined') {
          return;
        }

        var callback = function callback() {
          var $el = _this.$el;
          $el.isotope(_this.options);
        };

        if (this !== document) {
          callback();
        } else {
          (0, _jquery.default)(window).on('load', function () {
            callback();
          });
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return Isotope;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Isotope);

  var _default = Isotope;
  _exports.default = _default;
});