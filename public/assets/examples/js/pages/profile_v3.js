(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/profile_v3", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesProfile_v3 = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Popup Gallery
  // ---------------------

  var galleryNum = (0, _jquery.default)('.imgs-gallery').length;

  for (var i = 0; i < galleryNum; i++) {
    (0, _jquery.default)((0, _jquery.default)('.imgs-gallery')[i]).magnificPopup({
      delegate: 'a',
      type: 'image',
      tLoading: 'Loading image #%curr%...',
      mainClass: 'mfp-img-mobile',
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1] // Will preload 0 - before current, and 1 after the current image

      },
      image: {
        tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
        titleSrc: function titleSrc(item) {
          return item.el.attr('title') + '<small>by amazingSurge</small>';
        }
      }
    });
  } //bind input focus


  (0, _jquery.default)('.wall-comment-reply .form-control').on('focus', function (event) {
    var $this = (0, _jquery.default)(this);
    var $reply_operation = $this.closest('form').find('.reply-operation'),
        $reply_cancel = $this.closest('form').find('.reply-operation .reply-cancel');

    if (!$reply_operation.hasClass('active')) {
      $reply_operation.addClass('active');
    }

    $reply_cancel.on('click', function () {
      $reply_operation.removeClass('active');
    });
    event.stopPropagation();
  });
});