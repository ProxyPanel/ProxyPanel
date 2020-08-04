/**
 * breakpoints-js v1.0.6
 * https://github.com/amazingSurge/breakpoints-js
 *
 * Copyright (c) amazingSurge
 * Released under the LGPL-3.0 license
 */
(function(global, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['exports'], factory);
  } else if (typeof exports !== 'undefined') {
    factory(exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports);
    global.breakpointsEs = mod.exports;
  }
})(this, function(exports) {
  'use strict';

  Object.defineProperty(exports, '__esModule', {
    value: true
  });

  function _possibleConstructorReturn(self, call) {
    if (!self) {
      throw new ReferenceError(
        "this hasn't been initialised - super() hasn't been called"
      );
    }

    return call && (typeof call === 'object' || typeof call === 'function')
      ? call
      : self;
  }

  function _inherits(subClass, superClass) {
    if (typeof superClass !== 'function' && superClass !== null) {
      throw new TypeError(
        'Super expression must either be null or a function, not ' +
          typeof superClass
      );
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    if (superClass)
      Object.setPrototypeOf
        ? Object.setPrototypeOf(subClass, superClass)
        : (subClass.__proto__ = superClass);
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

  var _typeof =
    typeof Symbol === 'function' && typeof Symbol.iterator === 'symbol'
      ? function(obj) {
          return typeof obj;
        }
      : function(obj) {
          return obj &&
            typeof Symbol === 'function' &&
            obj.constructor === Symbol &&
            obj !== Symbol.prototype
            ? 'symbol'
            : typeof obj;
        };

  /**
   * breakpoints-js v1.0.6
   * https://github.com/amazingSurge/breakpoints-js
   *
   * Copyright (c) amazingSurge
   * Released under the LGPL-3.0 license
   */
  var defaults = {
    // Extra small devices (phones)
    xs: {
      min: 0,
      max: 767
    },
    // Small devices (tablets)
    sm: {
      min: 768,
      max: 991
    },
    // Medium devices (desktops)
    md: {
      min: 992,
      max: 1199
    },
    // Large devices (large desktops)
    lg: {
      min: 1200,
      max: Infinity
    }
  };

  var util = {
    each: function each(obj, fn) {
      var continues = void 0;

      for (var i in obj) {
        if (
          (typeof obj === 'undefined' ? 'undefined' : _typeof(obj)) !==
            'object' ||
          obj.hasOwnProperty(i)
        ) {
          continues = fn(i, obj[i]);
          if (continues === false) {
            break; //allow early exit
          }
        }
      }
    },

    isFunction: function isFunction(obj) {
      return typeof obj === 'function' || false;
    },

    extend: function extend(obj, source) {
      for (var property in source) {
        obj[property] = source[property];
      }
      return obj;
    }
  };

  var Callbacks = (function() {
    function Callbacks() {
      _classCallCheck(this, Callbacks);

      this.length = 0;
      this.list = [];
    }

    _createClass(Callbacks, [
      {
        key: 'add',
        value: function add(fn, data) {
          var one =
            arguments.length > 2 && arguments[2] !== undefined
              ? arguments[2]
              : false;

          this.list.push({
            fn: fn,
            data: data,
            one: one
          });

          this.length++;
        }
      },
      {
        key: 'remove',
        value: function remove(fn) {
          for (var i = 0; i < this.list.length; i++) {
            if (this.list[i].fn === fn) {
              this.list.splice(i, 1);
              this.length--;
              i--;
            }
          }
        }
      },
      {
        key: 'empty',
        value: function empty() {
          this.list = [];
          this.length = 0;
        }
      },
      {
        key: 'call',
        value: function call(caller, i) {
          var fn =
            arguments.length > 2 && arguments[2] !== undefined
              ? arguments[2]
              : null;

          if (!i) {
            i = this.length - 1;
          }
          var callback = this.list[i];

          if (util.isFunction(fn)) {
            fn.call(this, caller, callback, i);
          } else if (util.isFunction(callback.fn)) {
            callback.fn.call(caller || window, callback.data);
          }

          if (callback.one) {
            delete this.list[i];
            this.length--;
          }
        }
      },
      {
        key: 'fire',
        value: function fire(caller) {
          var fn =
            arguments.length > 1 && arguments[1] !== undefined
              ? arguments[1]
              : null;

          for (var i in this.list) {
            if (this.list.hasOwnProperty(i)) {
              this.call(caller, i, fn);
            }
          }
        }
      }
    ]);

    return Callbacks;
  })();

  var ChangeEvent = {
    current: null,
    callbacks: new Callbacks(),
    trigger: function trigger(size) {
      var previous = this.current;
      this.current = size;
      this.callbacks.fire(size, function(caller, callback) {
        if (util.isFunction(callback.fn)) {
          callback.fn.call(
            {
              current: size,
              previous: previous
            },
            callback.data
          );
        }
      });
    },
    one: function one(data, fn) {
      return this.on(data, fn, true);
    },
    on: function on(data, fn) {
      var one =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : false;

      if (typeof fn === 'undefined' && util.isFunction(data)) {
        fn = data;
        data = undefined;
      }
      if (util.isFunction(fn)) {
        this.callbacks.add(fn, data, one);
      }
    },
    off: function off(fn) {
      if (typeof fn === 'undefined') {
        this.callbacks.empty();
      }
    }
  };

  var MediaQuery = (function() {
    function MediaQuery(name, media) {
      _classCallCheck(this, MediaQuery);

      this.name = name;
      this.media = media;

      this.initialize();
    }

    _createClass(MediaQuery, [
      {
        key: 'initialize',
        value: function initialize() {
          this.callbacks = {
            enter: new Callbacks(),
            leave: new Callbacks()
          };

          this.mql = (window.matchMedia && window.matchMedia(this.media)) || {
            matches: false,
            media: this.media,
            addListener: function addListener() {
              // do nothing
            },
            removeListener: function removeListener() {
              // do nothing
            }
          };

          var that = this;
          this.mqlListener = function(mql) {
            var type = (mql.matches && 'enter') || 'leave';

            that.callbacks[type].fire(that);
          };
          this.mql.addListener(this.mqlListener);
        }
      },
      {
        key: 'on',
        value: function on(types, data, fn) {
          var one =
            arguments.length > 3 && arguments[3] !== undefined
              ? arguments[3]
              : false;

          if (
            (typeof types === 'undefined' ? 'undefined' : _typeof(types)) ===
            'object'
          ) {
            for (var type in types) {
              if (types.hasOwnProperty(type)) {
                this.on(type, data, types[type], one);
              }
            }
            return this;
          }

          if (typeof fn === 'undefined' && util.isFunction(data)) {
            fn = data;
            data = undefined;
          }

          if (!util.isFunction(fn)) {
            return this;
          }

          if (typeof this.callbacks[types] !== 'undefined') {
            this.callbacks[types].add(fn, data, one);

            if (types === 'enter' && this.isMatched()) {
              this.callbacks[types].call(this);
            }
          }

          return this;
        }
      },
      {
        key: 'one',
        value: function one(types, data, fn) {
          return this.on(types, data, fn, true);
        }
      },
      {
        key: 'off',
        value: function off(types, fn) {
          var type = void 0;

          if (
            (typeof types === 'undefined' ? 'undefined' : _typeof(types)) ===
            'object'
          ) {
            for (type in types) {
              if (types.hasOwnProperty(type)) {
                this.off(type, types[type]);
              }
            }
            return this;
          }

          if (typeof types === 'undefined') {
            this.callbacks.enter.empty();
            this.callbacks.leave.empty();
          } else if (types in this.callbacks) {
            if (fn) {
              this.callbacks[types].remove(fn);
            } else {
              this.callbacks[types].empty();
            }
          }

          return this;
        }
      },
      {
        key: 'isMatched',
        value: function isMatched() {
          return this.mql.matches;
        }
      },
      {
        key: 'destroy',
        value: function destroy() {
          this.off();
        }
      }
    ]);

    return MediaQuery;
  })();

  var MediaBuilder = {
    min: function min(_min) {
      var unit =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : 'px';

      return '(min-width: ' + _min + unit + ')';
    },
    max: function max(_max) {
      var unit =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : 'px';

      return '(max-width: ' + _max + unit + ')';
    },
    between: function between(min, max) {
      var unit =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : 'px';

      return (
        '(min-width: ' + min + unit + ') and (max-width: ' + max + unit + ')'
      );
    },
    get: function get(min, max) {
      var unit =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : 'px';

      if (min === 0) {
        return this.max(max, unit);
      }
      if (max === Infinity) {
        return this.min(min, unit);
      }
      return this.between(min, max, unit);
    }
  };

  var Size = (function(_MediaQuery) {
    _inherits(Size, _MediaQuery);

    function Size(name) {
      var min =
        arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
      var max =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : Infinity;
      var unit =
        arguments.length > 3 && arguments[3] !== undefined
          ? arguments[3]
          : 'px';

      _classCallCheck(this, Size);

      var media = MediaBuilder.get(min, max, unit);

      var _this = _possibleConstructorReturn(
        this,
        (Size.__proto__ || Object.getPrototypeOf(Size)).call(this, name, media)
      );

      _this.min = min;
      _this.max = max;
      _this.unit = unit;

      var that = _this;
      _this.changeListener = function() {
        if (that.isMatched()) {
          ChangeEvent.trigger(that);
        }
      };
      if (_this.isMatched()) {
        ChangeEvent.current = _this;
      }

      _this.mql.addListener(_this.changeListener);
      return _this;
    }

    _createClass(Size, [
      {
        key: 'destroy',
        value: function destroy() {
          this.off();
          this.mql.removeListener(this.changeListener);
        }
      }
    ]);

    return Size;
  })(MediaQuery);

  var UnionSize = (function(_MediaQuery2) {
    _inherits(UnionSize, _MediaQuery2);

    function UnionSize(names) {
      _classCallCheck(this, UnionSize);

      var sizes = [];
      var media = [];

      util.each(names.split(' '), function(i, name) {
        var size = Breakpoints$1.get(name);
        if (size) {
          sizes.push(size);
          media.push(size.media);
        }
      });

      return _possibleConstructorReturn(
        this,
        (UnionSize.__proto__ || Object.getPrototypeOf(UnionSize)).call(
          this,
          names,
          media.join(',')
        )
      );
    }

    return UnionSize;
  })(MediaQuery);

  var info = {
    version: '1.0.6'
  };

  var sizes = {};
  var unionSizes = {};

  var Breakpoints = (window.Breakpoints = function() {
    for (
      var _len = arguments.length, args = Array(_len), _key = 0;
      _key < _len;
      _key++
    ) {
      args[_key] = arguments[_key];
    }

    Breakpoints.define.apply(Breakpoints, args);
  });

  Breakpoints.defaults = defaults;

  Breakpoints = util.extend(Breakpoints, {
    version: info.version,
    defined: false,
    define: function define(values) {
      var options =
        arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      if (this.defined) {
        this.destroy();
      }

      if (!values) {
        values = Breakpoints.defaults;
      }

      this.options = util.extend(options, {
        unit: 'px'
      });

      for (var size in values) {
        if (values.hasOwnProperty(size)) {
          this.set(size, values[size].min, values[size].max, this.options.unit);
        }
      }

      this.defined = true;
    },
    destroy: function destroy() {
      util.each(sizes, function(name, size) {
        size.destroy();
      });
      sizes = {};
      ChangeEvent.current = null;
    },
    is: function is(size) {
      var breakpoint = this.get(size);
      if (!breakpoint) {
        return null;
      }

      return breakpoint.isMatched();
    },
    all: function all() {
      var names = [];
      util.each(sizes, function(name) {
        names.push(name);
      });
      return names;
    },

    set: function set(name) {
      var min =
        arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
      var max =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : Infinity;
      var unit =
        arguments.length > 3 && arguments[3] !== undefined
          ? arguments[3]
          : 'px';

      var size = this.get(name);
      if (size) {
        size.destroy();
      }

      sizes[name] = new Size(name, min, max, unit);
      return sizes[name];
    },

    get: function get(size) {
      if (sizes.hasOwnProperty(size)) {
        return sizes[size];
      }

      return null;
    },

    getUnion: function getUnion(sizes) {
      if (unionSizes.hasOwnProperty(sizes)) {
        return unionSizes[sizes];
      }

      unionSizes[sizes] = new UnionSize(sizes);

      return unionSizes[sizes];
    },
    getMin: function getMin(size) {
      var obj = this.get(size);
      if (obj) {
        return obj.min;
      }
      return null;
    },
    getMax: function getMax(size) {
      var obj = this.get(size);
      if (obj) {
        return obj.max;
      }
      return null;
    },
    current: function current() {
      return ChangeEvent.current;
    },
    getMedia: function getMedia(size) {
      var obj = this.get(size);
      if (obj) {
        return obj.media;
      }
      return null;
    },
    on: function on(sizes, types, data, fn) {
      var one =
        arguments.length > 4 && arguments[4] !== undefined
          ? arguments[4]
          : false;

      sizes = sizes.trim();

      if (sizes === 'change') {
        fn = data;
        data = types;
        return ChangeEvent.on(data, fn, one);
      }
      if (sizes.includes(' ')) {
        var union = this.getUnion(sizes);

        if (union) {
          union.on(types, data, fn, one);
        }
      } else {
        var size = this.get(sizes);

        if (size) {
          size.on(types, data, fn, one);
        }
      }

      return this;
    },
    one: function one(sizes, types, data, fn) {
      return this.on(sizes, types, data, fn, true);
    },
    off: function off(sizes, types, fn) {
      sizes = sizes.trim();

      if (sizes === 'change') {
        return ChangeEvent.off(types);
      }

      if (sizes.includes(' ')) {
        var union = this.getUnion(sizes);

        if (union) {
          union.off(types, fn);
        }
      } else {
        var size = this.get(sizes);

        if (size) {
          size.off(types, fn);
        }
      }

      return this;
    }
  });

  var Breakpoints$1 = Breakpoints;

  exports.default = Breakpoints$1;
});
