(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/filterable", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginFilterable = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'filterable';

  var Filterable =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Filterable, _Plugin);

    function Filterable() {
      babelHelpers.classCallCheck(this, Filterable);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Filterable).apply(this, arguments));
    }

    babelHelpers.createClass(Filterable, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof _jquery.default.fn.isotope === 'undefined') {
          return;
        }

        var $el = this.$el;

        var options = _jquery.default.extend(this.options, {
          filter: '*'
        });

        this.$el.isotope(options);
        this.$filters = (0, _jquery.default)(options.filters);
        var self = this;
        (0, _jquery.default)('[data-filter]', this.$filters).on('click', function (e) {
          var $this = (0, _jquery.default)(this);
          var $li = $this.parent('li');
          $li.siblings().find('.nav-link.active').each(function () {
            (0, _jquery.default)(this).attr('aria-expanded', false).removeClass('active');
          });
          $this.addClass('active').attr('aria-expanded', true);
          var filter = $this.attr('data-filter');

          if (filter !== '*') {
            filter = "[data-type=\"".concat(filter, "\"]");
          }

          self.$el.isotope({
            filter: filter
          });
          e.preventDefault();
        });
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          animationOptions: {
            duration: 750,
            easing: 'linear',
            queue: false
          }
        };
      }
    }]);
    return Filterable;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Filterable);

  var _default = Filterable;
  _exports.default = _default;
});