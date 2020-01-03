(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/bootstrap", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesBootstrap = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);

  function buildTable($el, cells, rows) {
    var i,
        j,
        row,
        columns = [],
        data = [];

    for (i = 0; i < cells; i++) {
      columns.push({
        field: 'field' + i,
        title: 'Cell' + i
      });
    }

    for (i = 0; i < rows; i++) {
      row = {};

      for (j = 0; j < cells; j++) {
        row['field' + j] = 'Row-' + i + '-' + j;
      }

      data.push(row);
    }

    $el.bootstrapTable('destroy').bootstrapTable({
      columns: columns,
      data: data,
      iconSize: 'outline',
      icons: {
        columns: 'wb-list-bulleted'
      }
    });
  }

  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Bootstrap Table From Data
  // ---------------------------------

  (function () {
    var bt_data = [{
      "Tid": "1",
      "First": "Jill",
      "Last": "Smith",
      "Score": "50"
    }, {
      "Tid": "2",
      "First": "Eve",
      "Last": "Jackson",
      "Score": "94"
    }, {
      "Tid": "3",
      "First": "John",
      "Last": "Doe",
      "Score": "80"
    }, {
      "Tid": "4",
      "First": "Adam",
      "Last": "Johnson",
      "Score": "67"
    }, {
      "Tid": "5",
      "First": "Fish",
      "Last": "Johnson",
      "Score": "100"
    }, {
      "Tid": "6",
      "First": "CC",
      "Last": "Joson",
      "Score": "77"
    }, {
      "Tid": "7",
      "First": "Piger",
      "Last": "Yoson",
      "Score": "87"
    }];
    (0, _jquery.default)('#exampleTableFromData').bootstrapTable({
      data: bt_data,
      // mobileResponsive: true,
      height: "250"
    });
  })(); // Example Bootstrap Table Columns
  // -------------------------------


  (function () {
    (0, _jquery.default)('#exampleTableColumns').bootstrapTable({
      url: "../../assets/data/bootstrap_table_test.json",
      height: "400",
      iconSize: 'outline',
      showColumns: true,
      icons: {
        refresh: 'wb-refresh',
        toggle: 'wb-order',
        columns: 'wb-list-bulleted'
      }
    });
  })(); // Example Bootstrap Table Large Columns
  // -------------------------------------


  buildTable((0, _jquery.default)('#exampleTableLargeColumns'), 50, 50); // Example Bootstrap Table Toolbar
  // -------------------------------

  (function () {
    (0, _jquery.default)('#exampleTableToolbar').bootstrapTable({
      url: "../../assets/data/bootstrap_table_test2.json",
      search: true,
      showRefresh: true,
      showToggle: true,
      showColumns: true,
      toolbar: '#exampleToolbar',
      iconSize: 'outline',
      icons: {
        refresh: 'wb-refresh',
        toggle: 'wb-order',
        columns: 'wb-list-bulleted'
      }
    });
  })(); // Example Bootstrap Table Events
  // ------------------------------


  (function () {
    (0, _jquery.default)('#exampleTableEvents').bootstrapTable({
      url: "../../assets/data/bootstrap_table_test.json",
      search: true,
      pagination: true,
      showRefresh: true,
      showToggle: true,
      showColumns: true,
      iconSize: 'outline',
      toolbar: '#exampleTableEventsToolbar',
      icons: {
        refresh: 'wb-refresh',
        toggle: 'wb-order',
        columns: 'wb-list-bulleted'
      }
    });
    var $result = (0, _jquery.default)('#examplebtTableEventsResult');
    (0, _jquery.default)('#exampleTableEvents').on('all.bs.table', function (e, name, args) {
      console.log('Event:', name, ', data:', args);
    }).on('click-row.bs.table', function (e, row, $element) {
      $result.text('Event: click-row.bs.table');
    }).on('dbl-click-row.bs.table', function (e, row, $element) {
      $result.text('Event: dbl-click-row.bs.table');
    }).on('sort.bs.table', function (e, name, order) {
      $result.text('Event: sort.bs.table');
    }).on('check.bs.table', function (e, row) {
      $result.text('Event: check.bs.table');
    }).on('uncheck.bs.table', function (e, row) {
      $result.text('Event: uncheck.bs.table');
    }).on('check-all.bs.table', function (e) {
      $result.text('Event: check-all.bs.table');
    }).on('uncheck-all.bs.table', function (e) {
      $result.text('Event: uncheck-all.bs.table');
    }).on('load-success.bs.table', function (e, data) {
      $result.text('Event: load-success.bs.table');
    }).on('load-error.bs.table', function (e, status) {
      $result.text('Event: load-error.bs.table');
    }).on('column-switch.bs.table', function (e, field, checked) {
      $result.text('Event: column-switch.bs.table');
    }).on('page-change.bs.table', function (e, size, number) {
      $result.text('Event: page-change.bs.table');
    }).on('search.bs.table', function (e, text) {
      $result.text('Event: search.bs.table');
    });
  })();
});