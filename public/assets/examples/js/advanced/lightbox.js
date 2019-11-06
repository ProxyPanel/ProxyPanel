(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/advanced/lightbox", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.advancedLightbox = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Popup Zoom Gallery
  // --------------------------

  (0, _jquery.default)('#exampleZoomGallery').magnificPopup({
    delegate: 'a',
    type: 'image',
    closeOnContentClick: false,
    closeBtnInside: false,
    mainClass: 'mfp-with-zoom mfp-img-mobile',
    image: {
      verticalFit: true,
      titleSrc: function titleSrc(item) {
        return item.el.attr('title') + ' &middot; <a class="image-source-link" href="' + item.el.attr('data-source') + '" target="_blank">image source</a>';
      }
    },
    gallery: {
      enabled: true
    },
    zoom: {
      enabled: true,
      duration: 300,
      // don't foget to change the duration also in CSS
      opener: function opener(element) {
        return element.find('img');
      }
    }
  }); // Example Popup Gallery
  // ---------------------

  (0, _jquery.default)('#exampleGallery').magnificPopup({
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
  }); // Example Popup With Css Animation
  // --------------------------------

  (0, _jquery.default)('.popup-with-css-anim').magnificPopup({
    type: 'image',
    removalDelay: 500,
    preloader: true,
    callbacks: {
      beforeOpen: function beforeOpen() {
        this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
        this.st.mainClass = this.st.el.attr('data-effect');
      }
    },
    closeOnContentClick: true,
    midClick: true
  }); // Example Popup With Video Rr Map
  // -------------------------------

  (0, _jquery.default)('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,
    fixedContentPos: false
  }); // Example Popup With Video Rr Map
  // -------------------------------

  (0, _jquery.default)('#examplePopupForm').magnificPopup({
    type: 'inline',
    preloader: false,
    focus: '#inputName',
    // When elemened is focused, some mobile browsers in some cases zoom in
    // It looks not nice, so we disable it:
    callbacks: {
      beforeOpen: function beforeOpen() {
        if ((0, _jquery.default)(window).width() < 700) {
          this.st.focus = false;
        } else {
          this.st.focus = '#inputName';
        }
      }
    }
  }); // Example Ajax Popup
  // ------------------

  (0, _jquery.default)('#examplePopupAjaxAlignTop').magnificPopup({
    type: 'ajax',
    alignTop: true,
    overflowY: 'scroll' // as we know that popup content is tall we set scroll overflow by default to avoid jump

  });
  (0, _jquery.default)('#examplePopupAjax').magnificPopup({
    type: 'ajax'
  }); // Example Popup Modal
  // -------------------

  (0, _jquery.default)('.popup-modal').magnificPopup({
    type: 'inline',
    preloader: false,
    modal: true
  });
  (0, _jquery.default)(document).on('click', '.popup-modal-dismiss', function (e) {
    e.preventDefault();

    _jquery.default.magnificPopup.close();
  }); // Example Error Handling
  // ----------------------

  (0, _jquery.default)('#exampleBrokenImage, #exampleBrokenAjax').magnificPopup({});
});