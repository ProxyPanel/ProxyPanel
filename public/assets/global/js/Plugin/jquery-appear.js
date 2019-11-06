(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jquery-appear", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginJqueryAppear = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'appear';

  var Appear =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Appear, _Plugin);

    function Appear() {
      babelHelpers.classCallCheck(this, Appear);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Appear).apply(this, arguments));
    }

    babelHelpers.createClass(Appear, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "bind",
      value: function bind() {
        var _this = this;

        this.$el.on('appear', function () {
          if (_this.$el.hasClass('appear-no-repeat')) {
            return;
          }

          _this.$el.removeClass('invisible').addClass("animation-".concat(_this.options.animate));

          if (_this.$el.data('repeat') === false) {
            _this.$el.addClass('appear-no-repeat');
          }
        });
        (0, _jquery.default)(document).on('disappear', function () {
          if (_this.$el.hasClass('appear-no-repeat')) {
            return;
          }

          _this.$el.addClass('invisible').removeClass("animation-".concat(_this.options.animate));
        });
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.appear) {
          return;
        }

        this.$el.appear(this.options);
        this.$el.not(':appeared').addClass('invisible');
        this.bind();
      }
    }]);
    return Appear;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Appear);

  var _default = Appear;
  _exports.default = _default;
});