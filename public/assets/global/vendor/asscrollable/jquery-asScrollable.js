/**
* jQuery asScrollable v0.4.10
* https://github.com/amazingSurge/jquery-asScrollable
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
    global.jqueryAsScrollableEs = mod.exports;
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
    namespace: 'asScrollable',

    skin: null,

    contentSelector: null,
    containerSelector: null,

    enabledClass: 'is-enabled',
    disabledClass: 'is-disabled',

    draggingClass: 'is-dragging',
    hoveringClass: 'is-hovering',
    scrollingClass: 'is-scrolling',

    direction: 'vertical', // vertical, horizontal, both, auto

    showOnHover: true,
    showOnBarHover: false,

    duration: 500,
    easing: 'ease-in', // linear, ease, ease-in, ease-out, ease-in-out

    responsive: true,
    throttle: 20,

    scrollbar: {}
  };

  function getTime() {
    if (typeof window.performance !== 'undefined' && window.performance.now) {
      return window.performance.now();
    }
    return Date.now();
  }

  function isPercentage(n) {
    return typeof n === 'string' && n.indexOf('%') !== -1;
  }

  function conventToPercentage(n) {
    if (n < 0) {
      n = 0;
    } else if (n > 1) {
      n = 1;
    }
    return parseFloat(n).toFixed(4) * 100 + '%';
  }

  function convertPercentageToFloat(n) {
    return parseFloat(n.slice(0, -1) / 100, 10);
  }

  var isFFLionScrollbar = (function() {
    'use strict';

    var isOSXFF = void 0,
      ua = void 0,
      version = void 0;
    ua = window.navigator.userAgent;
    isOSXFF = /(?=.+Mac OS X)(?=.+Firefox)/.test(ua);
    if (!isOSXFF) {
      return false;
    }
    version = /Firefox\/\d{2}\./.exec(ua);
    if (version) {
      version = version[0].replace(/\D+/g, '');
    }
    return isOSXFF && +version > 23;
  })();

  var NAMESPACE$1 = 'asScrollable';

  var instanceId = 0;

  var AsScrollable = (function() {
    function AsScrollable(element, options) {
      _classCallCheck(this, AsScrollable);

      this.$element = (0, _jquery2.default)(element);
      options = this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options || {},
        this.$element.data('options') || {}
      );

      this.classes = {
        wrap: options.namespace,
        content: options.namespace + '-content',
        container: options.namespace + '-container',
        bar: options.namespace + '-bar',
        barHide: options.namespace + '-bar-hide',
        skin: options.skin
      };

      this.attributes = {
        vertical: {
          axis: 'Y',
          overflow: 'overflow-y',

          scroll: 'scrollTop',
          scrollLength: 'scrollHeight',
          pageOffset: 'pageYOffset',

          ffPadding: 'padding-right',

          length: 'height',
          clientLength: 'clientHeight',
          offset: 'offsetHeight',

          crossLength: 'width',
          crossClientLength: 'clientWidth',
          crossOffset: 'offsetWidth'
        },
        horizontal: {
          axis: 'X',
          overflow: 'overflow-x',

          scroll: 'scrollLeft',
          scrollLength: 'scrollWidth',
          pageOffset: 'pageXOffset',

          ffPadding: 'padding-bottom',

          length: 'width',
          clientLength: 'clientWidth',
          offset: 'offsetWidth',

          crossLength: 'height',
          crossClientLength: 'clientHeight',
          crossOffset: 'offsetHeight'
        }
      };

      // Current state information.
      this._states = {};

      // Supported direction
      this.horizontal = null;
      this.vertical = null;

      this.$bar = null;

      // Current timeout
      this._frameId = null;
      this._timeoutId = null;

      this.instanceId = ++instanceId;

      this.easing =
        _jquery2.default.asScrollbar.getEasing(this.options.easing) ||
        _jquery2.default.asScrollbar.getEasing('ease');

      this.init();
    }

    _createClass(
      AsScrollable,
      [
        {
          key: 'init',
          value: function init() {
            var position = this.$element.css('position');

            if (this.options.containerSelector) {
              this.$container = this.$element.find(
                this.options.containerSelector
              );
              this.$wrap = this.$element;

              if (position === 'static') {
                this.$wrap.css('position', 'relative');
              }
            } else {
              this.$container = this.$element.wrap('<div>');
              this.$wrap = this.$container.parent();
              this.$wrap.height(this.$element.height());

              if (position !== 'static') {
                this.$wrap.css('position', position);
              } else {
                this.$wrap.css('position', 'relative');
              }
            }

            if (this.options.contentSelector) {
              this.$content = this.$container.find(
                this.options.contentSelector
              );
            } else {
              this.$content = this.$container.wrap('<div>');
              this.$container = this.$content.parent();
            }

            switch (this.options.direction) {
              case 'vertical': {
                this.vertical = true;
                break;
              }
              case 'horizontal': {
                this.horizontal = true;
                break;
              }
              case 'both': {
                this.horizontal = true;
                this.vertical = true;
                break;
              }
              case 'auto': {
                var overflowX = this.$element.css('overflow-x'),
                  overflowY = this.$element.css('overflow-y');

                if (overflowX === 'scroll' || overflowX === 'auto') {
                  this.horizontal = true;
                }
                if (overflowY === 'scroll' || overflowY === 'auto') {
                  this.vertical = true;
                }
                break;
              }
              default: {
                break;
              }
            }

            if (!this.vertical && !this.horizontal) {
              return;
            }

            this.$wrap.addClass(this.classes.wrap);
            this.$container.addClass(this.classes.container);
            this.$content.addClass(this.classes.content);

            if (this.options.skin) {
              this.$wrap.addClass(this.classes.skin);
            }

            this.$wrap.addClass(this.options.enabledClass);

            if (this.vertical) {
              this.$wrap.addClass(this.classes.wrap + '-vertical');
              this.initLayout('vertical');
              this.createBar('vertical');
            }

            if (this.horizontal) {
              this.$wrap.addClass(this.classes.wrap + '-horizontal');
              this.initLayout('horizontal');
              this.createBar('horizontal');
            }

            this.bindEvents();

            this.trigger('ready');
          }
        },
        {
          key: 'bindEvents',
          value: function bindEvents() {
            var _this = this;

            if (this.options.responsive) {
              (0, _jquery2.default)(window).on(
                this.eventNameWithId('orientationchange'),
                function() {
                  _this.update();
                }
              );
              (0, _jquery2.default)(window).on(
                this.eventNameWithId('resize'),
                this.throttle(function() {
                  _this.update();
                }, this.options.throttle)
              );
            }

            if (!this.horizontal && !this.vertical) {
              return;
            }

            var that = this;

            this.$wrap.on(this.eventName('mouseenter'), function() {
              that.$wrap.addClass(_this.options.hoveringClass);
              that.enter('hovering');
              that.trigger('hover');
            });

            this.$wrap.on(this.eventName('mouseleave'), function() {
              that.$wrap.removeClass(_this.options.hoveringClass);

              if (!that.is('hovering')) {
                return;
              }
              that.leave('hovering');
              that.trigger('hovered');
            });

            if (this.options.showOnHover) {
              if (this.options.showOnBarHover) {
                this.$bar
                  .on('asScrollbar::hover', function() {
                    if (that.horizontal) {
                      that.showBar('horizontal');
                    }
                    if (that.vertical) {
                      that.showBar('vertical');
                    }
                  })
                  .on('asScrollbar::hovered', function() {
                    if (that.horizontal) {
                      that.hideBar('horizontal');
                    }
                    if (that.vertical) {
                      that.hideBar('vertical');
                    }
                  });
              } else {
                this.$element.on(
                  NAMESPACE$1 + '::hover',
                  _jquery2.default.proxy(this.showBar, this)
                );
                this.$element.on(
                  NAMESPACE$1 + '::hovered',
                  _jquery2.default.proxy(this.hideBar, this)
                );
              }
            }

            this.$container.on(this.eventName('scroll'), function() {
              if (that.horizontal) {
                var oldLeft = that.offsetLeft;
                that.offsetLeft = that.getOffset('horizontal');

                if (oldLeft !== that.offsetLeft) {
                  that.trigger(
                    'scroll',
                    that.getPercentOffset('horizontal'),
                    'horizontal'
                  );

                  if (that.offsetLeft === 0) {
                    that.trigger('scrolltop', 'horizontal');
                  }
                  if (that.offsetLeft === that.getScrollLength('horizontal')) {
                    that.trigger('scrollend', 'horizontal');
                  }
                }
              }

              if (that.vertical) {
                var oldTop = that.offsetTop;

                that.offsetTop = that.getOffset('vertical');

                if (oldTop !== that.offsetTop) {
                  that.trigger(
                    'scroll',
                    that.getPercentOffset('vertical'),
                    'vertical'
                  );

                  if (that.offsetTop === 0) {
                    that.trigger('scrolltop', 'vertical');
                  }
                  if (that.offsetTop === that.getScrollLength('vertical')) {
                    that.trigger('scrollend', 'vertical');
                  }
                }
              }
            });

            this.$element.on(NAMESPACE$1 + '::scroll', function(
              e,
              api,
              value,
              direction
            ) {
              if (!that.is('scrolling')) {
                that.enter('scrolling');
                that.$wrap.addClass(that.options.scrollingClass);
              }
              var bar = api.getBarApi(direction);

              bar.moveTo(conventToPercentage(value), false, true);

              clearTimeout(that._timeoutId);
              that._timeoutId = setTimeout(function() {
                that.$wrap.removeClass(that.options.scrollingClass);
                that.leave('scrolling');
              }, 200);
            });

            this.$bar.on('asScrollbar::change', function(e, api, value) {
              if (typeof e.target.direction === 'string') {
                that.scrollTo(
                  e.target.direction,
                  conventToPercentage(value),
                  false,
                  true
                );
              }
            });

            this.$bar
              .on('asScrollbar::drag', function() {
                that.$wrap.addClass(that.options.draggingClass);
              })
              .on('asScrollbar::dragged', function() {
                that.$wrap.removeClass(that.options.draggingClass);
              });
          }
        },
        {
          key: 'unbindEvents',
          value: function unbindEvents() {
            this.$wrap.off(this.eventName());
            this.$element
              .off(NAMESPACE$1 + '::scroll')
              .off(NAMESPACE$1 + '::hover')
              .off(NAMESPACE$1 + '::hovered');
            this.$container.off(this.eventName());
            (0, _jquery2.default)(window).off(this.eventNameWithId());
          }
        },
        {
          key: 'initLayout',
          value: function initLayout(direction) {
            if (direction === 'vertical') {
              this.$container.css('height', this.$wrap.height());
            }
            var attributes = this.attributes[direction],
              container = this.$container[0];

            // this.$container.css(attributes.overflow, 'scroll');

            var parentLength =
                container.parentNode[attributes.crossClientLength],
              scrollbarWidth = this.getBrowserScrollbarWidth(direction);

            this.$content.css(attributes.crossLength, parentLength + 'px');
            this.$container.css(
              attributes.crossLength,
              scrollbarWidth + parentLength + 'px'
            );

            if (scrollbarWidth === 0 && isFFLionScrollbar) {
              this.$container.css(attributes.ffPadding, 16);
            }
          }
        },
        {
          key: 'createBar',
          value: function createBar(direction) {
            var options = _jquery2.default.extend(this.options.scrollbar, {
              namespace: this.classes.bar,
              direction: direction,
              useCssTransitions: false,
              keyboard: false
            });
            var $bar = (0, _jquery2.default)('<div>');
            $bar.asScrollbar(options);

            if (this.options.showOnHover) {
              $bar.addClass(this.classes.barHide);
            }

            $bar.appendTo(this.$wrap);

            this['$' + direction] = $bar;

            if (this.$bar === null) {
              this.$bar = $bar;
            } else {
              this.$bar = this.$bar.add($bar);
            }

            this.updateBarHandle(direction);
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

            // this._states[state]++;
            this._states[state] = 1;
          }
        },
        {
          key: 'leave',
          value: function leave(state) {
            // this._states[state]--;

            this._states[state] = -1;
          }
        },
        {
          key: 'eventName',
          value: function eventName(events) {
            if (typeof events !== 'string' || events === '') {
              return '.' + this.options.namespace;
            }

            events = events.split(' ');
            var length = events.length;
            for (var i = 0; i < length; i++) {
              events[i] = events[i] + '.' + this.options.namespace;
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
          key: 'throttle',
          value: function throttle(func, wait) {
            var _this2 = this;

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
              context = _this2;
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
          key: 'getBrowserScrollbarWidth',
          value: function getBrowserScrollbarWidth(direction) {
            var attributes = this.attributes[direction],
              outer = void 0,
              outerStyle = void 0;
            if (attributes.scrollbarWidth) {
              return attributes.scrollbarWidth;
            }
            outer = document.createElement('div');
            outerStyle = outer.style;
            outerStyle.position = 'absolute';
            outerStyle.width = '100px';
            outerStyle.height = '100px';
            outerStyle.overflow = 'scroll';
            outerStyle.top = '-9999px';
            document.body.appendChild(outer);
            attributes.scrollbarWidth =
              outer[attributes.offset] - outer[attributes.clientLength];
            document.body.removeChild(outer);
            return attributes.scrollbarWidth;
          }
        },
        {
          key: 'getOffset',
          value: function getOffset(direction) {
            var attributes = this.attributes[direction],
              container = this.$container[0];

            return (
              container[attributes.pageOffset] || container[attributes.scroll]
            );
          }
        },
        {
          key: 'getPercentOffset',
          value: function getPercentOffset(direction) {
            return this.getOffset(direction) / this.getScrollLength(direction);
          }
        },
        {
          key: 'getContainerLength',
          value: function getContainerLength(direction) {
            return this.$container[0][this.attributes[direction].clientLength];
          }
        },
        {
          key: 'getScrollLength',
          value: function getScrollLength(direction) {
            var scrollLength = this.$content[0][
              this.attributes[direction].scrollLength
            ];
            return scrollLength - this.getContainerLength(direction);
          }
        },
        {
          key: 'scrollTo',
          value: function scrollTo(direction, value, trigger, sync) {
            var type =
              typeof value === 'undefined' ? 'undefined' : _typeof(value);

            if (type === 'string') {
              if (isPercentage(value)) {
                value =
                  convertPercentageToFloat(value) *
                  this.getScrollLength(direction);
              }

              value = parseFloat(value);
              type = 'number';
            }

            if (type !== 'number') {
              return;
            }

            this.move(direction, value, trigger, sync);
          }
        },
        {
          key: 'scrollBy',
          value: function scrollBy(direction, value, trigger, sync) {
            var type =
              typeof value === 'undefined' ? 'undefined' : _typeof(value);

            if (type === 'string') {
              if (isPercentage(value)) {
                value =
                  convertPercentageToFloat(value) *
                  this.getScrollLength(direction);
              }

              value = parseFloat(value);
              type = 'number';
            }

            if (type !== 'number') {
              return;
            }

            this.move(
              direction,
              this.getOffset(direction) + value,
              trigger,
              sync
            );
          }
        },
        {
          key: 'move',
          value: function move(direction, value, trigger, sync) {
            if (this[direction] !== true || typeof value !== 'number') {
              return;
            }

            this.enter('moving');

            if (value < 0) {
              value = 0;
            } else if (value > this.getScrollLength(direction)) {
              value = this.getScrollLength(direction);
            }

            var attributes = this.attributes[direction];

            var that = this;
            var callback = function callback() {
              that.leave('moving');
            };

            if (sync) {
              this.$container[0][attributes.scroll] = value;

              if (trigger !== false) {
                this.trigger(
                  'change',
                  value / this.getScrollLength(direction),
                  direction
                );
              }
              callback();
            } else {
              this.enter('animating');

              var startTime = getTime();
              var start = this.getOffset(direction);
              var end = value;

              var run = function run(time) {
                var percent = (time - startTime) / that.options.duration;

                if (percent > 1) {
                  percent = 1;
                }

                percent = that.easing.fn(percent);

                var current = parseFloat(start + percent * (end - start), 10);
                that.$container[0][attributes.scroll] = current;

                if (trigger !== false) {
                  that.trigger(
                    'change',
                    value / that.getScrollLength(direction),
                    direction
                  );
                }

                if (percent === 1) {
                  window.cancelAnimationFrame(that._frameId);
                  that._frameId = null;

                  that.leave('animating');
                  callback();
                } else {
                  that._frameId = window.requestAnimationFrame(run);
                }
              };

              this._frameId = window.requestAnimationFrame(run);
            }
          }
        },
        {
          key: 'scrollXto',
          value: function scrollXto(value, trigger, sync) {
            return this.scrollTo('horizontal', value, trigger, sync);
          }
        },
        {
          key: 'scrollYto',
          value: function scrollYto(value, trigger, sync) {
            return this.scrollTo('vertical', value, trigger, sync);
          }
        },
        {
          key: 'scrollXby',
          value: function scrollXby(value, trigger, sync) {
            return this.scrollBy('horizontal', value, trigger, sync);
          }
        },
        {
          key: 'scrollYby',
          value: function scrollYby(value, trigger, sync) {
            return this.scrollBy('vertical', value, trigger, sync);
          }
        },
        {
          key: 'getBar',
          value: function getBar(direction) {
            if (direction && this['$' + direction]) {
              return this['$' + direction];
            }
            return this.$bar;
          }
        },
        {
          key: 'getBarApi',
          value: function getBarApi(direction) {
            return this.getBar(direction).data('asScrollbar');
          }
        },
        {
          key: 'getBarX',
          value: function getBarX() {
            return this.getBar('horizontal');
          }
        },
        {
          key: 'getBarY',
          value: function getBarY() {
            return this.getBar('vertical');
          }
        },
        {
          key: 'showBar',
          value: function showBar(direction) {
            this.getBar(direction).removeClass(this.classes.barHide);
          }
        },
        {
          key: 'hideBar',
          value: function hideBar(direction) {
            this.getBar(direction).addClass(this.classes.barHide);
          }
        },
        {
          key: 'updateBarHandle',
          value: function updateBarHandle(direction) {
            var api = this.getBarApi(direction);

            if (!api) {
              return;
            }

            var containerLength = this.getContainerLength(direction),
              scrollLength = this.getScrollLength(direction);

            if (scrollLength > 0) {
              if (api.is('disabled')) {
                api.enable();
              }
              api.setHandleLength(
                api.getBarLength() *
                  containerLength /
                  (scrollLength + containerLength),
                true
              );
            } else {
              api.disable();
            }
          }
        },
        {
          key: 'disable',
          value: function disable() {
            if (!this.is('disabled')) {
              this.enter('disabled');
              this.$wrap
                .addClass(this.options.disabledClass)
                .removeClass(this.options.enabledClass);

              this.unbindEvents();
              this.unStyle();
            }

            this.trigger('disable');
          }
        },
        {
          key: 'enable',
          value: function enable() {
            if (this.is('disabled')) {
              this.leave('disabled');
              this.$wrap
                .addClass(this.options.enabledClass)
                .removeClass(this.options.disabledClass);

              this.bindEvents();
              this.update();
            }

            this.trigger('enable');
          }
        },
        {
          key: 'update',
          value: function update() {
            if (this.is('disabled')) {
              return;
            }
            if (this.$element.is(':visible')) {
              if (this.vertical) {
                this.initLayout('vertical');
                this.updateBarHandle('vertical');
              }
              if (this.horizontal) {
                this.initLayout('horizontal');
                this.updateBarHandle('horizontal');
              }
            }
          }
        },
        {
          key: 'unStyle',
          value: function unStyle() {
            if (this.horizontal) {
              this.$container.css({
                height: '',
                'padding-bottom': ''
              });
              this.$content.css({
                height: ''
              });
            }
            if (this.vertical) {
              this.$container.css({
                width: '',
                height: '',
                'padding-right': ''
              });
              this.$content.css({
                width: ''
              });
            }
            if (!this.options.containerSelector) {
              this.$wrap.css({
                height: ''
              });
            }
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.$wrap
              .removeClass(this.classes.wrap + '-vertical')
              .removeClass(this.classes.wrap + '-horizontal')
              .removeClass(this.classes.wrap)
              .removeClass(this.options.enabledClass)
              .removeClass(this.classes.disabledClass);
            this.unStyle();

            if (this.$bar) {
              this.$bar.remove();
            }

            this.unbindEvents();

            if (this.options.containerSelector) {
              this.$container.removeClass(this.classes.container);
            } else {
              this.$container.unwrap();
            }
            if (!this.options.contentSelector) {
              this.$content.unwrap();
            }
            this.$content.removeClass(this.classes.content);
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

    return AsScrollable;
  })();

  var info = {
    version: '0.4.10'
  };

  var NAMESPACE = 'asScrollable';
  var OtherAsScrollable = _jquery2.default.fn.asScrollable;

  var jQueryAsScrollable = function jQueryAsScrollable(options) {
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
          new AsScrollable(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.asScrollable = jQueryAsScrollable;

  _jquery2.default.asScrollable = _jquery2.default.extend(
    {
      setDefaults: AsScrollable.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asScrollable = OtherAsScrollable;
        return jQueryAsScrollable;
      }
    },
    info
  );
});
