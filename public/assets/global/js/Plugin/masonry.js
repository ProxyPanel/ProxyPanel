(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/masonry", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginMasonry = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'masonry';

  var Masonry =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Masonry, _Plugin);

    function Masonry() {
      babelHelpers.classCallCheck(this, Masonry);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Masonry).apply(this, arguments));
    }

    babelHelpers.createClass(Masonry, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof _jquery.default.fn.masonry === 'undefined') {
          return;
        }

        var $el = this.$el;

        if (_jquery.default.fn.imagesLoaded) {
          $el.imagesLoaded(function () {
            $el.masonry(this.options);
          });
        } else {
          $el.masonry(this.options);
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          itemSelector: '.masonry-item'
        };
      }
    }]);
    return Masonry;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Masonry);

  var _default = Masonry;
  _exports.default = _default;
});