/**
* jQuery asBreadcrumbs v0.2.3
* https://github.com/amazingSurge/jquery-asBreadcrumbs
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
    global.jqueryAsBreadcrumbsEs = mod.exports;
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
    namespace: 'breadcrumb',
    overflow: 'left',

    responsive: true,

    ellipsisText: '&#8230;',
    ellipsisClass: null,

    hiddenClass: 'is-hidden',

    dropdownClass: null,
    dropdownMenuClass: null,
    dropdownItemClass: null,
    dropdownItemDisableClass: 'disabled',

    toggleClass: null,
    toggleIconClass: 'caret',

    getItems: function getItems($parent) {
      return $parent.children();
    },

    getItemLink: function getItemLink($item) {
      return $item.find('a');
    },

    // templates
    ellipsis: function ellipsis(classes, label) {
      return '<li class="' + classes.ellipsisClass + '">' + label + '</li>';
    },

    dropdown: function dropdown(classes) {
      var dropdownClass = 'dropdown';
      var dropdownMenuClass = 'dropdown-menu';

      if (this.options.overflow === 'right') {
        dropdownMenuClass += ' dropdown-menu-right';
      }

      return (
        '<li class="' +
        dropdownClass +
        ' ' +
        classes.dropdownClass +
        '">\n      <a href="javascript:void(0);" class="' +
        classes.toggleClass +
        '" data-toggle="dropdown">\n        <i class="' +
        classes.toggleIconClass +
        '"></i>\n      </a>\n      <ul class="' +
        dropdownMenuClass +
        ' ' +
        classes.dropdownMenuClass +
        '"></ul>\n    </li>'
      );
    },

    dropdownItem: function dropdownItem(classes, label, href) {
      if (!href) {
        return (
          '<li class="' +
          classes.dropdownItemClass +
          ' ' +
          classes.dropdownItemDisableClass +
          '"><a href="#">' +
          label +
          '</a></li>'
        );
      }
      return (
        '<li class="' +
        classes.dropdownItemClass +
        '"><a href="' +
        href +
        '">' +
        label +
        '</a></li>'
      );
    },

    // callbacks
    onInit: null,
    onReady: null
  };

  var NAMESPACE = 'asBreadcrumbs';
  var instanceId = 0;

  /**
   * Plugin constructor
   **/

  var asBreadcrumbs = (function() {
    function asBreadcrumbs(element, options) {
      _classCallCheck(this, asBreadcrumbs);

      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      this.options = _jquery2.default.extend(
        {},
        DEFAULTS,
        options,
        this.$element.data()
      );

      this.namespace = this.options.namespace;
      this.$element.addClass(this.namespace);

      this.classes = {
        toggleClass: this.options.toggleClass
          ? this.options.toggleClass
          : this.namespace + '-toggle',
        toggleIconClass: this.options.toggleIconClass,
        dropdownClass: this.options.dropdownClass
          ? this.options.dropdownClass
          : this.namespace + '-dropdown',
        dropdownMenuClass: this.options.dropdownMenuClass
          ? this.options.dropdownMenuClass
          : this.namespace + '-dropdown-menu',
        dropdownItemClass: this.options.dropdownItemClass
          ? this.options.dropdownItemClass
          : '',
        dropdownItemDisableClass: this.options.dropdownItemDisableClass
          ? this.options.dropdownItemDisableClass
          : '',
        ellipsisClass: this.options.ellipsisClass
          ? this.options.ellipsisClass
          : this.namespace + '-ellipsis',
        hiddenClass: this.options.hiddenClass
      };

      // flag
      this.initialized = false;
      this.instanceId = ++instanceId;

      this.$children = this.options.getItems(this.$element);
      this.$firstChild = this.$children.eq(0);

      this.$dropdown = null;
      this.$dropdownMenu = null;

      this.gap = 6;
      this.items = [];

      this._trigger('init');
      this.init();
    }

    _createClass(
      asBreadcrumbs,
      [
        {
          key: 'init',
          value: function init() {
            var _this = this;

            this.$element.addClass(
              this.namespace + '-' + this.options.overflow
            );

            this._prepareItems();
            this._createDropdown();
            this._createEllipsis();

            this.render();

            if (this.options.responsive) {
              (0, _jquery2.default)(window).on(
                this.eventNameWithId('resize'),
                this._throttle(function() {
                  _this.resize();
                }, 250)
              );
            }

            this.initialized = true;
            this._trigger('ready');
          }
        },
        {
          key: '_prepareItems',
          value: function _prepareItems() {
            var that = this;

            this.$children.each(function() {
              var $this = (0, _jquery2.default)(this);
              var $link = that.options.getItemLink($this);
              var $dropdownItem = (0, _jquery2.default)(
                that.options.dropdownItem.call(
                  that,
                  that.classes,
                  $this.text(),
                  $link.attr('href')
                )
              );

              that.items.push({
                $this: $this,
                outerWidth: $this.outerWidth(),
                $item: $dropdownItem
              });
            });

            if (this.options.overflow === 'left') {
              this.items.reverse();
            }
          }
        },
        {
          key: '_createDropdown',
          value: function _createDropdown() {
            this.$dropdown = (0, _jquery2.default)(
              this.options.dropdown.call(this, this.classes)
            )
              .addClass(this.classes.hiddenClass)
              .appendTo(this.$element);
            this.$dropdownMenu = this.$dropdown.find(
              '.' + this.classes.dropdownMenuClass
            );

            this._createDropdownItems();

            if (this.options.overflow === 'right') {
              this.$dropdown.appendTo(this.$element);
            } else {
              this.$dropdown.prependTo(this.$element);
            }
          }
        },
        {
          key: '_createDropdownItems',
          value: function _createDropdownItems() {
            for (var i = 0; i < this.items.length; i++) {
              this.items[i].$item
                .appendTo(this.$dropdownMenu)
                .addClass(this.classes.hiddenClass);
            }
          }
        },
        {
          key: '_createEllipsis',
          value: function _createEllipsis() {
            if (!this.options.ellipsisText) {
              return;
            }

            this.$ellipsis = (0, _jquery2.default)(
              this.options.ellipsis.call(
                this,
                this.classes,
                this.options.ellipsisText
              )
            ).addClass(this.classes.hiddenClass);

            if (this.options.overflow === 'right') {
              this.$ellipsis.insertBefore(this.$dropdown);
            } else {
              this.$ellipsis.insertAfter(this.$dropdown);
            }
          }
        },
        {
          key: 'render',
          value: function render() {
            var dropdownWidth = this.getDropdownWidth();
            var childrenWidthTotal = 0;
            var containerWidth = this.getConatinerWidth();

            var showDropdown = false;

            for (var i = 0; i < this.items.length; i++) {
              childrenWidthTotal += this.items[i].outerWidth;

              if (childrenWidthTotal + dropdownWidth > containerWidth) {
                showDropdown = true;
                this._showDropdownItem(i);
              } else {
                this._hideDropdownItem(i);
              }
            }

            if (showDropdown) {
              this.$ellipsis.removeClass(this.classes.hiddenClass);
              this.$dropdown.removeClass(this.classes.hiddenClass);
            } else {
              this.$ellipsis.addClass(this.classes.hiddenClass);
              this.$dropdown.addClass(this.classes.hiddenClass);
            }

            this._trigger('update');
          }
        },
        {
          key: 'resize',
          value: function resize() {
            this.render();
          }
        },
        {
          key: 'getDropdownWidth',
          value: function getDropdownWidth() {
            return (
              this.$dropdown.outerWidth() +
              (this.options.ellipsisText ? this.$ellipsis.outerWidth() : 0)
            );
          }
        },
        {
          key: 'getConatinerWidth',
          value: function getConatinerWidth() {
            var width = 0;
            var that = this;

            this.$element.children().each(function() {
              if (
                (0, _jquery2.default)(this).css('display') === 'inline-block' &&
                (0, _jquery2.default)(this).css('float') === 'none'
              ) {
                width += that.gap;
              }
            });
            return this.$element.width() - width;
          }
        },
        {
          key: '_showDropdownItem',
          value: function _showDropdownItem(i) {
            this.items[i].$item.removeClass(this.classes.hiddenClass);
            this.items[i].$this.addClass(this.classes.hiddenClass);
          }
        },
        {
          key: '_hideDropdownItem',
          value: function _hideDropdownItem(i) {
            this.items[i].$this.removeClass(this.classes.hiddenClass);
            this.items[i].$item.addClass(this.classes.hiddenClass);
          }
        },
        {
          key: '_trigger',
          value: function _trigger(eventType) {
            for (
              var _len = arguments.length,
                params = Array(_len > 1 ? _len - 1 : 0),
                _key = 1;
              _key < _len;
              _key++
            ) {
              params[_key - 1] = arguments[_key];
            }

            var data = [this].concat(params);

            // event
            this.$element.trigger(NAMESPACE + '::' + eventType, data);

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
        },
        {
          key: 'eventNameWithId',
          value: function eventNameWithId(events) {
            if (typeof events !== 'string' || events === '') {
              return '.' + this.options.namespace + '-' + this.instanceId;
            }

            events = events.split(' ');
            var length = events.length;
            for (var i = 0; i < length; i++) {
              events[i] =
                events[i] +
                '.' +
                this.options.namespace +
                '-' +
                this.instanceId;
            }
            return events.join(' ');
          }
        },
        {
          key: '_throttle',
          value: function _throttle(func, wait) {
            var _this2 = this;

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
                var _len2 = arguments.length, params = Array(_len2), _key2 = 0;
                _key2 < _len2;
                _key2++
              ) {
                params[_key2] = arguments[_key2];
              }

              /*eslint consistent-this: "off"*/
              var now = _now();
              var remaining = wait - (now - previous);
              context = _this2;
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
        },
        {
          key: 'destroy',
          value: function destroy() {
            this.$element.children().removeClass(this.classes.hiddenClass);
            this.$dropdown.remove();

            if (this.options.ellipsisText) {
              this.$ellipsis.remove();
            }

            this.initialized = false;

            this.$element.data(NAMESPACE, null);
            (0, _jquery2.default)(window).off(this.eventNameWithId('resize'));
            this._trigger('destroy');
          }
        }
      ],
      [
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

    return asBreadcrumbs;
  })();

  var info = {
    version: '0.2.3'
  };

  var NAME = 'asBreadcrumbs';
  var OtherAsBreadcrumbs = _jquery2.default.fn.asBreadcrumbs;

  var jQueryAsBreadcrumbs = function jQueryAsBreadcrumbs(options) {
    for (
      var _len3 = arguments.length,
        args = Array(_len3 > 1 ? _len3 - 1 : 0),
        _key3 = 1;
      _key3 < _len3;
      _key3++
    ) {
      args[_key3 - 1] = arguments[_key3];
    }

    if (typeof options === 'string') {
      var method = options;

      if (/^_/.test(method)) {
        return false;
      } else if (/^(get)/.test(method)) {
        var instance = this.first().data(NAME);
        if (instance && typeof instance[method] === 'function') {
          return instance[method].apply(instance, args);
        }
      } else {
        return this.each(function() {
          var instance = _jquery2.default.data(this, NAME);
          if (instance && typeof instance[method] === 'function') {
            instance[method].apply(instance, args);
          }
        });
      }
    }

    return this.each(function() {
      if (!(0, _jquery2.default)(this).data(NAME)) {
        (0, _jquery2.default)(this).data(
          NAME,
          new asBreadcrumbs(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.asBreadcrumbs = jQueryAsBreadcrumbs;

  _jquery2.default.asBreadcrumbs = _jquery2.default.extend(
    {
      setDefaults: asBreadcrumbs.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.asBreadcrumbs = OtherAsBreadcrumbs;
        return jQueryAsBreadcrumbs;
      }
    },
    info
  );
});
