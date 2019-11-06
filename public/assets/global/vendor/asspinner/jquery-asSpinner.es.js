/**
* jQuery asSpinner v0.4.3
* https://github.com/amazingSurge/jquery-asSpinner
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

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
  mousewheel: false, // support mouse wheel

  format(value) { // function, define custom format
    return value;
  },
  parse(value) { // function, parse custom format value
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
    min: 0.00,
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

const NAMESPACE$1 = 'asSpinner';

class asSpinner {
  constructor(element, options) {
    this.element = element;
    this.$element = $(element);

    this.options = $.extend({}, DEFAULTS, options, this.$element.data());
    this.namespace = this.options.namespace;

    if (this.options.rule) {
      const that = this;
      const array = ['min', 'max', 'step', 'precision'];
      $.each(array, (key, value) => {
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
    if (this.mousewheel && !$.event.special.mousewheel) {
      this.mousewheel = false;
    }

    this.eventBinded = false;
    this.spinTimeout = null;
    this.isFocused = false;

    this.classes = {
      disabled: `${this.namespace}_disabled`,
      skin: `${this.namespace}_${this.options.skin}`,
      focus: `${this.namespace}_focus`,

      control: `${this.namespace}-control`,
      down: `${this.namespace}-down`,
      up: `${this.namespace}-up`,
      wrap: this.namespace
    };

    this._trigger('init');
    this.init();
  }

  init() {
    this.$control = $(`<div class="${this.namespace}-control"><span class="${this.classes.up}"></span><span class="${this.classes.down}"></span></div>`);
    this.$wrap = this.$element.wrap(`<div tabindex="0" class="${this.classes.wrap}"></div>`).parent();
    this.$down = this.$control.find(`.${this.classes.down}`);
    this.$up = this.$control.find(`.${this.classes.up}`);

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

  // 500ms to detect if it is a click event
  // 60ms interval execute if it if long pressdown
  spin(fn, timeout) {
    const that = this;
    const spinFn = timeout => {
      clearTimeout(that.spinTimeout);
      that.spinTimeout = setTimeout(() => {
        fn.call(that);
        spinFn(60);
      }, timeout);
    };
    spinFn(timeout || 500);
  }

  bindEvent() {
    const that = this;
    this.eventBinded = true;

    this.$wrap.on('focus.asSpinner', () => {
      that.$wrap.addClass(that.classes.focus);
    }).on('blur.asSpinner', () => {
      if (!that.isFocused) {
        that.$wrap.removeClass(that.classes.focus);
      }
    });

    this.$down.on('mousedown.asSpinner', () => {
      $(document).one('mouseup.asSpinner', () => {
        clearTimeout(that.spinTimeout);
      });
      that.spin(that.spinDown);
    }).on('mouseup.asSpinner', () => {
      clearTimeout(that.spinTimeout);
      $(document).off('mouseup.asSpinner');
    }).on('click.asSpinner', () => {
      that.spinDown();

    });

    this.$up.on('mousedown.asSpinner', () => {
      $(document).one('mouseup.asSpinner', () => {
        clearTimeout(that.spinTimeout);
      });
      that.spin(that.spinUp);
    }).on('mouseup.asSpinner', () => {
      clearTimeout(that.spinTimeout);
      $(document).off('mouseup.asSpinner');
    }).on('click.asSpinner', () => {
      that.spinUp();
    });

    this.$element.on('focus.asSpinner', function() {
      that.isFocused = true;
      that.$wrap.addClass(that.classes.focus);

      // keyboard support
      $(this).on('keydown.asSpinner', e => {
        /*eslint consistent-return: "off"*/
        const key = e.keyCode || e.which;
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
          setTimeout(() => {
            //that.set(parseFloat(it.value));
          }, 0);
        }
      });

      // mousewheel support
      if (that.mousewheel === true) {
        $(this).mousewheel((event, delta) => {
          if (delta > 0) {
            that.spinUp();
          } else {
            that.spinDown();
          }
          return false;
        });
      }
    }).on('blur.asSpinner', function() {
      that.isFocused = false;
      that.$wrap.removeClass(that.classes.focus);
      $(this).off('keydown.asSpinner');
      if (that.mousewheel === true) {
        $(this).unmousewheel();
      }
      that.applyValue();
    });
  }

  unbindEvent() {
    this.eventBinded = false;
    this.$element.off('.asSpinner');
    this.$down.off('.asSpinner');
    this.$up.off('.asSpinner');
    this.$wrap.off('.asSpinner');
  }

  isNumber(value) {
    if (typeof value === 'number' && $.isNumeric(value)) {
      return true;
    }
    return false;
  }

  isOutOfBounds(value) {
    if (value < this.min) {
      return -1;
    }
    if (value > this.max) {
      return 1;
    }
    return 0;
  }

  applyValue() {
    if (this.options.format(this.value) !== this.$element.val()) {
      this.set(this.options.parse(this.$element.val()));
    }
  }

  _set(value) {
    if (isNaN(value)) {
      value = this.min;
    }
    const valid = this.isOutOfBounds(value);
    if (valid !== 0) {
      if (this.options.looping === true) {
        value = (valid === 1) ? this.min : this.max;
      } else {
        value = (valid === -1) ? this.min : this.max;
      }
    }
    this.value = value = Number(value).toFixed(this.precision);

    this.$element.val(this.options.format(this.value));
  }

  set(value) {
    this._set(value);

    this._trigger('change', this.value);
  }

  get() {
    return this.value;
  }

  /* Public methods */
  update(obj) {
    const that = this;

    ['min', 'max', 'precision', 'step'].forEach(value => {
      if (obj[value]) {
        that[value] = obj[value];
      }
    });
    if (obj.value) {
      this.set(obj.value);
    }
    return this;
  }

  val(value) {
    if (value) {
      this.set(this.options.parse(value));
    } else {
      return this.get();
    }
  }

  spinDown() {
    if (!$.isNumeric(this.value)) {
      this.value = 0;
    }
    this.value = parseFloat(this.value) - parseFloat(this.step);
    this.set(this.value);

    return this;
  }

  spinUp() {
    if (!$.isNumeric(this.value)) {
      this.value = 0;
    }
    this.value = parseFloat(this.value) + parseFloat(this.step);
    this.set(this.value);

    return this;
  }

  enable() {
    this.disabled = false;
    this.$wrap.removeClass(this.classes.disabled);
    this.$element.prop('disabled', false);

    if (this.eventBinded === false) {
      this.bindEvent();
    }

    this._trigger('enable');

    return this;
  }

  disable() {
    this.disabled = true;
    this.$element.prop('disabled', true);

    this.$wrap.addClass(this.classes.disabled);
    this.unbindEvent();

    this._trigger('disable');

    return this;
  }

  destroy() {
    this.unbindEvent();
    this.$control.remove();
    this.$element.unwrap();

    this._trigger('destroy');
    return this;
  }

  static setDefaults(options) {
    $.extend(DEFAULTS, $.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.4.3'
};

const NAMESPACE = 'asSpinner';
const OtherAsSpinner = $.fn.asSpinner;

const jQueryAsSpinner = function(options, ...args) {
  if (typeof options === 'string') {
    const method = options;

    if (/^_/.test(method)) {
      return false;
    } else if ((/^(get)$/.test(method)) || (method === 'val' && args.length === 0)) {
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
      $(this).data(NAMESPACE, new asSpinner(this, options));
    }
  });
};

$.fn.asSpinner = jQueryAsSpinner;

$.asSpinner = $.extend({
  setDefaults: asSpinner.setDefaults,
  noConflict: function() {
    $.fn.asSpinner = OtherAsSpinner;
    return jQueryAsSpinner;
  }
}, info);
