(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Forum", ["exports", "BaseApp"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("BaseApp"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.BaseApp);
    global.AppForum = mod.exports;
  }
})(this, function (_exports, _BaseApp2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppForum = void 0;
  _BaseApp2 = babelHelpers.interopRequireDefault(_BaseApp2);

  var AppForum =
  /*#__PURE__*/
  function (_BaseApp) {
    babelHelpers.inherits(AppForum, _BaseApp);

    function AppForum() {
      babelHelpers.classCallCheck(this, AppForum);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppForum).apply(this, arguments));
    }

    return AppForum;
  }(_BaseApp2.default);

  _exports.AppForum = AppForum;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppForum();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppForum;
  _exports.default = _default;
});