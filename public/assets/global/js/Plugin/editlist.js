(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/editlist", ["exports", "jquery", "bootbox", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("bootbox"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.bootbox, global.Plugin);
    global.PluginEditlist = mod.exports;
  }
})(this, function (_exports, _jquery, _bootbox, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _bootbox = babelHelpers.interopRequireDefault(_bootbox);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var pluginName = 'editlist';
  var defaults = {};

  var editlist =
  /*#__PURE__*/
  function () {
    function editlist(element, options) {
      babelHelpers.classCallCheck(this, editlist);
      this.element = element;
      this.$element = (0, _jquery.default)(element);
      this.$content = this.$element.find('.list-content');
      this.$text = this.$element.find('.list-text');
      this.$editable = this.$element.find('.list-editable');
      this.$editBtn = this.$element.find('[data-toggle=list-editable]');
      this.$delBtn = this.$element.find('[data-toggle=list-delete]');
      this.$closeBtn = this.$element.find('[data-toggle=list-editable-close]');
      this.$input = this.$element.find('input');
      this.options = _jquery.default.extend({}, _Plugin2.default.defaults, options, this.$element.data());
      this.init();
    }

    babelHelpers.createClass(editlist, [{
      key: "init",
      value: function init() {
        this.bind();
      }
    }, {
      key: "bind",
      value: function bind() {
        var self = this;
        this.$editBtn.on('click', function () {
          self.enable();
        });
        this.$closeBtn.on('click', function () {
          self.disable();
        });
        this.$delBtn.on('click', function () {
          if (typeof _bootbox.default === 'undefined') {
            return;
          }

          _bootbox.default.dialog({
            message: 'Do you want to delete the contact?',
            buttons: {
              success: {
                label: 'Delete',
                className: 'btn-danger',
                callback: function callback() {
                  self.$element.remove();
                }
              }
            }
          });
        });
        this.$input.on('keydown', function (e) {
          var keycode = e.keyCode ? e.keyCode : e.which;

          if (keycode === 13 || keycode === 27) {
            if (keycode === 13) {
              self.$text.html(self.$input.val());
            } else {
              self.$input.val(self.$text.text());
            }

            self.disable();
          }
        });
      }
    }, {
      key: "enable",
      value: function enable() {
        this.$content.hide();
        this.$editable.show();
        this.$input.focus().select();
      }
    }, {
      key: "disable",
      value: function disable() {
        this.$content.show();
        this.$editable.hide();
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
              _jquery.default.data(this, pluginName, new editlist(this, options));
            }
          });
        }
      }
    }]);
    return editlist;
  }();

  _jquery.default.fn[pluginName] = editlist._jQueryInterface;
  _jquery.default.fn[pluginName].constructor = editlist;

  _jquery.default.fn[pluginName].noConflict = function () {
    _jquery.default.fn[pluginName] = window.JQUERY_NO_CONFLICT;
    return editlist._jQueryInterface;
  };

  var Editlist =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Editlist, _Plugin);

    function Editlist() {
      babelHelpers.classCallCheck(this, Editlist);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Editlist).apply(this, arguments));
    }

    babelHelpers.createClass(Editlist, [{
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
    return Editlist;
  }(_Plugin2.default);

  _Plugin2.default.register(pluginName, Editlist);

  var _default = Editlist;
  _exports.default = _default;
});