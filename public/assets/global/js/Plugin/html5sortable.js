(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/html5sortable", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginHtml5sortable = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'sortable';

  var Sortable =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Sortable, _Plugin);

    function Sortable() {
      babelHelpers.classCallCheck(this, Sortable);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Sortable).apply(this, arguments));
    }

    babelHelpers.createClass(Sortable, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        var $el = this.$el;
        sortable(this.$el.get(0), this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          connectWith: false,
          placeholder: null,
          // dragImage can be null or a Element
          dragImage: null,
          disableIEFix: false,
          placeholderClass: 'sortable-placeholder',
          draggingClass: 'sortable-dragging',
          hoverClass: false
        };
      }
    }]);
    return Sortable;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Sortable);

  var _default = Sortable;
  _exports.default = _default;
});