/**
* jQuery asPaginator v0.3.3
* https://github.com/amazingSurge/jquery-asPaginator
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

var DEFAULTS = {
  namespace: 'asPaginator',

  currentPage: 1,
  itemsPerPage: 10,
  visibleNum: 5,
  resizeThrottle: 250,

  disabledClass: 'asPaginator_disable',
  activeClass: 'asPaginator_active',

  tpl() {
    return '<ul>{{first}}{{prev}}{{lists}}{{next}}{{last}}</ul>';
  },

  skin: null,
  components: {
    first: true,
    prev: true,
    next: true,
    last: true,
    lists: true
  },

  // callback function
  onInit: null,
  onReady: null,
  onChange: null // function(page) {}
};

function throttle(func, wait) {
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

const NAMESPACE$1 = 'asPaginator';
const COMPONENTS = {};

/**
 * Plugin constructor
 **/
class AsPaginator {
  constructor(element, totalItems, options) {
    this.element = element;
    this.$element = $(element).empty();

    this.options = $.extend({}, DEFAULTS, options);
    this.namespace = this.options.namespace;

    this.currentPage = this.options.currentPage || 1;
    this.itemsPerPage = this.options.itemsPerPage;
    this.totalItems = totalItems;
    this.totalPages = this.getTotalPages();

    if (this.isOutOfBounds()) {
      this.currentPage = this.totalPages;
    }

    this.initialized = false;
    this.components = COMPONENTS;
    this.$element.addClass(this.namespace);

    if (this.options.skin) {
      this.$element.addClass(this.options.skin);
    }

    this.classes = {
      disabled: this.options.disabledClass,
      active: this.options.activeClass
    };

    this.disabled = false;

    this._trigger('init');
    this.init();
  }

  init() {
    const that = this;

    that.visible = that.getVisible();

    $.each(this.options.components, (key, value) => {
      if (value === null || value === false) {
        return false;
      }

      that.components[key].init(that);
    });

    that.createHtml();
    that.bindEvents();

    that.goTo(that.currentPage);

    that.initialized = true;

    // responsive
    if (typeof this.options.visibleNum !== 'number') {
      $(window).on('resize', throttle(() => {
        that.resize();
      }, this.options.resizeTime));
    }

    this._trigger('ready');
  }

  createHtml() {
    const that = this;
    let contents;
    that.contents = that.options.tpl();

    const length = that.contents.match(/\{\{([^\}]+)\}\}/g).length;
    let components;

    for (let i = 0; i < length; i++) {
      components = that.contents.match(/\{\{([^\}]+)\}\}/);

      if (components[1] === 'namespace') {
        that.contents = that.contents.replace(components[0], that.namespace);
        continue;
      }

