(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/webui-popover", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginWebuiPopover = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'webuiPopover';

  var WebuiPopover =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(WebuiPopover, _Plugin);

    function WebuiPopover() {
      babelHelpers.classCallCheck(this, WebuiPopover);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebuiPopover).apply(this, arguments));
    }

    babelHelpers.createClass(WebuiPopover, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          trigger: 'click',
          width: 320,
          multi: true,
          cloaseable: false,
          style: '',
          delay: 300,
          padding: true
        };
      }
    }]);
    return WebuiPopover;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, WebuiPopover);

  var _default = WebuiPopover;
  _exports.default = _default;
});