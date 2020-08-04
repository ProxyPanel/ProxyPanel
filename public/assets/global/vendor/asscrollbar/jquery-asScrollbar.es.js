/**
* jQuery asScrollbar v0.5.7
* https://github.com/amazingSurge/jquery-asScrollbar
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

var DEFAULTS = {
  namespace: 'asScrollbar',

  skin: null,
  handleSelector: null,
  handleTemplate: '<div class="{{handle}}"></div>',

  barClass: null,
  handleClass: null,

  disabledClass: 'is-disabled',
  draggingClass: 'is-dragging',
  hoveringClass: 'is-hovering',

  direction: 'vertical',

  barLength: null,
  handleLength: null,

  minHandleLength: 30,
  maxHandleLength: null,

  mouseDrag: true,
  touchDrag: true,
  pointerDrag: true,
  clickMove: true,
  clickMoveStep: 0.3, // 0 - 1
  mousewheel: true,
  mousewheelSpeed: 50,

  keyboard: true,

  useCssTransforms3d: true,
  useCssTransforms: true,
  useCssTransitions: true,

  duration: '500',
  easing: 'ease' // linear, ease-in, ease-out, ease-in-out
};

const easingBezier = (mX1, mY1, mX2, mY2) => {
  'use strict';

  const a = (aA1, aA2) => {
    return 1.0 - 3.0 * aA2 + 3.0 * aA1;
  };

  const b = (aA1, aA2) => {
    return 3.0 * aA2 - 6.0 * aA1;
  };

  const c = (aA1) => {
    return 3.0 * aA1;
  };

  // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
  const calcBezier = (aT, aA1, aA2) => {
    return ((a(aA1, aA2) * aT + b(aA1, aA2)) * aT + c(aA1)) * aT;
  };

  // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
  const getSlope = (aT, aA1, aA2) => {
    return 3.0 * a(aA1, aA2) * aT * aT + 2.0 * b(aA1, aA2) * aT + c(aA1);
  };

  const getTForX = (aX) => {
    // Newton raphson iteration
    let aGuessT = aX;
    for (let i = 0; i < 4; ++i) {
      let currentSlope = getSlope(aGuessT, mX1, mX2);
      if (currentSlope === 0.0) {
        return aGuessT;
      }
      let currentX = calcBezier(aGuessT, mX1, mX2) - aX;
      aGuessT -= currentX / currentSlope;
    }
    return aGuessT;
  };

  if (mX1 === mY1 && mX2 === mY2) {
    return {
      css: 'linear',
      fn(aX) {
        return aX;
      }
    };
  }

  return {
    css: `cubic-bezier(${mX1},${mY1},${mX2},${mY2})`,
    fn(aX) {
      return calcBezier(getTForX(aX), mY1, mY2);
    }
  };
};

var EASING = {
  ease: easingBezier(0.25, 0.1, 0.25, 1.0),
  linear: easingBezier(0.00, 0.0, 1.00, 1.0),
  'ease-in': easingBezier(0.42, 0.0, 1.00, 1.0),
  'ease-out': easingBezier(0.00, 0.0, 0.58, 1.0),
  'ease-in-out': easingBezier(0.42, 0.0, 0.58, 1.0)
};

if (!Date.now) {
  Date.now = () => {
    return new Date().getTime();
  };
}

const vendors = ['webkit', 'moz'];
for (let i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
  let vp = vendors[i];
  window.requestAnimationFrame = window[`${vp}RequestAnimationFrame`];
  window.cancelAnimationFrame = (window[`${vp}CancelAnimationFrame`] || window[`${vp}CancelRequestAnimationFrame`]);
}

if (/iP(ad|hone|od).*OS (6|7|8)/.test(window.navigator.userAgent) || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
  let lastTime = 0;
  window.requestAnimationFrame = (callback) => {
    let now = getTime();
    let timePlus = 16;
    let nextTime = Math.max(lastTime + timePlus, now);
    return setTimeout(() => {
        callback(lastTime = nextTime);
      },
      nextTime - now);
  };
  window.cancelAnimationFrame = clearTimeout;
}

function isPercentage(n) {
  return typeof n === 'string' && n.indexOf('%') !== -1;
}

function convertPercentageToFloat(n) {
  return parseFloat(n.slice(0, -1) / 100, 10);
}

function convertMatrixToArray(value) {
  if (value && (value.substr(0, 6) === 'matrix')) {
    return value.replace(/^.*\((.*)\)$/g, '$1').replace(/px/g, '').split(/, +/);
  }
  return false;
}

function getTime () {
  if (typeof window.performance !== 'undefined' && window.performance.now) {
    return window.performance.now();
  }
  return Date.now();
}

/**
 * Css features detect
 **/
