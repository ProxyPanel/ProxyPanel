/**
* jQuery asProgress v0.2.4
* https://github.com/amazingSurge/jquery-asProgress
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

var DEFAULTS = {
  namespace: 'progress',
  bootstrap: false,
  min: 0,
  max: 100,
  goal: 100,
  speed: 20, // speed of 1/100
  easing: 'ease',
  labelCallback(n) {
    const percentage = this.getPercentage(n);
    return `${percentage}%`;
  }
};

let easingBezier = (mX1, mY1, mX2, mY2) => {
  'use strict';

  let a = (aA1, aA2) => {
    return 1.0 - 3.0 * aA2 + 3.0 * aA1;
  };

  let b = (aA1, aA2) => {
    return 3.0 * aA2 - 6.0 * aA1;
  };

  let c = (aA1) => {
    return 3.0 * aA1;
  };

  // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
  let calcBezier = (aT, aA1, aA2) => {
    return ((a(aA1, aA2) * aT + b(aA1, aA2)) * aT + c(aA1)) * aT;
  };

  // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
  let getSlope = (aT, aA1, aA2) => {
    return 3.0 * a(aA1, aA2) * aT * aT + 2.0 * b(aA1, aA2) * aT + c(aA1);
  };

  let getTForX = (aX) => {
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

if (!Date.now){
  Date.now = () => new Date().getTime();
}

const vendors = ['webkit', 'moz'];
for (let i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
  const vp = vendors[i];
  window.requestAnimationFrame = window[`${vp}RequestAnimationFrame`];
  window.cancelAnimationFrame = (window[`${vp}CancelAnimationFrame`]
                 || window[`${vp}CancelRequestAnimationFrame`]);
}

if (/iP(ad|hone|od).*OS (6|7)/.test(window.navigator.userAgent) // iOS6 is buggy
  || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
  let lastTime = 0;
  window.requestAnimationFrame = callback => {
    const now = Date.now();
    const nextTime = Math.max(lastTime + 16, now);
    return setTimeout(() => {
        callback(lastTime = nextTime);
      },
      nextTime - now);
  };
  window.cancelAnimationFrame = clearTimeout;
}

function isPercentage(n) {
  return typeof n === 'string' && n.includes('%');
}

function getTime(){
  if (typeof window.performance !== 'undefined' && window.performance.now) {
    return window.performance.now();
  }
  return Date.now();
}

const NAMESPACE$1 = 'asProgress';

/**
 * Plugin constructor
 **/
class asProgress {
  constructor(element, options) {
    this.element = element;
    this.$element = $(element);

    this.options = $.extend({}, DEFAULTS, options, this.$element.data());

    if(this.options.bootstrap){
      this.namespace = 'progress';

      this.$target = this.$element.find('.progress-bar');

      this.classes = {
        label: `${this.namespace}-label`,
        bar: `${this.namespace}-bar`,
        disabled: 'is-disabled'
      };
    } else {
      this.namespace = this.options.namespace;

      this.classes = {
        label: `${this.namespace}__label`,
        bar: `${this.namespace}__bar`,
        disabled: 'is-disabled'
      };

      this.$target = this.$element;

      this.$element.addClass(this.namespace);
    }

    this.easing = EASING[this.options.easing] || EASING.ease;

    this.min = this.$target.attr('aria-valuemin');
    this.max = this.$target.attr('aria-valuemax');
    this.min = this.min? parseInt(this.min, 10): this.options.min;
    this.max = this.max? parseInt(this.max, 10): this.options.max;
    this.first = this.$target.attr('aria-valuenow');
    this.first = this.first? parseInt(this.first, 10): this.min;

    this.now = this.first;
    this.goal = this.options.goal;
    this._frameId = null;

    // Current state information.
    this._states = {};

    this.initialized = false;
    this._trigger('init');
    this.init();
  }

  init() {
    this.$bar = this.$element.find(`.${this.classes.bar}`);
    this.$label = this.$element.find(`.${this.classes.label}`);

    this.reset();
    this.initialized = true;
    this._trigger('ready');
  }

  _trigger(eventType, ...params) {
    let data = [this].concat(params);

    // event
    this.$element.trigger(`${NAMESPACE$1}::${eventType}`, data);

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

  getPercentage(n) {
    return Math.round(100 * (n - this.min) / (this.max - this.min));
  }

  go(goal) {
    if(!this.is('disabled')) {
      const that = this;
      this._clear();

      if (isPercentage(goal)) {
        goal = parseInt(goal.replace('%', ''), 10);
        goal = Math.round(this.min + (goal / 100) * (this.max - this.min));
      }
      if (typeof goal === 'undefined') {
        goal = this.goal;
      }

      if (goal > this.max) {
        goal = this.max;
      } else if (goal < this.min) {
        goal = this.min;
      }

      const start = that.now;
      const startTime = getTime();
      const animation = time => {
        const distance = (time - startTime)/that.options.speed;
        let next = Math.round(that.easing.fn(distance/100) * (that.max - that.min));

        if(goal > start){
          next = start + next;
          if(next > goal){
            next = goal;
          }
        } else{
          next = start - next;
          if(next < goal){
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
          that._frameId =  window.requestAnimationFrame(animation);
        }
      };

      that._frameId =  window.requestAnimationFrame(animation);
    }
  }

  _update(n) {
    this.now = n;

    const percenage = this.getPercentage(this.now);
    this.$bar.css('width', `${percenage}%`);
    this.$target.attr('aria-valuenow', this.now);
    if (this.$label.length > 0 && typeof this.options.labelCallback === 'function') {
      this.$label.html(this.options.labelCallback.call(this, [this.now]));
    }

    this._trigger('update', n);
  }

  _clear() {
    if (this._frameId) {
      window.cancelAnimationFrame(this._frameId);
      this._frameId = null;
    }
  }

  get() {
    return this.now;
  }

  start() {
    if(!this.is('disabled')) {
      this._clear();
      this._trigger('start');
      this.go(this.goal);
    }
  }

  reset() {
    if(!this.is('disabled')) {
      this._clear();
      this._update(this.first);
      this._trigger('reset');
    }
  }

  stop() {
    this._clear();
    this._trigger('stop');
  }

  finish() {
    if(!this.is('disabled')) {
      this._clear();
      this._update(this.goal);
      this._trigger('finish');
    }
  }

  destroy() {
    this.$element.data(NAMESPACE$1, null);
    this._trigger('destroy');
  }

  enable() {
    this._states.disabled = 0;

    this.$element.removeClass(this.classes.disabled);
  }

  disable() {
    this._states.disabled = 1;

    this.$element.addClass(this.classes.disabled);
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
  version:'0.2.4'
};

const NAMESPACE = 'asProgress';
const OtherAsProgress = $.fn.asProgress;

const jQueryAsProgress = function(options, ...args) {
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
      $(this).data(NAMESPACE, new asProgress(this, options));
    }
  });
};

$.fn.asProgress = jQueryAsProgress;

$.asProgress = $.extend({
  setDefaults: asProgress.setDefaults,
  registerEasing: asProgress.registerEasing,
  getEasing: asProgress.getEasing,
  noConflict: function() {
    $.fn.asProgress = OtherAsProgress;
    return jQueryAsProgress;
  }
}, info);
