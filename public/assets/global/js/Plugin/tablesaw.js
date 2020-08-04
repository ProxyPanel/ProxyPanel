(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/tablesaw", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginTablesaw = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tablesaw';

  var Tablesaw =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Tablesaw, _Plugin);

    function Tablesaw() {
      babelHelpers.classCallCheck(this, Tablesaw);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tablesaw).apply(this, arguments));
    }

    babelHelpers.createClass(Tablesaw, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }, {
      key: "api",
      value: function api() {
        return function () {
          if (typeof _jquery.default.fn.tablesaw === 'undefined') {
            return;
          }

          (0, _jquery.default)(document).trigger('enhance.tablesaw');
        };
      }
    }]);
    return Tablesaw;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Tablesaw);

  var _default = Tablesaw;
  _exports.default = _default;
});