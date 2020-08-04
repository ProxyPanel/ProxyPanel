/**
* jQuery slidePanel v0.3.5
* https://github.com/amazingSurge/jquery-slidePanel
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $$1 from 'jquery';

var info = {
  version:'0.3.5'
};

function convertMatrixToArray(value) {
    if (value && (value.substr(0, 6) === 'matrix')) {
    return value.replace(/^.*\((.*)\)$/g, '$1').replace(/px/g, '').split(/, +/);
  }
  return false;
}

function getHashCode(object) {
  /* eslint no-bitwise: "off" */
  if (typeof object !== 'string') {
    object = JSON.stringify(object);
  }

  let chr, hash = 0,
    i, len;
  if (object.length === 0) {
    return hash;
  }
  for (i = 0, len = object.length; i < len; i++) {
    chr = object.charCodeAt(i);
    hash = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }

  return hash;
}

function getTime() {
  if (typeof window.performance !== 'undefined' && window.performance.now) {
    return window.performance.now();
  }
  return Date.now();
}

function isPercentage(n) {
  return typeof n === 'string' && n.indexOf('%') !== -1;
}

function isPx(n) {
  return typeof n === 'string' && n.indexOf('px') !== -1;
}

/* eslint no-unused-vars: "off" */
var DEFAULTS = {
  skin: null,

  classes: {
    base: 'slidePanel',
    show: 'slidePanel-show',
    loading: 'slidePanel-loading',
    content: 'slidePanel-content',
    dragging: 'slidePanel-dragging',
    willClose: 'slidePanel-will-close'
  },

  closeSelector: null,

  template(options) {
    return `<div class="${options.classes.base} ${options.classes.base}-${options.direction}"><div class="${options.classes.content}"></div></div>`;
  },

  loading: {
    appendTo: 'panel', // body, panel
    template(options) {
      return `<div class="${options.classes.loading}"></div>`;
    },
    showCallback(options) {
      this.$el.addClass(`${options.classes.loading}-show`);
    },
    hideCallback(options) {
      this.$el.removeClass(`${options.classes.loading}-show`);
    }
  },

  contentFilter(content, object) {
    return content;
  },

  useCssTransforms3d: true,
  useCssTransforms: true,
  useCssTransitions: true,

  dragTolerance: 150,

  mouseDragHandler: null,
  mouseDrag: true,
  touchDrag: true,
  pointerDrag: true,

  direction: 'right', // top, bottom, left, right
  duration: '500',
  easing: 'ease', // linear, ease-in, ease-out, ease-in-out

  // callbacks
  beforeLoad: $.noop, // Before loading
  afterLoad: $.noop, // After loading
  beforeShow: $.noop, // Before opening
  afterShow: $.noop, // After opening
  onChange: $.noop, // On changing
  beforeHide: $.noop, // Before closing
  afterHide: $.noop, // After closing
  beforeDrag: $.noop, // Before drag
  afterDrag: $.noop // After drag
};

class Instance {
  constructor(object,...args){
    this.initialize(object,...args);
  }
  initialize(object,...args) {
    const options = args[0] || {};

    if (typeof object === 'string') {
      object = {
        url: object
      };
    } else if (object && object.nodeType === 1) {
      const $element = $$1(object);

      object = {
        url: $element.attr('href'),
        settings: $element.data('settings') || {},
        options: $element.data() || {}
      };
    }

    if (object && object.options) {
      object.options = $$1.extend(true, options, object.options);
    } else {
      object.options = options;
    }

    object.options = $$1.extend(true, {}, DEFAULTS, object.options);

    $$1.extend(this, object);

    return this;
  }
}

/**
 * Css features detect
 **/
let Support = {};

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
    style = $$1('<support>').get(0).style,
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
      $$1.each(prefixes, (i, prefix) => {
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
})(Support);

