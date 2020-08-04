(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/matchheight", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginMatchheight = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'matchHeight';

  var MatchHeight =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(MatchHeight, _Plugin);

    function MatchHeight() {
      babelHelpers.classCallCheck(this, MatchHeight);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MatchHeight).apply(this, arguments));
    }

    babelHelpers.createClass(MatchHeight, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof _jquery.default.fn.matchHeight === 'undefined') {
          return;
        }

        var $el = this.$el;
        var matchSelector = $el.data('matchSelector');

        if (matchSelector) {
          $el.find(matchSelector).matchHeight(this.options);
        } else {
          $el.children().matchHeight(this.options);
        }
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return MatchHeight;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, MatchHeight);

  var _default = MatchHeight;
  _exports.default = _default;
});