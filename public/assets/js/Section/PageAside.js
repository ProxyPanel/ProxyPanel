(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Section/PageAside", ["exports", "jquery", "Component"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Component"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Component);
    global.SectionPageAside = mod.exports;
  }
})(this, function (_exports, _jquery, _Component2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Component2 = babelHelpers.interopRequireDefault(_Component2);
  var $BODY = (0, _jquery.default)('body');

  var PageAside =
  /*#__PURE__*/
  function (_Component) {
    babelHelpers.inherits(PageAside, _Component);

    function PageAside() {
      var _babelHelpers$getProt;

      var _this;

      babelHelpers.classCallCheck(this, PageAside);

      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(PageAside)).call.apply(_babelHelpers$getProt, [this].concat(args)));
      _this.$scroll = _this.$el.find('.page-aside-scroll');
      _this.scrollable = _this.$scroll.asScrollable({
        namespace: 'scrollable',
        contentSelector: '> [data-role=\'content\']',
        containerSelector: '> [data-role=\'container\']'
      }).data('asScrollable');
      return _this;
    }

    babelHelpers.createClass(PageAside, [{
      key: "process",
      value: function process() {
        var _this2 = this;

        if ($BODY.is('.page-aside-fixed') || $BODY.is('.page-aside-scroll')) {
          this.$el.on('transitionend', function () {
            _this2.scrollable.update();
          });
        }

        Breakpoints.on('change', function () {
          var current = Breakpoints.current().name;

          if (!$BODY.is('.page-aside-fixed') && !$BODY.is('.page-aside-scroll')) {
            if (current === 'xs') {
              _this2.scrollable.enable();

              _this2.$el.on('transitionend', function () {
                _this2.scrollable.update();
              });
            } else {
              _this2.$el.off('transitionend');

              _this2.scrollable.update();
            }
          }
        });
        (0, _jquery.default)(document).on('click.pageAsideScroll', '.page-aside-switch', function () {
          var isOpen = _this2.$el.hasClass('open');

          if (isOpen) {
            _this2.$el.removeClass('open');
          } else {
            _this2.scrollable.update();

            _this2.$el.addClass('open');
          }
        });
        (0, _jquery.default)(document).on('click.pageAsideScroll', '[data-toggle="collapse"]', function (e) {
          var $trigger = (0, _jquery.default)(e.target);

          if (!$trigger.is('[data-toggle="collapse"]')) {
            $trigger = $trigger.parents('[data-toggle="collapse"]');
          }

          var href;
          var target = $trigger.attr('data-target') || (href = $trigger.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '');
          var $target = (0, _jquery.default)(target);

          if ($target.attr('id') === 'site-navbar-collapse') {
            _this2.scrollable.update();
          }
        });
      }
    }]);
    return PageAside;
  }(_Component2.default);

  var _default = PageAside;
  _exports.default = _default;
});