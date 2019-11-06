(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Base", ["exports", "jquery", "Component", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Component"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Component, global.Plugin);
    global.Base = mod.exports;
  }
})(this, function (_exports, _jquery, _Component2, _Plugin) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Component2 = babelHelpers.interopRequireDefault(_Component2);

  var Base =
  /*#__PURE__*/
  function (_Component) {
    babelHelpers.inherits(Base, _Component);

    function Base() {
      babelHelpers.classCallCheck(this, Base);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Base).apply(this, arguments));
    }

    babelHelpers.createClass(Base, [{
      key: "initializePlugins",
      value: function initializePlugins() {
        var context = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
        (0, _jquery.default)('[data-plugin]', context || this.$el).each(function () {
          var $this = (0, _jquery.default)(this);
          var name = $this.data('plugin');
          var plugin = (0, _Plugin.pluginFactory)(name, $this, $this.data());

          if (plugin) {
            plugin.initialize();
          }
        });
      }
    }, {
      key: "initializePluginAPIs",
      value: function initializePluginAPIs() {
        var context = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
        var apis = (0, _Plugin.getPluginAPI)();

        for (var name in apis) {
          (0, _Plugin.getPluginAPI)(name)("[data-plugin=".concat(name, "]"), context);
        }
      }
    }]);
    return Base;
  }(_Component2.default);

  var _default = Base;
  _exports.default = _default;
});