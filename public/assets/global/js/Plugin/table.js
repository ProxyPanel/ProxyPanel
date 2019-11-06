(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/table", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginTable = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tableSection';

  var TableSection =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(TableSection, _Plugin);

    function TableSection() {
      babelHelpers.classCallCheck(this, TableSection);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TableSection).apply(this, arguments));
    }

    babelHelpers.createClass(TableSection, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        this.$el.data('tableApi', this);
      }
    }, {
      key: "toggle",
      value: function toggle(e) {
        var $el = this.$el;

        if (e.target.type !== 'checkbox' && e.target.type !== 'button' && e.target.tagName.toLowerCase() !== 'a' && !(0, _jquery.default)(e.target).parent('div.checkbox-custom').length) {
          if ($el.hasClass('active')) {
            $el.removeClass('active');
          } else {
            $el.siblings('.table-section').removeClass('active');
            $el.addClass('active');
          }
        }
      }
    }], [{
      key: "api",
      value: function api() {
        var api = 'click|toggle';
        var touch = typeof document.ontouchstart !== 'undefined';

        if (touch) {
          api = 'touchstart|toggle';
        }

        return api;
      }
    }]);
    return TableSection;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, TableSection);

  var _default = TableSection;
  _exports.default = _default;
});