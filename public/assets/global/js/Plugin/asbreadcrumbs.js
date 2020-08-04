(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/asbreadcrumbs", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginAsbreadcrumbs = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'breadcrumb';

  var Breadcrumb =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Breadcrumb, _Plugin);

    function Breadcrumb() {
      babelHelpers.classCallCheck(this, Breadcrumb);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Breadcrumb).apply(this, arguments));
    }

    babelHelpers.createClass(Breadcrumb, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        var $el = this.$el;
        $el.asBreadcrumbs(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          overflow: 'left',
          namespace: 'breadcrumb',
          dropdownMenuClass: 'dropdown-menu',
          dropdownItemClass: 'dropdown-item',
          toggleIconClass: 'wb-chevron-down-mini',
          ellipsis: function ellipsis(classes, label) {
            return "<li class=\"breadcrumb-item ".concat(classes.ellipsisClass, "\">").concat(label, "</li>");
          },
          dropdown: function dropdown(classes) {
            var dropdownClass = 'dropdown';
            var dropdownMenuClass = 'dropdown-menu';

            if (this.options.overflow === 'right') {
              dropdownMenuClass += ' dropdown-menu-right';
            }

            return "<li class=\"breadcrumb-item ".concat(dropdownClass, " ").concat(classes.dropdownClass, "\">\n          <a href=\"javascript:void(0);\" class=\"").concat(classes.toggleClass, "\" data-toggle=\"dropdown\">\n            <i class=\"").concat(classes.toggleIconClass, "\"></i>\n          </a>\n          <div class=\"").concat(dropdownMenuClass, " ").concat(classes.dropdownMenuClass, "\" role=\"menu\"></div>\n        </li>");
          },
          dropdownItem: function dropdownItem(classes, label, href) {
            if (!href) {
              return "<a class=\"".concat(classes.dropdownItemClass, " ").concat(classes.dropdownItemDisableClass, "\" href=\"#\">").concat(label, "</a>");
            }

            return "<a class=\"".concat(classes.dropdownItemClass, "\" href=\"").concat(href, "\">").concat(label, "</a>");
          }
        };
      }
    }]);
    return Breadcrumb;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Breadcrumb);

  var _default = Breadcrumb;
  _exports.default = _default;
});