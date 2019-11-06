(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/uikit/icon", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.uikitIcon = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
    $$$1('#icon_change').asRange({
      tip: false,
      scale: false,
      onChange: function onChange(value) {
        $$$1('#icon_size').text(value + "px");
        $$$1('.panel .icon').css('font-size', value);
      }
    });
    $$$1('.input-search input[type=text]').on('keyup', function () {
      var val = $$$1(this).val();

      if (val !== '') {
        $$$1('[data-name]').addClass('is-hide');
        $$$1('[data-name*=' + val + ']').removeClass('is-hide');
      } else {
        $$$1('[data-name]').removeClass('is-hide');
      }

      $$$1('.icon-group').each(function () {
        var $group = $$$1(this);

        if ($group.find('[data-name]:not(.is-hide)').length === 0) {
          $group.hide();
        } else {
          $group.show();
        }
      });
    });
  });
});