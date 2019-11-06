(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/aspaginator", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginAspaginator = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'paginator';

  var Paginator =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Paginator, _Plugin);

    function Paginator() {
      babelHelpers.classCallCheck(this, Paginator);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Paginator).apply(this, arguments));
    }

    babelHelpers.createClass(Paginator, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.asPaginator) {
          return;
        }

        var $el = this.$el;
        var total = $el.data('total');
        $el.asPaginator(total, this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          namespace: 'pagination',
          currentPage: 1,
          itemsPerPage: 10,
          disabledClass: 'disabled',
          activeClass: 'active',
          visibleNum: {
            0: 3,
            480: 5
          },
          tpl: function tpl() {
            return '{{prev}}{{lists}}{{next}}';
          },
          components: {
            prev: {
              tpl: function tpl() {
                return "<li class=\"".concat(this.namespace, "-prev page-item\"><a class=\"page-link\" href=\"javascript:void(0)\" aria-label=\"Prev\"><span class=\"icon wb-chevron-left-mini\"></span></a></li>");
              }
            },
            next: {
              tpl: function tpl() {
                return "<li class=\"".concat(this.namespace, "-next page-item\"><a class=\"page-link\" href=\"javascript:void(0)\" aria-label=\"Next\"><span class=\"icon wb-chevron-right-mini\"></span></a></li>");
              }
            },
            lists: {
              tpl: function tpl() {
                var lists = '';
                var remainder = this.currentPage >= this.visible ? this.currentPage % this.visible : this.currentPage;
                remainder = remainder === 0 ? this.visible : remainder;

                for (var k = 1; k < remainder; k++) {
                  lists += "<li class=\"".concat(this.namespace, "-items page-item\" data-value=\"").concat(this.currentPage - remainder + k, "\"><a class=\"page-link\" href=\"javascript:void(0)\">").concat(this.currentPage - remainder + k, "</a></li>");
                }

                lists += "<li class=\"".concat(this.namespace, "-items page-item ").concat(this.classes.active, "\" data-value=\"").concat(this.currentPage, "\"><a class=\"page-link\" href=\"javascript:void(0)\">").concat(this.currentPage, "</a></li>");

                for (var i = this.currentPage + 1, limit = i + this.visible - remainder - 1 > this.totalPages ? this.totalPages : i + this.visible - remainder - 1; i <= limit; i++) {
                  lists += "<li class=\"".concat(this.namespace, "-items page-item\" data-value=\"").concat(i, "\"><a class=\"page-link\" href=\"javascript:void(0)\">").concat(i, "</a></li>");
                }

                return lists;
              }
            }
          }
        };
      }
    }]);
    return Paginator;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Paginator);

  var _default = Paginator;
  _exports.default = _default;
});