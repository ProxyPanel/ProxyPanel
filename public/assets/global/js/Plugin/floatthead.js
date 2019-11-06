(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/floatthead", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginFloatthead = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'floatThead';

  var FloatThead =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(FloatThead, _Plugin);

    function FloatThead() {
      babelHelpers.classCallCheck(this, FloatThead);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FloatThead).apply(this, arguments));
    }

    babelHelpers.createClass(FloatThead, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {
          position: 'auto',
          top: function top() {
            var offset = (0, _jquery.default)('.page').offset();
            return offset.top;
          },
          responsiveContainer: function responsiveContainer($table) {
            return $table.closest('.table-responsive');
          }
        };
      }
    }]);
    return FloatThead;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, FloatThead);

  var _default = FloatThead;
  _exports.default = _default;
});