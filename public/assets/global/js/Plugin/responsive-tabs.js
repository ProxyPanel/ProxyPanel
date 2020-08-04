(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/responsive-tabs", ["exports", "jquery"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery);
    global.PluginResponsiveTabs = mod.exports;
  }
})(this, function (_exports, _jquery) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  var pluginName = 'responsiveHorizontalTabs';
  var defaults = {
    navSelector: '.nav-tabs',
    itemSelector: '.nav-item',
    dropdownSelector: '>.dropdown',
    dropdownItemSelector: '.dropdown-item',
    tabSelector: '.tab-pane',
    activeClassName: 'active'
  };

  var responsiveHorizontalTabs =
  /*#__PURE__*/
  function () {
    function responsiveHorizontalTabs(el, options) {
      babelHelpers.classCallCheck(this, responsiveHorizontalTabs);
      var $tabs = this.$tabs = (0, _jquery.default)(el);
      this.options = options = _jquery.default.extend(true, {}, defaults, options);
      var $nav = this.$nav = $tabs.find(this.options.navSelector);
      var $dropdown = this.$dropdown = $nav.find(this.options.dropdownSelector);
      var $items = this.$items = $nav.find(this.options.itemSelector).filter(function () {
        return !(0, _jquery.default)(this).is($dropdown);
      });
      this.$dropdownItems = $dropdown.find(this.options.dropdownItemSelector);
      this.$tabPanel = this.$tabs.find(this.options.tabSelector);
      this.breakpoints = [];
      $items.each(function () {
        (0, _jquery.default)(this).data('width', (0, _jquery.default)(this).width());
      });
      this.init();
      this.bind();
    }

    babelHelpers.createClass(responsiveHorizontalTabs, [{
      key: "init",
      value: function init() {
        if (this.$dropdown.length === 0) {
          return;
        }

        this.$dropdown.show();
        this.breakpoints = [];
        var length = this.length = this.$items.length;
        var dropWidth = this.dropWidth = this.$dropdown.width();
        var total = 0;
        this.flag = length;

        if (length <= 1) {
          this.$dropdown.hide();
          return;
        }

        for (var i = 0; i < length - 2; i++) {
          if (i === 0) {
            this.breakpoints.push(this.$items.eq(i).outerWidth() + dropWidth);
          } else {
            this.breakpoints.push(this.breakpoints[i - 1] + this.$items.eq(i).width());
          }
        }

        for (i = 0; i < length; i++) {
          total += this.$items.eq(i).outerWidth();
        }

        this.breakpoints.push(total);
        this.layout();
      }
    }, {
      key: "layout",
      value: function layout() {
        if (this.breakpoints.length <= 0) {
          return;
        }

        var width = this.$nav.width();
        var i = 0;
        var activeClassName = this.options.activeClassName;
        var active = this.$tabPanel.filter(".".concat(activeClassName)).index();

        for (; i < this.breakpoints.length; i++) {
          if (this.breakpoints[i] > width) {
            break;
          }
        }

        if (i === this.flag) {
          return;
        }

        this.$items.children().removeClass(activeClassName);
        this.$dropdownItems.removeClass(activeClassName);
        this.$dropdown.children().removeClass(activeClassName);

        if (i === this.breakpoints.length) {
          this.$dropdown.hide();
          this.$items.show();
          this.$items.eq(active).children().addClass(activeClassName);
        } else {
          this.$dropdown.show();

          for (var j = 0; j < this.length; j++) {
            if (j < i) {
              this.$items.eq(j).show();
              this.$dropdownItems.eq(j).hide();
            } else {
              this.$items.eq(j).hide();
              this.$dropdownItems.eq(j).show();
            }
          }

          if (active < i) {
            this.$items.eq(active).children().addClass(activeClassName);
          } else {
            this.$dropdown.children().addClass(activeClassName);
            this.$dropdownItems.eq(active).addClass(activeClassName);
          }
        }

        this.flag = i;
      }
    }, {
      key: "bind",
      value: function bind() {
        var self = this;
        (0, _jquery.default)(window).resize(function () {
          self.layout();
        });
      }
    }], [{
      key: "_jQueryInterface",
      value: function _jQueryInterface(options) {
        for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          args[_key - 1] = arguments[_key];
        }

        if (typeof options === 'string') {
          var method = options;

          if (/^\_/.test(method)) {
            return false;
          }

          return this.each(function () {
            var api = _jquery.default.data(this, pluginName);

            if (api && typeof api[method] === 'function') {
              api[method].apply(api, args);
            }
          });
        }

        return this.each(function () {
          if (!_jquery.default.data(this, pluginName)) {
            _jquery.default.data(this, pluginName, new responsiveHorizontalTabs(this, options));
          } else {
            _jquery.default.data(this, pluginName).init();
          }
        });
      }
    }]);
    return responsiveHorizontalTabs;
  }();

  _jquery.default.fn[pluginName] = responsiveHorizontalTabs._jQueryInterface;
  _jquery.default.fn[pluginName].constructor = responsiveHorizontalTabs;

  _jquery.default.fn[pluginName].noConflict = function () {
    _jquery.default.fn[pluginName] = window.JQUERY_NO_CONFLICT;
    return responsiveHorizontalTabs._jQueryInterface;
  };

  var _default = responsiveHorizontalTabs;
  _exports.default = _default;
});