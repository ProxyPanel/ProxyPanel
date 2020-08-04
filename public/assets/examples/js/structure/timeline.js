(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/structure/timeline", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.structureTimeline = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
    $$$1('.timeline-item').appear();
    $$$1('.timeline-item').not(':appeared').each(function () {
      var $item = $$$1(this);
      $item.addClass('timeline-invisible');
      $item.find('.timeline-dot').addClass('invisible');
      $item.find('.timeline-info').addClass('invisible');
      $item.find('.timeline-content').addClass('invisible');
    });
    $$$1(document).on('appear', '.timeline-item.timeline-invisible', function (e) {
      var $item = $$$1(this);
      $item.removeClass('timeline-invisible');
      $item.find('.timeline-dot').removeClass('invisible').addClass('animation-scale-up');

      if ($item.hasClass('timeline-reverse') || $item.css('float') === 'none') {
        $item.find('.timeline-info').removeClass('invisible').addClass('animation-slide-right');
        $item.find('.timeline-content').removeClass('invisible').addClass('animation-slide-right');
      } else {
        $item.find('.timeline-info').removeClass('invisible').addClass('animation-slide-left');
        $item.find('.timeline-content').removeClass('invisible').addClass('animation-slide-left');
      }
    });
  });
});