function easingBezier(mX1, mY1, mX2, mY2) {
  'use strict';
  function a(aA1, aA2) {
    return 1.0 - 3.0 * aA2 + 3.0 * aA1;
  }

  function b(aA1, aA2) {
    return 3.0 * aA2 - 6.0 * aA1;
  }

  function c(aA1) {
    return 3.0 * aA1;
  }

  // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
  function calcBezier(aT, aA1, aA2) {
    return ((a(aA1, aA2) * aT + b(aA1, aA2)) * aT + c(aA1)) * aT;
  }

  // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
  function getSlope(aT, aA1, aA2) {
    return 3.0 * a(aA1, aA2) * aT * aT + 2.0 * b(aA1, aA2) * aT + c(aA1);
  }

  function getTForX(aX) {
    // Newton raphson iteration
    let aGuessT = aX;
    for (let i = 0; i < 4; ++i) {
      const currentSlope = getSlope(aGuessT, mX1, mX2);
      if (currentSlope === 0.0) {
        return aGuessT;
      }
      const currentX = calcBezier(aGuessT, mX1, mX2) - aX;
      aGuessT -= currentX / currentSlope;
    }
    return aGuessT;
  }

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
}

const Easings = {
  ease: easingBezier(0.25, 0.1, 0.25, 1.0),
  linear: easingBezier(0.00, 0.0, 1.00, 1.0),
  'ease-in': easingBezier(0.42, 0.0, 1.00, 1.0),
  'ease-out': easingBezier(0.00, 0.0, 0.58, 1.0),
  'ease-in-out': easingBezier(0.42, 0.0, 0.58, 1.0)
};

