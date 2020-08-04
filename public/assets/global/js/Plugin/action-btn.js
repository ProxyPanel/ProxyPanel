(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/action-btn", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginActionBtn = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var pluginName = 'actionBtn';
  var defaults = {
    trigger: 'click',
    // click, hover
    toggleSelector: '.site-action-toggle',
    listSelector: '.site-action-buttons',
    activeClass: 'active',
    onShow: function onShow() {},
    onHide: function onHide() {}
  };

  var actionBtn =
  /*#__PURE__*/
  function () {
    function actionBtn(element, options) {
      babelHelpers.classCallCheck(this, actionBtn);
      this.element = element;
      this.$element = (0, _jquery.default)(element);
      this.options = _jquery.default.extend({}, defaults, options, this.$element.data());
      this.init();
    }

    babelHelpers.createClass(actionBtn, [{
      key: "init",
      value: function init() {
        this.showed = false;
        this.$toggle = this.$element.find(this.options.toggleSelector);
        this.$list = this.$element.find(this.options.listSelector);
        var self = this;

        if (this.options.trigger === 'hover') {
          this.$element.on('mouseenter', this.options.toggleSelector, function () {
            if (!self.showed) {
              self.show();
            }
          });
          this.$element.on('mouseleave', this.options.toggleSelector, function () {
            if (self.showed) {
              self.hide();
            }
          });
        } else {
          this.$element.on('click', this.options.toggleSelector, function () {
            if (self.showed) {
              self.hide();
            } else {
              self.show();
            }
          });
        }
      }
    }, {
      key: "show",
      value: function show() {
        if (!this.showed) {
          this.$element.addClass(this.options.activeClass);
          this.showed = true;
          this.options.onShow.call(this);
        }
      }
    }, {
      key: "hide",
      value: function hide() {
        if (this.showed) {
          this.$element.removeClass(this.options.activeClass);
          this.showed = false;
          this.options.onHide.call(this);
        }
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
          } else if (/^(get)$/.test(method)) {
            var api = this.first().data(pluginName);

            if (api && typeof api[method] === 'function') {
              return api[method].apply(api, args);
            }
          } else {
            return this.each(function () {
              var api = _jquery.default.data(this, pluginName);

              if (api && typeof api[method] === 'function') {
                api[method].apply(api, args);
              }
            });
          }
        } else {
          return this.each(function () {
            if (!_jquery.default.data(this, pluginName)) {
              _jquery.default.data(this, pluginName, new actionBtn(this, options));
            }
          });
        }
      }
    }]);
    return actionBtn;
  }();

  _jquery.default.fn[pluginName] = actionBtn._jQueryInterface;
  _jquery.default.fn[pluginName].constructor = actionBtn;

  _jquery.default.fn[pluginName].noConflict = function () {
    _jquery.default.fn[pluginName] = window.JQUERY_NO_CONFLICT;
    return actionBtn._jQueryInterface;
  };

  var ActionBtn =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(ActionBtn, _Plugin);

    function ActionBtn() {
      babelHelpers.classCallCheck(this, ActionBtn);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionBtn).apply(this, arguments));
    }

    babelHelpers.createClass(ActionBtn, [{
      key: "getName",
      value: function getName() {
        return pluginName;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return defaults;
      }
    }]);
    return ActionBtn;
  }(_Plugin2.default);

  _Plugin2.default.register(pluginName, ActionBtn);

  var _default = ActionBtn;
  _exports.default = _default;
});