let support = {};

((support) => {
  /**
   * Borrowed from Owl carousel
   **/
   'use strict';

  const events = {
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
    style = $('<support>').get(0).style,
    tests = {
      csstransforms() {
        return Boolean(test('transform'));
      },
      csstransforms3d() {
        return Boolean(test('perspective'));
      },
      csstransitions() {
        return Boolean(test('transition'));
      },
      cssanimations() {
        return Boolean(test('animation'));
      }
    };

  const test = (property, prefixed) => {
    let result = false,
      upper = property.charAt(0).toUpperCase() + property.slice(1);

    if (style[property] !== undefined) {
      result = property;
    }
    if (!result) {
      $.each(prefixes, (i, prefix) => {
        if (style[prefix + upper] !== undefined) {
          result = `-${prefix.toLowerCase()}-${upper}`;
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

  const prefixed = (property) => {
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

  if (('ontouchstart' in window) || window.DocumentTouch && document instanceof window.DocumentTouch) {
    support.touch = true;
  } else {
    support.touch = false;
  }

  if (window.PointerEvent || window.MSPointerEvent) {
    support.pointer = true;
  } else {
    support.pointer = false;
  }

  support.prefixPointerEvent = (pointerEvent) => {
    return window.MSPointerEvent ?
      `MSPointer${pointerEvent.charAt(9).toUpperCase()}${pointerEvent.substr(10)}` :
      pointerEvent;
  };
})(support);

const NAMESPACE$1 = 'asScrollbar';

/**
 * Plugin constructor
 **/
class asScrollbar {
  constructor(bar, options = {}) {
    this.$bar = $(bar);
    options = this.options = $.extend({}, DEFAULTS, options, this.$bar.data('options') || {});
    bar.direction = this.options.direction;

    this.classes = {
      directionClass: `${options.namespace}-${options.direction}`,
      barClass: options.barClass? options.barClass: options.namespace,
      handleClass: options.handleClass? options.handleClass: `${options.namespace}-handle`
    };

    if (this.options.direction === 'vertical') {
      this.attributes = {
        axis: 'Y',
        position: 'top',
        length: 'height',
        clientLength: 'clientHeight'
      };
    } else if (this.options.direction === 'horizontal') {
      this.attributes = {
        axis: 'X',
        position: 'left',
        length: 'width',
        clientLength: 'clientWidth'
      };
    }

    // Current state information.
    this._states = {};

    // Current state information for the drag operation.
    this._drag = {
      time: null,
      pointer: null
    };

    // Current timeout
    this._frameId = null;

    // Current handle position
    this.handlePosition = 0;

    this.easing = EASING[this.options.easing] || EASING.ease;

    this.init();
  }

  init() {
    let options = this.options;

    this.$handle = this.$bar.find(this.options.handleSelector);
    if (this.$handle.length === 0) {
      this.$handle = $(options.handleTemplate.replace(/\{\{handle\}\}/g, this.classes.handleClass)).appendTo(this.$bar);
    } else {
      this.$handle.addClass(this.classes.handleClass);
    }

    this.$bar.addClass(this.classes.barClass).addClass(this.classes.directionClass).attr('draggable', false);

    if (options.skin) {
      this.$bar.addClass(options.skin);
    }
    if (options.barLength !== null) {
      this.setBarLength(options.barLength);
    }

    if (options.handleLength !== null) {
      this.setHandleLength(options.handleLength);
    }

    this.updateLength();

    this.bindEvents();

    this.trigger('ready');
  }

  trigger(eventType, ...params) {
    let data = [this].concat(params);

    // event
    this.$bar.trigger(`${NAMESPACE$1}::${eventType}`, data);

    // callback
    eventType = eventType.replace(/\b\w+\b/g, (word) => {
      return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
    let onFunction = `on${eventType}`;

    if (typeof this.options[onFunction] === 'function') {
      this.options[onFunction].apply(this, params);
    }
  }

  /**
   * Checks whether the carousel is in a specific state or not.
   */
  is(state) {
    return this._states[state] && this._states[state] > 0;
  }

  /**
   * Enters a state.
   */
  enter(state) {
    if (this._states[state] === undefined) {
      this._states[state] = 0;
    }

    this._states[state]++;
  }

  /**
   * Leaves a state.
   */
  leave(state) {
    this._states[state]--;
  }

  eventName(events) {
    if (typeof events !== 'string' || events === '') {
      return `.${this.options.namespace}`;
    }
    events = events.split(' ');

    let length = events.length;
    for (let i = 0; i < length; i++) {
      events[i] = `${events[i]}.${this.options.namespace}`;
    }
    return events.join(' ');
  }

  bindEvents() {
    if (this.options.mouseDrag) {
      this.$handle.on(this.eventName('mousedown'), $.proxy(this.onDragStart, this));
      this.$handle.on(this.eventName('dragstart selectstart'), () => {
        return false;
      });
    }

    if (this.options.touchDrag && support.touch) {
      this.$handle.on(this.eventName('touchstart'), $.proxy(this.onDragStart, this));
      this.$handle.on(this.eventName('touchcancel'), $.proxy(this.onDragEnd, this));
    }

    if (this.options.pointerDrag && support.pointer) {
      this.$handle.on(this.eventName(support.prefixPointerEvent('pointerdown')), $.proxy(this.onDragStart, this));
      this.$handle.on(this.eventName(support.prefixPointerEvent('pointercancel')), $.proxy(this.onDragEnd, this));
    }

    if (this.options.clickMove) {
      this.$bar.on(this.eventName('mousedown'), $.proxy(this.onClick, this));
    }

    if (this.options.mousewheel) {
      this.$bar.on('mousewheel', (e) => {
        let delta;
        if (this.options.direction === 'vertical') {
          delta = e.deltaFactor * e.deltaY;
        } else if (this.options.direction === 'horizontal') {
          delta = -1 * e.deltaFactor * e.deltaX;
        }
        let offset = this.getHandlePosition();
        if (offset <= 0 && delta > 0) {
          return true;
        } else if (offset >= this.barLength && delta < 0) {
          return true;
        }
        offset -= this.options.mousewheelSpeed * delta;
        this.move(offset, true);
        return false;
      });
    }

    this.$bar.on(this.eventName('mouseenter'), () => {
      this.$bar.addClass(this.options.hoveringClass);
      this.enter('hovering');
      this.trigger('hover');
    });

    this.$bar.on(this.eventName('mouseleave'), () => {
      this.$bar.removeClass(this.options.hoveringClass);

      if (!this.is('hovering')) {
        return;
      }
      this.leave('hovering');
      this.trigger('hovered');
    });

    if (this.options.keyboard) {
      $(document).on(this.eventName('keydown'), (e) => {
        if (e.isDefaultPrevented && e.isDefaultPrevented()) {
          return;
        }

        if (!this.is('hovering')) {
          return;
        }
        let activeElement = document.activeElement;
        // go deeper if element is a webcomponent
        while (activeElement.shadowRoot) {
          activeElement = activeElement.shadowRoot.activeElement;
        }
        if ($(activeElement).is(':input,select,option,[contenteditable]')) {
          return;
        }
        let by = 0,
          to = null;

        let down = 40,
          end = 35,
          home = 36,
          left = 37,
          pageDown = 34,
          pageUp = 33,
          right = 39,
          spaceBar = 32,
          up = 38;

        let webkitDown = 63233,
          webkitEnd = 63275,
          webkitHome = 63273,
          webkitLeft = 63234,
          webkitPageDown = 63277,
          webkitPageUp = 63276,
          webkitRight = 63235,
          webkitUp = 63232;

        switch (e.which) {
          case left: // left
          case webkitUp:
            by = -30;
            break;
          case up: // up
          case webkitDown:
            by = -30;
            break;
          case right: // right
          case webkitLeft:
            by = 30;
            break;
          case down: // down
          case webkitRight:
            by = 30;
            break;
          case pageUp: // page up
          case webkitPageUp:
            by = -90;
            break;
          case spaceBar: // space bar
          case pageDown: // page down
          case webkitPageDown:
            by = -90;
            break;
          case end: // end
          case webkitEnd:
            to = '100%';
            break;
          case home: // home
          case webkitHome:
            to = 0;
            break;
          default:
            return;
        }

        if (by || to !== null) {
          if (by) {
            this.moveBy(by, true);
          } else if (to !== null) {
            this.moveTo(to, true);
          }
          e.preventDefault();
        }
      });
    }
  }

  onClick(event) {
    let num = 3;

    if (event.which === num) {
      return;
    }

    if (event.target === this.$handle[0]) {
      return;
    }

    this._drag.time = new Date().getTime();
    this._drag.pointer = this.pointer(event);

    let offset = this.$handle.offset();
    let distance = this.distance({
        x: offset.left,
        y: offset.top
      }, this._drag.pointer),
      factor = 1;

    if (distance > 0) {
      distance -= this.handleLength;
    } else {
      distance = Math.abs(distance);
      factor = -1;
    }

    if (distance > this.barLength * this.options.clickMoveStep) {
      distance = this.barLength * this.options.clickMoveStep;
    }
    this.moveBy(factor * distance, true);
  }

  /**
   * Handles `touchstart` and `mousedown` events.
   */
  onDragStart(event) {
    let num = 3;
    if (event.which === num) {
      return;
    }

    // this.$bar.toggleClass(this.options.draggingClass, event.type === 'mousedown');
    this.$bar.addClass(this.options.draggingClass);

    this._drag.time = new Date().getTime();
    this._drag.pointer = this.pointer(event);

    let callback = () => {
      this.enter('dragging');
      this.trigger('drag');
    };

    if (this.options.mouseDrag) {
      $(document).on(this.eventName('mouseup'), $.proxy(this.onDragEnd, this));

      $(document).one(this.eventName('mousemove'), $.proxy(() => {
        $(document).on(this.eventName('mousemove'), $.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    if (this.options.touchDrag && support.touch) {
      $(document).on(this.eventName('touchend'), $.proxy(this.onDragEnd, this));

      $(document).one(this.eventName('touchmove'), $.proxy(() => {
        $(document).on(this.eventName('touchmove'), $.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    if (this.options.pointerDrag && support.pointer) {
      $(document).on(this.eventName(support.prefixPointerEvent('pointerup')), $.proxy(this.onDragEnd, this));

      $(document).one(this.eventName(support.prefixPointerEvent('pointermove')), $.proxy(() => {
        $(document).on(this.eventName(support.prefixPointerEvent('pointermove')), $.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    $(document).on(this.eventName('blur'), $.proxy(this.onDragEnd, this));
  }


  /**
   * Handles the `touchmove` and `mousemove` events.
   */
  onDragMove(event) {
    let distance = this.distance(this._drag.pointer, this.pointer(event));

    if (!this.is('dragging')) {
      return;
    }

    event.preventDefault();
    this.moveBy(distance, true);
  }


  /**
   * Handles the `touchend` and `mouseup` events.
   */
  onDragEnd() {
    $(document).off(this.eventName('mousemove mouseup touchmove touchend pointermove pointerup MSPointerMove MSPointerUp blur'));

    this.$bar.removeClass(this.options.draggingClass);
    this.handlePosition = this.getHandlePosition();

    if (!this.is('dragging')) {
      return;
    }

    this.leave('dragging');
    this.trigger('dragged');
  }

  /**
   * Gets unified pointer coordinates from event.
   * @returns {Object} - Contains `x` and `y` coordinates of current pointer position.
   */
  pointer(event) {
    let result = {
      x: null,
      y: null
    };

    event = event.originalEvent || event || window.event;

    event = event.touches && event.touches.length ?
      event.touches[0] : event.changedTouches && event.changedTouches.length ?
      event.changedTouches[0] : event;

    if (event.pageX) {
      result.x = event.pageX;
      result.y = event.pageY;
    } else {
      result.x = event.clientX;
      result.y = event.clientY;
    }

    return result;
  }

  /**
   * Gets the distance of two pointer.
   */
  distance(first, second) {
    if (this.options.direction === 'vertical') {
      return second.y - first.y;
    }
    return second.x - first.x;
  }

  setBarLength(length, update) {
    if (typeof length !== 'undefined') {
      this.$bar.css(this.attributes.length, length);
    }
    if (update !== false) {
      this.updateLength();
    }
  }

  setHandleLength(length, update) {
    if (typeof length !== 'undefined') {
      if (length < this.options.minHandleLength) {
        length = this.options.minHandleLength;
      } else if (this.options.maxHandleLength && length > this.options.maxHandleLength) {
        length = this.options.maxHandleLength;
      }
      this.$handle.css(this.attributes.length, length);

      if (update !== false) {
        this.updateLength(length);
      }
    }
  }

  updateLength(length, barLength) {
    if (typeof length !== 'undefined') {
      this.handleLength = length;
    } else {
      this.handleLength = this.getHandleLenght();
    }
    if (typeof barLength !== 'undefined') {
      this.barLength = barLength;
    } else {
      this.barLength = this.getBarLength();
    }
  }

  getBarLength() {
    return this.$bar[0][this.attributes.clientLength];
  }

  getHandleLenght() {
    return this.$handle[0][this.attributes.clientLength];
  }

  getHandlePosition() {
    let value;

    if (this.options.useCssTransforms && support.transform) {
      value = convertMatrixToArray(this.$handle.css(support.transform));

      if (!value) {
        return 0;
      }

      if (this.attributes.axis === 'X') {
        value = value[12] || value[4];
      } else {
        value = value[13] || value[5];
      }
    } else {
      value = this.$handle.css(this.attributes.position);
    }

    return parseFloat(value.replace('px', ''));
  }

  makeHandlePositionStyle(value) {
    let property, x = '0',
      y = '0';

    if (this.options.useCssTransforms && support.transform) {
      if (this.attributes.axis === 'X') {
        x = `${value}px`;
      } else {
        y = `${value}px`;
      }

      property = support.transform.toString();

      if (this.options.useCssTransforms3d && support.transform3d) {
        value = `translate3d(${x},${y},0)`;
      } else {
        value = `translate(${x},${y})`;
      }
    } else {
      property = this.attributes.position;
    }
    let temp = {};
    temp[property] = value;

    return temp;
  }

  setHandlePosition(value) {
    let style = this.makeHandlePositionStyle(value);
    this.$handle.css(style);

    if (!this.is('dragging')) {
      this.handlePosition = parseFloat(value);
    }
  }

  moveTo(value, trigger, sync) {
    let type = typeof value;

    if (type === 'string') {
      if (isPercentage(value)) {
        value = convertPercentageToFloat(value) * (this.barLength - this.handleLength);
      }

      value = parseFloat(value);
      type = 'number';
    }

    if (type !== 'number') {
      return;
    }

    this.move(value, trigger, sync);
  }

  moveBy(value, trigger, sync) {
    let type = typeof value;

    if (type === 'string') {
      if (isPercentage(value)) {
        value = convertPercentageToFloat(value) * (this.barLength - this.handleLength);
      }

      value = parseFloat(value);
      type = 'number';
    }

    if (type !== 'number') {
      return;
    }

    this.move(this.handlePosition + value, trigger, sync);
  }

  move(value, trigger, sync) {
    if (typeof value !== 'number' || this.is('disabled')) {
      return;
    }
    if (value < 0) {
      value = 0;
    } else if (value + this.handleLength > this.barLength) {
      value = this.barLength - this.handleLength;
    }

    if (!this.is('dragging') && sync !== true) {
      this.doMove(value, this.options.duration, this.options.easing, trigger);
    } else {
      this.setHandlePosition(value);

      if (trigger) {
        this.trigger('change', value / (this.barLength - this.handleLength));
      }
    }
  }

  doMove(value, duration, easing, trigger) {
    let property;
    this.enter('moving');
    duration = duration ? duration : this.options.duration;
    easing = easing ? easing : this.options.easing;

    let style = this.makeHandlePositionStyle(value);
    for (property in style) {
      if ({}.hasOwnProperty.call(style, property)) {
        break;
      }
    }

    if (this.options.useCssTransitions && support.transition) {
      this.enter('transition');
      this.prepareTransition(property, duration, easing);

      this.$handle.one(support.transition.end, () => {
        this.$handle.css(support.transition, '');

        if (trigger) {
          this.trigger('change', value / (this.barLength - this.handleLength));
        }
        this.leave('transition');
        this.leave('moving');
      });

      this.setHandlePosition(value);
    } else {
      this.enter('animating');

      let startTime = getTime();
      let start = this.getHandlePosition();
      let end = value;

      let run = (time) => {
        let percent = (time - startTime) / this.options.duration;

        if (percent > 1) {
          percent = 1;
        }

        percent = this.easing.fn(percent);
        let scale = 10;
        let current = parseFloat(start + percent * (end - start), scale);
        this.setHandlePosition(current);

        if (trigger) {
          this.trigger('change', current / (this.barLength - this.handleLength));
        }

        if (percent === 1) {
          window.cancelAnimationFrame(this._frameId);
          this._frameId = null;

          this.leave('animating');
          this.leave('moving');
        } else {
          this._frameId = window.requestAnimationFrame(run);
        }
      };

      this._frameId = window.requestAnimationFrame(run);
    }
  }

  prepareTransition(property, duration, easing, delay) {
    let temp = [];
    if (property) {
      temp.push(property);
    }
    if (duration) {
      if ($.isNumeric(duration)) {
        duration = `${duration}ms`;
      }
      temp.push(duration);
    }
    if (easing) {
      temp.push(easing);
    } else {
      temp.push(this.easing.css);
    }
    if (delay) {
      temp.push(delay);
    }
    this.$handle.css(support.transition, temp.join(' '));
  }

  enable() {
    this._states.disabled = 0;

    this.$bar.removeClass(this.options.disabledClass);

    this.trigger('enable');
  }

  disable() {
    this._states.disabled = 1;

    this.$bar.addClass(this.options.disabledClass);

    this.trigger('disable');
  }

  destroy() {
    this.$handle.removeClass(this.classes.handleClass);
    this.$bar.removeClass(this.classes.barClass).removeClass(this.classes.directionClass).attr('draggable', null);
    if (this.options.skin) {
      this.$bar.removeClass(this.options.skin);
    }
    this.$bar.off(this.eventName());
    this.$handle.off(this.eventName());

    this.trigger('destroy');
  }

  static registerEasing(name, ...args) {
    EASING[name] = easingBezier(...args);
  }

  static getEasing(name) {
    return EASING[name];
  }

  static setDefaults(options) {
    $.extend(DEFAULTS, $.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.5.7'
};

const NAMESPACE = 'asScrollbar';
const OtherAsScrollbar = $.fn.asScrollbar;

const jQueryAsScrollbar = function(options, ...args) {
  if (typeof options === 'string') {
    const method = options;

    if (/^_/.test(method)) {
      return false;
    } else if ((/^(get)/.test(method))) {
      const instance = this.first().data(NAMESPACE);
      if (instance && typeof instance[method] === 'function') {
        return instance[method](...args);
      }
    } else {
      return this.each(function() {
        const instance = $.data(this, NAMESPACE);
        if (instance && typeof instance[method] === 'function') {
          instance[method](...args);
        }
      });
    }
  }

  return this.each(function() {
    if (!$(this).data(NAMESPACE)) {
      $(this).data(NAMESPACE, new asScrollbar(this, options));
    }
  });
};

$.fn.asScrollbar = jQueryAsScrollbar;

$.asScrollbar = $.extend({
  setDefaults: asScrollbar.setDefaults,
  registerEasing: asScrollbar.registerEasing,
  getEasing: asScrollbar.getEasing,
  noConflict: function() {
    $.fn.asScrollbar = OtherAsScrollbar;
    return jQueryAsScrollbar;
  }
}, info);
