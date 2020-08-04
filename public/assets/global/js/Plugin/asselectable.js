(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/asselectable", ["exports", "jquery"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery);
    global.PluginAsselectable = mod.exports;
  }
})(this, function (_exports, _jquery) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  var pluginName = 'asSelectable';
  var defaults = {
    allSelector: '.selectable-all',
    itemSelector: '.selectable-item',
    rowSelector: 'tr',
    rowSelectable: false,
    rowActiveClass: 'active',
    onChange: null
  };

  var asSelectable =
  /*#__PURE__*/
  function () {
    function asSelectable(element, options) {
      babelHelpers.classCallCheck(this, asSelectable);
      this.element = element;
      this.$element = (0, _jquery.default)(element);
      this.options = _jquery.default.extend({}, defaults, options, this.$element.data());
      this.init();
    }

    babelHelpers.createClass(asSelectable, [{
      key: "init",
      value: function init() {
        var self = this;
        var options = this.options;
        self.$element.on('change', options.allSelector, function () {
          var value = (0, _jquery.default)(this).prop('checked');
          self.getItems().each(function () {
            var $one = (0, _jquery.default)(this);
            $one.prop('checked', value).trigger('change', [true]);
            self.selectRow($one, value);
          });
        });
        self.$element.on('click', options.itemSelector, function (e) {
          var $one = (0, _jquery.default)(this);
          var value = $one.prop('checked');
          self.selectRow($one, value);
          e.stopPropagation();
        });
        self.$element.on('change', options.itemSelector, function () {
          var $all = self.$element.find(options.allSelector);
          var $row = self.getItems();
          var total = $row.length;
          var checked = self.getSelected().length;

          if (total === checked) {
            $all.prop('checked', true);
          } else {
            $all.prop('checked', false);
          }

          self._trigger('change', checked);

          if (typeof options.callback === 'function') {
            options.callback.call(this);
          }
        });

        if (options.rowSelectable) {
          self.$element.on('click', options.rowSelector, function (e) {
            if (e.target.type !== 'checkbox' && e.target.type !== 'button' && e.target.tagName.toLowerCase() !== 'a' && !(0, _jquery.default)(e.target).parent('div.checkbox-custom').length) {
              var $checkbox = (0, _jquery.default)(options.itemSelector, this);
              var value = $checkbox.prop('checked');
              $checkbox.prop('checked', !value);
              self.selectRow($checkbox, !value);
            }
          });
        }
      }
    }, {
      key: "selectRow",
      value: function selectRow(item, value) {
        if (value) {
          item.parents(this.options.rowSelector).addClass(this.options.rowActiveClass);
        } else {
          item.parents(this.options.rowSelector).removeClass(this.options.rowActiveClass);
        }
      }
    }, {
      key: "getItems",
      value: function getItems() {
        return this.$element.find(this.options.itemSelector);
      }
    }, {
      key: "getSelected",
      value: function getSelected() {
        return this.getItems().filter(':checked');
      }
    }, {
      key: "_trigger",
      value: function _trigger(eventType) {
        var method_arguments = Array.prototype.slice.call(arguments, 1);
        var data = [this].concat(method_arguments); // event

        this.$element.trigger("".concat(pluginName, "::").concat(eventType), data); // callback

        eventType = eventType.replace(/\b\w+\b/g, function (word) {
          return word.substring(0, 1).toUpperCase() + word.substring(1);
        });
        var onFunction = "on".concat(eventType);

        if (typeof this.options[onFunction] === 'function') {
          this.options[onFunction].apply(this, method_arguments);
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
          } else if (/^(get)/.test(method)) {
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
              _jquery.default.data(this, pluginName, new asSelectable(this, options));
            }
          });
        }
      }
    }]);
    return asSelectable;
  }();

  _jquery.default.fn[pluginName] = asSelectable._jQueryInterface;
  _jquery.default.fn[pluginName].constructor = asSelectable;

  _jquery.default.fn[pluginName].noConflict = function () {
    _jquery.default.fn[pluginName] = window.JQUERY_NO_CONFLICT;
    return asSelectable._jQueryInterface;
  };

  var _default = asSelectable;
  _exports.default = _default;
});