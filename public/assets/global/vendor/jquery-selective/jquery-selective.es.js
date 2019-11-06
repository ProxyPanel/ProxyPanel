/**
* jQuery Selective v0.3.5
* https://github.com/amazingSurge/jquery-selective
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $$1 from 'jquery';

/*eslint no-empty-function: "off"*/
var DEFAULTS = {
  namespace: 'selective',
  buildFromHtml: true,
  closeOnSelect: false,
  local: null,
  selected: null,
  withSearch: false,
  searchType: null, //'change' or 'keyup'
  ajax: {
    work: false,
    url: null,
    quietMills: null,
    loadMore: false,
    pageSize: null
  },
  query: function() {}, //function(api, search_text, page) {},
  tpl: {
    frame: function() {
      return `<div class="${this.namespace}"><div class="${this.namespace}-trigger">${this.options.tpl.triggerButton.call(this)}<div class="${this.namespace}-trigger-dropdown"><div class="${this.namespace}-list-wrap">${this.options.tpl.list.call(this)}</div></div></div>${this.options.tpl.items.call(this)}</div>`;
    },
    search: function() {
      return `<input class="${this.namespace}-search" type="text" placeholder="Search...">`;
    },
    select: function() {
      return `<select class="${this.namespace}-select" name="${this.namespace}" multiple="multiple"></select>`;
    },
    optionValue: function(data) {
      if('name' in data) {
        return data.name;
      }
      return data;
    },
    option: function(content) {
      return `<option value="${this.options.tpl.optionValue.call(this)}">${content}</option>`;
    },
    items: function() {
      return `<ul class="${this.namespace}-items"></ul>`;
    },
    item: function(content) {
      return `<li class="${this.namespace}-item">${content}${this.options.tpl.itemRemove.call(this)}</li>`;
    },
    itemRemove: function() {
      return `<span class="${this.namespace}-remove">x</span>`;
    },
    triggerButton: function() {
      return `<div class="${this.namespace}-trigger-button">Add</div>`;
    },
    list: function() {
      return `<ul class="${this.namespace}-list"></ul>`;
    },
    listItem: function(content) {
      return `<li class="${this.namespace}-list-item">${content}</li>`;
    }
  },

  onBeforeShow: null,
  onAfterShow: null,
  onBeforeHide: null,
  onAfterHide: null,
  onBeforeSearch: null,
  onAfterSearch: null,
  onBeforeSelected: null,
  onAfterSelected: null,
  onBeforeUnselect: null,
  onAfterUnselect: null,
  onBeforeItemRemove: null,
  onAfterItemRemove: null,
  onBeforeItemAdd: null,
  onAfterItemAdd: null
};

class Options {
  constructor(instance) {
    this.instance = instance;
  }

  getOptions() {
    this.instance.$options = this.instance.$select.find('option');
    return this.instance.$options;
  }

  select(opt) {
    $(opt).prop('selected', true);
    return this.instance;
  }

  unselect(opt) {
    $(opt).prop('selected', false);
    return this.instance;
  }

  add(data) {
    /*eslint consistent-return: "off"*/
    if (this.instance.options.buildFromHtml === false &&
      this.instance.getItem('option', this.instance.$select, this.instance.options.tpl.optionValue(data)) === undefined) {
      const $option = $(this.instance.options.tpl.option.call(this.instance, data));

      this.instance.setIndex($option, data);
      this.instance.$select.append($option);
      return $option;
    }
  }

  remove(opt) {
    $(opt).remove();
    return this.instance;
  }
}

class List {
  constructor(instance) {
    this.instance = instance;
  }

  build(data) {
    const $list = $('<ul></ul>');
    const $options = this.instance._options.getOptions();
    if (this.instance.options.buildFromHtml === true) {
      if ($options.length !== 0) {
        $.each($options, (i, n) => {
          const $li = $(this.instance.options.tpl.listItem.call(this.instance, n.text));
          const $n = $(n);
          this.instance.setIndex($li, $n);
          if ($n.attr('selected') !== undefined) {
            this.instance.select($li);
          }
          $list.append($li);
        });
      }
    } else if (data !== null) {
      $.each(data, i => {
        const $li = $(this.instance.options.tpl.listItem.call(this.instance, data[i]));

        this.instance.setIndex($li, data[i]);
        $list.append($li);
      });

      if ($options.length !== 0) {
        $.each($options, (i, n) => {
          const $n = $(n);
          const li = this.instance.getItem('li', $list, this.instance.options.tpl.optionValue($n.data('selective_index')));

          if (li !== undefined) {
            this.instance._list.select(li);
          }
        });
      }   
    }

    this.instance.$list.append($list.children('li'));
    return this.instance;
  }

  buildSearch() {
    if (this.instance.options.withSearch === true) {
      this.instance.$triggerDropdown.prepend(this.instance.options.tpl.search.call(this.instance));
      this.instance.$search = this.instance.$triggerDropdown.find(`.${this.instance.namespace}-search`);
    }
    return this.instance;
  }

