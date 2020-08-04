/**
* jQuery asHoverScroll v0.3.7
* https://github.com/amazingSurge/jquery-asHoverScroll
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
    global.jqueryAsHoverScrollEs = mod.exports;
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
    namespace: 'asHoverScroll',

    list: '> ul',
    item: '> li',
    exception: null,

    direction: 'vertical',
    fixed: false,

    mouseMove: true,
    touchScroll: true,
    pointerScroll: true,

    useCssTransforms: true,
    useCssTransforms3d: true,
    boundary: 10,

    throttle: 20,

    onEnter: function onEnter() {
      $(this)
        .siblings()
        .removeClass('is-active');
      $(this).addClass('is-active');
    },
    onLeave: function onLeave() {
      $(this).removeClass('is-active');
    }
  };

  /**
   * Css features detect
   **/
  var support = {};

  (function(support) {
    /**
     * Borrowed from Owl carousel
     **/
    'use strict';

    var events = {
        transition: {
          end: {
            WebkitTransition: 'webkitTransitionEnd',
            MozTransition: 'transitionend',
            OTransition: 'oTransitionEnd',
            transition: 'transitionend'
          }
        },
        animation: {
          end: {
            WebkitAnimation: 'webkitAnimationEnd',
            MozAnimation: 'animationend',
            OAnimation: 'oAnimationEnd',
            animation: 'animationend'
          }
        }
      },
      prefixes = ['webkit', 'Moz', 'O', 'ms'],
      style = (0, _jquery2.default)('<support>').get(0).style,
      tests = {
        csstransforms: function csstransforms() {
          return Boolean(test('transform'));
        },
        csstransforms3d: function csstransforms3d() {
          return Boolean(test('perspective'));
        },
        csstransitions: function csstransitions() {
          return Boolean(test('transition'));
        },
        cssanimations: function cssanimations() {
          return Boolean(test('animation'));
        }
      };

    var test = function test(property, prefixed) {
      var result = false,
        upper = property.charAt(0).toUpperCase() + property.slice(1);

      if (style[property] !== undefined) {
        result = property;
      }
      if (!result) {
        _jquery2.default.each(prefixes, function(i, prefix) {
          if (style[prefix + upper] !== undefined) {
            result = '-' + prefix.toLowerCase() + '-' + upper;
            return false;
          }
          return true;
        });
      }

      if (prefixed) {
        return result;
      }
      if (result) {
        return true;
      }
      return false;
    };

    var prefixed = function prefixed(property) {
      return test(property, true);
    };

    if (tests.csstransitions()) {
      /*eslint no-new-wrappers: "off"*/
      support.transition = new String(prefixed('transition'));
      support.transition.end = events.transition.end[support.transition];
    }

    if (tests.cssanimations()) {
      /*eslint no-new-wrappers: "off"*/
      support.animation = new String(prefixed('animation'));
      support.animation.end = events.animation.end[support.animation];
    }

    if (tests.csstransforms()) {
      /*eslint no-new-wrappers: "off"*/
      support.transform = new String(prefixed('transform'));
      support.transform3d = tests.csstransforms3d();
    }

    if (
      'ontouchstart' in window ||
      (window.DocumentTouch && document instanceof window.DocumentTouch)
    ) {
      support.touch = true;
    } else {
      support.touch = false;
    }

    if (window.PointerEvent || window.MSPointerEvent) {
      support.pointer = true;
    } else {
      support.pointer = false;
    }

    support.convertMatrixToArray = function(value) {
      if (value && value.substr(0, 6) === 'matrix') {
        return value
          .replace(/^.*\((.*)\)$/g, '$1')
          .replace(/px/g, '')
          .split(/, +/);
      }
      return false;
    };

    support.prefixPointerEvent = function(pointerEvent) {
      var charStart = 9,
        subStart = 10;

      return window.MSPointerEvent
        ? 'MSPointer' +
          pointerEvent.charAt(charStart).toUpperCase() +
          pointerEvent.substr(subStart)
        : pointerEvent;
    };
  })(support);

  var NAMESPACE$1 = 'asHoverScroll';
  var instanceId = 0;

  /**
   * Plugin constructor
   **/

  var asHoverScroll = (function() {
    function asHoverScroll(element, options) {
      _classCallCheck(this, asHoverScroll);

      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options,
        this.$element.data()
      );
      this.$list = (0, _jquery2.default)(this.options.list, this.$element);

      this.classes = {
        disabled: this.options.namespace + '-disabled'
      };

      if (this.options.direction === 'vertical') {
        this.attributes = {
          page: 'pageY',
          axis: 'Y',
          position: 'top',
          length: 'height',
          offset: 'offsetTop',
          client: 'clientY',
          clientLength: 'clientHeight'
        };
      } else if (this.options.direction === 'horizontal') {
        this.attributes = {
          page: 'pageX',
          axis: 'X',
          position: 'left',
          length: 'width',
          offset: 'offsetLeft',
          client: 'clientX',
          clientLength: 'clientWidth'
        };
      }

      // Current state information.
      this._states = {};

      // Current state information for the touch operation.
      this._scroll = {
        time: null,
        pointer: null
      };

      this.instanceId = ++instanceId;

      this.trigger('init');
      this.init();
    }

    _createClass(
      asHoverScroll,
      [
        {
          key: 'init',
          value: function init() {
            this.initPosition();

            // init length data
            this.updateLength();

            this.bindEvents();
          }
        },
        {
          key: 'bindEvents',
          value: function bindEvents() {
            var _this = this;

            var that = this;
            var enterEvents = ['enter'];
            var leaveEvents = [];

            if (this.options.mouseMove) {
              this.$element.on(
                this.eventName('mousemove'),
                _jquery2.default.proxy(this.onMove, this)
              );
              enterEvents.push('mouseenter');
              leaveEvents.push('mouseleave');
            }

            if (this.options.touchScroll && support.touch) {
              this.$element.on(
                this.eventName('touchstart'),
                _jquery2.default.proxy(this.onScrollStart, this)
              );
              this.$element.on(
                this.eventName('touchcancel'),
                _jquery2.default.proxy(this.onScrollEnd, this)
              );
            }

            if (this.options.pointerScroll && support.pointer) {
              this.$element.on(
                this.eventName(support.prefixPointerEvent('pointerdown')),
                _jquery2.default.proxy(this.onScrollStart, this)
              );

              // fixed by FreMaNgo
              // this.$element.on(this.eventName(support.prefixPointerEvent('pointerdown')),(e) => {
              //   let isUp = false;
              //   this.$element.one('pointerup', () => {
              //     isUp = true;
              //   });

              //   window.setTimeout(() => {
              //     if(isUp){
              //       return false;
              //     }else{
              //       this.$element.off('pointerup');
              //       $.proxy(this.onScrollStart, this)(e);
              //     }
              //   }, 100)
              // });
              // fixed by FreMaNgo -- END

              this.$element.on(
                this.eventName(support.prefixPointerEvent('pointercancel')),
                _jquery2.default.proxy(this.onScrollEnd, this)
              );
            }

            this.$list.on(
              this.eventName(enterEvents.join(' ')),
              this.options.item,
              function() {
                if (!that.is('scrolling')) {
                  that.options.onEnter.call(_this);
                }
              }
            );
            this.$list.on(
              this.eventName(leaveEvents.join(' ')),
              this.options.item,
              function() {
                if (!that.is('scrolling')) {
                  that.options.onLeave.call(_this);
                }
              }
            );

            (0, _jquery2.default)(window).on(
              this.eventNameWithId('orientationchange'),
              function() {
                that.update();
              }
            );
            (0, _jquery2.default)(window).on(
              this.eventNameWithId('resize'),
              this.throttle(function() {
                that.update();
              }, this.options.throttle)
            );
          }
        },
        {
          key: 'unbindEvents',
          value: function unbindEvents() {
            this.$element.off(this.eventName());
            this.$list.off(this.eventName());
            (0, _jquery2.default)(window).off(this.eventNameWithId());
          }
        },
        {
          key: 'onScrollStart',
          value: function onScrollStart(event) {
            var _this2 = this;

            var that = this;
            if (this.is('scrolling')) {
              return;
            }

            if (event.which === 3) {
              return;
            }

            if (
              (0, _jquery2.default)(event.target).closest(
                this.options.exception
              ).length > 0
            ) {
              return;
            }

            this._scroll.time = new Date().getTime();
            this._scroll.pointer = this.pointer(event);
            this._scroll.start = this.getPosition();
            this._scroll.moved = false;

            var callback = function callback() {
              _this2.enter('scrolling');
              _this2.trigger('scroll');
            };

            if (this.options.touchScroll && support.touch) {
              (0, _jquery2.default)(document).on(
                this.eventName('touchend'),
                _jquery2.default.proxy(this.onScrollEnd, this)
              );

              (0, _jquery2.default)(document).one(
                this.eventName('touchmove'),
                _jquery2.default.proxy(function() {
                  if (!this.is('scrolling')) {
                    (0, _jquery2.default)(document).on(
                      that.eventName('touchmove'),
                      _jquery2.default.proxy(this.onScrollMove, this)
                    );
                    callback();
                  }
                }, this)
              );
            }

            if (this.options.pointerScroll && support.pointer) {
              (0, _jquery2.default)(document).on(
                this.eventName(support.prefixPointerEvent('pointerup')),
                _jquery2.default.proxy(this.onScrollEnd, this)
              );

              (0, _jquery2.default)(document).one(
                this.eventName(support.prefixPointerEvent('pointermove')),
                _jquery2.default.proxy(function() {
                  if (!this.is('scrolling')) {
                    (0, _jquery2.default)(document).on(
                      that.eventName(support.prefixPointerEvent('pointermove')),
                      _jquery2.default.proxy(this.onScrollMove, this)
                    );

                    callback();
                  }
                }, this)
              );
            }

            (0, _jquery2.default)(document).on(
              this.eventName('blur'),
              _jquery2.default.proxy(this.onScrollEnd, this)
            );

            event.preventDefault();
          }
        },
        {
          key: 'onScrollMove',
          value: function onScrollMove(event) {
            this._scroll.updated = this.pointer(event);
            var distance = this.distance(
              this._scroll.pointer,
              this._scroll.updated
            );

            if (
              Math.abs(this._scroll.pointer.x - this._scroll.updated.x) > 10 ||
              Math.abs(this._scroll.pointer.y - this._scroll.updated.y) > 10
            ) {
              this._scroll.moved = true;
            }

            if (!this.is('scrolling')) {
              return;
            }

            event.preventDefault();
            var postion = this._scroll.start + distance;

            if (this.canScroll()) {
              if (postion > 0) {
                postion = 0;
              } else if (postion < this.containerLength - this.listLength) {
                postion = this.containerLength - this.listLength;
              }
              this.updatePosition(postion);
            }
          }
        },
        {
          key: 'onScrollEnd',
          value: function onScrollEnd(event) {
            if (!this._scroll.moved) {
              (0, _jquery2.default)(event.target).trigger('tap');
            }

            // if (!this.is('scrolling')) {
            //   return;
            // }

            if (this.options.touchScroll && support.touch) {
              (0, _jquery2.default)(document).off(
                this.eventName('touchmove touchend')
              );
            }

            if (this.options.pointerScroll && support.pointer) {
              (0, _jquery2.default)(document).off(
                this.eventName(
                  support.prefixPointerEvent('pointermove pointerup')
                )
              );
            }

            (0, _jquery2.default)(document).off(this.eventName('blur'));

            // touch will trigger mousemove event after 300ms delay. So we need avoid it
            // setTimeout(() => {
            this.leave('scrolling');
            this.trigger('scrolled');
            // }, 500);
          }
        },
        {
          key: 'pointer',
          value: function pointer(event) {
            var result = {
              x: null,
              y: null
            };

            event = this.getEvent(event);

            if (event.pageX && !this.options.fixed) {
              result.x = event.pageX;
              result.y = event.pageY;
            } else {
              result.x = event.clientX;
              result.y = event.clientY;
            }

            return result;
          }
        },
        {
          key: 'getEvent',
          value: function getEvent(event) {
            event = event.originalEvent || event || window.event;

            event =
              event.touches && event.touches.length
                ? event.touches[0]
                : event.changedTouches && event.changedTouches.length
                  ? event.changedTouches[0]
                  : event;

            return event;
          }
        },
        {
          key: 'distance',
          value: function distance(first, second) {
            if (this.options.direction === 'vertical') {
              return second.y - first.y;
            }
            return second.x - first.x;
          }
        },
        {
          key: 'onMove',
          value: function onMove(event) {
            event = this.getEvent(event);

            if (this.is('scrolling')) {
              return;
            }

            if (this.isMatchScroll(event)) {
              var pointer = void 0;
              var distance = void 0;
              var offset = void 0;
              if (event[this.attributes.page] && !this.options.fixed) {
                pointer = event[this.attributes.page];
              } else {
                pointer = event[this.attributes.client];
              }

              offset = pointer - this.element[this.attributes.offset];

              if (offset < this.options.boundary) {
                distance = 0;
              } else {
                distance = (offset - this.options.boundary) * this.multiplier;

                if (distance > this.listLength - this.containerLength) {
                  distance = this.listLength - this.containerLength;
                }
              }

              this.updatePosition(-distance);
            }
          }
        },
        {
          key: 'isMatchScroll',
          value: function isMatchScroll(event) {
            if (!this.is('disabled') && this.canScroll()) {
              if (this.options.exception) {
                if (
                  (0, _jquery2.default)(event.target).closest(
                    this.options.exception
                  ).length === 0
                ) {
                  return true;
                }
                return false;
              }
              return true;
            }
            return false;
          }
        },
        {
          key: 'canScroll',
          value: function canScroll() {
            return this.listLength > this.containerLength;
          }
        },
        {
          key: 'getContainerLength',
          value: function getContainerLength() {
            return this.element[this.attributes.clientLength];
          }
        },
        {
          key: 'getListhLength',
          value: function getListhLength() {
            return this.$list[0][this.attributes.clientLength];
          }
        },
        {
          key: 'updateLength',
          value: function updateLength() {
            this.containerLength = this.getContainerLength();
            this.listLength = this.getListhLength();
            this.multiplier =
              (this.listLength - this.containerLength) /
              (this.containerLength - 2 * this.options.boundary);
          }
        },
        {
          key: 'initPosition',
          value: function initPosition() {
            var style = this.makePositionStyle(0);
            this.$list.css(style);
          }
        },
        {
          key: 'getPosition',
          value: function getPosition() {
            var value = void 0;

            if (this.options.useCssTransforms && support.transform) {
              if (this.options.useCssTransforms3d && support.transform3d) {
                value = support.convertMatrixToArray(
                  this.$list.css(support.transform)
                );
              } else {
                value = support.convertMatrixToArray(
                  this.$list.css(support.transform)
                );
              }
              if (!value) {
                return 0;
              }

              if (this.attributes.axis === 'X') {
                value = value[12] || value[4];
              } else {
                value = value[13] || value[5];
              }
            } else {
              value = this.$list.css(this.attributes.position);
            }

            return parseFloat(value.replace('px', ''));
          }
        },
        {
          key: 'makePositionStyle',
          value: function makePositionStyle(value) {
            var property = void 0;
            var x = '0px';
            var y = '0px';

            if (this.options.useCssTransforms && support.transform) {
              if (this.attributes.axis === 'X') {
                x = value + 'px';
              } else {
                y = value + 'px';
              }

              property = support.transform.toString();

              if (this.options.useCssTransforms3d && support.transform3d) {
                value = 'translate3d(' + x + ',' + y + ',0px)';
              } else {
                value = 'translate(' + x + ',' + y + ')';
              }
            } else {
              property = this.attributes.position;
            }
            var temp = {};
            temp[property] = value;

            return temp;
          }
        },
        {
          key: 'updatePosition',
          value: function updatePosition(value) {
            var style = this.makePositionStyle(value);
            this.$list.css(style);
          }
        },
        {
          key: 'update',
          value: function update() {
            if (!this.is('disabled')) {
              this.updateLength();

              if (!this.canScroll()) {
                this.initPosition();
              }
            }
          }
        },
        {
          key: 'eventName',
          value: function eventName(events) {
            if (typeof events !== 'string' || events === '') {
              return '.' + NAMESPACE$1;
            }
            events = events.split(' ');

            var length = events.length;
            for (var i = 0; i < length; i++) {
              events[i] = events[i] + '.' + NAMESPACE$1;
            }
            return events.join(' ');
          }
        },
        {
          key: 'eventNameWithId',
          value: function eventNameWithId(events) {
            if (typeof events !== 'string' || events === '') {
              return '.' + this.options.namespace + '-' + this.instanceId;
            }

            events = events.split(' ');
            var length = events.length;
            for (var i = 0; i < length; i++) {
              events[i] =
                events[i] +
                '.' +
                this.options.namespace +
                '-' +
                this.instanceId;
            }
            return events.join(' ');
          }
        },
        {
          key: 'trigger',
          value: function trigger(eventType) {
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
          key: 'enter',
          value: function enter(state) {
            if (this._states[state] === undefined) {
              this._states[state] = 0;
            }

            this._states[state] = 1;
          }
        },
        {
          key: 'leave',
          value: function leave(state) {
            this._states[state] = 0;
          }
        },
        {
          key: 'throttle',
          value: function throttle(func, wait) {
            var _this3 = this;

            var _now =
              Date.now ||
              function() {
                return new Date().getTime();
              };

            var timeout = void 0;
            var context = void 0;
            var args = void 0;
            var result = void 0;
            var previous = 0;
            var later = function later() {
              previous = _now();
              timeout = null;
              result = func.apply(context, args);
              if (!timeout) {
                context = args = null;
              }
            };

            return function() {
              for (
                var _len2 = arguments.length, params = Array(_len2), _key2 = 0;
                _key2 < _len2;
                _key2++
              ) {
                params[_key2] = arguments[_key2];
              }

              /*eslint consistent-this: "off"*/
              var now = _now();
              var remaining = wait - (now - previous);
              context = _this3;
              args = params;
              if (remaining <= 0 || remaining > wait) {
                if (timeout) {
                  clearTimeout(timeout);
                  timeout = null;
                }
                previous = now;
                result = func.apply(context, args);
                if (!timeout) {
                  context = args = null;
                }
              } else if (!timeout) {
                timeout = setTimeout(later, remaining);
              }
              return result;
            };
          }
        },
        {
          key: 'enable',
          value: function enable() {
            if (this.is('disabled')) {
              this.leave('disabled');

              this.$element.removeClass(this.classes.disabled);

              this.bindEvents();
            }

            this.trigger('enable');
          }
        },
        {
          key: 'disable',
          value: function disable() {
            if (!this.is('disabled')) {
              this.enter('disabled');

              this.initPosition();
              this.$element.addClass(this.classes.disabled);

              this.unbindEvents();
            }

            this.trigger('disable');
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.$element.removeClass(this.classes.disabled);
            this.unbindEvents();
            this.$element.data(NAMESPACE$1, null);

            this.trigger('destroy');
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

    return asHoverScroll;
  })();

  var info = {
    version: '0.3.7'
  };

  var NAMESPACE = 'asHoverScroll';
  var OtherAsHoverScroll = _jquery2.default.fn.asHoverScroll;

  var jQueryAsHoverScroll = function jQueryAsHoverScroll(options) {
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
          new asHoverScroll(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.asHoverScroll = jQueryAsHoverScroll;

  _jquery2.default.asHoverScroll = _jquery2.default.extend(
    {
      setDefaults: asHoverScroll.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asHoverScroll = OtherAsHoverScroll;
        return jQueryAsHoverScroll;
      }
    },
    info
  );
});
