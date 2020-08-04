(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/aspieprogress", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginAspieprogress = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'pieProgress';

  var PieProgress =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(PieProgress, _Plugin);

    function PieProgress() {
      babelHelpers.classCallCheck(this, PieProgress);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PieProgress).apply(this, arguments));
    }

    babelHelpers.createClass(PieProgress, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.asPieProgress) {
          return;
        }

        var $el = this.$el;
        $el.asPieProgress(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          namespace: 'pie-progress',
          speed: 30,
          classes: {
            svg: 'pie-progress-svg',
            element: 'pie-progress',
            number: 'pie-progress-number',
            content: 'pie-progress-content'
          }
        };
      }
    }]);
    return PieProgress;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, PieProgress);

  var _default = PieProgress;
  _exports.default = _default;
});