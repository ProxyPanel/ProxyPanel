(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/panel", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginPanel = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'panel';

  function getPanelAPI($el) {
    if ($el.length <= 0) {
      return;
    }

    var api = $el.data('panelAPI');

    if (api) {
      return api;
    }

    api = new Panel($el, _jquery.default.extend(true, {}, Panel.getDefaults(), $el.data()));
    api.render();
    return api;
  }

  var Panel =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Panel, _Plugin);

    function Panel() {
      babelHelpers.classCallCheck(this, Panel);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Panel).apply(this, arguments));
    }

    babelHelpers.createClass(Panel, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render(context) {
        var $el = this.$el;
        this.isFullscreen = false;
        this.isClose = false;
        this.isCollapse = false;
        this.isLoading = false;
        this.$panelBody = $el.find('.panel-body');
        this.$fullscreen = $el.find('[data-toggle="panel-fullscreen"]');
        this.$collapse = $el.find('[data-toggle="panel-collapse"]');
        this.$loading = null;

        if ($el.hasClass('is-collapse')) {
          this.isCollapse = true;
        }

        if (typeof this.options.loadCallback === 'string') {
          this.options.loadCallback = window[this.options.loadCallback];
        }

        $el.data('panelAPI', this);
      }
    }, {
      key: "load",
      value: function load(callback) {
        var $el = this.$el;
        var type = $el.data('loader-type');

        if (!type) {
          type = 'default';
        }

        callback = callback || this.options.loadCallback;
        this.$loading = (0, _jquery.default)("<div class=\"panel-loading\">\n                          <div class=\"loader loader-".concat(type, "\"></div>\n                        </div>")).appendTo($el);
        $el.addClass('is-loading');
        $el.trigger('loading.uikit.panel');
        this.isLoading = true;

        if (typeof callback === 'function') {
          callback.call(this);
        }
      }
    }, {
      key: "done",
      value: function done() {
        if (this.isLoading === true) {
          this.$loading.remove();
          this.$el.removeClass('is-loading');
          this.$el.trigger('loading.done.uikit.panel');
        }
      }
    }, {
      key: "toggleContent",
      value: function toggleContent() {
        if (this.isCollapse) {
          this.showContent();
        } else {
          this.hideContent();
        }
      }
    }, {
      key: "showContent",
      value: function showContent() {
        if (this.isCollapse !== false) {
          this.$el.removeClass('is-collapse');

          if (this.$collapse.hasClass('wb-plus')) {
            this.$collapse.removeClass('wb-plus').addClass('wb-minus');
          }

          this.$el.trigger('shown.uikit.panel');
          this.isCollapse = false;
        }
      }
    }, {
      key: "hideContent",
      value: function hideContent() {
        if (this.isCollapse !== true) {
          this.$el.addClass('is-collapse');

          if (this.$collapse.hasClass('wb-minus')) {
            this.$collapse.removeClass('wb-minus').addClass('wb-plus');
          }

          this.$el.trigger('hidden.uikit.panel');
          this.isCollapse = true;
        }
      }
    }, {
      key: "toggleFullscreen",
      value: function toggleFullscreen() {
        if (this.isFullscreen) {
          this.leaveFullscreen();
        } else {
          this.enterFullscreen();
        }
      }
    }, {
      key: "enterFullscreen",
      value: function enterFullscreen() {
        if (this.isFullscreen !== true) {
          this.$el.addClass('is-fullscreen');

          if (this.$fullscreen.hasClass('wb-expand')) {
            this.$fullscreen.removeClass('wb-expand').addClass('wb-contract');
          }

          this.$el.trigger('enter.fullscreen.uikit.panel');
          this.isFullscreen = true;
        }
      }
    }, {
      key: "leaveFullscreen",
      value: function leaveFullscreen() {
        if (this.isFullscreen !== false) {
          this.$el.removeClass('is-fullscreen');

          if (this.$fullscreen.hasClass('wb-contract')) {
            this.$fullscreen.removeClass('wb-contract').addClass('wb-expand');
          }

          this.$el.trigger('leave.fullscreen.uikit.panel');
          this.isFullscreen = false;
        }
      }
    }, {
      key: "toggle",
      value: function toggle() {
        if (this.isClose) {
          this.open();
        } else {
          this.close();
        }
      }
    }, {
      key: "open",
      value: function open() {
        if (this.isClose !== false) {
          this.$el.removeClass('is-close');
          this.$el.trigger('open.uikit.panel');
          this.isClose = false;
        }
      }
    }, {
      key: "close",
      value: function close() {
        if (this.isClose !== true) {
          this.$el.addClass('is-close');
          this.$el.trigger('close.uikit.panel');
          this.isClose = true;
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }, {
      key: "api",
      value: function api() {
        return function () {
          (0, _jquery.default)(document).on('click.site.panel', '[data-toggle="panel-fullscreen"]', function (e) {
            e.preventDefault();
            var api = getPanelAPI((0, _jquery.default)(this).closest('.panel'));
            api.toggleFullscreen();
          });
          (0, _jquery.default)(document).on('click.site.panel', '[data-toggle="panel-collapse"]', function (e) {
            e.preventDefault();
            var api = getPanelAPI((0, _jquery.default)(this).closest('.panel'));
            api.toggleContent();
          });
          (0, _jquery.default)(document).on('click.site.panel', '[data-toggle="panel-close"]', function (e) {
            e.preventDefault();
            var api = getPanelAPI((0, _jquery.default)(this).closest('.panel'));
            api.close();
          });
          (0, _jquery.default)(document).on('click.site.panel', '[data-toggle="panel-refresh"]', function (e) {
            e.preventDefault();
            var api = getPanelAPI((0, _jquery.default)(this).closest('.panel'));
            api.load();
          });
        };
      }
    }]);
    return Panel;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Panel);

  var _default = Panel;
  _exports.default = _default;
});