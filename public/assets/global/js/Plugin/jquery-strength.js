(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jquery-strength", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginJqueryStrength = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'strength';

  var Strength =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Strength, _Plugin);

    function Strength() {
      babelHelpers.classCallCheck(this, Strength);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Strength).apply(this, arguments));
    }

    babelHelpers.createClass(Strength, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          showMeter: true,
          showToggle: false,
          templates: {
            toggle: '<div class="checkbox-custom checkbox-primary show-password-wrap"><input type="checkbox" class="{toggleClass}" title="Show/Hide Password" id="show_password" /><label for="show_password">Show Password</label></div>',
            meter: '<div class="{meterClass}">{score}</div>',
            score: '<div class="{scoreClass}"></div>',
            main: '<div class="{containerClass}">{input}{meter}{toggle}</div>'
          },
          classes: {
            container: 'strength-container',
            status: 'strength-{status}',
            input: 'strength-input',
            toggle: 'strength-toggle',
            meter: 'strength-meter',
            score: 'strength-score'
          },
          scoreLables: {
            invalid: 'Invalid',
            weak: 'Weak',
            good: 'Good',
            strong: 'Strong'
          },
          scoreClasses: {
            invalid: 'strength-invalid',
            weak: 'strength-weak',
            good: 'strength-good',
            strong: 'strength-strong'
          }
        };
      }
    }]);
    return Strength;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Strength);

  var _default = Strength;
  _exports.default = _default;
});