  select(obj) {
    this.instance._trigger("beforeSelected");
    $(obj).addClass(`${this.instance.namespace}-selected`);
    this.instance._trigger("afterSelected");
    return this.instance;
  }

  unselect(obj) {
    this.instance._trigger("beforeUnselected");
    $(obj).removeClass(`${this.instance.namespace}-selected`);
    this.instance._trigger("afterUnselected");
    return this.instance;
  }

  click() {
    const that = this;
    this.instance.$list.on('click', 'li', function() {
      const $this = $(this);
      if (!$this.hasClass(`${that.instance.namespace}-selected`)) {
        that.instance.select($this);
      }
    });
  }

  filter(val) {
    $.expr[':'].Contains = (a, i, m) => jQuery(a).text().toUpperCase().includes(m[3].toUpperCase());
    if (val) {
      this.instance.$list.find(`li:not(:Contains(${val}))`).slideUp();
      this.instance.$list.find(`li:Contains(${val})`).slideDown();
    } else {
      this.instance.$list.children('li').slideDown();
    }
    return this.instance;
  }

  loadMore() {
    const pageMax = this.instance.options.ajax.pageSize || 9999;
       
    this.instance.$listWrap.on('scroll.selective', () => {
      if (pageMax > this.instance.page) {
        const listHeight = this.instance.$list.outerHeight(true);
        const wrapHeight = this.instance.$listWrap.outerHeight();
        const wrapScrollTop = this.instance.$listWrap.scrollTop();
        const below = listHeight - wrapHeight - wrapScrollTop;
        if (below === 0) {
          this.instance.options.query(this.instance, this.instance.$search.val(), ++this.instance.page);
        }
      }
    });
    return this.instance;
  }

  loadMoreRemove() {
    this.instance.$listWrap.off('scroll.selective');
    return this.instance;
  }
}

class Search {
  constructor(instance) {
    this.instance = instance;
  }

  change() {
    this.instance.$search.change(() => {
      this.instance._trigger("beforeSearch");
      if (this.instance.options.buildFromHtml === true) {
        this.instance._list.filter(this.instance.$search.val());
      } else if (this.instance.$search.val() !== '') {
        this.instance.page = 1;

        this.instance.options.query(this.instance, this.instance.$search.val(), this.instance.page);
      } else {
        this.instance.update(this.instance.options.local);
      }
      this.instance._trigger("afterSearch");
    });
  }

  keyup() {
    const quietMills = this.instance.options.ajax.quietMills || 1000;
    let oldValue = '';
    let currentValue = '';
    let timeout;

    this.instance.$search.on('keyup', e => {
      this.instance._trigger("beforeSearch");
      currentValue = this.instance.$search.val();
      if (this.instance.options.buildFromHtml === true) {
        if (currentValue !== oldValue) {
          this.instance._list.filter(currentValue);
        }
      } else if (currentValue !== oldValue || e.keyCode === 13) {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => {
          if (currentValue !== '') {
            this.instance.page = 1;
            this.instance.options.query(this.instance, currentValue, this.instance.page);
          } else {
            this.instance.update(this.instance.options.local);
          }
        }, quietMills);
      }
      oldValue = currentValue;
      this.instance._trigger("afterSearch");
    });
  }

  bind(type) {
    if (type === 'change') {
      this.change();
    } else if (type === 'keyup') {
      this.keyup();
    }
  }
}

class Items {
  constructor(instance) {
    this.instance = instance;
  }

  withDefaults(data) {
    if (data !== null) {
      $.each(data, i => {
        this.instance._options.add(data[i]);
        this.instance._options.select(this.instance.getItem('option', this.instance.$select, this.instance.options.tpl.optionValue(data[i])));
        this.instance._items.add(data[i]);
      });
    }
  }

  add(data, content) {
    let $item;

    let fill;
    if (this.instance.options.buildFromHtml === true) {
      fill = content;
    } else {
      fill = data;
    }
    $item = $(this.instance.options.tpl.item.call(this.instance, fill));
    this.instance.setIndex($item, data);
    this.instance.$items.append($item);
  }

  remove(obj) {
    obj = $(obj);
    let $li;
    let $option;
    if (this.instance.options.buildFromHtml === true) {
      this.instance._list.unselect(obj.data('selective_index'));
      this.instance._options.unselect(obj.data('selective_index').data('selective_index'));
    } else {
      $li = this.instance.getItem('li', this.instance.$list, this.instance.options.tpl.optionValue(obj.data('selective_index')));
      if ($li !== undefined) {
        this.instance._list.unselect($li);
      }
      $option = this.instance.getItem('option', this.instance.$select, this.instance.options.tpl.optionValue(obj.data('selective_index')));
      this.instance._options.unselect($option)._options.remove($option);
    }

    obj.remove();
    return this.instance;
  }

  click() {
    const that = this;
    this.instance.$items.on('click', `.${this.instance.namespace}-remove`, function() {
      const $this = $(this);
      const $item = $this.parents('li');
      that.instance.itemRemove($item);
    });
  }
}

const NAMESPACE$1 = 'selective';

