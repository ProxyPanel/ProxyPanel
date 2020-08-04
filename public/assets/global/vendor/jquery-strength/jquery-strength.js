/**
* jQuery strength v0.2.5
* https://github.com/amazingSurge/jquery-strength
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
(function(global, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery', 'password_strength'], factory);
  } else if (typeof exports !== 'undefined') {
    factory(require('jquery'), require('password_strength'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.PasswordStrength);
    global.jqueryStrengthEs = mod.exports;
  }
})(this, function(_jquery, _password_strength) {
  'use strict';

  var _jquery2 = _interopRequireDefault(_jquery);

  var _password_strength2 = _interopRequireDefault(_password_strength);

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
    namespace: 'strength',
    skin: null,

    showMeter: true,
    showToggle: true,

    usernameField: '',

    templates: {
      toggle:
        '<span class="input-group-addon"><input type="checkbox" class="{toggleClass}" title="Show/Hide Password" /></span>',
      meter: '<div class="{meterClass}">{score}</div>',
      score: '<span class="label {scoreClass}"></span>',
      main:
        '<div class="{containerClass}"><div class="input-group">{input}{toggle}</div>{meter}</div>'
    },

    classes: {
      container: 'strength-container',
      status: 'strength-{status}',
      input: 'strength-input',
      toggle: 'strength-toggle',
      meter: 'strength-meter',
      score: 'strength-score',
      shown: 'strength-shown'
    },

    scoreLables: {
      empty: 'Empty',
      invalid: 'Invalid',
      weak: 'Weak',
      good: 'Good',
      strong: 'Strong'
    },

    scoreClasses: {
      empty: '',
      invalid: 'label-danger',
      weak: 'label-warning',
      good: 'label-info',
      strong: 'label-success'
    },

    emptyStatus: true,

    scoreCallback: null,
    statusCallback: null
  };

  var NAMESPACE$1 = 'strength';

  /**
   * Plugin constructor
   **/

  var Strength = (function() {
    function Strength(element) {
      var options =
        arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      _classCallCheck(this, Strength);

      this.element = element;
      this.$element = (0, _jquery2.default)(element);

      this.options = _jquery2.default.extend(
        true,
        {},
        DEFAULTS,
        options,
        this.$element.data()
      );
      this.classes = this.options.classes;

      this.$username = (0, _jquery2.default)(this.options.usernameField);

      this.score = 0;
      this.status = null;

      this.shown = false;

      this.trigger('init');
      this.init();
    }

    _createClass(
      Strength,
      [
        {
          key: 'init',
          value: function init() {
            this.createHtml();

            this.$element.addClass(this.classes.input);

            this.$toggle = this.$container.find('.' + this.classes.toggle);
            this.$meter = this.$container.find('.' + this.classes.meter);

            this.$score = this.$container.find('.' + this.classes.score);
            this.$input = this.$container.find('.' + this.classes.input);

            this.bindEvents();

            this.initialized = true;
            this.trigger('ready');
          }
        },
        {
          key: 'bindEvents',
          value: function bindEvents() {
            var _this = this;

            if (this.$toggle.is(':checkbox')) {
              this.$toggle.on('change', function() {
                _this.toggle();
              });
            } else {
              this.$toggle.on('click', function() {
                _this.toggle();
              });
            }

            this.$input.bind('keyup.strength keydown.strength', function() {
              _this.check();
            });

            this.$element.on(NAMESPACE$1 + '::check', function(
              e,
              api,
              score,
              status
            ) {
              _this.$score.html(_this.options.scoreLables[status]);

              if (status !== _this.status) {
                var newClass = _this.options.scoreClasses[status];
                var oldClass = _this.options.scoreClasses[_this.status];
                _this.$score.removeClass(oldClass).addClass(newClass);

                _this.trigger('statusChange', status, _this.status);
              }

              _this.status = status;
              _this.score = score;
            });

            this.$element.on(NAMESPACE$1 + '::statusChange', function(
              e,
              api,
              current,
              old
            ) {
              _this.$container
                .removeClass(_this.getStatusClass(old))
                .addClass(_this.getStatusClass(current));
            });
          }
        },
        {
          key: 'getStatusClass',
          value: function getStatusClass(status) {
            return this.options.classes.status.replace('{status}', status);
          }
        },
        {
          key: 'createHtml',
          value: function createHtml() {
            var output = this.options.templates.main;

            output = output.replace('{containerClass}', this.classes.container);
            output = output.replace('{toggle}', this.generateToggle());
            output = output.replace('{meter}', this.generateMeter());
            output = output.replace('{score}', this.generateScore());
            output = output.replace(
              '{input}',
              '<div class="' + this.classes.input + '"></div>'
            );
            this.$container = (0, _jquery2.default)(output);

            if (this.options.skin) {
              this.$container.addClass(this.options.skin);
            }

            this.$element.before(this.$container);
            var $holder = this.$container.find('.' + this.classes.input);
            var el = this.$element.detach();
            $holder.before(el);
            $holder.remove();
          }
        },
        {
          key: 'generateToggle',
          value: function generateToggle() {
            if (this.options.showToggle) {
              var output = this.options.templates.toggle;

              output = output.replace('{toggleClass}', this.classes.toggle);
              return output;
            }
            return '';
          }
        },
        {
          key: 'generateMeter',
          value: function generateMeter() {
            if (this.options.showMeter) {
              var output = this.options.templates.meter;

              output = output.replace('{meterClass}', this.classes.meter);
              return output;
            }
            return '';
          }
        },
        {
          key: 'generateScore',
          value: function generateScore() {
            var output = this.options.templates.score;

            output = output.replace('{scoreClass}', this.classes.score);
            return output;
          }
        },
        {
          key: 'check',
          value: function check() {
            var score = 0;
            var status = null;

            if (_jquery2.default.isFunction(this.options.scoreCallback)) {
              score = this.options.scoreCallback.call(this);

              if (_jquery2.default.isFunction(this.options.statusCallback)) {
                status = this.options.statusCallback.call(this, score);
              }
            } else {
              var check = new _password_strength2.default();
              check.username = this.$username.val() || null;
              check.password = this.$input.val();

              score = check.test();
              status = check.status;
            }

            if (
              this.options.emptyStatus &&
              status !== 'invalid' &&
              this.$input.val() === ''
            ) {
              status = 'empty';
            }

            this.trigger('check', score, status);
          }
        },
        {
          key: 'getScore',
          value: function getScore() {
            if (!this.score) {
              this.check();
            }
            return this.score;
          }
        },
        {
          key: 'getStatus',
          value: function getStatus() {
            if (!this.status) {
              this.check();
            }
            return this.status;
          }
        },
        {
          key: 'toggle',
          value: function toggle() {
            var type = void 0;

            if (this.$toggle.is(':checkbox')) {
              type = this.$toggle.is(':checked') ? 'text' : 'password';
            } else {
              type = this.shown === false ? 'text' : 'password';
            }

            this.shown = type === 'text';

            if (this.shown) {
              this.$container.addClass(this.classes.shown);
            } else {
              this.$container.removeClass(this.classes.shown);
            }
            this.$input.attr('type', type);

            this.trigger('toggle', type);
          }
        },
        {
          key: 'trigger',
          value: function trigger(eventType) {
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
          key: 'destroy',
          value: function destroy() {
            this.$element.data(NAMESPACE$1, null);
            this.trigger('destroy');
          }
        }
      ],
      [
        {
          key: 'setDefaults',
          value: function setDefaults(options) {
            _jquery2.default.extend(
              true,
              DEFAULTS,
              _jquery2.default.isPlainObject(options) && options
            );
          }
        }
      ]
    );

    return Strength;
  })();

  var info = {
    version: '0.2.5'
  };

  var NAMESPACE = 'strength';
  var OtherStrength = _jquery2.default.fn.strength;

  var jQueryStrength = function jQueryStrength(options) {
    for (
      var _len2 = arguments.length,
        args = Array(_len2 > 1 ? _len2 - 1 : 0),
        _key2 = 1;
      _key2 < _len2;
      _key2++
    ) {
      args[_key2 - 1] = arguments[_key2];
    }

    if (typeof options === 'string') {
      var method = options;

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
          new Strength(this, options)
        );
      }
    });
  };

  _jquery2.default.fn.strength = jQueryStrength;

  _jquery2.default.strength = _jquery2.default.extend(
    {
      setDefaults: Strength.setDefaults,
      noConflict: function noConflict() {
        _jquery2.default.fn.strength = OtherStrength;
        return jQueryStrength;
      }
    },
    info
  );
});
