/**
* asRange v0.3.4
* https://github.com/amazingSurge/jquery-asRange
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

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
  format(value) {
    return value;
  }
};

function getEventObject (event) {
  let e = event.originalEvent;
  if (e.touches && e.touches.length && e.touches[0]) {
    e = e.touches[0];
  }

  return e;
}

class Pointer {
  constructor ($element, id, parent) {
    this.$element = $element;
    this.uid = id;
    this.parent = parent;
    this.options = $.extend(true, {}, this.parent.options);
    this.direction = this.options.direction;
    this.value = null;
    this.classes = {
      active: `${this.parent.namespace}-pointer_active`
    };
  }

  mousedown(event) {
    const axis = this.parent.direction.axis;
    const position = this.parent.direction.position;
    const offset = this.parent.$wrap.offset();

    this.$element.trigger(`${this.parent.namespace}::moveStart`, this);

    this.data = {};
    this.data.start = event[axis];
    this.data.position = event[axis] - offset[position];

    const value = this.parent.getValueFromPosition(this.data.position);
    this.set(value);

    $.each(this.parent.pointer, (i, p) => {
      p.deactive();
    });

    this.active();

    this.mousemove = function(event) {
      const eventObj = getEventObject(event);
      const value = this.parent.getValueFromPosition(this.data.position + (eventObj[axis] || this.data.start) - this.data.start);
      this.set(value);

      event.preventDefault();
      return false;
    };
    this.mouseup = function() {
      $(document).off('.asRange mousemove.asRange touchend.asRange mouseup.asRange touchcancel.asRange');
      this.$element.trigger(`${this.parent.namespace}::moveEnd`, this);
      return false;
    };

    $(document).on('touchmove.asRange mousemove.asRange', $.proxy(this.mousemove, this))
      .on('touchend.asRange mouseup.asRange', $.proxy(this.mouseup, this));
    return false;
  }

  active() {
    this.$element.addClass(this.classes.active);
  }

  deactive() {
    this.$element.removeClass(this.classes.active);
  }

  set(value) {
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

    this.$element.trigger(`${this.parent.namespace}::move`, this);
  }

  updatePosition() {
    const position = {};

    position[this.parent.direction.position] = `${this.getPercent()}%`;
    this.$element.css(position);
  }

  getPercent() {
    return ((this.value - this.parent.min) / this.parent.interval) * 100;
  }

  get() {
    return this.value;
  }

  matchStep(value) {
    const step = this.parent.step;
    const decimal = step.toString().split('.')[1];

    value = Math.round(value / step) * step;

    if (decimal) {
      value = value.toFixed(decimal.length);
    }

    return parseFloat(value);
  }

  matchLimit(value) {
    let left;
    let right;
    const pointer = this.parent.pointer;

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

  destroy() {
    this.$element.off('.asRange');
    this.$element.remove();
  }
}

var scale = {
  defaults: {
    scale: {
      valuesNumber: 3,
      gap: 1,
      grid: 5
    }
  },
  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.scale);
    const scale = opts.scale;
    scale.values = [];
    scale.values.push(instance.min);
    const part = (instance.max - instance.min) / (scale.valuesNumber - 1);
    for (let j = 1; j <= (scale.valuesNumber - 2); j++) {
      scale.values.push(part * j);
    }
    scale.values.push(instance.max);
    const classes = {
      scale: `${instance.namespace}-scale`,
      lines: `${instance.namespace}-scale-lines`,
      grid: `${instance.namespace}-scale-grid`,
      inlineGrid: `${instance.namespace}-scale-inlineGrid`,
      values: `${instance.namespace}-scale-values`
    };

    const len = scale.values.length;
    const num = ((scale.grid - 1) * (scale.gap + 1) + scale.gap) * (len - 1) + len;
    const perOfGrid = 100 / (num - 1);
    const perOfValue = 100 / (len - 1);

    this.$scale = $('<div></div>').addClass(classes.scale);
    this.$lines = $('<ul></ul>').addClass(classes.lines);
    this.$values = $('<ul></ul>').addClass(classes.values);

    for (let i = 0; i < num; i++) {
      let $list;
      if (i === 0 || i === num || i % ((num - 1) / (len - 1)) === 0) {
        $list = $(`<li class="${classes.grid}"></li>`);
      } else if (i % scale.grid === 0) {
        $list = $(`<li class="${classes.inlineGrid}"></li>`);
      } else {
        $list = $('<li></li>');
      }

      // position scale
      $list.css({
        left: `${perOfGrid * i}%`
      }).appendTo(this.$lines);
    }

    for (let v = 0; v < len; v++) {
      // position value
      $(`<li><span>${scale.values[v]}</span></li>`).css({
        left: `${perOfValue * v}%`
      }).appendTo(this.$values);
    }

    this.$lines.add(this.$values).appendTo(this.$scale);
    this.$scale.appendTo(instance.$wrap);
  },
  update(instance) {
    this.$scale.remove();
    this.init(instance);
  }
};

var selected = {
  defaults: {},
  init(instance) {
    this.$arrow = $('<span></span>').appendTo(instance.$wrap);
    this.$arrow.addClass(`${instance.namespace}-selected`);

    if (instance.options.range === false) {
      instance.p1.$element.on(`${instance.namespace}::move`, (e, pointer) => {
        this.$arrow.css({
          left: 0,
          width: `${pointer.getPercent()}%`
        });
      });
    }

    if (instance.options.range === true) {
      const onUpdate = () => {
        let width = instance.p2.getPercent() - instance.p1.getPercent();
        let left;
        if (width >= 0) {
          left = instance.p1.getPercent();
        } else {
          width = -width;
          left = instance.p2.getPercent();
        }
        this.$arrow.css({
          left: `${left}%`,
          width: `${width}%`
        });
      };
      instance.p1.$element.on(`${instance.namespace}::move`, onUpdate);
      instance.p2.$element.on(`${instance.namespace}::move`, onUpdate);
    }
  }
};

var tip = {
  defaults: {
    active: 'always' // 'always' 'onMove'
  },
  init(instance) {
    const that = this;
    const opts = $.extend({}, this.defaults, instance.options.tip);

    this.opts = opts;
    this.classes = {
      tip: `${instance.namespace}-tip`,
      show: `${instance.namespace}-tip-show`
    };
    $.each(instance.pointer, (i, p) => {
      const $tip = $('<span></span>').appendTo(instance.pointer[i].$element);

      $tip.addClass(that.classes.tip);
      if (that.opts.active === 'onMove') {
        $tip.css({
          display: 'none'
        });
        p.$element.on(`${instance.namespace}::moveEnd`, () => {
          that.hide($tip);
          return false;
        }).on(`${instance.namespace}::moveStart`, () => {
          that.show($tip);
          return false;
        });
      }
      p.$element.on(`${instance.namespace}::move`, () => {
        let value;
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
            if (typeof instance.options.replaceFirst === 'object') {
              for (const key in instance.options.replaceFirst) {
                if(Object.hasOwnProperty(instance.options.replaceFirst, key)){
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
  show($tip) {
    $tip.addClass(this.classes.show);
    $tip.css({
      display: 'block'
    });
  },
  hide($tip) {
    $tip.removeClass(this.classes.show);
    $tip.css({
      display: 'none'
    });
  }
};

var keyboard = function() {
  const $doc = $(document);

  $doc.on('asRange::ready', (event, instance) => {
    let step;

    const keyboard = {
      keys: {
        'UP': 38,
        'DOWN': 40,
        'LEFT': 37,
        'RIGHT': 39,
        'RETURN': 13,
        'ESCAPE': 27,
        'BACKSPACE': 8,
        'SPACE': 32
      },
      map: {},
      bound: false,
      press(e) {
        /*eslint consistent-return: "off"*/
        const key = e.keyCode || e.which;
        if (key in keyboard.map && typeof keyboard.map[key] === 'function') {
          keyboard.map[key](e);
          return false;
        }
      },
      attach(map) {
        let key;
        let up;
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
      detach() {
        keyboard.bound = false;
        keyboard.map = {};
        $doc.unbind('keydown', keyboard.press);
      }
    };

    if (instance.options.keyboard === true) {
      $.each(instance.pointer, (i, p) => {
        if (instance.options.step) {
          step = instance.options.step;
        } else {
          step = 1;
        }
        const left = () => {
          const value = p.value;
          p.set(value - step);
        };
        const right = () => {
          const value = p.value;
          p.set(value + step);
        };
        p.$element.attr('tabindex', '0').on('focus', () => {
          keyboard.attach({
            left,
            right
          });
          return false;
        }).on('blur', () => {
          keyboard.detach();
          return false;
        });
      });
    }
  });
};

