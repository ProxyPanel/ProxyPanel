/**
* jQuery asSpinner v0.4.3
* https://github.com/amazingSurge/jquery-asSpinner
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
(function(global, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if (typeof exports !== 'undefined') {
    factory(require('jquery'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery);
    global.jqueryAsSpinnerEs = mod.exports;
  }
})(this, function(_jquery) {
  'use strict';

  var _jquery2 = _interopRequireDefault(_jquery);

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule
      ? obj
      : {
          default: obj
        };
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError('Cannot call a class as a function');
    }
  }

  var _createClass = (function() {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ('value' in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function(Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  })();

  var DEFAULTS = {
    namespace: 'asSpinner',
    skin: null,

    disabled: false,
    min: -10,
    max: 10,
    step: 1,
    name: null,
    precision: 0,
    rule: null, //string, shortcut define max min step precision

    looping: true, // if cycling the value when it is outofbound
    mousewheel: false,
    format: function format(value) {
      // function, define custom format
      return value;
    },
    parse: function parse(value) {
      // function, parse custom format value
      return parseFloat(value);
    }
  };

  var RULES = {
    defaults: {
      min: null,
      max: null,
      step: 1,
      precision: 0
    },
    currency: {
      min: 0.0,
      max: 99999,
      step: 0.01,
      precision: 2
    },
    quantity: {
      min: 1,
      max: 999,
      step: 1,
      precision: 0
    },
    percent: {
      min: 1,
      max: 100,
      step: 1,
      precision: 0
    },
    month: {
      min: 1,
      max: 12,
      step: 1,
      precision: 0
    },
    day: {
      min: 1,
      max: 31,
      step: 1,
      precision: 0
    },
    hour: {
      min: 0,
      max: 23,
      step: 1,
      precision: 0
    },
    minute: {
      min: 1,
      max: 59,
      step: 1,
      precision: 0
    },
    second: {
      min: 1,
      max: 59,
      step: 1,
      precision: 0
    }
  };

  var NAMESPACE$1 = 'asSpinner';

  var asSpinner = (function() {
    function asSpinner(element, options) {
      _classCallCheck(this, asSpinner);

      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options,
        this.$element.data()
      );
      this.namespace = this.options.namespace;

      if (this.options.rule) {
        var that = this;
        var array = ['min', 'max', 'step', 'precision'];
        _jquery2.default.each(array, function(key, value) {
          that[value] = RULES[that.options.rule][value];
        });
      } else {
        this.min = this.options.min;
        this.max = this.options.max;
        this.step = this.options.step;
        this.precision = this.options.precision;
      }

      this.disabled = this.options.disabled;
      if (this.$element.prop('disabled')) {
        this.disabled = true;
      }

      this.value = this.options.parse(this.$element.val());

      this.mousewheel = this.options.mousewheel;
      if (this.mousewheel && !_jquery2.default.event.special.mousewheel) {
        this.mousewheel = false;
      }

      this.eventBinded = false;
      this.spinTimeout = null;
      this.isFocused = false;

      this.classes = {
        disabled: this.namespace + '_disabled',
        skin: this.namespace + '_' + this.options.skin,
        focus: this.namespace + '_focus',

        control: this.namespace + '-control',
        down: this.namespace + '-down',
        up: this.namespace + '-up',
        wrap: this.namespace
      };

      this._trigger('init');
      this.init();
    }

    _createClass(
      asSpinner,
      [
        {
          key: 'init',
          value: function init() {
            this.$control = (0, _jquery2.default)(
              '<div class="' +
                this.namespace +
                '-control"><span class="' +
                this.classes.up +
                '"></span><span class="' +
                this.classes.down +
                '"></span></div>'
            );
            this.$wrap = this.$element
              .wrap(
                '<div tabindex="0" class="' + this.classes.wrap + '"></div>'
              )
              .parent();
            this.$down = this.$control.find('.' + this.classes.down);
            this.$up = this.$control.find('.' + this.classes.up);

            if (this.options.skin) {
              this.$wrap.addClass(this.classes.skin);
            }

            this.$control.appendTo(this.$wrap);

            if (this.disabled === false) {
              // attach event
              this.bindEvent();
            } else {
              this.disable();
            }

            // inital
            this._trigger('ready');
          }
        },
        {
          key: '_trigger',
          value: function _trigger(eventType) {
            for (
              var _len = arguments.length,
                params = Array(_len > 1 ? _len - 1 : 0),
                _key = 1;
              _key < _len;
              _key++
            ) {
              params[_key - 1] = arguments[_key];
            }

            var data = [this].concat(params);

            // event
            this.$element.trigger(NAMESPACE$1 + '::' + eventType, data);

            // callback
            eventType = eventType.replace(/\b\w+\b/g, function(word) {
              return word.substring(0, 1).toUpperCase() + word.substring(1);
            });
            var onFunction = 'on' + eventType;

            if (typeof this.options[onFunction] === 'function') {
              this.options[onFunction].apply(this, params);
            }
          }
        },
        {
          key: 'spin',
          value: function spin(fn, timeout) {
            var that = this;
            var spinFn = function spinFn(timeout) {
              clearTimeout(that.spinTimeout);
              that.spinTimeout = setTimeout(function() {
                fn.call(that);
                spinFn(60);
              }, timeout);
            };
            spinFn(timeout || 500);
          }
        },
        {
          key: 'bindEvent',
          value: function bindEvent() {
            var that = this;
            this.eventBinded = true;

            this.$wrap
              .on('focus.asSpinner', function() {
                that.$wrap.addClass(that.classes.focus);
              })
              .on('blur.asSpinner', function() {
                if (!that.isFocused) {
                  that.$wrap.removeClass(that.classes.focus);
                }
              });

            this.$down
              .on('mousedown.asSpinner', function() {
                (0, _jquery2.default)(
                  document
                ).one('mouseup.asSpinner', function() {
                  clearTimeout(that.spinTimeout);
                });
                that.spin(that.spinDown);
              })
              .on('mouseup.asSpinner', function() {
                clearTimeout(that.spinTimeout);
                (0, _jquery2.default)(document).off('mouseup.asSpinner');
              })
              .on('click.asSpinner', function() {
                that.spinDown();
              });

            this.$up
              .on('mousedown.asSpinner', function() {
                (0, _jquery2.default)(
                  document
                ).one('mouseup.asSpinner', function() {
                  clearTimeout(that.spinTimeout);
                });
                that.spin(that.spinUp);
              })
              .on('mouseup.asSpinner', function() {
                clearTimeout(that.spinTimeout);
                (0, _jquery2.default)(document).off('mouseup.asSpinner');
              })
              .on('click.asSpinner', function() {
                that.spinUp();
              });

            this.$element
              .on('focus.asSpinner', function() {
                that.isFocused = true;
                that.$wrap.addClass(that.classes.focus);

                // keyboard support
                (0, _jquery2.default)(this).on('keydown.asSpinner', function(
                  e
                ) {
                  /*eslint consistent-return: "off"*/
                  var key = e.keyCode || e.which;
                  if (key === 38) {
                    that.applyValue();
                    that.spinUp();
                    return false;
                  }
                  if (key === 40) {
                    that.applyValue();
                    that.spinDown();
                    return false;
                  }
                  if (key <= 57 && key >= 48) {
                    setTimeout(function() {
                      //that.set(parseFloat(it.value));
                    }, 0);
                  }
                });

                // mousewheel support
                if (that.mousewheel === true) {
                  (0, _jquery2.default)(this).mousewheel(function(
                    event,
                    delta
                  ) {
                    if (delta > 0) {
                      that.spinUp();
                    } else {
                      that.spinDown();
                    }
                    return false;
                  });
                }
              })
              .on('blur.asSpinner', function() {
                that.isFocused = false;
                that.$wrap.removeClass(that.classes.focus);
                (0, _jquery2.default)(this).off('keydown.asSpinner');
                if (that.mousewheel === true) {
                  (0, _jquery2.default)(this).unmousewheel();
                }
                that.applyValue();
              });
          }
        },
        {
          key: 'unbindEvent',
          value: function unbindEvent() {
            this.eventBinded = false;
            this.$element.off('.asSpinner');
            this.$down.off('.asSpinner');
            this.$up.off('.asSpinner');
            this.$wrap.off('.asSpinner');
          }
        },
        {
          key: 'isNumber',
          value: function isNumber(value) {
            if (
              typeof value === 'number' &&
              _jquery2.default.isNumeric(value)
            ) {
              return true;
            }
            return false;
          }
        },
        {
          key: 'isOutOfBounds',
          value: function isOutOfBounds(value) {
            if (value < this.min) {
              return -1;
            }
            if (value > this.max) {
              return 1;
            }
            return 0;
          }
        },
        {
          key: 'applyValue',
          value: function applyValue() {
            if (this.options.format(this.value) !== this.$element.val()) {
              this.set(this.options.parse(this.$element.val()));
            }
          }
        },
        {
          key: '_set',
          value: function _set(value) {
            if (isNaN(value)) {
              value = this.min;
            }
            var valid = this.isOutOfBounds(value);
            if (valid !== 0) {
              if (this.options.looping === true) {
                value = valid === 1 ? this.min : this.max;
              } else {
                value = valid === -1 ? this.min : this.max;
              }
            }
            this.value = value = Number(value).toFixed(this.precision);

            this.$element.val(this.options.format(this.value));
          }
        },
        {
          key: 'set',
          value: function set(value) {
            this._set(value);

            this._trigger('change', this.value);
          }
        },
        {
          key: 'get',
          value: function get() {
            return this.value;
          }
        },
        {
          key: 'update',
          value: function update(obj) {
            var that = this;

            ['min', 'max', 'precision', 'step'].forEach(function(value) {
              if (obj[value]) {
                that[value] = obj[value];
              }
            });
            if (obj.value) {
              this.set(obj.value);
            }
            return this;
          }
        },
        {
          key: 'val',
          value: function val(value) {
            if (value) {
              this.set(this.options.parse(value));
            } else {
              return this.get();
            }
          }
        },
        {
          key: 'spinDown',
          value: function spinDown() {
            if (!_jquery2.default.isNumeric(this.value)) {
              this.value = 0;
            }
            this.value = parseFloat(this.value) - parseFloat(this.step);
            this.set(this.value);

            return this;
          }
        },
        {
          key: 'spinUp',
          value: function spinUp() {
            if (!_jquery2.default.isNumeric(this.value)) {
              this.value = 0;
            }
            this.value = parseFloat(this.value) + parseFloat(this.step);
            this.set(this.value);

            return this;
          }
        },
        {
          key: 'enable',
          value: function enable() {
            this.disabled = false;
            this.$wrap.removeClass(this.classes.disabled);
            this.$element.prop('disabled', false);

            if (this.eventBinded === false) {
              this.bindEvent();
            }

            this._trigger('enable');

            return this;
          }
        },
        {
          key: 'disable',
          value: function disable() {
            this.disabled = true;
            this.$element.prop('disabled', true);

            this.$wrap.addClass(this.classes.disabled);
            this.unbindEvent();

            this._trigger('disable');

            return this;
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.unbindEvent();
            this.$control.remove();
            this.$element.unwrap();

            this._trigger('destroy');
            return this;
          }
        }
      ],
      [
        {
          key: 'setDefaults',
          value: function setDefaults(options) {
            _jquery2.default.extend(
              DEFAULTS,
              _jquery2.default.isPlainObject(options) && options
            );
          }
        }
      ]
    );

    return asSpinner;
  })();

  var info = {
    version: '0.4.3'
  };

  var NAMESPACE = 'asSpinner';
  var OtherAsSpinner = _jquery2.default.fn.asSpinner;

  var jQueryAsSpinner = function jQueryAsSpinner(options) {
    for (
      var _len2 = arguments.length,
        args = Array(_len2 > 1 ? _len2 - 1 : 0),
        _key2 = 1;
      _key2 < _len2;
      _key2++
    ) {
      args[_key2 - 1] = arguments[_key2];
    }

    if (typeof options === 'string') {
      var method = options;

      if (/^_/.test(method)) {
        return false;
      } else if (
        /^(get)$/.test(method) ||
        (method === 'val' && args.length === 0)
      ) {
        var instance = this.first().data(NAMESPACE);
        if (instance && typeof instance[method] === 'function') {
          return instance[method].apply(instance, args);
        }
      } else {
        return this.each(function() {
          var instance = _jquery2.default.data(this, NAMESPACE);
          if (instance && typeof instance[method] === 'function') {
            instance[method].apply(instance, args);
          }
        });
      }
    }

    return this.each(function() {
      if (!(0, _jquery2.default)(this).data(NAMESPACE)) {
        (0, _jquery2.default)(this).data(
          NAMESPACE,
          new asSpinner(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.asSpinner = jQueryAsSpinner;

  _jquery2.default.asSpinner = _jquery2.default.extend(
    {
      setDefaults: asSpinner.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asSpinner = OtherAsSpinner;
        return jQueryAsSpinner;
      }
    },
    info
  );
});
