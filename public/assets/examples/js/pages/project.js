(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/project", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesProject = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example File Upload
  // -------------------

  (0, _jquery.default)('#projectUploadForm').fileupload({
    url: '../../server/fileupload/',
    dropzone: (0, _jquery.default)('#projectUploadForm'),
    filesContainer: (0, _jquery.default)('.file-list'),
    uploadTemplateId: false,
    downloadTemplateId: false,
    uploadTemplate: tmpl('{% for (var i=0, file; file=o.files[i]; i++) { %}' + '<div class="file-item-wrap template-upload fade col-xxl-2 col-xl-3 col-md-4 col-sm-6 {%=file.type.search("image") !== -1? "image" : "other-file"%}">' + '<div class="file-item">' + '<div class="preview vertical-align">' + '<div class="file-action-wrap">' + '<div class="file-action">' + '{% if (!i && !o.options.autoUpload) { %}' + '<i class="icon wb-upload start" data-toggle="tooltip" data-original-title="Upload file" aria-hidden="true"></i>' + '{% } %}' + '{% if (!i) { %}' + '<i class="icon wb-close cancel" data-toggle="tooltip" data-original-title="Stop upload file" aria-hidden="true"></i>' + '{% } %}' + '</div>' + '</div>' + '</div>' + '<div class="info-wrap">' + '<div class="title">{%=file.name%}</div>' + '</div>' + '<div class="progress progress-striped active" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" role="progressbar">' + '<div class="progress-bar progress-bar-success" style="width:0%;"></div>' + '</div>' + '</div>' + '</div>' + '{% } %}'),
    downloadTemplate: tmpl('{% for (var i=0, file; file=o.files[i]; i++) { %}' + '<div class="file-item-wrap template-download fade col-xxl-2 col-xl-3 col-md-4 col-sm-6 {%=file.type.search("image") !== -1? "image" : "other-file"%}">' + '<div class="file-item">' + '<div class="preview vertical-align">' + '<div class="file-action-wrap">' + '<div class="file-action">' + '<i class="icon wb-trash delete" data-toggle="tooltip" data-original-title="Delete files" aria-hidden="true"></i>' + '</div>' + '</div>' + '<img src="{%=file.url%}"/>' + '</div>' + '<div class="info-wrap">' + '<div class="title">{%=file.name%}</div>' + '</div>' + '</div>' + '</div>' + '{% } %}'),
    forceResize: true,
    previewCanvas: false,
    previewMaxWidth: false,
    previewMaxHeight: false,
    previewThumbnail: false
  }).on('fileuploadprocessalways', function (e, data) {
    var length = data.files.length;

    for (var i = 0; i < length; i++) {
      if (!data.files[i].type.match(/^image\/(gif|jpeg|png|svg\+xml)$/)) {
        data.files[i].filetype = 'other-file';
      } else {
        data.files[i].filetype = 'image';
      }
    }
  }).on('fileuploadadded', function (e) {
    var $this = (0, _jquery.default)(e.target);

    if ($this.find('.file-item-wrap').length > 0) {
      $this.addClass('has-file');
    } else {
      $this.removeClass('has-file');
    }
  }).on('fileuploadfinished', function (e) {
    var $this = (0, _jquery.default)(e.target);

    if ($this.find('.file-item-wrap').length > 0) {
      $this.addClass('has-file');
    } else {
      $this.removeClass('has-file');
    }
  }).on('fileuploaddestroyed', function (e) {
    var $this = (0, _jquery.default)(e.target);

    if ($this.find('.file-item-wrap').length > 0) {
      $this.addClass('has-file');
    } else {
      $this.removeClass('has-file');
    }
  }).on('click', function (e) {
    if ((0, _jquery.default)(e.target).parents('.file-item-wrap').length === 0) (0, _jquery.default)('#inputUpload').trigger('click');
  });
  (0, _jquery.default)(document).bind('dragover', function (e) {
    var dropZone = (0, _jquery.default)('#projectUploadForm'),
        timeout = window.dropZoneTimeout;

    if (!timeout) {
      dropZone.addClass('in');
    } else {
      clearTimeout(timeout);
    }

    var found = false,
        node = e.target;

    do {
      if (node === dropZone[0]) {
        found = true;
        break;
      }

      node = node.parentNode;
    } while (node !== null);

    if (found) {
      dropZone.addClass('hover');
    } else {
      dropZone.removeClass('hover');
    }

    window.dropZoneTimeout = setTimeout(function () {
      window.dropZoneTimeout = null;
      dropZone.removeClass('in hover');
    }, 100);
  });
  (0, _jquery.default)('#inputUpload').on('click', function (e) {
    e.stopPropagation();
  });
  (0, _jquery.default)('#uploadlink').on('click', function (e) {
    e.stopPropagation();
  });

  (function () {
    // bind checklist and progress bar
    (0, _jquery.default)('input[type=checkbox]').on('click', function () {
      var $checklistItems = (0, _jquery.default)('.project-checklist .checkbox-custom');
      var allLength = $checklistItems.length;
      var checkedLength = 0;

      for (var i = 0; i < allLength; i++) {
        if ((0, _jquery.default)($checklistItems[i]).find('input').prop('checked')) {
          checkedLength++;
        }
      }

      var percent = 100 * (checkedLength / allLength);
      (0, _jquery.default)('.project-checklist [data-plugin="progress"]').asProgress('go', percent);
    }); //bind add checklist btn

    (0, _jquery.default)('.project-checklist .btn-add').on('click', function () {
      var $projectChecklist = (0, _jquery.default)('.project-checklist');
      $projectChecklist.toggleClass('checklist-editable');
    });
  })();
});