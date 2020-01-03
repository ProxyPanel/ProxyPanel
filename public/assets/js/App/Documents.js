(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Documents", ["exports", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site);
    global.AppDocuments = mod.exports;
  }
})(this, function (_exports, _Site2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppDocuments = void 0;
  _Site2 = babelHelpers.interopRequireDefault(_Site2);

  var AppDocuments =
  /*#__PURE__*/
  function (_Site) {
    babelHelpers.inherits(AppDocuments, _Site);

    function AppDocuments() {
      babelHelpers.classCallCheck(this, AppDocuments);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppDocuments).apply(this, arguments));
    }

    babelHelpers.createClass(AppDocuments, [{
      key: "initialize",
      value: function initialize() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppDocuments.prototype), "initialize", this).call(this);
        this.scrollHandle();
        this.stickyfillHandle();
      }
    }, {
      key: "process",
      value: function process() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppDocuments.prototype), "process", this).call(this);
        this.handleResize();
      }
    }, {
      key: "scrollHandle",
      value: function scrollHandle() {
        $('body').scrollspy({
          target: '#articleSticky',
          offset: 80
        });
      }
    }, {
      key: "stickyfillHandle",
      value: function stickyfillHandle() {
        if (!window.Stickyfill) {
          return false;
        }

        Stickyfill.add($('#articleSticky')); // $('#articleSticky').Stickyfill();
      }
    }, {
      key: "handleResize",
      value: function handleResize() {
        $(window).on('resize orientationchange', function () {
          $(this).width() > 767 ? Stickyfill.refreshAll() : Stickyfill.removeAll();
        }).resize();
      }
    }]);
    return AppDocuments;
  }(_Site2.default);

  _exports.AppDocuments = AppDocuments;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppDocuments();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppDocuments;
  _exports.default = _default;
});