let components = {};

/**
 * Plugin constructor
 **/
class asRange {
  constructor(element, options) {
    const metas = {};
    this.element = element;
    this.$element = $(element);

    if (this.$element.is('input')) {
      const value = this.$element.val();

      if (typeof value === 'string') {
        metas.value = value.split(',');
      }

      $.each(['min', 'max', 'step'], (index, key) => {
        const val = parseFloat(this.$element.attr(key));
        if (!isNaN(val)) {
          metas[key] = val;
        }
      });

      this.$element.css({
        display: 'none'
      });
      this.$wrap = $("<div></div>");
      this.$element.after(this.$wrap);
    } else {
      this.$wrap = this.$element;
    }

    this.options = $.extend({}, DEFAULTS, options, this.$element.data(), metas);
    this.namespace = this.options.namespace;
    this.components = $.extend(true, {}, components);
    if (this.options.range) {
      this.options.replaceFirst = false;
    }

    // public properties
    this.value = this.options.value;
    if (this.value === null) {
      this.value = this.options.min;
    }

    if (!this.options.range) {
      if ($.isArray(this.value)) {
        this.value = this.value[0];
      }
    } else if (!$.isArray(this.value)) {
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
      this.$wrap.addClass(`${this.namespace}_${this.options.skin}`);
    }

    if (this.max < this.min || this.step >= this.interval) {
      throw new Error('error options about max min step');
    }

    this.init();
  }