      if (this.options.components[components[1]]) {
        contents = that.components[components[1]].opts.tpl.call(that);
        that.contents = that.contents.replace(components[0], contents);
      }
    }

    that.$element.append($(that.contents));
  }

  bindEvents() {
    const that = this;

    $.each(this.options.components, (key, value) => {
      if (value === null || value === false) {
        return false;
      }

      that.components[key].bindEvents(that);
    });
  }

  unbindEvents() {
    const that = this;

    $.each(this.options.components, (key, value) => {
      if (value === null || value === false) {
        return false;
      }

      that.components[key].unbindEvents(that);
    });
  }

  resize() {
    const that = this;
    that._trigger('resize');
    that.goTo(that.currentPage);
    that.visible = that.getVisible();

    $.each(this.options.components, (key, value) => {
      if (value === null || value === false) {
        return false;
      }

      if (typeof that.components[key].resize === 'undefined') {
        return;
      }

      that.components[key].resize(that);
    });
  }

  getVisible() {
    const width = $('body, html').width();
    let adjacent = 0;
    if (typeof this.options.visibleNum !== 'number') {
      $.each(this.options.visibleNum, (i, v) => {
        if (width > i) {
          adjacent = v;
        }
      });
    } else {
      adjacent = this.options.visibleNum;
    }

    return adjacent;
  }

  calculate(current, total, visible) {
    let omitLeft = 1;
    let omitRight = 1;

    if (current <= visible + 2) {
      omitLeft = 0;
    }

    if (current + visible + 1 >= total) {
      omitRight = 0;
    }

    return {
      left: omitLeft,
      right: omitRight
    };
  }

  goTo(page) {
    page = Math.max(1, Math.min(page, this.totalPages));

    // if true , dont relaod again
    if (page === this.currentPage && this.initialized === true) {
      return false;
    }

    this.$element.find(`.${this.classes.disabled}`).removeClass(this.classes.disabled);

    // when add class when go to the first one or the last one
    if (page === this.totalPages) {
      this.$element.find(`.${this.namespace}-next`).addClass(this.classes.disabled);
      this.$element.find(`.${this.namespace}-last`).addClass(this.classes.disabled);
    }

    if (page === 1) {
      this.$element.find(`.${this.namespace}-prev`).addClass(this.classes.disabled);
      this.$element.find(`.${this.namespace}-first`).addClass(this.classes.disabled);
    }

    // here change current page first, and then trigger 'change' event
    this.currentPage = page;

    if (this.initialized) {
      this._trigger('change', page);
    }
  }

  prev() {
    if (this.hasPreviousPage()) {
      this.goTo(this.getPreviousPage());
      return true;
    }

    return false;
  }

  next() {
    if (this.hasNextPage()) {
      this.goTo(this.getNextPage());
      return true;
    }

    return false;
  }

  goFirst() {
    return this.goTo(1);
  }

  goLast() {
    return this.goTo(this.totalPages);
  }

  // update({totalItems: 10, itemsPerPage: 5, currentPage:3});
  // update('totalPage', 10);
  update(data, value) {
    let changes = {};

    if (typeof data === "string") {
      changes[data] = value;
    } else {
      changes = data;
    }

    for (const option in changes) {
      if (Object.hasOwnProperty.call(changes, option)) {
        switch (option) {
          case 'totalItems':
            this.totalItems = changes[option];
            break;
          case 'itemsPerPage':
            this.itemsPerPage = changes[option];
            break;
          case 'currentPage':
            this.currentPage = changes[option];
            break;
          default:
            break;
        }
      }
    }

    this.totalPages = this.totalPages();
    // wait to do
  }

  isOutOfBounds() {
    return this.currentPage > this.totalPages;
  }

  getItemsPerPage() {
    return this.itemsPerPage;
  }

  getTotalItems() {
    return this.totalItems;
  }

  getTotalPages() {
    this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
    this.lastPage = this.totalPages;
    return this.totalPages;
  }

  getCurrentPage() {
    return this.currentPage;
  }

  hasPreviousPage() {
    return this.currentPage > 1;
  }

  getPreviousPage() {
    if (this.hasPreviousPage()) {
      return this.currentPage - 1;
    }
    return false;
  }

  hasNextPage() {
    return this.currentPage < this.totalPages;
  }

  getNextPage() {
    if (this.hasNextPage()) {
      return this.currentPage + 1;
    }
    return false;
  }

  enable() {
    if (this.disabled) {
      this.disabled = false;

      this.$element.removeClass(this.classes.disabled);

      this.bindEvents();
    }

    this._trigger('enable');
  }

  disable() {
    if (this.disabled !== true) {
      this.disabled = true;

      this.$element.addClass(this.classes.disabled);

      this.unbindEvents();
    }

    this._trigger('disable');
  }

  destroy() {
    this.$element.removeClass(this.classes.disabled);
    this.unbindEvents();
    this.$element.data(NAMESPACE$1, null);
    this._trigger('destroy');
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

  static registerComponent(name, method) {
    COMPONENTS[name] = method;
  }

  static setDefaults(options) {
    $.extend(DEFAULTS, $.isPlainObject(options) && options);
  }
}

AsPaginator.registerComponent('prev', {
  defaults: {
    tpl() {
      return `<li class="${this.namespace}-prev"><a>Prev</a></li>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.prev);

    this.opts = opts;
  },

  bindEvents(instance) {
    this.$prev = instance.$element.find(`.${instance.namespace}-prev`);
    this.$prev.on('click.asPaginator', $.proxy(instance.prev, instance));
  },

  unbindEvents() {
    this.$prev.off('click.asPaginator');
  }
});

AsPaginator.registerComponent('next', {
  defaults: {
    tpl() {
      return `<li class="${this.namespace}-next"><a>Next</a></li>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.next);

    this.opts = opts;
  },

  bindEvents(instance) {
    this.$next = instance.$element.find(`.${instance.namespace}-next`);
    this.$next.on('click.asPaginator', $.proxy(instance.next, instance));
  },

  unbindEvents() {
    this.$next.off('click.asPaginator');
  }
});

