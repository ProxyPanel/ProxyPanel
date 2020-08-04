(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/profile-v2", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesProfileV2 = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
    $$$1(".user-posts .user-posts-list").slick({
      dots: false,
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 1,
      adaptiveHeight: true,
      arrows: false,
      autoplay: true,
      swipeToSlide: true
    });
  });
});