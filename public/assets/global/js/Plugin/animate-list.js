(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/animate-list", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginAnimateList = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'animateList';

  var AnimateList =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(AnimateList, _Plugin);

    function AnimateList() {
      babelHelpers.classCallCheck(this, AnimateList);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AnimateList).apply(this, arguments));
    }

    babelHelpers.createClass(AnimateList, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        var $el = this.$el;

        var animatedBox =
        /*#__PURE__*/
        function () {
          function animatedBox($el, opts) {
            babelHelpers.classCallCheck(this, animatedBox);
            this.options = opts;
            this.$children = $el.find(opts.child);
            this.$children.addClass("animation-".concat(opts.animate));
            this.$children.css('animation-fill-mode', opts.fill);
            this.$children.css('animation-duration', "".concat(opts.duration, "ms"));
            var delay = 0;
            var self = this;
            this.$children.each(function () {
              (0, _jquery.default)(this).css('animation-delay', "".concat(delay, "ms"));
              delay += self.options.delay;
            });
          }

          babelHelpers.createClass(animatedBox, [{
            key: "run",
            value: function run(type) {
              var _this = this;

              this.$children.removeClass("animation-".concat(this.options.animate));

              if (typeof type !== 'undefined') {
                this.options.animate = type;
              }

              setTimeout(function () {
                _this.$children.addClass("animation-".concat(_this.options.animate));
              }, 0);
            }
          }]);
          return animatedBox;
        }();

        $el.data('animateList', new animatedBox($el, this.options));
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          child: '.panel',
          duration: 250,
          delay: 50,
          animate: 'scale-up',
          fill: 'backwards'
        };
      }
    }]);
    return AnimateList;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, AnimateList);

  var _default = AnimateList;
  _exports.default = _default;
});