/**
 * Plugin constructor
 **/
class Selective {
  constructor(element, options = {}) {
    this.element = element;
    this.$element = $$1(element).hide() || $$1('<select></select>');

    this.options = $$1.extend(true, {}, DEFAULTS, options);

    this.namespace = this.options.namespace;

    const $frame = $$1(this.options.tpl.frame.call(this));

    //get the select
    const _build = () => {
      this.$element.html(this.options.tpl.select.call(this));
      return this.$element.children('select');
    };

    this.$select = this.$element.is('select') === true ? this.$element : _build();

    this.$element.after($frame);

    this.init();
    this.opened = false;
  }

  init() {
    this.$selective = this.$element.next(`.${this.namespace}`);
    this.$items = this.$selective.find(`.${this.namespace}-items`);
    this.$trigger = this.$selective.find(`.${this.namespace}-trigger`);
    this.$triggerButton = this.$selective.find(`.${this.namespace}-trigger-button`);
    this.$triggerDropdown = this.$selective.find(`.${this.namespace}-trigger-dropdown`);
    this.$listWrap = this.$selective.find(`.${this.namespace}-list-wrap`);
    this.$list = this.$selective.find(`.${this.namespace}-list`);

    this._list = new List(this);
    this._options = new Options(this);
    this._search = new Search(this);
    this._items = new Items(this);

    this._items.withDefaults(this.options.selected);
    this.update(this.options.local)._list.buildSearch();

    this.$triggerButton.on('click', () => {
      if (this.opened === false) {
        this.show();
      } else {
        this.hide();
      }
    });

    this._list.click(this);
    this._items.click(this);

    if (this.options.withSearch === true) {
      this._search.bind(this.options.searchType);
    }

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

  _show() {
    $$1(document).on('click.selective', e => {
      if (this.options.closeOnSelect === true) {
        if ($$1(e.target).closest(this.$triggerButton).length === 0 &&
          $$1(e.target).closest(this.$search).length === 0) {
          this._hide();
        }
      } else if ($$1(e.target).closest(this.$trigger).length === 0) {
        this._hide();
      }
    });

    this.$trigger.addClass(`${this.namespace}-active`);
    this.opened = true;

    if (this.options.ajax.loadMore === true) {
      this._list.loadMore();
    }
    return this;
  }

  _hide() {
    $$1(document).off('click.selective');

    this.$trigger.removeClass(`${this.namespace}-active`);
    this.opened = false;

    if (this.options.ajax.loadMore === true) {
      this._list.loadMoreRemove();
    }
    return this;
  }

  show() {
    this._trigger("beforeShow");
    this._show();
    this._trigger("afterShow");
    return this;
  }

  hide() {
    this._trigger("beforeHide");
    this._hide();
    this._trigger("afterHide");
    return this;
  }

  select($li) {
    this._list.select($li);
    const data = $li.data('selective_index');

    if (this.options.buildFromHtml === true) {
      this._options.select(data);
      this.itemAdd($li, data.text());
    } else {
      this._options.add(data);
      this._options.select(this.getItem('option', this.$select, this.options.tpl.optionValue(data)));
      this.itemAdd(data);
    }

    return this;
  }

  unselect($li) {
    this._list.unselect($li);
    return this;
  }

  setIndex(obj, index) {
    obj.data('selective_index', index);
    return this;
  }

  getItem(type, $list, index) {
    const $items = $list.children(type);
    let position = '';
    for (let i = 0; i < $items.length; i++) {
      if (this.options.tpl.optionValue($items.eq(i).data('selective_index')) === index) {
        position = i;
      }
    }
    return position === '' ? undefined : $items.eq(position);
  }

  itemAdd(data, content) {
    this._trigger("beforeItemAdd");
    this._items.add(data, content);
    this._trigger("afterItemAdd");

    return this;
  }

  itemRemove($li) {
    this._trigger("beforeItemRemove");
    this._items.remove($li);
    this._trigger("afterItemRemove");

    return this;
  }

  optionAdd(data) {
    this._options.add(data);

    return this;
  }

  optionRemove(opt) {
    this._options.remove(opt);

    return this;
  }

  update(data) {
    this.$list.empty();
    this.page = 1;
    if (data !== null) {
      this._list.build(data);
    } else {
      this._list.build();
    }

    return this;
  }

  destroy() {
    this.$selective.remove();
    this.$element.show();
    $$1(document).off('click.selective');

    this._trigger('destroy');
  }

  static setDefaults(options) {
    $$1.extend(true, DEFAULTS, $$1.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.3.5'
};

const NAMESPACE = 'selective';
const OtherSelective = $$1.fn.selective;

const jQuerySelective = function(options, ...args) {
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
      $$1(this).data(NAMESPACE, new Selective(this, options));
    }
  });
};

$$1.fn.selective = jQuerySelective;

$$1.selective = $$1.extend({
  setDefaults: Selective.setDefaults,
  noConflict: function() {
    $$1.fn.selective = OtherSelective;
    return jQuerySelective;
  }
}, info);
