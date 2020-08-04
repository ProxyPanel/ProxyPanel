/**
* jQuery asPaginator v0.3.3
* https://github.com/amazingSurge/jquery-asPaginator
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
(function(global, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if (typeof exports !== 'undefined') {
    factory(require('jquery'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery);
    global.jqueryAsPaginatorEs = mod.exports;
  }
})(this, function(_jquery) {
  'use strict';

  var _jquery2 = _interopRequireDefault(_jquery);

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule
      ? obj
      : {
          default: obj
        };
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError('Cannot call a class as a function');
    }
  }

  var _createClass = (function() {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ('value' in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function(Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  })();

  var DEFAULTS = {
    namespace: 'asPaginator',

    currentPage: 1,
    itemsPerPage: 10,
    visibleNum: 5,
    resizeThrottle: 250,

    disabledClass: 'asPaginator_disable',
    activeClass: 'asPaginator_active',

    tpl: function tpl() {
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
    var _this = this;

    var _now =
      Date.now ||
      function() {
        return new Date().getTime();
      };

    var timeout = void 0;
    var context = void 0;
    var args = void 0;
    var result = void 0;
    var previous = 0;
    var later = function later() {
      previous = _now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) {
        context = args = null;
      }
    };

    return function() {
      for (
        var _len = arguments.length, params = Array(_len), _key = 0;
        _key < _len;
        _key++
      ) {
        params[_key] = arguments[_key];
      }

      /*eslint consistent-this: "off"*/
      var now = _now();
      var remaining = wait - (now - previous);
      context = _this;
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

  var NAMESPACE$1 = 'asPaginator';
  var COMPONENTS = {};

  /**
   * Plugin constructor
   **/

  var AsPaginator = (function() {
    function AsPaginator(element, totalItems, options) {
      _classCallCheck(this, AsPaginator);

      this.element = element;
      this.$element = (0, _jquery2.default)(element).empty();

      this.options = _jquery2.default.extend({}, DEFAULTS, options);
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

    _createClass(
      AsPaginator,
      [
        {
          key: 'init',
          value: function init() {
            var that = this;

            that.visible = that.getVisible();

            _jquery2.default.each(this.options.components, function(
              key,
              value
            ) {
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
              (0, _jquery2.default)(window).on(
                'resize',
                throttle(function() {
                  that.resize();
                }, this.options.resizeTime)
              );
            }

            this._trigger('ready');
          }
        },
        {
          key: 'createHtml',
          value: function createHtml() {
            var that = this;
            var contents = void 0;
            that.contents = that.options.tpl();

            var length = that.contents.match(/\{\{([^\}]+)\}\}/g).length;
            var components = void 0;

            for (var i = 0; i < length; i++) {
              components = that.contents.match(/\{\{([^\}]+)\}\}/);

              if (components[1] === 'namespace') {
                that.contents = that.contents.replace(
                  components[0],
                  that.namespace
                );
                continue;
              }

              if (this.options.components[components[1]]) {
                contents = that.components[components[1]].opts.tpl.call(that);
                that.contents = that.contents.replace(components[0], contents);
              }
            }

            that.$element.append((0, _jquery2.default)(that.contents));
          }
        },
        {
          key: 'bindEvents',
          value: function bindEvents() {
            var that = this;

            _jquery2.default.each(this.options.components, function(
              key,
              value
            ) {
              if (value === null || value === false) {
                return false;
              }

              that.components[key].bindEvents(that);
            });
          }
        },
        {
          key: 'unbindEvents',
          value: function unbindEvents() {
            var that = this;

            _jquery2.default.each(this.options.components, function(
              key,
              value
            ) {
              if (value === null || value === false) {
                return false;
              }

              that.components[key].unbindEvents(that);
            });
          }
        },
        {
          key: 'resize',
          value: function resize() {
            var that = this;
            that._trigger('resize');
            that.goTo(that.currentPage);
            that.visible = that.getVisible();

            _jquery2.default.each(this.options.components, function(
              key,
              value
            ) {
              if (value === null || value === false) {
                return false;
              }

              if (typeof that.components[key].resize === 'undefined') {
                return;
              }

              that.components[key].resize(that);
            });
          }
        },
        {
          key: 'getVisible',
          value: function getVisible() {
            var width = (0, _jquery2.default)('body, html').width();
            var adjacent = 0;
            if (typeof this.options.visibleNum !== 'number') {
              _jquery2.default.each(this.options.visibleNum, function(i, v) {
                if (width > i) {
                  adjacent = v;
                }
              });
            } else {
              adjacent = this.options.visibleNum;
            }

            return adjacent;
          }
        },
        {
          key: 'calculate',
          value: function calculate(current, total, visible) {
            var omitLeft = 1;
            var omitRight = 1;

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
        },
        {
          key: 'goTo',
          value: function goTo(page) {
            page = Math.max(1, Math.min(page, this.totalPages));

            // if true , dont relaod again
            if (page === this.currentPage && this.initialized === true) {
              return false;
            }

            this.$element
              .find('.' + this.classes.disabled)
              .removeClass(this.classes.disabled);

            // when add class when go to the first one or the last one
            if (page === this.totalPages) {
              this.$element
                .find('.' + this.namespace + '-next')
                .addClass(this.classes.disabled);
              this.$element
                .find('.' + this.namespace + '-last')
                .addClass(this.classes.disabled);
            }

            if (page === 1) {
              this.$element
                .find('.' + this.namespace + '-prev')
                .addClass(this.classes.disabled);
              this.$element
                .find('.' + this.namespace + '-first')
                .addClass(this.classes.disabled);
            }

            // here change current page first, and then trigger 'change' event
            this.currentPage = page;

            if (this.initialized) {
              this._trigger('change', page);
            }
          }
        },
        {
          key: 'prev',
          value: function prev() {
            if (this.hasPreviousPage()) {
              this.goTo(this.getPreviousPage());
              return true;
            }

            return false;
          }
        },
        {
          key: 'next',
          value: function next() {
            if (this.hasNextPage()) {
              this.goTo(this.getNextPage());
              return true;
            }

            return false;
          }
        },
        {
          key: 'goFirst',
          value: function goFirst() {
            return this.goTo(1);
          }
        },
        {
          key: 'goLast',
          value: function goLast() {
            return this.goTo(this.totalPages);
          }
        },
        {
          key: 'update',
          value: function update(data, value) {
            var changes = {};

            if (typeof data === 'string') {
              changes[data] = value;
            } else {
              changes = data;
            }

            for (var option in changes) {
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
        },
        {
          key: 'isOutOfBounds',
          value: function isOutOfBounds() {
            return this.currentPage > this.totalPages;
          }
        },
        {
          key: 'getItemsPerPage',
          value: function getItemsPerPage() {
            return this.itemsPerPage;
          }
        },
        {
          key: 'getTotalItems',
          value: function getTotalItems() {
            return this.totalItems;
          }
        },
        {
          key: 'getTotalPages',
          value: function getTotalPages() {
            this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
            this.lastPage = this.totalPages;
            return this.totalPages;
          }
        },
        {
          key: 'getCurrentPage',
          value: function getCurrentPage() {
            return this.currentPage;
          }
        },
        {
          key: 'hasPreviousPage',
          value: function hasPreviousPage() {
            return this.currentPage > 1;
          }
        },
        {
          key: 'getPreviousPage',
          value: function getPreviousPage() {
            if (this.hasPreviousPage()) {
              return this.currentPage - 1;
            }
            return false;
          }
        },
        {
          key: 'hasNextPage',
          value: function hasNextPage() {
            return this.currentPage < this.totalPages;
          }
        },
        {
          key: 'getNextPage',
          value: function getNextPage() {
            if (this.hasNextPage()) {
              return this.currentPage + 1;
            }
            return false;
          }
        },
        {
          key: 'enable',
          value: function enable() {
            if (this.disabled) {
              this.disabled = false;

              this.$element.removeClass(this.classes.disabled);

              this.bindEvents();
            }

            this._trigger('enable');
          }
        },
        {
          key: 'disable',
          value: function disable() {
            if (this.disabled !== true) {
              this.disabled = true;

              this.$element.addClass(this.classes.disabled);

              this.unbindEvents();
            }

            this._trigger('disable');
          }
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.$element.removeClass(this.classes.disabled);
            this.unbindEvents();
            this.$element.data(NAMESPACE$1, null);
            this._trigger('destroy');
          }
        },
        {
          key: '_trigger',
          value: function _trigger(eventType) {
            for (
              var _len2 = arguments.length,
                params = Array(_len2 > 1 ? _len2 - 1 : 0),
                _key2 = 1;
              _key2 < _len2;
              _key2++
            ) {
              params[_key2 - 1] = arguments[_key2];
            }

            var data = [this].concat(params);

            // event
            this.$element.trigger(NAMESPACE$1 + '::' + eventType, data);

            // callback
            eventType = eventType.replace(/\b\w+\b/g, function(word) {
              return word.substring(0, 1).toUpperCase() + word.substring(1);
            });
            var onFunction = 'on' + eventType;

            if (typeof this.options[onFunction] === 'function') {
              this.options[onFunction].apply(this, params);
            }
          }
        },
        {
          key: 'eventName',
          value: function eventName(events) {
            if (typeof events !== 'string' || events === '') {
              return '.' + this.options.namespace;
            }
            events = events.split(' ');

            var length = events.length;
            for (var i = 0; i < length; i++) {
              events[i] = events[i] + '.' + this.options.namespace;
            }
            return events.join(' ');
          }
        }
      ],
      [
        {
          key: 'registerComponent',
          value: function registerComponent(name, method) {
            COMPONENTS[name] = method;
          }
        },
        {
          key: 'setDefaults',
          value: function setDefaults(options) {
            _jquery2.default.extend(
              DEFAULTS,
              _jquery2.default.isPlainObject(options) && options
            );
          }
        }
      ]
    );

    return AsPaginator;
  })();

  AsPaginator.registerComponent('prev', {
    defaults: {
      tpl: function tpl() {
        return '<li class="' + this.namespace + '-prev"><a>Prev</a></li>';
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.prev
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      this.$prev = instance.$element.find('.' + instance.namespace + '-prev');
      this.$prev.on(
        'click.asPaginator',
        _jquery2.default.proxy(instance.prev, instance)
      );
    },
    unbindEvents: function unbindEvents() {
      this.$prev.off('click.asPaginator');
    }
  });

  AsPaginator.registerComponent('next', {
    defaults: {
      tpl: function tpl() {
        return '<li class="' + this.namespace + '-next"><a>Next</a></li>';
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.next
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      this.$next = instance.$element.find('.' + instance.namespace + '-next');
      this.$next.on(
        'click.asPaginator',
        _jquery2.default.proxy(instance.next, instance)
      );
    },
    unbindEvents: function unbindEvents() {
      this.$next.off('click.asPaginator');
    }
  });

  AsPaginator.registerComponent('first', {
    defaults: {
      tpl: function tpl() {
        return '<li class="' + this.namespace + '-first"><a>First</a></li>';
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.first
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      this.$first = instance.$element.find('.' + instance.namespace + '-first');
      this.$first.on(
        'click.asPaginator',
        _jquery2.default.proxy(instance.goFirst, instance)
      );
    },
    unbindEvents: function unbindEvents() {
      this.$first.off('click.asPaginator');
    }
  });

  AsPaginator.registerComponent('last', {
    defaults: {
      tpl: function tpl() {
        return '<li class="' + this.namespace + '-last"><a>Last</a></li>';
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.last
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      this.$last = instance.$element.find('.' + instance.namespace + '-last');
      this.$last.on(
        'click.asPaginator',
        _jquery2.default.proxy(instance.goLast, instance)
      );
    },
    unbindEvents: function unbindEvents() {
      this.$last.off('click.asPaginator');
    }
  });

  AsPaginator.registerComponent('lists', {
    defaults: {
      tpl: function tpl() {
        var lists = '';
        var remainder =
          this.currentPage >= this.visible
            ? this.currentPage % this.visible
            : this.currentPage;
        remainder = remainder === 0 ? this.visible : remainder;
        for (var k = 1; k < remainder; k++) {
          lists +=
            '<li class="' +
            this.namespace +
            '-items" data-value="' +
            (this.currentPage - remainder + k) +
            '"><a href="#">' +
            (this.currentPage - remainder + k) +
            '</a></li>';
        }
        lists +=
          '<li class="' +
          this.namespace +
          '-items ' +
          this.classes.active +
          '" data-value="' +
          this.currentPage +
          '"><a href="#">' +
          this.currentPage +
          '</a></li>';
        for (
          var i = this.currentPage + 1,
            limit =
              i + this.visible - remainder - 1 > this.totalPages
                ? this.totalPages
                : i + this.visible - remainder - 1;
          i <= limit;
          i++
        ) {
          lists +=
            '<li class="' +
            this.namespace +
            '-items" data-value="' +
            i +
            '"><a href="#">' +
            i +
            '</a></li>';
        }

        return lists;
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.lists
      );

      this.opts = opts;

      instance.itemsTpl = this.opts.tpl.call(instance);
    },
    bindEvents: function bindEvents(instance) {
      var that = this;
      this.$items = instance.$element.find('.' + instance.namespace + '-items');
      instance.$element.on('click', this.$items, function(e) {
        var page =
          (0, _jquery2.default)(e.target)
            .parent()
            .data('value') || (0, _jquery2.default)(e.target).data('value');

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
      instance.$element.on('asPaginator::change', function() {
        that.render(instance);
      });
    },
    unbindEvents: function unbindEvents(instance) {
      instance.$element.off('click', this.$items);
    },
    resize: function resize(instance) {
      this.render(instance);
    },
    render: function render(instance) {
      var current = instance.currentPage;
      var overflow = void 0;
      var that = this;

      var array = this.$items.removeClass(instance.classes.active);
      _jquery2.default.each(array, function(i, v) {
        if ((0, _jquery2.default)(v).data('value') === current) {
          (0, _jquery2.default)(v).addClass(instance.classes.active);
          overflow = false;
          return false;
        }
      });

      if (overflow === false && this.visibleBefore === instance.visible) {
        return;
      }

      this.visibleBefore = instance.visible;

      _jquery2.default.each(array, function(i, v) {
        if (i === 0) {
          (0, _jquery2.default)(v).replaceWith(that.opts.tpl.call(instance));
        } else {
          (0, _jquery2.default)(v).remove();
        }
      });
      this.$items = instance.$element.find('.' + instance.namespace + '-items');
    }
  });

  AsPaginator.registerComponent('goTo', {
    defaults: {
      tpl: function tpl() {
        return (
          '<div class="' +
          this.namespace +
          '-goTo"><input type="text" class="' +
          this.namespace +
          '-input" /><button type="submit" class="' +
          this.namespace +
          '-submit">Go</button></div>'
        );
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.goTo
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      var that = this;
      that.$goTo = instance.$element.find('.' + instance.namespace + '-goTo');
      that.$input = that.$goTo.find('.' + instance.namespace + '-input');
      that.$button = that.$goTo.find('.' + instance.namespace + '-submit');

      that.$button.on('click', function() {
        var page = parseInt(that.$input.val(), 10);
        page = page > 0 ? page : instance.currentPage;
        instance.goTo(page);
      });
    },
    unbindEvents: function unbindEvents() {
      this.$button.off('click');
    }
  });

  AsPaginator.registerComponent('altLists', {
    defaults: {
      tpl: function tpl() {
        var lists = '';
        var max = this.totalPages;
        var current = this.currentPage;
        var omit = this.calculate(current, max, this.visible);
        var that = this;
        var i = void 0;
        var item = function item(i, classes) {
          if (classes === 'active') {
            return (
              '<li class="' +
              that.namespace +
              '-items ' +
              that.classes.active +
              '" data-value="' +
              i +
              '"><a href="#">' +
              i +
              '</a></li>'
            );
          } else if (classes === 'omit') {
            return (
              '<li class="' +
              that.namespace +
              '-items ' +
              that.namespace +
              '_ellipsis" data-value="ellipsis"><a href="#">...</a></li>'
            );
          } else {
            return (
              '<li class="' +
              that.namespace +
              '-items" data-value="' +
              i +
              '"><a href="#">' +
              i +
              '</a></li>'
            );
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

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.altLists
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      var that = this;
      this.$items = instance.$element.find('.' + instance.namespace + '-items');
      instance.$element.on('click', this.$items, function(e) {
        var page =
          (0, _jquery2.default)(e.target)
            .parent()
            .data('value') || (0, _jquery2.default)(e.target).data('value');

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
      instance.$element.on('asPaginator::change', function() {
        that.render(instance);
      });
    },
    unbindEvents: function unbindEvents(instance) {
      instance.$wrap.off('click', this.$items);
    },
    resize: function resize(instance) {
      this.render(instance);
    },
    render: function render(instance) {
      var that = this;
      var array = this.$items.removeClass(instance.classes.active);
      _jquery2.default.each(array, function(i, v) {
        if (i === 0) {
          (0, _jquery2.default)(v).replaceWith(that.opts.tpl.call(instance));
        } else {
          (0, _jquery2.default)(v).remove();
        }
      });
      this.$items = instance.$element.find('.' + instance.namespace + '-items');
    }
  });

  AsPaginator.registerComponent('info', {
    defaults: {
      tpl: function tpl() {
        return (
          '<li class="' +
          this.namespace +
          '-info"><a href="javascript:void(0);"><span class="' +
          this.namespace +
          '-current"></span> / <span class="' +
          this.namespace +
          '-total"></span></a></li>'
        );
      }
    },

    init: function init(instance) {
      var opts = _jquery2.default.extend(
        {},
        this.defaults,
        instance.options.components.info
      );

      this.opts = opts;
    },
    bindEvents: function bindEvents(instance) {
      var $info = instance.$element.find('.' + instance.namespace + '-info');
      var $current = $info.find('.' + instance.namespace + '-current');
      $info.find('.' + instance.namespace + '-total').text(instance.totalPages);

      $current.text(instance.currentPage);
      instance.$element.on('asPaginator::change', function() {
        $current.text(instance.currentPage);
      });
    }
  });

  var info = {
    version: '0.3.3'
  };

  var NAMESPACE = 'asPaginator';
  var OtherAsPaginator = _jquery2.default.fn.asPaginator;

  var jQueryAsPaginator = function jQueryAsPaginator(totalItems) {
    for (
      var _len3 = arguments.length,
        args = Array(_len3 > 1 ? _len3 - 1 : 0),
        _key3 = 1;
      _key3 < _len3;
      _key3++
    ) {
      args[_key3 - 1] = arguments[_key3];
    }

    if (
      !_jquery2.default.isNumeric(totalItems) &&
      typeof totalItems === 'string'
    ) {
      var method = totalItems;

      if (/^_/.test(method)) {
        return false;
      } else if (/^(get)/.test(method)) {
        var instance = this.first().data(NAMESPACE);
        if (instance && typeof instance[method] === 'function') {
          return instance[method].apply(instance, args);
        }
      } else {
        return this.each(function() {
          var instance = _jquery2.default.data(this, NAMESPACE);
          if (instance && typeof instance[method] === 'function') {
            instance[method].apply(instance, args);
          }
        });
      }
    }

    return this.each(function() {
      if (!(0, _jquery2.default)(this).data(NAMESPACE)) {
        (0, _jquery2.default)(this).data(
          NAMESPACE,
          new (Function.prototype.bind.apply(
            AsPaginator,
            [null].concat([this, totalItems], args)
          ))()
        );
      }
    });
  };

  _jquery2.default.fn.asPaginator = jQueryAsPaginator;

  _jquery2.default.asPaginator = _jquery2.default.extend(
    {
      registerComponent: AsPaginator.registerComponent,
      setDefaults: AsPaginator.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asPaginator = OtherAsPaginator;
        return jQueryAsPaginator;
      }
    },
    info
  );
});