const Animate = {
  prepareTransition($el, property, duration, easing, delay) {
        const temp = [];
    if (property) {
      temp.push(property);
    }
    if (duration) {
      if ($$1.isNumeric(duration)) {
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
    $el.css(Support.transition, temp.join(' '));
  },
  do(view, value, callback) {
        SlidePanel.enter('animating');

    const duration = view.options.duration,
      easing = view.options.easing || 'ease';

    const that = this;
    let style = view.makePositionStyle(value);
    let property = null;

    for (property in style) {
      if ({}.hasOwnProperty.call(style, property)) {
        break;
      }
    }

    if (view.options.useCssTransitions && Support.transition) {
      setTimeout(() => {
        that.prepareTransition(view.$panel, property, duration, easing);
      }, 20);

      view.$panel.one(Support.transition.end, () => {
        if ($$1.isFunction(callback)) {
          callback();
        }

        view.$panel.css(Support.transition, '');

        SlidePanel.leave('animating');
      });
      setTimeout(() => {
        view.setPosition(value);
      }, 20);
    } else {
      const startTime = getTime();
      const start = view.getPosition();
      const end = value;

      const run = time => {
        let percent = (time - startTime) / view.options.duration;

        if (percent > 1) {
          percent = 1;
        }

        percent = Easings[easing].fn(percent);

        const current = parseFloat(start + percent * (end - start), 10);

        view.setPosition(current);

        if (percent === 1) {
          window.cancelAnimationFrame(that._frameId);
          that._frameId = null;

          if ($$1.isFunction(callback)) {
            callback();
          }

          SlidePanel.leave('animating');
        } else {
          that._frameId = window.requestAnimationFrame(run);
        }
      };

      that._frameId = window.requestAnimationFrame(run);
    }
  }
};

class Loading {
  constructor(view) {
    this.initialize(view);
  }

  initialize(view) {
    this._view = view;
    this.build();
  }

  build() {
    if (this._builded) {
      return;
    }

    const options = this._view.options;
    const html = options.loading.template.call(this, options);
    this.$el = $$1(html);

    switch (options.loading.appendTo) {
      case 'panel':
        this.$el.appendTo(this._view.$panel);
        break;
      case 'body':
        this.$el.appendTo('body');
        break;
      default:
        this.$el.appendTo(options.loading.appendTo);
    }

    this._builded = true;
  }

  show(callback) {
    this.build();
    const options = this._view.options;
    options.loading.showCallback.call(this, options);

    if ($$1.isFunction(callback)) {
      callback.call(this);
    }
  }

  hide(callback) {
    const options = this._view.options;
    options.loading.hideCallback.call(this, options);

    if ($$1.isFunction(callback)) {
      callback.call(this);
    }
  }
}

class Drag {
  constructor(...args){
    this.initialize(...args);
  }

  initialize(view) {
    this._view = view;
    this.options = view.options;
    this._drag = {
      time: null,
      pointer: null
    };

    this.bindEvents();
  }
  bindEvents() {
    const $panel = this._view.$panel,
      options = this.options;

    if (options.mouseDrag) {
      $panel.on(SlidePanel.eventName('mousedown'), $$1.proxy(this.onDragStart, this));
      $panel.on(SlidePanel.eventName('dragstart selectstart'), (event) => {
        /* eslint consistent-return: "off" */
        if (options.mouseDragHandler) {
          if (!($$1(event.target).is(options.mouseDragHandler)) && !($$1(event.target).parents(options.mouseDragHandler).length > 0)) {
            return;
          }
        }
        return false;
      });
    }

    if (options.touchDrag && Support.touch) {
      $panel.on(SlidePanel.eventName('touchstart'), $$1.proxy(this.onDragStart, this));
      $panel.on(SlidePanel.eventName('touchcancel'), $$1.proxy(this.onDragEnd, this));
    }

    if (options.pointerDrag && Support.pointer) {
      $panel.on(SlidePanel.eventName(Support.prefixPointerEvent('pointerdown')), $$1.proxy(this.onDragStart, this));
      $panel.on(SlidePanel.eventName(Support.prefixPointerEvent('pointercancel')), $$1.proxy(this.onDragEnd, this));
    }
  }

  /**
   * Handles `touchstart` and `mousedown` events.
   */
  onDragStart(event) {
    const that = this;

    if (event.which === 3) {
      return;
    }

    const options = this.options;

    this._view.$panel.addClass(this.options.classes.dragging);

    this._position = this._view.getPosition(true);

    this._drag.time = new Date().getTime();
    this._drag.pointer = this.pointer(event);

    const callback = () => {
      SlidePanel.enter('dragging');
      SlidePanel.trigger(that._view, 'beforeDrag');
    };

    if (options.mouseDrag) {
      if (options.mouseDragHandler) {
        if (!($$1(event.target).is(options.mouseDragHandler)) && !($$1(event.target).parents(options.mouseDragHandler).length > 0)) {
          return;
        }
      }

      $$1(document).on(SlidePanel.eventName('mouseup'), $$1.proxy(this.onDragEnd, this));

      $$1(document).one(SlidePanel.eventName('mousemove'), $$1.proxy(function () {
        $$1(document).on(SlidePanel.eventName('mousemove'), $$1.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    if (options.touchDrag && Support.touch) {
      $$1(document).on(SlidePanel.eventName('touchend'), $$1.proxy(this.onDragEnd, this));

      $$1(document).one(SlidePanel.eventName('touchmove'), $$1.proxy(function () {
        $$1(document).on(SlidePanel.eventName('touchmove'), $$1.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    if (options.pointerDrag && Support.pointer) {
      $$1(document).on(SlidePanel.eventName(Support.prefixPointerEvent('pointerup')), $$1.proxy(this.onDragEnd, this));

      $$1(document).one(SlidePanel.eventName(Support.prefixPointerEvent('pointermove')), $$1.proxy(function () {
        $$1(document).on(SlidePanel.eventName(Support.prefixPointerEvent('pointermove')), $$1.proxy(this.onDragMove, this));

        callback();
      }, this));
    }

    $$1(document).on(SlidePanel.eventName('blur'), $$1.proxy(this.onDragEnd, this));

    event.preventDefault();
  }

  /**
   * Handles the `touchmove` and `mousemove` events.
   */
  onDragMove(event) {
    const distance = this.distance(this._drag.pointer, this.pointer(event));

    if (!SlidePanel.is('dragging')) {
      return;
    }

    if (Math.abs(distance) > this.options.dragTolerance) {
      if (this._willClose !== true) {
        this._willClose = true;
        this._view.$panel.addClass(this.options.classes.willClose);
      }
    } else if (this._willClose !== false) {
      this._willClose = false;
      this._view.$panel.removeClass(this.options.classes.willClose);
    }

    if (!SlidePanel.is('dragging')) {
      return;
    }

    event.preventDefault();
    this.move(distance);
  }

  /**
   * Handles the `touchend` and `mouseup` events.
   */
  onDragEnd(event) {
    const distance = this.distance(this._drag.pointer, this.pointer(event));

    $$1(document).off(SlidePanel.eventName('mousemove mouseup touchmove touchend pointermove pointerup MSPointerMove MSPointerUp blur'));

    this._view.$panel.removeClass(this.options.classes.dragging);

    if (this._willClose === true) {
      this._willClose = false;
      this._view.$panel.removeClass(this.options.classes.willClose);
    }

    if (!SlidePanel.is('dragging')) {
      return;
    }

    SlidePanel.leave('dragging');

    SlidePanel.trigger(this._view, 'afterDrag');

    if (Math.abs(distance) < this.options.dragTolerance) {
      this._view.revert();
    } else {
      this._view.hide();
      // SlidePanel.hide();
    }
  }

  /**
   * Gets unified pointer coordinates from event.
   * @returns {Object} - Contains `x` and `y` coordinates of current pointer position.
   */
  pointer(event) {
    const result = {
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

  /**distance
   * Gets the distance of two pointer.
   */
  distance(first, second) {
    const d = this.options.direction;
    if (d === 'left' || d === 'right') {
      return second.x - first.x;
    }
    return second.y - first.y;
  }

  move(value) {
    let position = this._position + value;

    if (this.options.direction === 'right' || this.options.direction === 'bottom') {
      if (position < 0) {
        return;
      }
    } else if (position > 0) {
      return;
    }

    if (!this.options.useCssTransforms && !this.options.useCssTransforms3d) {
      if (this.options.direction === 'right' || this.options.direction === 'bottom') {
        position = -position;
      }
    }

    this._view.setPosition(`${position}px`);
  }
}

class View {
  constructor(options) {
    this.initialize(options);
  }

  initialize(options) {
    this.options = options;
    this._instance = null;
    this._showed = false;
    this._isLoading = false;

    this.build();
  }

  setLength() {
    switch (this.options.direction) {
      case 'top':
      case 'bottom':
        this._length = this.$panel.outerHeight();
        break;
      case 'left':
      case 'right':
        this._length = this.$panel.outerWidth();
        break;
      // no default
    }
  }

  build() {
    if (this._builded) {
      return;
    }

    const options = this.options;

    const html = options.template.call(this, options);
    const that = this;

    this.$panel = $$1(html).appendTo('body');
    if (options.skin) {
      this.$panel.addClass(options.skin);
    }
    this.$content = this.$panel.find(`.${this.options.classes.content}`);

    if (options.closeSelector) {
      this.$panel.on('click touchstart', options.closeSelector, () => {
        that.hide();
        return false;
      });
    }
    this.loading = new Loading(this);

    this.setLength();
    this.setPosition(this.getHidePosition());

    if (options.mouseDrag || options.touchDrag || options.pointerDrag) {
      this.drag = new Drag(this);
    }

    this._builded = true;
  }

  getHidePosition() {
    /* eslint consistent-return: "off" */
    const options = this.options;

    if (options.useCssTransforms || options.useCssTransforms3d) {
      switch (options.direction) {
        case 'top':
        case 'left':
          return '-100';
        case 'bottom':
        case 'right':
          return '100';
        // no default
      }
    }
    switch (options.direction) {
      case 'top':
      case 'bottom':
        return parseFloat(-(this._length / $$1(window).height()) * 100, 10);
      case 'left':
      case 'right':
        return parseFloat(-(this._length / $$1(window).width()) * 100, 10);
      // no default
    }
  }

  empty() {
    this._instance = null;
    this.$content.empty();
  }

  load(object) {
    const that = this;
    const options = object.options;

    SlidePanel.trigger(this, 'beforeLoad', object);
    this.empty();

    function setContent(content) {
      content = options.contentFilter.call(this, content, object);
      that.$content.html(content);
      that.hideLoading();

      that._instance = object;

      SlidePanel.trigger(that, 'afterLoad', object);
    }

    if (object.content) {
      setContent(object.content);
    } else if (object.url) {
      this.showLoading();

      $$1.ajax(object.url, object.settings || {}).done(data => {
        setContent(data);
      });
    } else {
      setContent('');
    }
  }

  showLoading() {
    const that = this;
    this.loading.show(() => {
      that._isLoading = true;
    });
  }

  hideLoading() {
    const that = this;
    this.loading.hide(() => {
      that._isLoading = false;
    });
  }

  show(callback) {
    this.build();

    SlidePanel.enter('show');
    SlidePanel.trigger(this, 'beforeShow');

    $$1('html').addClass(`${this.options.classes.base}-html`);
    this.$panel.addClass(this.options.classes.show);

    const that = this;
    Animate.do(this, 0, () => {
      that._showed = true;
      SlidePanel.trigger(that, 'afterShow');

      if ($$1.isFunction(callback)) {
        callback.call(that);
      }
    });
  }

  change(object) {
    SlidePanel.trigger(this, 'beforeShow');

    SlidePanel.trigger(this, 'onChange', object, this._instance);

    this.load(object);

    SlidePanel.trigger(this, 'afterShow');
  }

  revert(callback) {
    const that = this;
    Animate.do(this, 0, () => {
      if ($$1.isFunction(callback)) {
        callback.call(that);
      }
    });
  }

  hide(callback) {
    SlidePanel.leave('show');
    SlidePanel.trigger(this, 'beforeHide');

    const that = this;

    Animate.do(this, this.getHidePosition(), () => {
      that.$panel.removeClass(that.options.classes.show);
      that._showed = false;
      that._instance = null;

      if (SlidePanel._current === that) {
        SlidePanel._current = null;
      }

      if (!SlidePanel.is('show')) {
        $$1('html').removeClass(`${that.options.classes.base}-html`);
      }

      if ($$1.isFunction(callback)) {
        callback.call(that);
      }

      SlidePanel.trigger(that, 'afterHide');
    });
  }

  makePositionStyle(value) {
    let property, x = '0',
      y = '0';

    if (!isPercentage(value) && !isPx(value)) {
      value = `${value}%`;
    }

    if (this.options.useCssTransforms && Support.transform) {
      if (this.options.direction === 'left' || this.options.direction === 'right') {
        x = value;
      } else {
        y = value;
      }

      property = Support.transform.toString();

      if (this.options.useCssTransforms3d && Support.transform3d) {
        value = `translate3d(${x},${y},0)`;
      } else {
        value = `translate(${x},${y})`;
      }
    } else {
      property = this.options.direction;
    }
    const temp = {};
    temp[property] = value;
    return temp;
  }

  getPosition(px) {
    let value;

    if (this.options.useCssTransforms && Support.transform) {
      value = convertMatrixToArray(this.$panel.css(Support.transform));
      if (!value) {
        return 0;
      }

      if (this.options.direction === 'left' || this.options.direction === 'right') {
        value = value[12] || value[4];

      } else {
        value = value[13] || value[5];
      }
    } else {
      value = this.$panel.css(this.options.direction);

      value = parseFloat(value.replace('px', ''));
    }

    if (px !== true) {
      value = (value / this._length) * 100;
    }

    return parseFloat(value, 10);
  }

  setPosition(value) {
    const style = this.makePositionStyle(value);
    this.$panel.css(style);
  }
}

const SlidePanel = {
  // Current state information.
  _states: {},
  _views: {},
  _current: null,

  /**
   * Checks whether the carousel is in a specific state or not.
   */
  is(state) {
    return this._states[state] && this._states[state] > 0;
  },

  /**
   * Enters a state.
   */
  enter(state) {
    if (this._states[state] === undefined) {
      this._states[state] = 0;
    }

    this._states[state]++;
  },

  /**
   * Leaves a state.
   */
  leave(state) {
    this._states[state]--;
  },

  trigger(view, event, ...args) {
    const data = [view].concat(args);

    // event
    $$1(document).trigger(`slidePanel::${event}`, data);
    if ($$1.isFunction(view.options[event])) {
      view.options[event].apply(view, args);
    }
  },

  eventName(events) {
    if (typeof events !== 'string' || events === '') {
      return '.slidepanel';
    }
    events = events.split(' ');

    const length = events.length;
    for (let i = 0; i < length; i++) {
      events[i] = `${events[i]}.slidepanel`;
    }
    return events.join(' ');
  },

  show(object, options) {
    if (!(object instanceof Instance)) {
      switch (arguments.length) {
        case 0:
          object = new Instance();
          break;
        case 1:
          object = new Instance(object);
          break;
        case 2:
          object = new Instance(object, options);
          break;
        // no default
      }
    }

    const view = this.getView(object.options);

    const callback = () => {
      view.show();
      view.load(object);
      this._current = view;
    };
    if (this._current !== null) {
      if (view === this._current) {
        this._current.change(object);
      } else {
        this._current.hide(callback);
      }
    } else {
      callback();
    }
  },

  getView(options) {
    const code = getHashCode(options);

    if (this._views.hasOwnProperty(code)) {
      return this._views[code];
    }

    return (this._views[code] = new View(options));
  },

  hide(object) {
    if (typeof object !== 'undefined' && typeof object.options !== 'undefined') {
      const view = this.getView(object.options);
      view.hide();
    } else if (this._current !== null) {
      this._current.hide();
    }
  }
};

var api = {
  is(state) {
    return SlidePanel.is(state);
  },

  show(object, options) {
    SlidePanel.show(object, options);
    return this;
  },

  hide(...args) {
    SlidePanel.hide(args);
    return this;
  }
};

if (!Date.now) {
  Date.now = () => {
    return new Date().getTime();
  };
}

const vendors = ['webkit', 'moz'];
for (let i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
  const vp = vendors[i];
  window.requestAnimationFrame = window[`${vp}RequestAnimationFrame`];
  window.cancelAnimationFrame = (window[`${vp}CancelAnimationFrame`] || window[`${vp}CancelRequestAnimationFrame`]);
}

if (/iP(ad|hone|od).*OS (6|7|8)/.test(window.navigator.userAgent) || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
  let lastTime = 0;
  window.requestAnimationFrame = callback => {
    const now = getTime();
    const nextTime = Math.max(lastTime + 16, now);
    return setTimeout(() => {
        callback(lastTime = nextTime);
      },
      nextTime - now);
  };
  window.cancelAnimationFrame = clearTimeout;
}

const OtherSlidePanel = $$1.fn.slidePanel;

const jQuerySlidePanel = function(options, ...args) {
  const method = options;

  if (typeof options === 'string') {
    return this.each(function() {
      let instance = $$1.data(this, 'slidePanel');

      if (!(instance instanceof Instance)) {
        instance = new Instance(this, args);
        $$1.data(this, 'slidePanel', instance);
      }

      switch (method) {
        case 'hide':
          SlidePanel.hide(instance);
          break;
        case 'show':
          SlidePanel.show(instance);
          break;
          // no default
      }
    });
  }
  return this.each(function() {
    if (!$$1.data(this, 'slidePanel')) {
      $$1.data(this, 'slidePanel', new Instance(this, options));

      $$1(this).on('click', function(e) {
        const instance = $$1.data(this, 'slidePanel');
        SlidePanel.show(instance);

        e.preventDefault();
        e.stopPropagation();
      });
    }
  });
};

$$1.fn.slidePanel = jQuerySlidePanel;

$$1.slidePanel = function(...args) {
  SlidePanel.show(...args);
};

$$1.extend($$1.slidePanel, {
  setDefaults: function(options) {
    $$1.extend(true, DEFAULTS, $$1.isPlainObject(options) && options);
  },
  noConflict: function() {
    $$1.fn.slidePanel = OtherSlidePanel;
    return jQuerySlidePanel;
  }
}, info, api);
