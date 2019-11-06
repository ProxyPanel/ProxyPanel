/**
* jQuery asProgress v0.2.4
* https://github.com/amazingSurge/jquery-asProgress
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
    global.jqueryAsProgressEs = mod.exports;
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
    namespace: 'progress',
    bootstrap: false,
    min: 0,
    max: 100,
    goal: 100,
    speed: 20, // speed of 1/100
    easing: 'ease',
    labelCallback: function labelCallback(n) {
      var percentage = this.getPercentage(n);
      return percentage + '%';
    }
  };

  var easingBezier = function easingBezier(mX1, mY1, mX2, mY2) {
    'use strict';

    var a = function a(aA1, aA2) {
      return 1.0 - 3.0 * aA2 + 3.0 * aA1;
    };

    var b = function b(aA1, aA2) {
      return 3.0 * aA2 - 6.0 * aA1;
    };

    var c = function c(aA1) {
      return 3.0 * aA1;
    };

    // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
    var calcBezier = function calcBezier(aT, aA1, aA2) {
      return ((a(aA1, aA2) * aT + b(aA1, aA2)) * aT + c(aA1)) * aT;
    };

    // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
    var getSlope = function getSlope(aT, aA1, aA2) {
      return 3.0 * a(aA1, aA2) * aT * aT + 2.0 * b(aA1, aA2) * aT + c(aA1);
    };

    var getTForX = function getTForX(aX) {
      // Newton raphson iteration
      var aGuessT = aX;
      for (var i = 0; i < 4; ++i) {
        var currentSlope = getSlope(aGuessT, mX1, mX2);
        if (currentSlope === 0.0) {
          return aGuessT;
        }
        var currentX = calcBezier(aGuessT, mX1, mX2) - aX;
        aGuessT -= currentX / currentSlope;
      }
      return aGuessT;
    };

    if (mX1 === mY1 && mX2 === mY2) {
      return {
        css: 'linear',
        fn: function fn(aX) {
          return aX;
        }
      };
    }

    return {
      css: 'cubic-bezier(' + mX1 + ',' + mY1 + ',' + mX2 + ',' + mY2 + ')',
      fn: function fn(aX) {
        return calcBezier(getTForX(aX), mY1, mY2);
      }
    };
  };

  var EASING = {
    ease: easingBezier(0.25, 0.1, 0.25, 1.0),
    linear: easingBezier(0.0, 0.0, 1.0, 1.0),
    'ease-in': easingBezier(0.42, 0.0, 1.0, 1.0),
    'ease-out': easingBezier(0.0, 0.0, 0.58, 1.0),
    'ease-in-out': easingBezier(0.42, 0.0, 0.58, 1.0)
  };

  if (!Date.now) {
    Date.now = function() {
      return new Date().getTime();
    };
  }

  var vendors = ['webkit', 'moz'];
  for (var i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
    var vp = vendors[i];
    window.requestAnimationFrame = window[vp + 'RequestAnimationFrame'];
    window.cancelAnimationFrame =
      window[vp + 'CancelAnimationFrame'] ||
      window[vp + 'CancelRequestAnimationFrame'];
  }

  if (
    /iP(ad|hone|od).*OS (6|7)/.test(window.navigator.userAgent) || // iOS6 is buggy
    !window.requestAnimationFrame ||
    !window.cancelAnimationFrame
  ) {
    var lastTime = 0;
    window.requestAnimationFrame = function(callback) {
      var now = Date.now();
      var nextTime = Math.max(lastTime + 16, now);
      return setTimeout(function() {
        callback((lastTime = nextTime));
      }, nextTime - now);
    };
    window.cancelAnimationFrame = clearTimeout;
  }

  function isPercentage(n) {
    return typeof n === 'string' && n.includes('%');
  }

  function getTime() {
    if (typeof window.performance !== 'undefined' && window.performance.now) {
      return window.performance.now();
    }
    return Date.now();
  }

  var NAMESPACE$1 = 'asProgress';

  /**
   * Plugin constructor
   **/

  var asProgress = (function() {
    function asProgress(element, options) {
      _classCallCheck(this, asProgress);

      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options,
        this.$element.data()
      );

      if (this.options.bootstrap) {
        this.namespace = 'progress';

        this.$target = this.$element.find('.progress-bar');

        this.classes = {
          label: this.namespace + '-label',
          bar: this.namespace + '-bar',
          disabled: 'is-disabled'
        };
      } else {
        this.namespace = this.options.namespace;

        this.classes = {
          label: this.namespace + '__label',
          bar: this.namespace + '__bar',
          disabled: 'is-disabled'
        };

        this.$target = this.$element;

        this.$element.addClass(this.namespace);
      }

      this.easing = EASING[this.options.easing] || EASING.ease;

      this.min = this.$target.attr('aria-valuemin');
      this.max = this.$target.attr('aria-valuemax');
      this.min = this.min ? parseInt(this.min, 10) : this.options.min;
      this.max = this.max ? parseInt(this.max, 10) : this.options.max;
      this.first = this.$target.attr('aria-valuenow');
      this.first = this.first ? parseInt(this.first, 10) : this.min;

      this.now = this.first;
      this.goal = this.options.goal;
      this._frameId = null;

      // Current state information.
      this._states = {};

      this.initialized = false;
      this._trigger('init');
      this.init();
    }

    _createClass(
      asProgress,
      [
        {
          key: 'init',
          value: function init() {
            this.$bar = this.$element.find('.' + this.classes.bar);
            this.$label = this.$element.find('.' + this.classes.label);

            this.reset();
            this.initialized = true;
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
          key: 'is',
          value: function is(state) {
            return this._states[state] && this._states[state] > 0;
          }
        },
        {
          key: 'getPercentage',
          value: function getPercentage(n) {
            return Math.round(100 * (n - this.min) / (this.max - this.min));
          }
        },
        {
          key: 'go',
          value: function go(goal) {
            if (!this.is('disabled')) {
              var that = this;
              this._clear();

              if (isPercentage(goal)) {
                goal = parseInt(goal.replace('%', ''), 10);
                goal = Math.round(
                  this.min + goal / 100 * (this.max - this.min)
                );
              }
              if (typeof goal === 'undefined') {
                goal = this.goal;
              }

              if (goal > this.max) {
                goal = this.max;
              } else if (goal < this.min) {
                goal = this.min;
              }

              var start = that.now;
              var startTime = getTime();
              var animation = function animation(time) {
                var distance = (time - startTime) / that.options.speed;
                var next = Math.round(
                  that.easing.fn(distance / 100) * (that.max - that.min)
                );

                if (goal > start) {
                  next = start + next;
                  if (next > goal) {
                    next = goal;
                  }
                } else {
                  next = start - next;
                  if (next < goal) {
                    next = goal;
                  }
                }

                that._update(next);
                if (next === goal) {
                  window.cancelAnimationFrame(that._frameId);
                  that._frameId = null;

                  if (that.now === that.goal) {
                    that._trigger('finish');
                  }
                } else {
                  that._frameId = window.requestAnimationFrame(animation);
                }
              };

              that._frameId = window.requestAnimationFrame(animation);
            }
          }
        },
        {
          key: '_update',
          value: function _update(n) {
            this.now = n;

            var percenage = this.getPercentage(this.now);
            this.$bar.css('width', percenage + '%');
            this.$target.attr('aria-valuenow', this.now);
            if (
              this.$label.length > 0 &&
              typeof this.options.labelCallback === 'function'
            ) {
              this.$label.html(
                this.options.labelCallback.call(this, [this.now])
              );
            }

            this._trigger('update', n);
          }
        },
        {
          key: '_clear',
          value: function _clear() {
            if (this._frameId) {
              window.cancelAnimationFrame(this._frameId);
              this._frameId = null;
            }
          }
        },
        {
          key: 'get',
          value: function get() {
            return this.now;
          }
        },
        {
          key: 'start',
          value: function start() {
            if (!this.is('disabled')) {
              this._clear();
              this._trigger('start');
              this.go(this.goal);
            }
          }
        },
        {
          key: 'reset',
          value: function reset() {
            if (!this.is('disabled')) {
              this._clear();
              this._update(this.first);
              this._trigger('reset');
            }
          }
        },
        {
          key: 'stop',
          value: function stop() {
            this._clear();
            this._trigger('stop');
          }
        },
        {
          key: 'finish',
          value: function finish() {
            if (!this.is('disabled')) {
              this._clear();
              this._update(this.goal);
              this._trigger('finish');
            }
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.$element.data(NAMESPACE$1, null);
            this._trigger('destroy');
          }
        },
        {
          key: 'enable',
          value: function enable() {
            this._states.disabled = 0;

            this.$element.removeClass(this.classes.disabled);
          }
        },
        {
          key: 'disable',
          value: function disable() {
            this._states.disabled = 1;

            this.$element.addClass(this.classes.disabled);
          }
        }
      ],
      [
        {
          key: 'registerEasing',
          value: function registerEasing(name) {
            for (
              var _len2 = arguments.length,
                args = Array(_len2 > 1 ? _len2 - 1 : 0),
                _key2 = 1;
              _key2 < _len2;
              _key2++
            ) {
              args[_key2 - 1] = arguments[_key2];
            }

            EASING[name] = easingBezier.apply(undefined, args);
          }
        },
        {
          key: 'getEasing',
          value: function getEasing(name) {
            return EASING[name];
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

    return asProgress;
  })();

  var info = {
    version: '0.2.4'
  };

  var NAMESPACE = 'asProgress';
  var OtherAsProgress = _jquery2.default.fn.asProgress;

  var jQueryAsProgress = function jQueryAsProgress(options) {
    for (
      var _len3 = arguments.length,
        args = Array(_len3 > 1 ? _len3 - 1 : 0),
        _key3 = 1;
      _key3 < _len3;
      _key3++
    ) {
      args[_key3 - 1] = arguments[_key3];
    }

    if (typeof options === 'string') {
      var method = options;

      if (/^_/.test(method)) {
        return false;
      } else if (/^(get)/.test(method)) {
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
          new asProgress(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.asProgress = jQueryAsProgress;

  _jquery2.default.asProgress = _jquery2.default.extend(
    {
      setDefaults: asProgress.setDefaults,
      registerEasing: asProgress.registerEasing,
      getEasing: asProgress.getEasing,
      noConflict: function noConflict() {
        _jquery2.default.fn.asProgress = OtherAsProgress;
        return jQueryAsProgress;
      }
    },
    info
  );
});
