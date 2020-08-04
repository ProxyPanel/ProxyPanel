(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/editable-table", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginEditableTable = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'editableTable';

  var EditableTable =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(EditableTable, _Plugin);

    function EditableTable() {
      babelHelpers.classCallCheck(this, EditableTable);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EditableTable).apply(this, arguments));
    }

    babelHelpers.createClass(EditableTable, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (!_jquery.default.fn.editableTableWidget) {
          return;
        }

        var $el = this.$el;
        $el.editableTableWidget(this.options);
      }
    }], [{
      key: "getDefaults",
      value: function getDefaults() {
        return {};
      }
    }]);
    return EditableTable;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, EditableTable);

  var _default = EditableTable;
  _exports.default = _default;
});