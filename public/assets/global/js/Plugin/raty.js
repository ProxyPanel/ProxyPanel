(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/raty", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginRaty = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'rating';

  var Rating =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Rating, _Plugin);

    function Rating() {
      babelHelpers.classCallCheck(this, Rating);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Rating).apply(this, arguments));
    }

    babelHelpers.createClass(Rating, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.raty) {
          return;
        }

        var $el = this.$el;

        if (this.options.hints) {
          this.options.hints = this.options.hints.split(',');
        }

        $el.raty(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          targetKeep: true,
          icon: 'font',
          starType: 'i',
          starOff: 'icon wb-star',
          starOn: 'icon wb-star orange-600',
          cancelOff: 'icon wb-minus-circle',
          cancelOn: 'icon wb-minus-circle orange-600',
          starHalf: 'icon wb-star-half orange-500'
        };
      }
    }]);
    return Rating;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Rating);

  var _default = Rating;
  _exports.default = _default;
});