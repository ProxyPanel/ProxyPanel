(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/tasklist", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginTasklist = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'tasklist';

  var TaskList =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(TaskList, _Plugin);

    function TaskList() {
      babelHelpers.classCallCheck(this, TaskList);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TaskList).apply(this, arguments));
    }

    babelHelpers.createClass(TaskList, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        this.$el.data('tasklistApi', this);
        this.$checkbox = this.$el.find('[type="checkbox"]');
        this.$el.trigger('change.site.task');
      }
    }, {
      key: "toggle",
      value: function toggle() {
        if (this.$checkbox.is(':checked')) {
          this.$el.addClass('task-done');
        } else {
          this.$el.removeClass('task-done');
        }
      }
    }], [{
      key: "api",
      value: function api() {
        return 'change|toggle';
      }
    }]);
    return TaskList;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, TaskList);

  var _default = TaskList;
  _exports.default = _default;
});