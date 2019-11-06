/**
* jQuery Selective v0.3.5
* https://github.com/amazingSurge/jquery-selective
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
(

  function(global, factory) {
  if (typeof define === "function" && define.amd) {
    define(['jquery'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery);
    global.jquerySelectiveEs = mod.exports;
  }
}
)(this,

  function(_jquery) {
    'use strict';

    var _jquery2 = _interopRequireDefault(_jquery);

    function _interopRequireDefault(obj) {
      return obj && obj.__esModule ? obj : {
        default: obj
      };
    }

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ?

      function(obj) {
        return typeof obj;
      }
      :

      function(obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };

    function _classCallCheck(instance, Constructor) {
      if (!(instance instanceof Constructor)) {
        throw new TypeError("Cannot call a class as a function");
      }
    }

    var _createClass = function() {
      function defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
          var descriptor = props[i];
          descriptor.enumerable = descriptor.enumerable || false;
          descriptor.configurable = true;

          if ("value" in descriptor)
            descriptor.writable = true;
          Object.defineProperty(target, descriptor.key, descriptor);
        }
      }

      return function(Constructor, protoProps, staticProps) {
        if (protoProps)
          defineProperties(Constructor.prototype, protoProps);

        if (staticProps)
          defineProperties(Constructor, staticProps);

        return Constructor;
      };
    }();

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
      query: function query() {}, //function(api, search_text, page) {},
      tpl: {
        frame: function frame() {
          return '<div class="' + this.namespace + '"><div class="' + this.namespace + '-trigger">' + this.options.tpl.triggerButton.call(this) + '<div class="' + this.namespace + '-trigger-dropdown"><div class="' + this.namespace + '-list-wrap">' + this.options.tpl.list.call(this) + '</div></div></div>' + this.options.tpl.items.call(this) + '</div>';
        },
        search: function search() {
          return '<input class="' + this.namespace + '-search" type="text" placeholder="Search...">';
        },
        select: function select() {
          return '<select class="' + this.namespace + '-select" name="' + this.namespace + '" multiple="multiple"></select>';
        },
        optionValue: function optionValue(data) {
          if ('name' in data) {

            return data.name;
          }

          return data;
        },
        option: function option(content) {
          return '<option value="' + this.options.tpl.optionValue.call(this) + '">' + content + '</option>';
        },
        items: function items() {
          return '<ul class="' + this.namespace + '-items"></ul>';
        },
        item: function item(content) {
          return '<li class="' + this.namespace + '-item">' + content + this.options.tpl.itemRemove.call(this) + '</li>';
        },
        itemRemove: function itemRemove() {
          return '<span class="' + this.namespace + '-remove">x</span>';
        },
        triggerButton: function triggerButton() {
          return '<div class="' + this.namespace + '-trigger-button">Add</div>';
        },
        list: function list() {
          return '<ul class="' + this.namespace + '-list"></ul>';
        },
        listItem: function listItem(content) {
          return '<li class="' + this.namespace + '-list-item">' + content + '</li>';
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

    var Options = function() {
      function Options(instance) {
        _classCallCheck(this, Options);

        this.instance = instance;
      }

      _createClass(Options, [{
        key: 'getOptions',
        value: function getOptions() {
          this.instance.$options = this.instance.$select.find('option');

          return this.instance.$options;
        }
      }, {
        key: 'select',
        value: function select(opt) {
          $(opt).prop('selected', true);

          return this.instance;
        }
      }, {
        key: 'unselect',
        value: function unselect(opt) {
          $(opt).prop('selected', false);

          return this.instance;
        }
      }, {
        key: 'add',
        value: function add(data) {
          /*eslint consistent-return: "off"*/

          if (this.instance.options.buildFromHtml === false && this.instance.getItem('option', this.instance.$select, this.instance.options.tpl.optionValue(data)) === undefined) {
            var $option = $(this.instance.options.tpl.option.call(this.instance, data));

            this.instance.setIndex($option, data);
            this.instance.$select.append($option);

            return $option;
          }
        }
      }, {
        key: 'remove',
        value: function remove(opt) {
          $(opt).remove();

          return this.instance;
        }
      }]);

      return Options;
    }();

    var List = function() {
      function List(instance) {
        _classCallCheck(this, List);

        this.instance = instance;
      }

      _createClass(List, [{
        key: 'build',
        value: function build(data) {
          var _this = this;

          var $list = $('<ul></ul>');
          var $options = this.instance._options.getOptions();

          if (this.instance.options.buildFromHtml === true) {

            if ($options.length !== 0) {
              $.each($options,

                function(i, n) {
                  var $li = $(_this.instance.options.tpl.listItem.call(_this.instance, n.text));
                  var $n = $(n);
                  _this.instance.setIndex($li, $n);

                  if ($n.attr('selected') !== undefined) {
                    _this.instance.select($li);
                  }
                  $list.append($li);
                }
              );
            }
          } else if (data !== null) {
            $.each(data,

              function(i) {
                var $li = $(_this.instance.options.tpl.listItem.call(_this.instance, data[i]));

                _this.instance.setIndex($li, data[i]);
                $list.append($li);
              }
            );

            if ($options.length !== 0) {
              $.each($options,

                function(i, n) {
                  var $n = $(n);
                  var li = _this.instance.getItem('li', $list, _this.instance.options.tpl.optionValue($n.data('selective_index')));

                  if (li !== undefined) {
                    _this.instance._list.select(li);
                  }
                }
              );
            }
          }

          this.instance.$list.append($list.children('li'));

          return this.instance;
        }
      }, {
        key: 'buildSearch',
        value: function buildSearch() {
          if (this.instance.options.withSearch === true) {
            this.instance.$triggerDropdown.prepend(this.instance.options.tpl.search.call(this.instance));
            this.instance.$search = this.instance.$triggerDropdown.find('.' + this.instance.namespace + '-search');
          }

          return this.instance;
        }
      }, {
        key: 'select',
        value: function select(obj) {
          this.instance._trigger("beforeSelected");
          $(obj).addClass(this.instance.namespace + '-selected');
          this.instance._trigger("afterSelected");

          return this.instance;
        }
      }, {
        key: 'unselect',
        value: function unselect(obj) {
          this.instance._trigger("beforeUnselected");
          $(obj).removeClass(this.instance.namespace + '-selected');
          this.instance._trigger("afterUnselected");

          return this.instance;
        }
      }, {
        key: 'click',
        value: function click() {
          var that = this;
          this.instance.$list.on('click', 'li',

            function() {
              var $this = $(this);

              if (!$this.hasClass(that.instance.namespace + '-selected')) {
                that.instance.select($this);
              }
            }
          );
        }
      }, {
        key: 'filter',
        value: function filter(val) {
          $.expr[':'].Contains = function(a, i, m) {
            return jQuery(a).text().toUpperCase().includes(m[3].toUpperCase());
          }
          ;

          if (val) {
            this.instance.$list.find('li:not(:Contains(' + val + '))').slideUp();
            this.instance.$list.find('li:Contains(' + val + ')').slideDown();
          } else {
            this.instance.$list.children('li').slideDown();
          }

          return this.instance;
        }
      }, {
        key: 'loadMore',
        value: function loadMore() {
          var _this2 = this;

          var pageMax = this.instance.options.ajax.pageSize || 9999;

          this.instance.$listWrap.on('scroll.selective',

            function() {
              if (pageMax > _this2.instance.page) {
                var listHeight = _this2.instance.$list.outerHeight(true);
                var wrapHeight = _this2.instance.$listWrap.outerHeight();
                var wrapScrollTop = _this2.instance.$listWrap.scrollTop();
                var below = listHeight - wrapHeight - wrapScrollTop;

                if (below === 0) {
                  _this2.instance.options.query(_this2.instance, _this2.instance.$search.val(), ++_this2.instance.page);
                }
              }
            }
          );

          return this.instance;
        }
      }, {
        key: 'loadMoreRemove',
        value: function loadMoreRemove() {
          this.instance.$listWrap.off('scroll.selective');

          return this.instance;
        }
      }]);

      return List;
    }();

    var Search = function() {
      function Search(instance) {
        _classCallCheck(this, Search);

        this.instance = instance;
      }

      _createClass(Search, [{
        key: 'change',
        value: function change() {
          var _this3 = this;

          this.instance.$search.change(

            function() {
              _this3.instance._trigger("beforeSearch");

              if (_this3.instance.options.buildFromHtml === true) {
                _this3.instance._list.filter(_this3.instance.$search.val());
              } else if (_this3.instance.$search.val() !== '') {
                _this3.instance.page = 1;

                _this3.instance.options.query(_this3.instance, _this3.instance.$search.val(), _this3.instance.page);
              } else {
                _this3.instance.update(_this3.instance.options.local);
              }
              _this3.instance._trigger("afterSearch");
            }
          );
        }
      }, {
        key: 'keyup',
        value: function keyup() {
          var _this4 = this;

          var quietMills = this.instance.options.ajax.quietMills || 1000;
          var oldValue = '';
          var currentValue = '';
          var timeout = void 0;

          this.instance.$search.on('keyup',

            function(e) {
              _this4.instance._trigger("beforeSearch");
              currentValue = _this4.instance.$search.val();

              if (_this4.instance.options.buildFromHtml === true) {

                if (currentValue !== oldValue) {
                  _this4.instance._list.filter(currentValue);
                }
              } else if (currentValue !== oldValue || e.keyCode === 13) {
                window.clearTimeout(timeout);
                timeout = window.setTimeout(

                  function() {
                    if (currentValue !== '') {
                      _this4.instance.page = 1;
                      _this4.instance.options.query(_this4.instance, currentValue, _this4.instance.page);
                    } else {
                      _this4.instance.update(_this4.instance.options.local);
                    }
                  }
                  , quietMills);
              }
              oldValue = currentValue;
              _this4.instance._trigger("afterSearch");
            }
          );
        }
      }, {
        key: 'bind',
        value: function bind(type) {
          if (type === 'change') {
            this.change();
          } else if (type === 'keyup') {
            this.keyup();
          }
        }
      }]);

      return Search;
    }();

    var Items = function() {
      function Items(instance) {
        _classCallCheck(this, Items);

        this.instance = instance;
      }

      _createClass(Items, [{
        key: 'withDefaults',
        value: function withDefaults(data) {
          var _this5 = this;

          if (data !== null) {
            $.each(data,

              function(i) {
                _this5.instance._options.add(data[i]);
                _this5.instance._options.select(_this5.instance.getItem('option', _this5.instance.$select, _this5.instance.options.tpl.optionValue(data[i])));
                _this5.instance._items.add(data[i]);
              }
            );
          }
        }
      }, {
        key: 'add',
        value: function add(data, content) {
          var $item = void 0;

          var fill = void 0;

          if (this.instance.options.buildFromHtml === true) {
            fill = content;
          } else {
            fill = data;
          }
          $item = $(this.instance.options.tpl.item.call(this.instance, fill));
          this.instance.setIndex($item, data);
          this.instance.$items.append($item);
        }
      }, {
        key: 'remove',
        value: function remove(obj) {
          obj = $(obj);
          var $li = void 0;
          var $option = void 0;

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
      }, {
        key: 'click',
        value: function click() {
          var that = this;
          this.instance.$items.on('click', '.' + this.instance.namespace + '-remove',

            function() {
              var $this = $(this);
              var $item = $this.parents('li');
              that.instance.itemRemove($item);
            }
          );
        }
      }]);

      return Items;
    }();

    var NAMESPACE$1 = 'selective';

    /**
     * Plugin constructor
     **/

    var Selective = function() {
      function Selective(element) {
        var _this6 = this;

        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

        _classCallCheck(this, Selective);

        this.element = element;
        this.$element = (0, _jquery2.default)(element).hide() || (0, _jquery2.default)('<select></select>');

        this.options = _jquery2.default.extend(true, {}, DEFAULTS, options);

        this.namespace = this.options.namespace;

        var $frame = (0, _jquery2.default)(this.options.tpl.frame.call(this));

        //get the select
        var _build = function _build() {
          _this6.$element.html(_this6.options.tpl.select.call(_this6));

          return _this6.$element.children('select');
        };

        this.$select = this.$element.is('select') === true ? this.$element : _build();

        this.$element.after($frame);

        this.init();
        this.opened = false;
      }

      _createClass(Selective, [{
        key: 'init',
        value: function init() {
          var _this7 = this;

          this.$selective = this.$element.next('.' + this.namespace);
          this.$items = this.$selective.find('.' + this.namespace + '-items');
          this.$trigger = this.$selective.find('.' + this.namespace + '-trigger');
          this.$triggerButton = this.$selective.find('.' + this.namespace + '-trigger-button');
          this.$triggerDropdown = this.$selective.find('.' + this.namespace + '-trigger-dropdown');
          this.$listWrap = this.$selective.find('.' + this.namespace + '-list-wrap');
          this.$list = this.$selective.find('.' + this.namespace + '-list');

          this._list = new List(this);
          this._options = new Options(this);
          this._search = new Search(this);
          this._items = new Items(this);

          this._items.withDefaults(this.options.selected);
          this.update(this.options.local)._list.buildSearch();

          this.$triggerButton.on('click',

            function() {
              if (_this7.opened === false) {
                _this7.show();
              } else {
                _this7.hide();
              }
            }
          );

          this._list.click(this);
          this._items.click(this);

          if (this.options.withSearch === true) {
            this._search.bind(this.options.searchType);
          }

          this._trigger('ready');
        }
      }, {
        key: '_trigger',
        value: function _trigger(eventType) {
          for (var _len = arguments.length, params = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
            params[_key - 1] = arguments[_key];
          }

          var data = [this].concat(params);

          // event
          this.$element.trigger(NAMESPACE$1 + '::' + eventType, data);

          // callback
          eventType = eventType.replace(/\b\w+\b/g,

            function(word) {
              return word.substring(0, 1).toUpperCase() + word.substring(1);
            }
          );
          var onFunction = 'on' + eventType;

          if (typeof this.options[onFunction] === 'function') {
            this.options[onFunction].apply(this, params);
          }
        }
      }, {
        key: '_show',
        value: function _show() {
          var _this8 = this;

          (0, _jquery2.default)(document).on('click.selective',

            function(e) {
              if (_this8.options.closeOnSelect === true) {

                if ((0, _jquery2.default)(e.target).closest(_this8.$triggerButton).length === 0 && (0, _jquery2.default)(e.target).closest(_this8.$search).length === 0) {
                  _this8._hide();
                }
              } else if ((0, _jquery2.default)(e.target).closest(_this8.$trigger).length === 0) {
                _this8._hide();
              }
            }
          );

          this.$trigger.addClass(this.namespace + '-active');
          this.opened = true;

          if (this.options.ajax.loadMore === true) {
            this._list.loadMore();
          }

          return this;
        }
      }, {
        key: '_hide',
        value: function _hide() {
          (0, _jquery2.default)(document).off('click.selective');

          this.$trigger.removeClass(this.namespace + '-active');
          this.opened = false;

          if (this.options.ajax.loadMore === true) {
            this._list.loadMoreRemove();
          }

          return this;
        }
      }, {
        key: 'show',
        value: function show() {
          this._trigger("beforeShow");
          this._show();
          this._trigger("afterShow");

          return this;
        }
      }, {
        key: 'hide',
        value: function hide() {
          this._trigger("beforeHide");
          this._hide();
          this._trigger("afterHide");

          return this;
        }
      }, {
        key: 'select',
        value: function select($li) {
          this._list.select($li);
          var data = $li.data('selective_index');

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
      }, {
        key: 'unselect',
        value: function unselect($li) {
          this._list.unselect($li);

          return this;
        }
      }, {
        key: 'setIndex',
        value: function setIndex(obj, index) {
          obj.data('selective_index', index);

          return this;
        }
      }, {
        key: 'getItem',
        value: function getItem(type, $list, index) {
          var $items = $list.children(type);
          var position = '';

          for (var i = 0; i < $items.length; i++) {

            if (this.options.tpl.optionValue($items.eq(i).data('selective_index')) === index) {
              position = i;
            }
          }

          return position === '' ? undefined : $items.eq(position);
        }
      }, {
        key: 'itemAdd',
        value: function itemAdd(data, content) {
          this._trigger("beforeItemAdd");
          this._items.add(data, content);
          this._trigger("afterItemAdd");

          return this;
        }
      }, {
        key: 'itemRemove',
        value: function itemRemove($li) {
          this._trigger("beforeItemRemove");
          this._items.remove($li);
          this._trigger("afterItemRemove");

          return this;
        }
      }, {
        key: 'optionAdd',
        value: function optionAdd(data) {
          this._options.add(data);

          return this;
        }
      }, {
        key: 'optionRemove',
        value: function optionRemove(opt) {
          this._options.remove(opt);

          return this;
        }
      }, {
        key: 'update',
        value: function update(data) {
          this.$list.empty();
          this.page = 1;

          if (data !== null) {
            this._list.build(data);
          } else {
            this._list.build();
          }

          return this;
        }
      }, {
        key: 'destroy',
        value: function destroy() {
          this.$selective.remove();
          this.$element.show();
          (0, _jquery2.default)(document).off('click.selective');

          this._trigger('destroy');
        }
      }], [{
        key: 'setDefaults',
        value: function setDefaults(options) {
          _jquery2.default.extend(true, DEFAULTS, _jquery2.default.isPlainObject(options) && options);
        }
      }]);

      return Selective;
    }();

    var info = {
      version: '0.3.5'
    };

    var NAMESPACE = 'selective';
    var OtherSelective = _jquery2.default.fn.selective;

    var jQuerySelective = function jQuerySelective(options) {
      var _this9 = this;

      for (var _len2 = arguments.length, args = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }

      if (typeof options === 'string') {
        var _ret = function() {
          var method = options;

          if (/^_/.test(method)) {

            return {
              v: false
            };
          } else if (/^(get)/.test(method)) {
            var instance = _this9.first().data(NAMESPACE);

            if (instance && typeof instance[method] === 'function') {

              return {
                v: instance[method].apply(instance, args)
              };
            }
          } else {

            return {
              v: _this9.each(

                function() {
                  var instance = _jquery2.default.data(this, NAMESPACE);

                  if (instance && typeof instance[method] === 'function') {
                    instance[method].apply(instance, args);
                  }
                }
              )
            };
          }
        }();

        if ((typeof _ret === 'undefined' ? 'undefined' : _typeof(_ret)) === "object")

          return _ret.v;
      }

      return this.each(

        function() {
          if (!(0, _jquery2.default)(this).data(NAMESPACE)) {
            (0, _jquery2.default)(this).data(NAMESPACE, new Selective(this, options));
          }
        }
      );
    };

    _jquery2.default.fn.selective = jQuerySelective;

    _jquery2.default.selective = _jquery2.default.extend({
      setDefaults: Selective.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.selective = OtherSelective;

        return jQuerySelective;
      }
    }, info);
  }
);