/**
* asRange v0.3.4
* https://github.com/amazingSurge/jquery-asRange
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
    global.jqueryAsRangeEs = mod.exports;
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

  var _typeof =
    typeof Symbol === 'function' && typeof Symbol.iterator === 'symbol'
      ? function(obj) {
          return typeof obj;
        }
      : function(obj) {
          return obj &&
          typeof Symbol === 'function' &&
          obj.constructor === Symbol &&
          obj !== Symbol.prototype
            ? 'symbol'
            : typeof obj;
        };

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
    namespace: 'asRange',
    skin: null,
    max: 100,
    min: 0,
    value: null,
    step: 10,
    limit: true,
    range: false,
    direction: 'h', // 'v' or 'h'
    keyboard: true,
    replaceFirst: false, // false, 'inherit', {'inherit': 'default'}
    tip: true,
    scale: true,
    format: function format(value) {
      return value;
    }
  };

  function getEventObject(event) {
    var e = event.originalEvent;
    if (e.touches && e.touches.length && e.touches[0]) {
      e = e.touches[0];
    }

    return e;
  }

  var Pointer = (function() {
    function Pointer($element, id, parent) {
      _classCallCheck(this, Pointer);

      this.$element = $element;
      this.uid = id;
      this.parent = parent;
      this.options = _jquery2.default.extend(true, {}, this.parent.options);
      this.direction = this.options.direction;
      this.value = null;
      this.classes = {
        active: this.parent.namespace + '-pointer_active'
      };
    }

    _createClass(Pointer, [
      {
        key: 'mousedown',
        value: function mousedown(event) {
          var axis = this.parent.direction.axis;
          var position = this.parent.direction.position;
          var offset = this.parent.$wrap.offset();

          this.$element.trigger(this.parent.namespace + '::moveStart', this);

          this.data = {};
          this.data.start = event[axis];
          this.data.position = event[axis] - offset[position];

          var value = this.parent.getValueFromPosition(this.data.position);
          this.set(value);

          _jquery2.default.each(this.parent.pointer, function(i, p) {
            p.deactive();
          });

          this.active();

          this.mousemove = function(event) {
            var eventObj = getEventObject(event);
            var value = this.parent.getValueFromPosition(
              this.data.position +
                (eventObj[axis] || this.data.start) -
                this.data.start
            );
            this.set(value);

            event.preventDefault();
            return false;
          };
          this.mouseup = function() {
            (0, _jquery2.default)(document).off(
              '.asRange mousemove.asRange touchend.asRange mouseup.asRange touchcancel.asRange'
            );
            this.$element.trigger(this.parent.namespace + '::moveEnd', this);
            return false;
          };

          (0, _jquery2.default)(document)
            .on(
              'touchmove.asRange mousemove.asRange',
              _jquery2.default.proxy(this.mousemove, this)
            )
            .on(
              'touchend.asRange mouseup.asRange',
              _jquery2.default.proxy(this.mouseup, this)
            );
          return false;
        }
      },
      {
        key: 'active',
        value: function active() {
          this.$element.addClass(this.classes.active);
        }
      },
      {
        key: 'deactive',
        value: function deactive() {
          this.$element.removeClass(this.classes.active);
        }
      },
      {
        key: 'set',
        value: function set(value) {
          if (this.value === value) {
            return;
          }

          if (this.parent.step) {
            value = this.matchStep(value);
          }
          if (this.options.limit === true) {
            value = this.matchLimit(value);
          } else {
            if (value <= this.parent.min) {
              value = this.parent.min;
            }
            if (value >= this.parent.max) {
              value = this.parent.max;
            }
          }
          this.value = value;

          this.updatePosition();
          this.$element.focus();

          this.$element.trigger(this.parent.namespace + '::move', this);
        }
      },
      {
        key: 'updatePosition',
        value: function updatePosition() {
          var position = {};

          position[this.parent.direction.position] = this.getPercent() + '%';
          this.$element.css(position);
        }
      },
      {
        key: 'getPercent',
        value: function getPercent() {
          return (this.value - this.parent.min) / this.parent.interval * 100;
        }
      },
      {
        key: 'get',
        value: function get() {
          return this.value;
        }
      },
      {
        key: 'matchStep',
        value: function matchStep(value) {
          var step = this.parent.step;
          var decimal = step.toString().split('.')[1];

          value = Math.round(value / step) * step;

          if (decimal) {
            value = value.toFixed(decimal.length);
          }

          return parseFloat(value);
        }
      },
      {
        key: 'matchLimit',
        value: function matchLimit(value) {
          var left = void 0;
          var right = void 0;
          var pointer = this.parent.pointer;

          if (this.uid === 1) {
            left = this.parent.min;
          } else {
            left = pointer[this.uid - 2].value;
          }

          if (pointer[this.uid] && pointer[this.uid].value !== null) {
            right = pointer[this.uid].value;
          } else {
            right = this.parent.max;
          }

          if (value <= left) {
            value = left;
          }
          if (value >= right) {
            value = right;
          }
          return value;
        }
      },
      {
        key: 'destroy',
        value: function destroy() {
          this.$element.off('.asRange');
          this.$element.remove();
        }
      }
    ]);

    return Pointer;
  })();

  var scale = {
    defaults: {
      scale: {
        valuesNumber: 3,
        gap: 1,
        grid: 5
      }
    },
    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.scale
      );
      var scale = opts.scale;
      scale.values = [];
      scale.values.push(instance.min);
      var part = (instance.max - instance.min) / (scale.valuesNumber - 1);
      for (var j = 1; j <= scale.valuesNumber - 2; j++) {
        scale.values.push(part * j);
      }
      scale.values.push(instance.max);
      var classes = {
        scale: instance.namespace + '-scale',
        lines: instance.namespace + '-scale-lines',
        grid: instance.namespace + '-scale-grid',
        inlineGrid: instance.namespace + '-scale-inlineGrid',
        values: instance.namespace + '-scale-values'
      };

      var len = scale.values.length;
      var num =
        ((scale.grid - 1) * (scale.gap + 1) + scale.gap) * (len - 1) + len;
      var perOfGrid = 100 / (num - 1);
      var perOfValue = 100 / (len - 1);

      this.$scale = (0, _jquery2.default)('<div></div>').addClass(
        classes.scale
      );
      this.$lines = (0, _jquery2.default)('<ul></ul>').addClass(classes.lines);
      this.$values = (0, _jquery2.default)('<ul></ul>').addClass(
        classes.values
      );

      for (var i = 0; i < num; i++) {
        var $list = void 0;
        if (i === 0 || i === num || i % ((num - 1) / (len - 1)) === 0) {
          $list = (0, _jquery2.default)(
            '<li class="' + classes.grid + '"></li>'
          );
        } else if (i % scale.grid === 0) {
          $list = (0, _jquery2.default)(
            '<li class="' + classes.inlineGrid + '"></li>'
          );
        } else {
          $list = (0, _jquery2.default)('<li></li>');
        }

        // position scale
        $list
          .css({
            left: perOfGrid * i + '%'
          })
          .appendTo(this.$lines);
      }

      for (var v = 0; v < len; v++) {
        // position value
        (0, _jquery2.default)('<li><span>' + scale.values[v] + '</span></li>')
          .css({
            left: perOfValue * v + '%'
          })
          .appendTo(this.$values);
      }

      this.$lines.add(this.$values).appendTo(this.$scale);
      this.$scale.appendTo(instance.$wrap);
    },
    update: function update(instance) {
      this.$scale.remove();
      this.init(instance);
    }
  };

  var selected = {
    defaults: {},
    init: function init(instance) {
      var _this = this;

      this.$arrow = (0, _jquery2.default)('<span></span>').appendTo(
        instance.$wrap
      );
      this.$arrow.addClass(instance.namespace + '-selected');

      if (instance.options.range === false) {
        instance.p1.$element.on(instance.namespace + '::move', function(
          e,
          pointer
        ) {
          _this.$arrow.css({
            left: 0,
            width: pointer.getPercent() + '%'
          });
        });
      }

      if (instance.options.range === true) {
        var onUpdate = function onUpdate() {
          var width = instance.p2.getPercent() - instance.p1.getPercent();
          var left = void 0;
          if (width >= 0) {
            left = instance.p1.getPercent();
          } else {
            width = -width;
            left = instance.p2.getPercent();
          }
          _this.$arrow.css({
            left: left + '%',
            width: width + '%'
          });
        };
        instance.p1.$element.on(instance.namespace + '::move', onUpdate);
        instance.p2.$element.on(instance.namespace + '::move', onUpdate);
      }
    }
  };

  var tip = {
    defaults: {
      active: 'always' // 'always' 'onMove'
    },
    init: function init(instance) {
      var that = this;
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.tip
      );

      this.opts = opts;
      this.classes = {
        tip: instance.namespace + '-tip',
        show: instance.namespace + '-tip-show'
      };
      _jquery2.default.each(instance.pointer, function(i, p) {
        var $tip = (0, _jquery2.default)('<span></span>').appendTo(
          instance.pointer[i].$element
        );

        $tip.addClass(that.classes.tip);
        if (that.opts.active === 'onMove') {
          $tip.css({
            display: 'none'
          });
          p.$element
            .on(instance.namespace + '::moveEnd', function() {
              that.hide($tip);
              return false;
            })
            .on(instance.namespace + '::moveStart', function() {
              that.show($tip);
              return false;
            });
        }
        p.$element.on(instance.namespace + '::move', function() {
          var value = void 0;
          if (instance.options.range) {
            value = instance.get()[i];
          } else {
            value = instance.get();
          }
          if (typeof instance.options.format === 'function') {
            if (instance.options.replaceFirst && typeof value !== 'number') {
              if (typeof instance.options.replaceFirst === 'string') {
                value = instance.options.replaceFirst;
              }
              if (_typeof(instance.options.replaceFirst) === 'object') {
                for (var key in instance.options.replaceFirst) {
                  if (
                    Object.hasOwnProperty(instance.options.replaceFirst, key)
                  ) {
                    value = instance.options.replaceFirst[key];
                  }
                }
              }
            } else {
              value = instance.options.format(value);
            }
          }
          $tip.text(value);
          return false;
        });
      });
    },
    show: function show($tip) {
      $tip.addClass(this.classes.show);
      $tip.css({
        display: 'block'
      });
    },
    hide: function hide($tip) {
      $tip.removeClass(this.classes.show);
      $tip.css({
        display: 'none'
      });
    }
  };

  var keyboard = function keyboard() {
    var $doc = (0, _jquery2.default)(document);

    $doc.on('asRange::ready', function(event, instance) {
      var step = void 0;

      var keyboard = {
        keys: {
          UP: 38,
          DOWN: 40,
          LEFT: 37,
          RIGHT: 39,
          RETURN: 13,
          ESCAPE: 27,
          BACKSPACE: 8,
          SPACE: 32
        },
        map: {},
        bound: false,
        press: function press(e) {
          /*eslint consistent-return: "off"*/
          var key = e.keyCode || e.which;
          if (key in keyboard.map && typeof keyboard.map[key] === 'function') {
            keyboard.map[key](e);
            return false;
          }
        },
        attach: function attach(map) {
          var key = void 0;
          var up = void 0;
          for (key in map) {
            if (map.hasOwnProperty(key)) {
              up = key.toUpperCase();
              if (up in keyboard.keys) {
                keyboard.map[keyboard.keys[up]] = map[key];
              } else {
                keyboard.map[up] = map[key];
              }
            }
          }
          if (!keyboard.bound) {
            keyboard.bound = true;
            $doc.bind('keydown', keyboard.press);
          }
        },
        detach: function detach() {
          keyboard.bound = false;
          keyboard.map = {};
          $doc.unbind('keydown', keyboard.press);
        }
      };

      if (instance.options.keyboard === true) {
        _jquery2.default.each(instance.pointer, function(i, p) {
          if (instance.options.step) {
            step = instance.options.step;
          } else {
            step = 1;
          }
          var left = function left() {
            var value = p.value;
            p.set(value - step);
          };
          var right = function right() {
            var value = p.value;
            p.set(value + step);
          };
          p.$element
            .attr('tabindex', '0')
            .on('focus', function() {
              keyboard.attach({
                left: left,
                right: right
              });
              return false;
            })
            .on('blur', function() {
              keyboard.detach();
              return false;
            });
        });
      }
    });
  };

  var components = {};

  /**
   * Plugin constructor
   **/

  var asRange = (function() {
    function asRange(element, options) {
      var _this2 = this;

      _classCallCheck(this, asRange);

      var metas = {};
      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      if (this.$element.is('input')) {
        var value = this.$element.val();

        if (typeof value === 'string') {
          metas.value = value.split(',');
        }

        _jquery2.default.each(['min', 'max', 'step'], function(index, key) {
          var val = parseFloat(_this2.$element.attr(key));
          if (!isNaN(val)) {
            metas[key] = val;
          }
        });

        this.$element.css({
          display: 'none'
        });
        this.$wrap = (0, _jquery2.default)('<div></div>');
        this.$element.after(this.$wrap);
      } else {
        this.$wrap = this.$element;
      }

      this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options,
        this.$element.data(),
        metas
      );
      this.namespace = this.options.namespace;
      this.components = _jquery2.default.extend(true, {}, components);
      if (this.options.range) {
        this.options.replaceFirst = false;
      }

      // public properties
      this.value = this.options.value;
      if (this.value === null) {
        this.value = this.options.min;
      }

      if (!this.options.range) {
        if (_jquery2.default.isArray(this.value)) {
          this.value = this.value[0];
        }
      } else if (!_jquery2.default.isArray(this.value)) {
        this.value = [this.value, this.value];
      } else if (this.value.length === 1) {
        this.value[1] = this.value[0];
      }

      this.min = this.options.min;
      this.max = this.options.max;
      this.step = this.options.step;
      this.interval = this.max - this.min;

      // flag
      this.initialized = false;
      this.updating = false;
      this.disabled = false;

      if (this.options.direction === 'v') {
        this.direction = {
          axis: 'pageY',
          position: 'top'
        };
      } else {
        this.direction = {
          axis: 'pageX',
          position: 'left'
        };
      }

      this.$wrap.addClass(this.namespace);

      if (this.options.skin) {
        this.$wrap.addClass(this.namespace + '_' + this.options.skin);
      }

      if (this.max < this.min || this.step >= this.interval) {
        throw new Error('error options about max min step');
      }

      this.init();
    }

    _createClass(
      asRange,
      [
        {
          key: 'init',
          value: function init() {
            this.$wrap.append('<div class="' + this.namespace + '-bar" />');

            // build pointers
            this.buildPointers();

            // initial components
            this.components.selected.init(this);

            if (this.options.tip !== false) {
              this.components.tip.init(this);
            }
            if (this.options.scale !== false) {
              this.components.scale.init(this);
            }

            // initial pointer value
            this.set(this.value);

            // Bind events
            this.bindEvents();

            this._trigger('ready');
            this.initialized = true;
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
            this.$element.trigger(this.namespace + ('::' + eventType), data);

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
          key: 'buildPointers',
          value: function buildPointers() {
            this.pointer = [];
            var pointerCount = 1;
            if (this.options.range) {
              pointerCount = 2;
            }
            for (var i = 1; i <= pointerCount; i++) {
              var $pointer = (0, _jquery2.default)(
                '<div class="' +
                  this.namespace +
                  '-pointer ' +
                  this.namespace +
                  '-pointer-' +
                  i +
                  '"></div>'
              ).appendTo(this.$wrap);
              var p = new Pointer($pointer, i, this);
              this.pointer.push(p);
            }

            // alias of pointer
            this.p1 = this.pointer[0];

            if (this.options.range) {
              this.p2 = this.pointer[1];
            }
          }
        },
        {
          key: 'bindEvents',
          value: function bindEvents() {
            var _this3 = this;

            var that = this;
            this.$wrap.on('touchstart.asRange mousedown.asRange', function(
              event
            ) {
              /*eslint consistent-return: "off"*/
              if (that.disabled === true) {
                return;
              }
              event = getEventObject(event);
              var rightclick = event.which
                ? event.which === 3
                : event.button === 2;
              if (rightclick) {
                return false;
              }

              var offset = that.$wrap.offset();
              var start =
                event[that.direction.axis] - offset[that.direction.position];
              var p = that.getAdjacentPointer(start);

              p.mousedown(event);
              return false;
            });

            if (this.$element.is('input')) {
              this.$element.on(this.namespace + '::change', function() {
                var value = _this3.get();
                _this3.$element.val(value);
              });
            }

            _jquery2.default.each(this.pointer, function(i, p) {
              p.$element.on(_this3.namespace + '::move', function() {
                that.value = that.get();
                if (!that.initialized || that.updating) {
                  return false;
                }
                that._trigger('change', that.value);
                return false;
              });
            });
          }
        },
        {
          key: 'getValueFromPosition',
          value: function getValueFromPosition(px) {
            if (px > 0) {
              return this.min + px / this.getLength() * this.interval;
            }
            return 0;
          }
        },
        {
          key: 'getAdjacentPointer',
          value: function getAdjacentPointer(start) {
            var value = this.getValueFromPosition(start);
            if (this.options.range) {
              var p1 = this.p1.value;
              var p2 = this.p2.value;
              var diff = Math.abs(p1 - p2);
              if (p1 <= p2) {
                if (value > p1 + diff / 2) {
                  return this.p2;
                }
                return this.p1;
              }

              if (value > p2 + diff / 2) {
                return this.p1;
              }

              return this.p2;
            }
            return this.p1;
          }
        },
        {
          key: 'getLength',
          value: function getLength() {
            if (this.options.direction === 'v') {
              return this.$wrap.height();
            }
            return this.$wrap.width();
          }
        },
        {
          key: 'update',
          value: function update(options) {
            var _this4 = this;

            this.updating = true;
            _jquery2.default.each(
              ['max', 'min', 'step', 'limit', 'value'],
              function(key, value) {
                if (options[value]) {
                  _this4[value] = options[value];
                }
              }
            );
            if (options.max || options.min) {
              this.setInterval(options.min, options.max);
            }

            if (!options.value) {
              this.value = options.min;
            }

            _jquery2.default.each(this.components, function(key, value) {
              if (typeof value.update === 'function') {
                value.update(_this4);
              }
            });

            this.set(this.value);

            this._trigger('update');

            this.updating = false;
          }
        },
        {
          key: 'get',
          value: function get() {
            var value = [];

            _jquery2.default.each(this.pointer, function(i, p) {
              value[i] = p.get();
            });

            if (this.options.range) {
              return value;
            }

            if (value[0] === this.options.min) {
              if (typeof this.options.replaceFirst === 'string') {
                value[0] = this.options.replaceFirst;
              }
              if (_typeof(this.options.replaceFirst) === 'object') {
                for (var key in this.options.replaceFirst) {
                  if (Object.hasOwnProperty(this.options.replaceFirst, key)) {
                    value[0] = key;
                  }
                }
              }
            }

            return value[0];
          }
        },
        {
          key: 'set',
          value: function set(value) {
            if (this.options.range) {
              if (typeof value === 'number') {
                value = [value];
              }
              if (!_jquery2.default.isArray(value)) {
                return;
              }
              _jquery2.default.each(this.pointer, function(i, p) {
                p.set(value[i]);
              });
            } else {
              this.p1.set(value);
            }

            this.value = value;
          }
        },
        {
          key: 'val',
          value: function val(value) {
            if (value) {
              this.set(value);
              return this;
            }
            return this.get();
          }
        },
        {
          key: 'setInterval',
          value: function setInterval(start, end) {
            this.min = start;
            this.max = end;
            this.interval = end - start;
          }
        },
        {
          key: 'enable',
          value: function enable() {
            this.disabled = false;
            this.$wrap.removeClass(this.namespace + '_disabled');

            this._trigger('enable');
            return this;
          }
        },
        {
          key: 'disable',
          value: function disable() {
            this.disabled = true;
            this.$wrap.addClass(this.namespace + '_disabled');

            this._trigger('disable');
            return this;
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            _jquery2.default.each(this.pointer, function(i, p) {
              p.destroy();
            });
            this.$wrap.destroy();

            this._trigger('destroy');
          }
        }
      ],
      [
        {
          key: 'registerComponent',
          value: function registerComponent(component, methods) {
            components[component] = methods;
          }
        },
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

    return asRange;
  })();

  asRange.registerComponent('scale', scale);
  asRange.registerComponent('selected', selected);
  asRange.registerComponent('tip', tip);
  keyboard();

  var info = {
    version: '0.3.4'
  };

  var NAMESPACE = 'asRange';
  var OtherAsRange = _jquery2.default.fn.asRange;

  var jQueryAsRange = function jQueryAsRange(options) {
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
        (0, _jquery2.default)(this).data(NAMESPACE, new asRange(this, options));
      }
    });
  };

  _jquery2.default.fn.asRange = jQueryAsRange;

  _jquery2.default.asRange = _jquery2.default.extend(
    {
      setDefaults: asRange.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asRange = OtherAsRange;
        return jQueryAsRange;
      }
    },
    info
  );
});
