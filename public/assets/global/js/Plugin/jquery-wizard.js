(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/jquery-wizard", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginJqueryWizard = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'wizard';

  var Wizard =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Wizard, _Plugin);

    function Wizard() {
      babelHelpers.classCallCheck(this, Wizard);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Wizard).apply(this, arguments));
    }

    babelHelpers.createClass(Wizard, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          step: '.steps .step, .pearls .pearl',
          templates: {
            buttons: function buttons() {
              var options = this.options;
              return "<div class=\"wizard-buttons\"><a class=\"btn btn-default btn-outline\" href=\"#".concat(this.id, "\" data-wizard=\"back\" role=\"button\">").concat(options.buttonLabels.back, "</a><a class=\"btn btn-primary btn-outline float-right\" href=\"#").concat(this.id, "\" data-wizard=\"next\" role=\"button\">").concat(options.buttonLabels.next, "</a><a class=\"btn btn-success btn-outline float-right\" href=\"#").concat(this.id, "\" data-wizard=\"finish\" role=\"button\">").concat(options.buttonLabels.finish, "</a></div>");
            }
          },
          classes: {
            step: {
              done: 'done',
              error: 'error',
              active: 'active',
              disabled: 'disabled',
              activing: 'activing',
              loading: 'loading'
            },
            pane: {
              active: 'active',
              activing: 'activing'
            },
            button: {
              hide: 'hidden-xs-up',
              disabled: 'disabled'
            }
          }
        };
      }
    }]);
    return Wizard;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Wizard);

  var _default = Wizard;
  _exports.default = _default;
});