AsPaginator.registerComponent('first', {
  defaults: {
    tpl() {
      return `<li class="${this.namespace}-first"><a>First</a></li>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.first);

    this.opts = opts;
  },

  bindEvents(instance) {
    this.$first = instance.$element.find(`.${instance.namespace}-first`);
    this.$first.on('click.asPaginator', $.proxy(instance.goFirst, instance));
  },

  unbindEvents() {
    this.$first.off('click.asPaginator');
  }
});

AsPaginator.registerComponent('last', {
  defaults: {
    tpl() {
      return `<li class="${this.namespace}-last"><a>Last</a></li>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.last);

    this.opts = opts;
  },

  bindEvents(instance) {
    this.$last = instance.$element.find(`.${instance.namespace}-last`);
    this.$last.on('click.asPaginator', $.proxy(instance.goLast, instance));
  },

  unbindEvents() {
    this.$last.off('click.asPaginator');
  }
});

AsPaginator.registerComponent('lists', {
  defaults: {
    tpl() {
      let lists = '';
      let remainder = this.currentPage >= this.visible ? this.currentPage % this.visible : this.currentPage;
      remainder = remainder === 0 ? this.visible : remainder;
      for (let k = 1; k < remainder; k++) {
        lists += `<li class="${this.namespace}-items" data-value="${this.currentPage - remainder + k}"><a href="#">${this.currentPage - remainder + k}</a></li>`;
      }
      lists += `<li class="${this.namespace}-items ${this.classes.active}" data-value="${this.currentPage}"><a href="#">${this.currentPage}</a></li>`;
      for (let i = this.currentPage + 1, limit = i + this.visible - remainder - 1 > this.totalPages ? this.totalPages : i + this.visible - remainder - 1; i <= limit; i++) {
        lists += `<li class="${this.namespace}-items" data-value="${i}"><a href="#">${i}</a></li>`;
      }

      return lists;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.lists);

    this.opts = opts;

    instance.itemsTpl = this.opts.tpl.call(instance);
  },

  bindEvents(instance) {
    const that = this;
    this.$items = instance.$element.find(`.${instance.namespace}-items`);
    instance.$element.on('click', this.$items, e => {
      const page = $(e.target).parent().data('value') || $(e.target).data('value');

      if (page === undefined) {
        //console.log("wrong page value or prev&&next");
        return false;
      }

      if (page === '') {
        return false;
      }

      instance.goTo(page);
    });

    that.render(instance);
    instance.$element.on('asPaginator::change', () => {
      that.render(instance);
    });
  },

  unbindEvents(instance) {
    instance.$element.off('click', this.$items);
  },

  resize(instance) {
    this.render(instance);
  },

  render(instance) {
    const current = instance.currentPage;
    let overflow;
    const that = this;

    const array = this.$items.removeClass(instance.classes.active);
    $.each(array, (i, v) => {

      if ($(v).data('value') === current) {
        $(v).addClass(instance.classes.active);
        overflow = false;
        return false;
      }
    });

    if (overflow === false && this.visibleBefore === instance.visible) {
      return;
    }

    this.visibleBefore = instance.visible;

    $.each(array, (i, v) => {
      if (i === 0) {
        $(v).replaceWith(that.opts.tpl.call(instance));
      } else {
        $(v).remove();
      }
    });
    this.$items = instance.$element.find(`.${instance.namespace}-items`);
  }
});

