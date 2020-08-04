/**
* jQuery asBreadcrumbs v0.2.3
* https://github.com/amazingSurge/jquery-asBreadcrumbs
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

var DEFAULTS = {
  namespace: 'breadcrumb',
  overflow: "left",

  responsive: true,

  ellipsisText: "&#8230;",
  ellipsisClass: null,

  hiddenClass: 'is-hidden',

  dropdownClass: null,
  dropdownMenuClass: null,
  dropdownItemClass: null,
  dropdownItemDisableClass: 'disabled',

  toggleClass: null,
  toggleIconClass: 'caret',

  getItems: function($parent) {
    return $parent.children();
  },

  getItemLink: function($item) {
    return $item.find('a');
  },

  // templates
  ellipsis: function(classes, label) {
    return `<li class="${classes.ellipsisClass}">${label}</li>`;
  },

  dropdown: function(classes) {
    const dropdownClass = 'dropdown';
    let dropdownMenuClass = 'dropdown-menu';

    if (this.options.overflow === 'right') {
      dropdownMenuClass += ' dropdown-menu-right';
    }

    return `<li class="${dropdownClass} ${classes.dropdownClass}">
      <a href="javascript:void(0);" class="${classes.toggleClass}" data-toggle="dropdown">
        <i class="${classes.toggleIconClass}"></i>
      </a>
      <ul class="${dropdownMenuClass} ${classes.dropdownMenuClass}"></ul>
    </li>`;
  },

  dropdownItem: function(classes, label, href) {
    if(!href) {
      return `<li class="${classes.dropdownItemClass} ${classes.dropdownItemDisableClass}"><a href="#">${label}</a></li>`;
    }
    return `<li class="${classes.dropdownItemClass}"><a href="${href}">${label}</a></li>`;
  },

  // callbacks
  onInit: null,
  onReady: null
};

const NAMESPACE = 'asBreadcrumbs';
let instanceId = 0;

/**
 * Plugin constructor
 **/
class asBreadcrumbs {
  constructor(element, options) {
    this.element = element;
    this.$element = $(element);

    this.options = $.extend({}, DEFAULTS, options, this.$element.data());

    this.namespace = this.options.namespace;
    this.$element.addClass(this.namespace);

    this.classes = {
      toggleClass: this.options.toggleClass? this.options.toggleClass: `${this.namespace}-toggle`,
      toggleIconClass: this.options.toggleIconClass,
      dropdownClass: this.options.dropdownClass? this.options.dropdownClass: `${this.namespace}-dropdown`,
      dropdownMenuClass: this.options.dropdownMenuClass? this.options.dropdownMenuClass: `${this.namespace}-dropdown-menu`,
      dropdownItemClass: this.options.dropdownItemClass? this.options.dropdownItemClass: '',
      dropdownItemDisableClass: this.options.dropdownItemDisableClass? this.options.dropdownItemDisableClass: '',
      ellipsisClass: this.options.ellipsisClass? this.options.ellipsisClass: `${this.namespace}-ellipsis`,
      hiddenClass: this.options.hiddenClass
    };

    // flag
    this.initialized = false;
    this.instanceId = (++instanceId);

    this.$children = this.options.getItems(this.$element);
    this.$firstChild = this.$children.eq(0);

    this.$dropdown = null;
    this.$dropdownMenu = null;

    this.gap = 6;
    this.items = [];

    this._trigger('init');
    this.init();
  }

  init() {
    this.$element.addClass(`${this.namespace}-${this.options.overflow}`);

    this._prepareItems();
    this._createDropdown();
    this._createEllipsis();

    this.render();

    if (this.options.responsive) {
      $(window).on(this.eventNameWithId('resize'), this._throttle(() => {
        this.resize();
      }, 250));
    }

    this.initialized = true;
    this._trigger('ready');
  }

  _prepareItems() {
    const that = this;

    this.$children.each(function(){
      const $this = $(this);
      const $link = that.options.getItemLink($this);
      const $dropdownItem = $(that.options.dropdownItem.call(that, that.classes, $this.text(), $link.attr('href')));

      that.items.push({
        $this,
        outerWidth: $this.outerWidth(),
        $item: $dropdownItem
      });
    });

    if (this.options.overflow === "left") {
      this.items.reverse();
    }
  }

  _createDropdown() {
    this.$dropdown = $(this.options.dropdown.call(this, this.classes)).addClass(this.classes.hiddenClass).appendTo(this.$element);
    this.$dropdownMenu = this.$dropdown.find(`.${this.classes.dropdownMenuClass}`);

    this._createDropdownItems();

    if (this.options.overflow === 'right') {
      this.$dropdown.appendTo(this.$element);
    } else {
      this.$dropdown.prependTo(this.$element);
    }
  }

