(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/table-dragger", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesTableDragger = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)(); // Example Default
    // ---------------

    tableDragger(document.querySelector("#default-table")); // Example Sort Rows
    // -----------------

    tableDragger(document.querySelector("#row-table"), {
      mode: "row"
    }); // Example Only Body
    // -----------------

    tableDragger(document.querySelector("#only-body-table"), {
      mode: "row",
      onlyBody: true
    }); // Example Handler
    // ---------------

    tableDragger(document.querySelector("#handle-table"), {
      dragHandler: ".table-dragger-handle"
    }); // Example Free
    // ------------

    tableDragger(document.querySelector("#free-table"), {
      mode: "row",
      onlyBody: true,
      dragHandler: ".table-dragger-handle"
    }); // Example Event
    // -------------

    tableDragger(document.querySelector('#event-table'), {
      mode: 'free',
      dragHandler: '.table-dragger-handle',
      onlyBody: true
    }).on('drag', function () {
      console.log('drag');
    }).on('drop', function (from, to, el, mode) {
      console.log('drop ' + el.nodeName + ' from ' + from + ' ' + mode + ' to ' + to + ' ' + mode);
    }).on('shadowMove', function (from, to, el, mode) {
      console.log('move ' + el.nodeName + ' from ' + from + ' ' + mode + ' to ' + to + ' ' + mode);
    }).on('out', function (el, mode) {
      console.log('move out or drop ' + el.nodeName + ' in mode ' + mode);
    });
  });
});