(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/ace-editor", ["exports", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Plugin);
    global.PluginAceEditor = mod.exports;
  }
})(this, function (_exports, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'ace';

  var AceEditor =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(AceEditor, _Plugin);

    function AceEditor() {
      babelHelpers.classCallCheck(this, AceEditor);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AceEditor).apply(this, arguments));
    }

    babelHelpers.createClass(AceEditor, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        if (typeof ace === 'undefined') {
          return;
        } // ace.config.set("themePath", "../theme");


        ace.config.loadModule('ace/ext/language_tools');
        var $el = this.$el;
        var id = $el.attr('id');
        var editor = ace.edit(id);
        editor.container.style.opacity = '';

        if (this.options.mode) {
          editor.session.setMode("ace/mode/".concat(this.options.mode));
        }

        if (this.options.theme) {
          editor.setTheme("ace/theme/".concat(this.options.theme));
        }

        editor.setOption('maxLines', 40);
        editor.setAutoScrollEditorIntoView(true);
        ace.config.loadModule('ace/ext/language_tools', function () {
          editor.setOptions({
            enableSnippets: true,
            enableBasicAutocompletion: true
          });
        });
      }
    }]);
    return AceEditor;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, AceEditor);

  var _default = AceEditor;
  _exports.default = _default;
});