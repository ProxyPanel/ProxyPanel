(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/uikit/panel-structure", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.uikitPanelStructure = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Button Random
  // ---------------------

  (function () {
    (0, _jquery.default)('#exampleButtonRandom').on('click', function (e) {
      e.preventDefault();
      (0, _jquery.default)('[data-plugin="progress"]').each(function () {
        var number = Math.round(Math.random(1) * 100) + '%';
        (0, _jquery.default)(this).asProgress('go', number);
      });
    });
  })(); // Example Panel With Tool
  // -----------------------


  window.customRefreshCallback = function (done) {
    var $panel = (0, _jquery.default)(this);
    setTimeout(function () {
      done();
      $panel.find('.panel-body').html('Lorem ipsum In nostrud Excepteur velit reprehenderit quis consequat veniam officia nisi labore in est.');
    }, 1000);
  }; // Example rating
  // ----------------------
  // data-plugin="rating" data-half="true" data-number="9" data-score="3" data-hints="bad,,,,regular,,,,gorgeous"


  (0, _jquery.default)(".yellow-rating").raty({
    targetKeep: true,
    half: true,
    number: 9,
    score: 3,
    hints: ["bad", "", "", "", "regular", "", "", "", "gorgeous"],
    icon: "font",
    starType: "i",
    starOff: "icon wb-star",
    starOn: "icon wb-star yellow-600",
    cancelOff: "icon wb-minus-circle",
    cancelOn: "icon wb-minus-circle yellow-600",
    starHalf: "icon wb-star-half yellow-500"
  });
});