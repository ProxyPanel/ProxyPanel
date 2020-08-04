(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/jqtabledit", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesJqtabledit = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Tabledit Toolbars
  // -------------------------------

  (function () {
    (0, _jquery.default)('#exampleTableditToolbars').Tabledit({
      columns: {
        identifier: [0, 'id'],
        editable: [[1, 'username'], [2, 'first'], [3, 'last']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        }
      }
    });
  })(); // Example Inline Tabledit Toolbars
  // -------------------------------


  (function () {
    (0, _jquery.default)('#exampleTableditInlineEdit').Tabledit({
      eventType: 'dblclick',
      editButton: false,
      columns: {
        identifier: [0, 'id'],
        editable: [[1, 'username'], [2, 'last', '{"1": "May", "2": "Green", "3": "Brant"}']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        }
      }
    });
  })(); // Example Inline Tabledit without identifier
  // -------------------------------


  (function () {
    (0, _jquery.default)('#InlineEditWithoutIndentify').Tabledit({
      editButton: false,
      deleteButton: false,
      hideIdentifier: true,
      columns: {
        identifier: [0, 'id'],
        editable: [[2, 'firstname'], [3, 'lastname']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        }
      }
    });
  })(); // Example Tabledit With Editbutton Only
  // -------------------------------


  (function () {
    (0, _jquery.default)('#tableditWithEditButtonOnly').Tabledit({
      deleteButton: false,
      saveButton: false,
      autoFocus: false,
      columns: {
        identifier: [0, 'id'],
        editable: [[1, 'car'], [2, 'color']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        }
      }
    });
  })(); // Example Toolbar With Deletebutton only
  // -------------------------------


  (function () {
    (0, _jquery.default)('#tableditWithDeleteButtonOnly').Tabledit({
      rowIdentifier: 'data-id',
      editButton: false,
      restoreButton: false,
      columns: {
        identifier: [0, 'id'],
        editable: [[1, 'nickname'], [2, 'firstname'], [3, 'lastname']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        },
        confirm: {
          class: 'btn btn-sm btn-default',
          html: 'Are you sure?'
        }
      }
    });
  })(); // Example Toolbar With Log All Hooks
  // -------------------------------


  (function () {
    (0, _jquery.default)('#tableditLogAllHooks').Tabledit({
      rowIdentifier: 'data-id',
      editButton: true,
      restoreButton: true,
      columns: {
        identifier: [0, 'id'],
        editable: [[1, 'username'], [2, 'email'], [3, 'avatar', '{"1": "Black Widow", "2": "Captain America", "3": "Iron Man"}']]
      },
      buttons: {
        edit: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-wrench"></span>',
          action: 'edit'
        },
        delete: {
          class: 'btn btn-sm btn-icon btn-flat btn-default',
          html: '<span class="icon wb-close"></span>',
          action: 'delete'
        }
      },
      onDraw: function onDraw() {
        console.log('onDraw()');
      },
      onSuccess: function onSuccess(data, textStatus, jqXHR) {
        console.log('onSuccess(data, textStatus, jqXHR)');
        console.log(data);
        console.log(textStatus);
        console.log(jqXHR);
      },
      onFail: function onFail(jqXHR, textStatus, errorThrown) {
        console.log('onFail(jqXHR, textStatus, errorThrown)');
        console.log(jqXHR);
        console.log(textStatus);
        console.log(errorThrown);
      },
      onAlways: function onAlways() {
        console.log('onAlways()');
      },
      onAjax: function onAjax(action, serialize) {
        console.log('onAjax(action, serialize)');
        console.log(action);
        console.log(serialize);
      }
    });
  })();
});