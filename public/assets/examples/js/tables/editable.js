(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/editable", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesEditable = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example editable Table
  // ----------------------

  /* this is an example for validation and change events */

  _jquery.default.fn.numericInputExample = function () {
    var element = (0, _jquery.default)(this),
        footer = element.find('tfoot tr'),
        dataRows = element.find('tbody tr'),
        initialTotal = function initialTotal() {
      var column, total;

      for (column = 1; column < footer.children().length; column++) {
        total = 0;
        dataRows.each(function () {
          var row = (0, _jquery.default)(this);
          total += parseFloat(row.children().eq(column).text());
        });
        footer.children().eq(column).text(total);
      }
    };

    element.find('td').on('change', function (evt) {
      var cell = (0, _jquery.default)(this),
          column = cell.index(),
          total = 0;

      if (column === 0) {
        return;
      }

      element.find('tbody tr').each(function () {
        var row = (0, _jquery.default)(this);
        total += parseFloat(row.children().eq(column).text());
      });

      if (column === 1 && total > 5000) {
        (0, _jquery.default)('.alert').show();
        return false; // changes can be rejected
      } else {
        (0, _jquery.default)('.alert').hide();
        footer.children().eq(column).text(total);
      }
    }).on('validate', function (evt, value) {
      var cell = (0, _jquery.default)(this),
          column = cell.index();

      if (column === 0) {
        return !!value && value.trim().length > 0;
      } else {
        return !isNaN(parseFloat(value)) && isFinite(value);
      }
    });
    initialTotal();
    return this;
  };

  (0, _jquery.default)('#editableTable').editableTableWidget().numericInputExample().find('td:first').focus();
});