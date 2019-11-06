(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Section/Sidebar", ["exports", "jquery", "Base", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Base"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Base, global.Plugin);
    global.SectionSidebar = mod.exports;
  }
})(this, function (_exports, _jquery, _Base2, _Plugin) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Base2 = babelHelpers.interopRequireDefault(_Base2);

  var Sidebar =
  /*#__PURE__*/
  function (_Base) {
    babelHelpers.inherits(Sidebar, _Base);

    function Sidebar() {
      babelHelpers.classCallCheck(this, Sidebar);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Sidebar).apply(this, arguments));
    }

    babelHelpers.createClass(Sidebar, [{
      key: "process",
      value: function process() {
        if (typeof _jquery.default.slidePanel === 'undefined') {
          return;
        }

        var sidebar = this;
        (0, _jquery.default)(document).on('click', '[data-toggle="site-sidebar"]', function () {
          var $this = (0, _jquery.default)(this);
          var direction = 'right';

          if ((0, _jquery.default)('body').hasClass('site-menubar-flipped')) {
            direction = 'left';
          }

          var options = _jquery.default.extend({}, (0, _Plugin.getDefaults)('slidePanel'), {
            direction: direction,
            skin: 'site-sidebar',
            dragTolerance: 80,
            template: function template(options) {
              return "<div class=\"".concat(options.classes.base, " ").concat(options.classes.base, "-").concat(options.direction, "\">\n      <div class=\"").concat(options.classes.content, " site-sidebar-content\"></div>\n      <div class=\"slidePanel-handler\"></div>\n      </div>");
            },
            afterLoad: function afterLoad() {
              var self = this;
              this.$panel.find('.tab-pane').asScrollable({
                namespace: 'scrollable',
                contentSelector: '> div',
                containerSelector: '> div'
              });
              sidebar.initializePlugins(self.$panel);
              this.$panel.on('shown.bs.tab', function () {
                self.$panel.find('.tab-pane.active').asScrollable('update');
              });
            },
            beforeShow: function beforeShow() {
              if (!$this.hasClass('active')) {
                $this.addClass('active');
              }
            },
            afterHide: function afterHide() {
              if ($this.hasClass('active')) {
                $this.removeClass('active');
              }
            }
          });

          if ($this.hasClass('active')) {
            _jquery.default.slidePanel.hide();
          } else {
            var url = $this.data('url');

            if (!url) {
              url = $this.attr('href');
              url = url && url.replace(/.*(?=#[^\s]*$)/, '');
            }

            _jquery.default.slidePanel.show({
              url: url
            }, options);
          }
        });
        (0, _jquery.default)(document).on('click', '[data-toggle="show-chat"]', function () {
          (0, _jquery.default)('#conversation').addClass('active');
        });
        (0, _jquery.default)(document).on('click', '[data-toggle="close-chat"]', function () {
          (0, _jquery.default)('#conversation').removeClass('active');
        });
      }
    }]);
    return Sidebar;
  }(_Base2.default);

  var _default = Sidebar;
  _exports.default = _default;
});