(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/forms/editor-summernote", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsEditorSummernote = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Click to edit
  // ---------------------

  window.edit = function () {
    (0, _jquery.default)('.click2edit').summernote({
      focus: true
    });
  };

  window.save = function () {
    (0, _jquery.default)('.click2edit').summernote('destroy');
  }; // Example Hint for words
  // ----------------------


  (function () {
    (0, _jquery.default)("#exampleHint2Basic").summernote({
      height: 100,
      toolbar: false,
      placeholder: 'type with apple, orange, watermelon and lemon',
      hint: {
        words: ['apple', 'arange', 'watermelon', 'lemon'],
        match: /\b(\w{1,})$/,
        search: function search(keyword, callback) {
          callback(_jquery.default.grep(this.words, function (item) {
            return item.indexOf(keyword) === 0;
          }));
        }
      }
    });
  })(); // Example Hint for words
  // ----------------------


  (function () {
    (0, _jquery.default)("#exampleHint2Mention").summernote({
      height: 100,
      toolbar: false,
      hint: {
        mentions: ['jayden', 'sam', 'alvin', 'david'],
        match: /\B@(\w*)$/,
        search: function search(keyword, callback) {
          callback(_jquery.default.grep(this.mentions, function (item) {
            return item.indexOf(keyword) === 0;
          }));
        },
        content: function content(item) {
          return '@' + item;
        }
      }
    });
  })();
});