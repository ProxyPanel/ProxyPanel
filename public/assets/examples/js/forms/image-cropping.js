(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/forms/image-cropping", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsImageCropping = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Cropper Simple
  // ----------------------

  (function () {
    (0, _jquery.default)("#simpleCropper img").cropper({
      preview: "#simpleCropperPreview >.img-preview",
      responsive: true
    });
  })(); // Example Cropper Full
  // --------------------


  (function () {
    var $exampleFullCropper = (0, _jquery.default)("#exampleFullCropper img"),
        $inputDataX = (0, _jquery.default)("#inputDataX"),
        $inputDataY = (0, _jquery.default)("#inputDataY"),
        $inputDataHeight = (0, _jquery.default)("#inputDataHeight"),
        $inputDataWidth = (0, _jquery.default)("#inputDataWidth");
    var options = {
      aspectRatio: 16 / 9,
      preview: "#exampleFullCropperPreview > .img-preview",
      responsive: true,
      crop: function crop() {
        var data = (0, _jquery.default)(this).data('cropper').getCropBoxData();
        $inputDataX.val(Math.round(data.left));
        $inputDataY.val(Math.round(data.top));
        $inputDataHeight.val(Math.round(data.height));
        $inputDataWidth.val(Math.round(data.width));
      }
    }; // set up cropper

    $exampleFullCropper.cropper(options); // set up method buttons

    (0, _jquery.default)(document).on("click", "[data-cropper-method]", function () {
      var data = (0, _jquery.default)(this).data(),
          method = (0, _jquery.default)(this).data('cropper-method'),
          result;

      if (method) {
        result = $exampleFullCropper.cropper(method, data.option);
      }

      if (method === 'getCroppedCanvas') {
        (0, _jquery.default)('#getDataURLModal').modal().find('.modal-body').html(result);
      }
    }); // deal wtih uploading

    var $inputImage = (0, _jquery.default)("#inputImage");

    if (window.FileReader) {
      $inputImage.change(function () {
        var fileReader = new FileReader(),
            files = this.files,
            file;

        if (!files.length) {
          return;
        }

        file = files[0];

        if (/^image\/\w+$/.test(file.type)) {
          fileReader.readAsDataURL(file);

          fileReader.onload = function () {
            $exampleFullCropper.cropper("reset", true).cropper("replace", this.result);
            $inputImage.val("");
          };
        } else {
          showMessage("Please choose an image file.");
        }
      });
    } else {
      $inputImage.addClass("hide");
    } // set data


    (0, _jquery.default)("#setCropperData").click(function () {
      var data = {
        left: parseInt($inputDataX.val()),
        top: parseInt($inputDataY.val()),
        width: parseInt($inputDataWidth.val()),
        height: parseInt($inputDataHeight.val())
      };
      $exampleFullCropper.cropper("setCropBoxData", data);
    });
  })();
});