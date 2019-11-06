(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/uikit/carousel", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.uikitCarousel = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)(); // Example Slick Single Item
    // -------------------------

    $$$1('#exampleSingleItem').slick(); // Example Slick Multiple Items
    // ----------------------------

    $$$1('#exampleMultipleItems').slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 3
    }); // Example Slick Responsive Display
    // --------------------------------

    $$$1('#exampleResponsive').slick({
      dots: true,
      infinite: false,
      speed: 500,
      slidesToShow: 4,
      slidesToScroll: 4,
      responsive: [{
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
          dots: true
        }
      }, {
        breakpoint: 600,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      }, {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        } // You can unslick at a given breakpoint now by adding:
        // settings: "unslick"
        // instead of a settings object

      }]
    }); // Example Slick Variable Width
    // ----------------------------

    $$$1('#exampleVariableWidth').slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 1,
      centerMode: true,
      variableWidth: true
    }); // Example Slick Adaptive Height
    // -----------------------------

    $$$1('#exampleAdaptiveHeight').slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 1,
      adaptiveHeight: true
    }); // Example Slick Data Attribute Settings
    // -----------------------------

    $$$1('#exampleData').slick(); // Example Slick Center Mode
    // -------------------------

    $$$1('#exampleCenter').slick({
      centerMode: true,
      centerPadding: '60px',
      slidesToShow: 3,
      responsive: [{
        breakpoint: 768,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '40px',
          slidesToShow: 3
        }
      }, {
        breakpoint: 480,
        settings: {
          arrows: false,
          centerMode: true,
          centerPadding: '40px',
          slidesToShow: 1
        }
      }]
    }); // Example Slick Lazy Loading
    // --------------------------

    $$$1('#exampleLazy').slick({
      lazyLoad: 'ondemand',
      slidesToShow: 3,
      slidesToScroll: 1
    }); // Example Slick Autoplay
    // ----------------------

    $$$1('#exampleAutoplay').slick({
      dots: true,
      infinite: true,
      speed: 500,
      slidesToShow: 3,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 2000
    }); // Example Slick Fade
    // ------------------

    $$$1('#exampleFade').slick({
      dots: true,
      infinite: true,
      speed: 500,
      fade: true,
      slide: 'div',
      cssEase: 'linear'
    }); // Example Slick Add & Remove
    // --------------------------

    var slideIndex = 1;
    $$$1('#exampleAddRemove').slick({
      dots: true,
      slidesToShow: 3,
      speed: 500,
      slidesToScroll: 3
    });
    $$$1('#exampleAddSlide').on('click', function () {
      slideIndex++;
      $$$1('#exampleAddRemove').slick('slickAdd', '<div><h3>' + slideIndex + '</h3></div>');
    });
    $$$1('#exampleRemoveSlide').on('click', function () {
      $$$1('#exampleAddRemove').slick('slickRemove', slideIndex - 1);

      if (slideIndex !== 0) {
        slideIndex--;
      }
    }); // Example Slick Filtering
    // -----------------------

    $$$1('#exampleFiltering').slick({
      slidesToShow: 4,
      slidesToScroll: 4
    });
    var filtered = false;
    $$$1('#exampleFilter').on('click', function () {
      if (filtered === false) {
        $$$1('#exampleFiltering').slick('slickFilter', ':even');
        $$$1(this).text('Unfilter Slides');
        filtered = true;
      } else {
        $$$1('#exampleFiltering').slick('slickUnfilter');
        $$$1(this).text('Filter Slides');
        filtered = false;
      }
    });
  });
});