  _createDropdownItems() {
    for (let i = 0; i < this.items.length; i++) {
      this.items[i].$item.appendTo(this.$dropdownMenu).addClass(this.classes.hiddenClass);
    }
  }

  _createEllipsis() {
    if (!this.options.ellipsisText) {
      return;
    }

    this.$ellipsis = $(this.options.ellipsis.call(this, this.classes, this.options.ellipsisText)).addClass(this.classes.hiddenClass);

    if (this.options.overflow === 'right') {
      this.$ellipsis.insertBefore(this.$dropdown);
    } else {
      this.$ellipsis.insertAfter(this.$dropdown);
    }
  }

  render() {
    const dropdownWidth = this.getDropdownWidth();
    let childrenWidthTotal = 0;
    let containerWidth = this.getConatinerWidth();

    let showDropdown = false;

    for (let i = 0; i < this.items.length; i++) {
      childrenWidthTotal += this.items[i].outerWidth;

      if (childrenWidthTotal + dropdownWidth > containerWidth) {
        showDropdown = true;
        this._showDropdownItem(i);
      } else {
        this._hideDropdownItem(i);
      }
    }

    if(showDropdown) {
      this.$ellipsis.removeClass(this.classes.hiddenClass);
      this.$dropdown.removeClass(this.classes.hiddenClass);
    } else {
      this.$ellipsis.addClass(this.classes.hiddenClass);
      this.$dropdown.addClass(this.classes.hiddenClass);
    }

    this._trigger('update');
  }

  resize() {
    this.render();
  }

  getDropdownWidth() {
    return this.$dropdown.outerWidth() + (this.options.ellipsisText? this.$ellipsis.outerWidth() : 0);
  }

  getConatinerWidth() {
    let width = 0;
    const that = this;

    this.$element.children().each(function() {
      if ($(this).css('display') === 'inline-block' && $(this).css('float') === 'none') {
        width += that.gap;
      }
    });
    return this.$element.width() - width;
  }

  _showDropdownItem(i) {
    this.items[i].$item.removeClass(this.classes.hiddenClass);
    this.items[i].$this.addClass(this.classes.hiddenClass);
  }

  _hideDropdownItem(i) {
    this.items[i].$this.removeClass(this.classes.hiddenClass);
    this.items[i].$item.addClass(this.classes.hiddenClass);
  }

  _trigger(eventType, ...params) {
    let data = [this].concat(params);

    // event
    this.$element.trigger(`${NAMESPACE}::${eventType}`, data);

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

  eventNameWithId(events) {
    if (typeof events !== 'string' || events === '') {
      return `.${this.options.namespace}-${this.instanceId}`;
    }

    events = events.split(' ');
    let length = events.length;
    for (let i = 0; i < length; i++) {
      events[i] = `${events[i]}.${this.options.namespace}-${this.instanceId}`;
    }
    return events.join(' ');
  }

  _throttle(func, wait) {
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

  destroy() {
    this.$element.children().removeClass(this.classes.hiddenClass);
    this.$dropdown.remove();

    if (this.options.ellipsisText) {
      this.$ellipsis.remove();
    }

    this.initialized = false;

    this.$element.data(NAMESPACE, null);
    $(window).off(this.eventNameWithId('resize'));
    this._trigger('destroy');
  }

  static setDefaults(options) {
    $.extend(DEFAULTS, $.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.2.3'
};

const NAME = 'asBreadcrumbs';
const OtherAsBreadcrumbs = $.fn.asBreadcrumbs;

const jQueryAsBreadcrumbs = function(options, ...args) {
  if (typeof options === 'string') {
    let method = options;

    if (/^_/.test(method)) {
      return false;
    } else if ((/^(get)/.test(method))) {
      let instance = this.first().data(NAME);
      if (instance && typeof instance[method] === 'function') {
        return instance[method](...args);
      }
    } else {
      return this.each(function() {
        let instance = $.data(this, NAME);
        if (instance && typeof instance[method] === 'function') {
          instance[method](...args);
        }
      });
    }
  }

  return this.each(function() {
    if (!$(this).data(NAME)) {
      $(this).data(NAME, new asBreadcrumbs(this, options));
    }
  });
};

$.fn.asBreadcrumbs = jQueryAsBreadcrumbs;

$.asBreadcrumbs = $.extend({
  setDefaults: asBreadcrumbs.setDefaults,
  noConflict: function() {
    $.fn.asBreadcrumbs = OtherAsBreadcrumbs;
    return jQueryAsBreadcrumbs;
  }
}, info);