  init() {
    this.$wrap.append(`<div class="${this.namespace}-bar" />`);

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

  _trigger(eventType, ...params) {
    let data = [this].concat(params);

    // event
    this.$element.trigger(this.namespace + `::${eventType}`, data);

    // callback
    eventType = eventType.replace(/\b\w+\b/g, (word) => {
      return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
    let onFunction = `on${eventType}`;

    if (typeof this.options[onFunction] === 'function') {
      this.options[onFunction].apply(this, params);
    }
  }

  buildPointers() {
    this.pointer = [];
    let pointerCount = 1;
    if (this.options.range) {
      pointerCount = 2;
    }
    for (let i = 1; i <= pointerCount; i++) {
      const $pointer = $(`<div class="${this.namespace}-pointer ${this.namespace}-pointer-${i}"></div>`).appendTo(this.$wrap);
      const p = new Pointer($pointer, i, this);
      this.pointer.push(p);
    }

    // alias of pointer
    this.p1 = this.pointer[0];

    if (this.options.range) {
      this.p2 = this.pointer[1];
    }
  }

  bindEvents() {
    const that = this;
    this.$wrap.on('touchstart.asRange mousedown.asRange', event => {
      /*eslint consistent-return: "off"*/
      if (that.disabled === true) {
        return;
      }
      event = getEventObject(event);
      const rightclick = (event.which) ? (event.which === 3) : (event.button === 2);
      if (rightclick) {
        return false;
      }

      const offset = that.$wrap.offset();
      const start = event[that.direction.axis] - offset[that.direction.position];
      const p = that.getAdjacentPointer(start);

      p.mousedown(event);
      return false;
    });

    if (this.$element.is('input')) {
      this.$element.on(this.namespace + `::change`, () => {
        const value = this.get();
        this.$element.val(value);
      });
    }

    $.each(this.pointer, (i, p) => {
      p.$element.on(this.namespace + `::move`, () => {
        that.value = that.get();
        if (!that.initialized || that.updating) {
          return false;
        }
        that._trigger('change', that.value);
        return false;
      });
    });
  }

  getValueFromPosition(px) {
    if (px > 0) {
      return this.min + (px / this.getLength()) * this.interval;
    }
    return 0;
  }

  getAdjacentPointer(start) {
    const value = this.getValueFromPosition(start);
    if (this.options.range) {
      const p1 = this.p1.value;
      const p2 = this.p2.value;
      const diff = Math.abs(p1 - p2);
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

  getLength() {
    if (this.options.direction === 'v') {
      return this.$wrap.height();
    }
    return this.$wrap.width();
  }

  update(options) {
    this.updating = true;
    $.each(['max', 'min', 'step', 'limit', 'value'], (key, value) => {
      if (options[value]) {
        this[value] = options[value];
      }
    });
    if (options.max || options.min) {
      this.setInterval(options.min, options.max);
    }

    if (!options.value) {
      this.value = options.min;
    }

    $.each(this.components, (key, value) => {
      if (typeof value.update === "function") {
        value.update(this);
      }
    });

    this.set(this.value);

    this._trigger('update');

    this.updating = false;
  }

  get() {
    const value = [];

    $.each(this.pointer, (i, p) => {
      value[i] = p.get();
    });

    if (this.options.range) {
      return value;
    }

    if (value[0] === this.options.min) {
      if (typeof this.options.replaceFirst === 'string') {
        value[0] = this.options.replaceFirst;
      }
      if (typeof this.options.replaceFirst === 'object') {
        for (const key in this.options.replaceFirst) {
          if(Object.hasOwnProperty(this.options.replaceFirst, key)){
            value[0] = key;
          }
        }
      }
    }

    return value[0];
  }

  set(value) {
    if (this.options.range) {
      if (typeof value === 'number') {
        value = [value];
      }
      if (!$.isArray(value)) {
        return;
      }
      $.each(this.pointer, (i, p) => {
        p.set(value[i]);
      });
    } else {
      this.p1.set(value);
    }

    this.value = value;
  }

  val(value) {
    if (value) {
      this.set(value);
      return this;
    }
    return this.get();
  }

  setInterval(start, end) {
    this.min = start;
    this.max = end;
    this.interval = end - start;
  }

  enable() {
    this.disabled = false;
    this.$wrap.removeClass(`${this.namespace}_disabled`);

    this._trigger('enable');
    return this;
  }

  disable() {
    this.disabled = true;
    this.$wrap.addClass(`${this.namespace}_disabled`);

    this._trigger('disable');
    return this;
  }

  destroy() {
    $.each(this.pointer, (i, p) => {
      p.destroy();
    });
    this.$wrap.destroy();

    this._trigger('destroy');
  }

  static registerComponent(component, methods) {
    components[component] = methods;
  }

  static setDefaults(options) {
    $.extend(DEFAULTS, $.isPlainObject(options) && options);
  }
}

asRange.registerComponent('scale', scale);
asRange.registerComponent('selected', selected);
asRange.registerComponent('tip', tip);
keyboard();

var info = {
  version:'0.3.4'
};

const NAMESPACE = 'asRange';
const OtherAsRange = $.fn.asRange;

const jQueryAsRange = function(options, ...args) {
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
      $(this).data(NAMESPACE, new asRange(this, options));
    }
  });
};

$.fn.asRange = jQueryAsRange;

$.asRange = $.extend({
  setDefaults: asRange.setDefaults,
  noConflict: function() {
    $.fn.asRange = OtherAsRange;
    return jQueryAsRange;
  }
}, info);
