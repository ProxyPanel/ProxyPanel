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

    (function () {
        (0, _jquery.default)('#exampleTableToolbar').bootstrapTable({
            showToggle: true,
            showColumns: true,
            toolbar: '#exampleToolbar',
            iconSize: 'outline',
            icons: {
                toggle: 'wb-order',
                columns: 'wb-list-bulleted'
            }
        });
    })(); // Example Bootstrap Table Events
    // ------------------------------
});