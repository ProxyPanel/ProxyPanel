(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/material", ["exports", "jquery", "Plugin"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("jquery"), require("Plugin"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.jQuery, global.Plugin);
    global.PluginMaterial = mod.exports;
  }
})(this, function (_exports, _jquery, _Plugin2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  _jquery = babelHelpers.interopRequireDefault(_jquery);
  _Plugin2 = babelHelpers.interopRequireDefault(_Plugin2);
  var NAME = 'formMaterial';

  function isChar(e) {
    if (typeof e.which === 'undefined') {
      return true;
    } else if (typeof e.which === 'number' && e.which > 0) {
      return !e.ctrlKey && !e.metaKey && !e.altKey && e.which !== 8 && e.which !== 9;
    }

    return false;
  }

  var FormMaterial =
  /*#__PURE__*/
  function (_Plugin) {
    babelHelpers.inherits(FormMaterial, _Plugin);

    function FormMaterial() {
      babelHelpers.classCallCheck(this, FormMaterial);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormMaterial).apply(this, arguments));
    }

    babelHelpers.createClass(FormMaterial, [{
      key: "getName",
      value: function getName() {
        return NAME;
      }
    }, {
      key: "render",
      value: function render() {
        var $el = this.$el;
        var $control = this.$control = $el.find('.form-control'); // Add hint label if required

        if ($control.attr('data-hint')) {
          $control.after("<div class=hint>".concat($control.attr('data-hint'), "</div>"));
        }

        if ($el.hasClass('floating')) {
          // Add floating label if required
          if ($control.hasClass('floating-label')) {
            var placeholder = $control.attr('placeholder');
            $control.attr('placeholder', null).removeClass('floating-label');
            $control.after("<div class=floating-label>".concat(placeholder, "</div>"));
          } // Set as empty if is empty


          if ($control.val() === null || $control.val() === 'undefined' || $control.val() === '') {
            $control.addClass('empty');
          }
        } // Support for file input


        if ($control.next().is('[type=file]')) {
          $el.addClass('form-material-file');
        }

        this.$file = $el.find('[type=file]');
        this.bind();
        $el.data('formMaterialAPI', this);
      }
    }, {
      key: "bind",
      value: function bind() {
        var _this = this;

        var $el = this.$el;
        var $control = this.$control = $el.find('.form-control');
        $el.on('keydown.site.material paste.site.material', '.form-control', function (e) {
          if (isChar(e)) {
            $control.removeClass('empty');
          }
        }).on('keyup.site.material change.site.material', '.form-control', function () {
          if ($control.val() === '' && typeof $control[0].checkValidity !== 'undefined' && $control[0].checkValidity()) {
            $control.addClass('empty');
          } else {
            $control.removeClass('empty');
          }
        });

        if (this.$file.length > 0) {
          this.$file.on('focus', function () {
            _this.$el.find('input').addClass('focus');
          }).on('blur', function () {
            _this.$el.find('input').removeClass('focus');
          }).on('change', function () {
            var $this = (0, _jquery.default)(this);
            var value = '';

            _jquery.default.each($this[0].files, function (i, file) {
              value += "".concat(file.name, ", ");
            });

            value = value.substring(0, value.length - 2);

            if (value) {
              $this.prev().removeClass('empty');
            } else {
              $this.prev().addClass('empty');
            }

            $this.prev().val(value);
          });
        }
      }
    }]);
    return FormMaterial;
  }(_Plugin2.default);

  _Plugin2.default.register(NAME, FormMaterial);

  var _default = FormMaterial;
  _exports.default = _default;
});