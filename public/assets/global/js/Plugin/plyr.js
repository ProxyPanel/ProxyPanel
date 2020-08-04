(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/plyr", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginPlyr = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'plyr';
  (0, _jquery.default)(document).ready(function () {
    var a = new XMLHttpRequest();
    var d = document;
    var u = 'https://cdn.plyr.io/1.1.5/sprite.svg';
    var b = d.body; // Check for CORS support

    if ('withCredentials' in a) {
      a.open('GET', u, true);
      a.send();

      a.onload = function () {
        var c = d.createElement('div');
        c.style.display = 'none';
        c.innerHTML = a.responseText;
        b.insertBefore(c, b.childNodes[0]);
      };
    }
  });

  var Plyr =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(Plyr, _Plugin);

    function Plyr() {
      babelHelpers.classCallCheck(this, Plyr);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Plyr).apply(this, arguments));
    }

    babelHelpers.createClass(Plyr, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof plyr === 'undefined') {
          return;
        }

        plyr.setup(this.$el[0], this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return Plyr;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, Plyr);

  var _default = Plyr;
  _exports.default = _default;
});