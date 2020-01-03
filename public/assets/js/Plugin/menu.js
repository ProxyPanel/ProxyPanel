(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/menu", ["Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.Plugin);
    global.PluginMenu = mod.exports;
  }
})(this, function (_Plugin2) {
  "use strict";

  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'menu';

  var Menu =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Menu, _Plugin);

    function Menu() {
      var _babelHelpers$getProt;

      var _this;

      babelHelpers.classCallCheck(this, Menu);

      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Menu)).call.apply(_babelHelpers$getProt, [this].concat(args)));
      _this.folded = true;
      _this.foldAlt = true;
      _this.outerHeight = 0;
      return _this;
    }

    babelHelpers.createClass(Menu, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        this.bindEvents();
        this.$el.data('menuApi', this);
      }
    }, {
      key: "bindEvents",
      value: function bindEvents() {
        var self = this;
        this.$el.on('mouseenter.site.menu', '.site-menu-item', function () {
          var $item = $(this);

          if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
            var $sub = $item.children('.site-menu-sub');
            self.position($item, $sub);
          }

          $item.addClass('hover');
        }).on('mouseleave.site.menu', '.site-menu-item', function () {
          var $item = $(this);

          if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
            $item.children('.site-menu-sub').css('max-height', '');
          }

          $item.removeClass('hover');
        }).on('open.site.menu', '.site-menu-item', function (e) {
          var $item = $(this);
          self.expand($item, function () {
            $item.addClass('open');
          });
          $item.siblings('.open').trigger('close.site.menu');
          e.stopPropagation();
        }).on('close.site.menu', '.site-menu-item.open', function (e) {
          var $item = $(this);
          self.collapse($item, function () {
            $item.removeClass('open');
          });
          e.stopPropagation();
        }).on('click.site.menu ', '.site-menu-item', function (e) {
          var $item = $(this);

          if ($item.parent('.site-menu').length === 0 && $item.is('.has-sub') && $(e.target).closest('.site-menu-item').is(this)) {
            if ($item.is('.open')) {
              $item.trigger('close.site.menu');
            } else {
              $item.trigger('open.site.menu');
            }
          }

          e.stopPropagation();
        }).on('tap.site.menu', '> .site-menu-item > a', function () {
          var link = $(this).attr('href');

          if (link) {
            window.location = link;
          }
        }).on('touchstart.site.menu', '.site-menu-item', function () {
          $(this).one('touchend.site.menu', function () {
            var $item = $(this);

            if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
              $item.siblings('.hover').each(function () {
                var $item = $(this);

                if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
                  $item.children('.site-menu-sub').css('max-height', '');
                }

                $item.removeClass('hover');
              });

              if ($item.is('.hover')) {
                if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
                  $item.children('.site-menu-sub').css('max-height', '');
                }

                $item.removeClass('hover');
              } else {
                if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
                  var $sub = $item.children('.site-menu-sub');
                  self.position($item, $sub);
                }

                $item.addClass('hover');
              }
            }
          });
        }).on('scroll.site.menu', '.site-menu-sub', function (e) {
          e.stopPropagation();
        });
      }
    }, {
      key: "collapse",
      value: function collapse($item, callback) {
        var self = this;
        var $sub = $item.children('.site-menu-sub');
        $sub.show().slideUp(this.options.speed, function () {
          $(this).css('display', '');
          $(this).find('> .site-menu-item').removeClass('is-shown');

          if (callback) {
            callback();
          }

          self.$el.trigger('collapsed.site.menu');
        });
      }
    }, {
      key: "expand",
      value: function expand($item, callback) {
        var self = this;
        var $sub = $item.children('.site-menu-sub');
        var $children = $sub.children('.site-menu-item').addClass('is-hidden');
        $sub.hide().slideDown(this.options.speed, function () {
          $(this).css('display', '');

          if (callback) {
            callback();
          }

          self.$el.trigger('expanded.site.menu');
        });
        setTimeout(function () {
          $children.addClass('is-shown');
          $children.removeClass('is-hidden');
        }, 0);
      }
    }, {
      key: "refresh",
      value: function refresh() {
        this.$el.find('.open').filter(':not(.active)').removeClass('open');
      }
    }, {
      key: "position",
      value: function position($item, $dropdown) {
        var itemHeight = $item.find('> a').outerHeight();
        var menubarHeight = this.outerHeight;
        var offsetTop = $item.position().top;
        $dropdown.removeClass('site-menu-sub-up').css('max-height', '');

        if (offsetTop > menubarHeight / 2) {
          $dropdown.addClass('site-menu-sub-up');

          if (this.foldAlt) {
            offsetTop -= itemHeight;
          }

          $dropdown.css('max-height', offsetTop + itemHeight);
        } else {
          if (this.foldAlt) {
            offsetTop += itemHeight;
          }

          $dropdown.removeClass('site-menu-sub-up');
          $dropdown.css('max-height', menubarHeight - offsetTop);
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          speed: 250
        };
      }
    }]);
    return Menu;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Menu);
});