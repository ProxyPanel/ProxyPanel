/**
* jQuery asHoverScroll v0.3.7
* https://github.com/amazingSurge/jquery-asHoverScroll
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $$1 from 'jquery';

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

  // callbacks
  onEnter() {
    $(this).siblings().removeClass('is-active');
    $(this).addClass('is-active');
  },
  onLeave() {
    $(this).removeClass('is-active');
  }
};

/**
 * Css features detect
 **/
let support = {};

((support) => {
  /**
   * Borrowed from Owl carousel
   **/
   'use strict';

  let events = {
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

  let test = (property, prefixed) => {
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

  let prefixed = (property) => {
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

  support.convertMatrixToArray = function(value) {
    if (value && (value.substr(0, 6) === "matrix")) {
      return value.replace(/^.*\((.*)\)$/g, "$1").replace(/px/g, '').split(/, +/);
    }
    return false;
  };

  support.prefixPointerEvent = (pointerEvent) => {
    let charStart = 9,
      subStart = 10;

    return window.MSPointerEvent ?
      `MSPointer${pointerEvent.charAt(charStart).toUpperCase()}${pointerEvent.substr(subStart)}` :
      pointerEvent;
  };
})(support);

const NAMESPACE$1 = 'asHoverScroll';
let instanceId = 0;

/**
 * Plugin constructor
 **/
class asHoverScroll {
  constructor(element, options) {
    this.element = element;
    this.$element = $$1(element);

    this.options = $$1.extend({}, DEFAULTS, options, this.$element.data());
    this.$list = $$1(this.options.list, this.$element);

    this.classes = {
      disabled: `${this.options.namespace}-disabled`
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

    this.instanceId = (++instanceId);

    this.trigger('init');
    this.init();
  }

  init() {
    this.initPosition();

    // init length data
    this.updateLength();

    this.bindEvents();
  }

  bindEvents() {
    const that = this;
    const enterEvents = ['enter'];
    const leaveEvents = [];

    if (this.options.mouseMove) {
      this.$element.on(this.eventName('mousemove'), $$1.proxy(this.onMove, this));
      enterEvents.push('mouseenter');
      leaveEvents.push('mouseleave');
    }

    if (this.options.touchScroll && support.touch) {
      this.$element.on(this.eventName('touchstart'), $$1.proxy(this.onScrollStart, this));
      this.$element.on(this.eventName('touchcancel'), $$1.proxy(this.onScrollEnd, this));
    }

    if (this.options.pointerScroll && support.pointer) {
      this.$element.on(this.eventName(support.prefixPointerEvent('pointerdown')), $$1.proxy(this.onScrollStart, this));

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

      this.$element.on(this.eventName(support.prefixPointerEvent('pointercancel')), $$1.proxy(this.onScrollEnd, this));
    }

    this.$list.on(this.eventName(enterEvents.join(' ')), this.options.item, () => {
      if (!that.is('scrolling')) {
        that.options.onEnter.call(this);
      }
    });
    this.$list.on(this.eventName(leaveEvents.join(' ')), this.options.item, () => {
      if (!that.is('scrolling')) {
        that.options.onLeave.call(this);
      }
    });

    $$1(window).on(this.eventNameWithId('orientationchange'), () => {
      that.update();
    });
    $$1(window).on(this.eventNameWithId('resize'), this.throttle(() => {
      that.update();
    }, this.options.throttle));
  }

  unbindEvents() {
    this.$element.off(this.eventName());
    this.$list.off(this.eventName());
    $$1(window).off(this.eventNameWithId());
  }

  /**
   * Handles `touchstart` and `mousedown` events.
   */
  onScrollStart(event) {
    const that = this;
    if (this.is('scrolling')) {
      return;
    }

    if (event.which === 3) {
      return;
    }

    if ($$1(event.target).closest(this.options.exception).length > 0) {
      return;
    }

    this._scroll.time = new Date().getTime();
    this._scroll.pointer = this.pointer(event);
    this._scroll.start = this.getPosition();
    this._scroll.moved = false;

    const callback = () => {
      this.enter('scrolling');
      this.trigger('scroll');
    };

    if (this.options.touchScroll && support.touch) {
      $$1(document).on(this.eventName('touchend'), $$1.proxy(this.onScrollEnd, this));

      $$1(document).one(this.eventName('touchmove'), $$1.proxy(function() {
        if(!this.is('scrolling')){
          $$1(document).on(that.eventName('touchmove'), $$1.proxy(this.onScrollMove, this));
          callback();
        }
      }, this));
    }

    if (this.options.pointerScroll && support.pointer) {
      $$1(document).on(this.eventName(support.prefixPointerEvent('pointerup')), $$1.proxy(this.onScrollEnd, this));

      $$1(document).one(this.eventName(support.prefixPointerEvent('pointermove')), $$1.proxy(function() {
        if(!this.is('scrolling')){
          $$1(document).on(that.eventName(support.prefixPointerEvent('pointermove')), $$1.proxy(this.onScrollMove, this));

          callback();
        }
      }, this));
    }

    $$1(document).on(this.eventName('blur'), $$1.proxy(this.onScrollEnd, this));

    event.preventDefault();
  }

  /**
   * Handles the `touchmove` and `mousemove` events.
   */
  onScrollMove(event) {
    this._scroll.updated = this.pointer(event);
    const distance = this.distance(this._scroll.pointer, this._scroll.updated);

    if (Math.abs(this._scroll.pointer.x - this._scroll.updated.x) > 10 || Math.abs(this._scroll.pointer.y - this._scroll.updated.y) > 10) {
      this._scroll.moved = true;
    }

    if (!this.is('scrolling')) {
      return;
    }

    event.preventDefault();
    let postion = this._scroll.start + distance;

    if (this.canScroll()) {
      if (postion > 0) {
        postion = 0;
      } else if (postion < this.containerLength - this.listLength) {
        postion = this.containerLength - this.listLength;
      }
      this.updatePosition(postion);
    }
  }

  /**
   * Handles the `touchend` and `mouseup` events.
   */
  onScrollEnd(event) {
    if (!this._scroll.moved) {
      $$1(event.target).trigger('tap');
    }

    // if (!this.is('scrolling')) {
    //   return;
    // }

    if (this.options.touchScroll && support.touch) {
      $$1(document).off(this.eventName('touchmove touchend'));
    }

    if (this.options.pointerScroll && support.pointer) {
      $$1(document).off(this.eventName(support.prefixPointerEvent('pointermove pointerup')));
    }

    $$1(document).off(this.eventName('blur'));

    // touch will trigger mousemove event after 300ms delay. So we need avoid it
    // setTimeout(() => {
      this.leave('scrolling');
      this.trigger('scrolled');
    // }, 500);
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

  getEvent(event) {
    event = event.originalEvent || event || window.event;

    event = event.touches && event.touches.length ?
      event.touches[0] : event.changedTouches && event.changedTouches.length ?
      event.changedTouches[0] : event;

    return event;
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

  onMove(event) {
    event = this.getEvent(event);

    if (this.is('scrolling')) {
      return;
    }

    if (this.isMatchScroll(event)) {
      let pointer;
      let distance;
      let offset;
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

  isMatchScroll(event) {
    if (!this.is('disabled') && this.canScroll()) {
      if (this.options.exception) {
        if ($$1(event.target).closest(this.options.exception).length === 0) {
          return true;
        }
        return false;
      }
      return true;
    }
    return false;
  }

  canScroll() {
    return this.listLength > this.containerLength;
  }

  getContainerLength() {
    return this.element[this.attributes.clientLength];
  }

  getListhLength() {
    return this.$list[0][this.attributes.clientLength];
  }

  updateLength() {
    this.containerLength = this.getContainerLength();
    this.listLength = this.getListhLength();
    this.multiplier = (this.listLength - this.containerLength) / (this.containerLength - 2 * this.options.boundary);
  }

  initPosition() {
    const style = this.makePositionStyle(0);
    this.$list.css(style);
  }

  getPosition() {
    let value;

    if (this.options.useCssTransforms && support.transform) {
      if (this.options.useCssTransforms3d && support.transform3d) {
        value = support.convertMatrixToArray(this.$list.css(support.transform));
      } else {
        value = support.convertMatrixToArray(this.$list.css(support.transform));
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

  makePositionStyle(value) {
    let property;
    let x = '0px';
    let y = '0px';

    if (this.options.useCssTransforms && support.transform) {
      if (this.attributes.axis === 'X') {
        x = `${value}px`;
      } else {
        y = `${value}px`;
      }

      property = support.transform.toString();

      if (this.options.useCssTransforms3d && support.transform3d) {
        value = `translate3d(${x},${y},0px)`;
      } else {
        value = `translate(${x},${y})`;
      }
    } else {
      property = this.attributes.position;
    }
    const temp = {};
    temp[property] = value;

    return temp;
  }

  updatePosition(value) {
    const style = this.makePositionStyle(value);
    this.$list.css(style);
  }

  update() {
    if (!this.is('disabled')) {
      this.updateLength();

      if (!this.canScroll()) {
        this.initPosition();
      }
    }
  }

  eventName(events) {
    if (typeof events !== 'string' || events === '') {
      return `.${NAMESPACE$1}`;
    }
    events = events.split(' ');

    const length = events.length;
    for (let i = 0; i < length; i++) {
      events[i] = `${events[i]}.${NAMESPACE$1}`;
    }
    return events.join(' ');
  }

  eventNameWithId(events) {
    if (typeof events !== 'string' || events === '') {
      return `.${this.options.namespace}-${this.instanceId}`;
    }

    events = events.split(' ');
    const length = events.length;
    for (let i = 0; i < length; i++) {
      events[i] = `${events[i]}.${this.options.namespace}-${this.instanceId}`;
    }
    return events.join(' ');
  }

  trigger(eventType, ...params) {
    const data = [this].concat(params);

    // event
    this.$element.trigger(`${NAMESPACE$1}::${eventType}`, data);

    // callback
    eventType = eventType.replace(/\b\w+\b/g, (word) => {
      return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
    const onFunction = `on${eventType}`;

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

    this._states[state] = 1;
  }

  /**
   * Leaves a state.
   */
  leave(state) {
    this._states[state] = 0;
  }

  /**
   * _throttle
   * @description Borrowed from Underscore.js
   */
  throttle(func, wait) {
    const _now = Date.now || function() {
      return new Date().getTime();
    };

    let timeout;
    let context;
    let args;
    let result;
    let previous = 0;
    let later = function() {
      previous = _now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) {
        context = args = null;
      }
    };

    return (...params) => {
      /*eslint consistent-this: "off"*/
      let now = _now();
      let remaining = wait - (now - previous);
      context = this;
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

  enable() {
    if (this.is('disabled')) {
      this.leave('disabled');

      this.$element.removeClass(this.classes.disabled);

      this.bindEvents();
    }

    this.trigger('enable');
  }

  disable() {
    if (!this.is('disabled')) {
      this.enter('disabled');

      this.initPosition();
      this.$element.addClass(this.classes.disabled);

      this.unbindEvents();
    }

    this.trigger('disable');
  }

  destroy() {
    this.$element.removeClass(this.classes.disabled);
    this.unbindEvents();
    this.$element.data(NAMESPACE$1, null);

    this.trigger('destroy');
  }

  static setDefaults(options) {
    $$1.extend(DEFAULTS, $$1.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.3.7'
};

const NAMESPACE = 'asHoverScroll';
const OtherAsHoverScroll = $$1.fn.asHoverScroll;

const jQueryAsHoverScroll = function(options, ...args) {
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
        const instance = $$1.data(this, NAMESPACE);
        if (instance && typeof instance[method] === 'function') {
          instance[method](...args);
        }
      });
    }
  }

  return this.each(function() {
    if (!$$1(this).data(NAMESPACE)) {
      $$1(this).data(NAMESPACE, new asHoverScroll(this, options));
    }
  });
};

$$1.fn.asHoverScroll = jQueryAsHoverScroll;

$$1.asHoverScroll = $$1.extend({
  setDefaults: asHoverScroll.setDefaults,
  noConflict: function() {
    $$1.fn.asHoverScroll = OtherAsHoverScroll;
    return jQueryAsHoverScroll;
  }
}, info);
