(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/advanced/context-menu", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.advancedContextMenu = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Demo 1
  // ------

  (function () {
    _jquery.default.contextMenu({
      selector: '#simpleContextMenu',
      // callback: function(key, options) {
      //   var m = "clicked: " + key;
      //   window.console && console.log(m) || alert(m);
      // },
      items: {
        "edit": {
          name: "Edit",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-edit';
          }
        },
        "cut": {
          name: "Cut",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-scissor';
          }
        },
        "copy": {
          name: "Copy",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-copy';
          }
        },
        "paste": {
          name: "Paste",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-clipboard';
          }
        },
        "delete": {
          name: "Delete",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-close';
          }
        },
        "sep1": "---------",
        "share": {
          name: "Share",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-share';
          }
        }
      }
    });
  })(); // Demo 2
  // ------


  (function () {
    _jquery.default.contextMenu({
      selector: '.contextMenu-example2 > span',
      // callback: function(key, options) {
      //   var m = "clicked: " + key;
      //   window.console && console.log(m) || alert(m);
      // },
      items: {
        "edit": {
          name: "Edit",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-edit';
          }
        },
        "cut": {
          name: "Cut",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-scissor';
          }
        },
        "copy": {
          name: "Copy",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-copy';
          }
        },
        "paste": {
          name: "Paste",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-clipboard';
          }
        },
        "delete": {
          name: "Delete",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-close';
          }
        },
        "sep1": "---------",
        "share": {
          name: "Share",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-share';
          }
        }
      }
    });
  })(); // Demo 3
  // ------


  (function () {
    _jquery.default.contextMenu({
      selector: '.contextMenu-example3',
      callback: function callback(key, options) {
        var m = "clicked: " + key;
        window.console && console.log(m) || alert(m);
      },
      items: {
        "edit": {
          name: "Edit",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-edit';
          }
        },
        "cut": {
          name: "Cut",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-scissor';
          }
        },
        "copy": {
          name: "Copy",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-copy';
          }
        },
        "paste": {
          name: "Paste",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-clipboard';
          }
        },
        "delete": {
          name: "Delete",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-close';
          }
        },
        "sep1": "---------",
        "share": {
          name: "Share",
          icon: function icon() {
            return 'context-menu-icon context-menu-extend-icon wb-share';
          }
        }
      }
    });
  })();
});