AsPaginator.registerComponent('goTo', {
  defaults: {
    tpl() {
      return `<div class="${this.namespace}-goTo"><input type="text" class="${this.namespace}-input" /><button type="submit" class="${this.namespace}-submit">Go</button></div>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.goTo);

    this.opts = opts;
  },

  bindEvents(instance) {
    const that = this;
    that.$goTo = instance.$element.find(`.${instance.namespace}-goTo`);
    that.$input = that.$goTo.find(`.${instance.namespace}-input`);
    that.$button = that.$goTo.find(`.${instance.namespace}-submit`);

    that.$button.on('click', () => {
      let page = parseInt(that.$input.val(), 10);
      page = page > 0 ? page : instance.currentPage;
      instance.goTo(page);
    });
  },

  unbindEvents() {
    this.$button.off('click');
  }
});

AsPaginator.registerComponent('altLists', {
  defaults: {
    tpl() {
      let lists = '';
      const max = this.totalPages;
      const current = this.currentPage;
      const omit = this.calculate(current, max, this.visible);
      const that = this;
      let i;
      const item = (i, classes) => {
        if (classes === 'active') {
          return `<li class="${that.namespace}-items ${that.classes.active}" data-value="${i}"><a href="#">${i}</a></li>`;
        } else if (classes === 'omit') {
          return `<li class="${that.namespace}-items ${that.namespace}_ellipsis" data-value="ellipsis"><a href="#">...</a></li>`;
        } else {
          return `<li class="${that.namespace}-items" data-value="${i}"><a href="#">${i}</a></li>`;
        }
      };

      if (omit.left === 0) {
        for (i = 1; i <= current - 1; i++) {
          lists += item(i);
        }
        lists += item(current, 'active');
      } else {
        for (i = 1; i <= 2; i++) {
          lists += item(i);
        }

        lists += item(current, 'omit');

        for (i = current - this.visible + 1; i <= current - 1; i++) {
          lists += item(i);
        }

        lists += item(current, 'active');
      }

      if (omit.right === 0) {
        for (i = current + 1; i <= max; i++) {
          lists += item(i);
        }
      } else {
        for (i = current + 1; i <= current + this.visible - 1; i++) {
          lists += item(i);
        }

        lists += item(current, 'omit');

        for (i = max - 1; i <= max; i++) {
          lists += item(i);
        }
      }

      return lists;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.altLists);

    this.opts = opts;
  },

  bindEvents(instance) {
    const that = this;
    this.$items = instance.$element.find(`.${instance.namespace}-items`);
    instance.$element.on('click', this.$items, e => {
      const page = $(e.target).parent().data('value') || $(e.target).data('value');

      if (page === undefined) {
        //console.log("wrong page value or prev&&next");
        return false;
      }

      if (page === 'ellipsis') {
        return false;
      }

      if (page === '') {
        return false;
      }

      instance.goTo(page);
    });

    that.render(instance);
    instance.$element.on('asPaginator::change', () => {
      that.render(instance);
    });
  },

  unbindEvents(instance) {
    instance.$wrap.off('click', this.$items);
  },

  resize(instance) {
    this.render(instance);
  },

  render(instance) {
    const that = this;
    const array = this.$items.removeClass(instance.classes.active);
    $.each(array, (i, v) => {
      if (i === 0) {
        $(v).replaceWith(that.opts.tpl.call(instance));
      } else {
        $(v).remove();
      }
    });
    this.$items = instance.$element.find(`.${instance.namespace}-items`);
  }
});

AsPaginator.registerComponent('info', {
  defaults: {
    tpl() {
      return `<li class="${this.namespace}-info"><a href="javascript:void(0);"><span class="${this.namespace}-current"></span> / <span class="${this.namespace}-total"></span></a></li>`;
    }
  },

  init(instance) {
    const opts = $.extend({}, this.defaults, instance.options.components.info);

    this.opts = opts;
  },

  bindEvents(instance) {
    const $info = instance.$element.find(`.${instance.namespace}-info`);
    const $current = $info.find(`.${instance.namespace}-current`);
    $info.find(`.${instance.namespace}-total`).text(instance.totalPages);

    $current.text(instance.currentPage);
    instance.$element.on('asPaginator::change', () => {
      $current.text(instance.currentPage);
    });
  }
});

var info = {
  version:'0.3.3'
};

const NAMESPACE = 'asPaginator';
const OtherAsPaginator = $.fn.asPaginator;

const jQueryAsPaginator = function(totalItems, ...args) {
  if (!$.isNumeric(totalItems) && typeof totalItems === 'string') {
    const method = totalItems;

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
      $(this).data(NAMESPACE, new AsPaginator(this, totalItems, ...args));
    }
  });
};

$.fn.asPaginator = jQueryAsPaginator;

$.asPaginator = $.extend({
  registerComponent: AsPaginator.registerComponent,
  setDefaults: AsPaginator.setDefaults,
  noConflict: function() {
    $.fn.asPaginator = OtherAsPaginator;
    return jQueryAsPaginator;
  }
}, info);
