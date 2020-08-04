(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Config", ["exports"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports);
    global.Config = mod.exports;
  }
})(this, function (_exports) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.get = get;
  _exports.set = set;
  _exports.getColor = getColor;
  _exports.colors = colors;
  var values = {
    fontFamily: 'Noto Sans, sans-serif',
    primaryColor: 'blue',
    assets: '../assets'
  };

  function get() {
    var data = values;

    var callback = function callback(data, name) {
      return data[name];
    };

    for (var i = 0; i < arguments.length; i++) {
      var name = i < 0 || arguments.length <= i ? undefined : arguments[i];
      data = callback(data, name);
    }

    return data;
  }

  function set(name, value) {
    if (typeof name === 'string' && typeof value !== 'undefined') {
      values[name] = value;
    } else if (babelHelpers.typeof(name) === 'object') {
      values = $.extend(true, {}, values, name);
    }
  }

  function getColor(name, level) {
    if (name === 'primary') {
      name = get('primaryColor');

      if (!name) {
        name = 'red';
      }
    }

    if (typeof values.colors === 'undefined') {
      return null;
    }

    if (typeof values.colors[name] !== 'undefined') {
      if (level && typeof values.colors[name][level] !== 'undefined') {
        return values.colors[name][level];
      }

      if (typeof level === 'undefined') {
        return values.colors[name];
      }
    }

    return null;
  }

  function colors(name, level) {
    return getColor(name, level);
  }
});