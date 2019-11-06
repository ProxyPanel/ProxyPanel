(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/tables/datatable", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.tablesDatatable = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Fixed Header Example
  // --------------------

  (function () {
    var offsetTop = 0;

    if ((0, _jquery.default)('.site-navbar').length > 0) {
      offsetTop = (0, _jquery.default)('.site-navbar').eq(0).innerHeight();
    } // initialize datatable


    var table = (0, _jquery.default)('#exampleFixedHeader').DataTable({
      responsive: true,
      fixedHeader: {
        header: true,
        headerOffset: offsetTop
      },
      "paging": false,
      "dom": "t" // just show table, no other controls

    }); // redraw fixedHeaders as necessary
    // $(window).resize(function() {
    //   fixedHeader._fnUpdateClones(true);
    //   fixedHeader._fnUpdatePositions();
    // });
  })(); // Individual column searching
  // ---------------------------


  (function () {
    (0, _jquery.default)(document).ready(function () {
      var defaults = Plugin.getDefaults("dataTable");

      var options = _jquery.default.extend(true, {}, defaults, {
        initComplete: function initComplete() {
          this.api().columns().every(function () {
            var column = this;
            var select = (0, _jquery.default)('<select class="form-control w-full"><option value=""></option></select>').appendTo((0, _jquery.default)(column.footer()).empty()).on('change', function () {
              var val = _jquery.default.fn.dataTable.util.escapeRegex((0, _jquery.default)(this).val());

              column.search(val ? '^' + val + '$' : '', true, false).draw();
            });
            column.data().unique().sort().each(function (d, j) {
              select.append('<option value="' + d + '">' + d + '</option>');
            });
          });
        }
      });

      (0, _jquery.default)('#exampleTableSearch').DataTable(options);
    });
  })(); // Table Tools
  // -----------


  (function () {
    (0, _jquery.default)(document).ready(function () {
      var defaults = Plugin.getDefaults("dataTable");

      var options = _jquery.default.extend(true, {}, defaults, {
        "aoColumnDefs": [{
          'bSortable': false,
          'aTargets': [-1]
        }],
        "iDisplayLength": 5,
        "aLengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "sDom": '<"dt-panelmenu clearfix"Bfr>t<"dt-panelfooter clearfix"ip>',
        "buttons": ['copy', 'excel', 'csv', 'pdf', 'print']
      });

      (0, _jquery.default)('#exampleTableTools').dataTable(options);
    });
  })(); // Table Add Row
  // -------------


  (function ($$$1) {
    var EditableTable = {
      options: {
        addButton: '#addToTable',
        table: '#exampleAddRow',
        dialog: {
          wrapper: '#dialog',
          cancelButton: '#dialogCancel',
          confirmButton: '#dialogConfirm'
        }
      },
      initialize: function initialize() {
        this.setVars().build().events();
      },
      setVars: function setVars() {
        this.$table = $$$1(this.options.table);
        this.$addButton = $$$1(this.options.addButton); // dialog

        this.dialog = {};
        this.dialog.$wrapper = $$$1(this.options.dialog.wrapper);
        this.dialog.$cancel = $$$1(this.options.dialog.cancelButton);
        this.dialog.$confirm = $$$1(this.options.dialog.confirmButton);
        return this;
      },
      build: function build() {
        this.datatable = this.$table.DataTable({
          aoColumns: [null, null, null, {
            "bSortable": false
          }],
          language: {
            "sSearchPlaceholder": "Search..",
            "lengthMenu": "_MENU_",
            "search": "_INPUT_"
          }
        });
        window.dt = this.datatable;
        return this;
      },
      events: function events() {
        var _self = this;

        this.$table.on('click', 'a.save-row', function (e) {
          e.preventDefault();

          _self.rowSave($$$1(this).closest('tr'));
        }).on('click', 'a.cancel-row', function (e) {
          e.preventDefault();

          _self.rowCancel($$$1(this).closest('tr'));
        }).on('click', 'a.edit-row', function (e) {
          e.preventDefault();

          _self.rowEdit($$$1(this).closest('tr'));
        }).on('click', 'a.remove-row', function (e) {
          e.preventDefault();
          var $row = $$$1(this).closest('tr');
          bootbox.dialog({
            message: "Are you sure that you want to delete this row?",
            title: "ARE YOU SURE?",
            buttons: {
              danger: {
                label: "Confirm",
                className: "btn-danger",
                callback: function callback() {
                  _self.rowRemove($row);
                }
              },
              main: {
                label: "Cancel",
                className: "btn-primary",
                callback: function callback() {}
              }
            }
          });
        });
        this.$addButton.on('click', function (e) {
          e.preventDefault();

          _self.rowAdd();
        });
        this.dialog.$cancel.on('click', function (e) {
          e.preventDefault();
          $$$1.magnificPopup.close();
        });
        return this;
      },
      // =============
      // ROW FUNCTIONS
      // =============
      rowAdd: function rowAdd() {
        this.$addButton.attr({
          'disabled': 'disabled'
        });
        var actions, data, $row;
        actions = ['<a href="#" class="btn btn-sm btn-icon btn-pure btn-default on-editing save-row" data-toggle="tooltip" data-original-title="Save" hidden><i class="icon wb-check" aria-hidden="true"></i></a>', '<a href="#" class="btn btn-sm btn-icon btn-pure btn-default on-editing cancel-row" data-toggle="tooltip" data-original-title="Delete" hidden><i class="icon wb-close" aria-hidden="true"></i></a>', '<a href="#" class="btn btn-sm btn-icon btn-pure btn-default on-default edit-row" data-toggle="tooltip" data-original-title="Edit"><i class="icon wb-edit" aria-hidden="true"></i></a>', '<a href="#" class="btn btn-sm btn-icon btn-pure btn-default on-default remove-row" data-toggle="tooltip" data-original-title="Remove"><i class="icon wb-trash" aria-hidden="true"></i></a>'].join(' ');
        data = this.datatable.row.add(['', '', '', actions]);
        $row = this.datatable.row(data[0]).nodes().to$();
        $row.addClass('adding').find('td:last').addClass('actions');
        this.rowEdit($row);
        this.datatable.order([0, 'asc']).draw(); // always show fields
      },
      rowCancel: function rowCancel($row) {
        var $actions, data, $cancel;

        if ($row.hasClass('adding')) {
          this.rowRemove($row);
        } else {
          $actions = $row.find('td.actions');
          $cancel = $actions.find('.cancel-row');
          $cancel.tooltip('hide');

          if ($actions.get(0)) {
            this.rowSetActionsDefault($row);
          }

          data = this.datatable.row($row.get(0)).data();
          this.datatable.row($row.get(0)).data(data);
          this.handleTooltip($row);
          this.datatable.draw();
        }
      },
      rowEdit: function rowEdit($row) {
        var _self = this,
            data;

        data = this.datatable.row($row.get(0)).data();
        $row.children('td').each(function (i) {
          var $this = $$$1(this);

          if ($this.hasClass('actions')) {
            _self.rowSetActionsEditing($row);
          } else {
            $this.html('<input type="text" class="form-control input-block" value="' + data[i] + '"/>');
          }
        });
      },
      rowSave: function rowSave($row) {
        var _self = this,
            $actions,
            values = [],
            $save;

        if ($row.hasClass('adding')) {
          this.$addButton.removeAttr('disabled');
          $row.removeClass('adding');
        }

        values = $row.find('td').map(function () {
          var $this = $$$1(this);

          if ($this.hasClass('actions')) {
            _self.rowSetActionsDefault($row);

            return _self.datatable.cell(this).data();
          } else {
            return $$$1.trim($this.find('input').val());
          }
        });
        $actions = $row.find('td.actions');
        $save = $actions.find('.save-row');
        $save.tooltip('hide');

        if ($actions.get(0)) {
          this.rowSetActionsDefault($row);
        }

        this.datatable.row($row.get(0)).data(values);
        this.handleTooltip($row);
        this.datatable.draw();
      },
      rowRemove: function rowRemove($row) {
        if ($row.hasClass('adding')) {
          this.$addButton.removeAttr('disabled');
        }

        this.datatable.row($row.get(0)).remove().draw();
      },
      rowSetActionsEditing: function rowSetActionsEditing($row) {
        $row.find('.on-editing').removeAttr('hidden');
        $row.find('.on-default').attr('hidden', true);
      },
      rowSetActionsDefault: function rowSetActionsDefault($row) {
        $row.find('.on-editing').attr('hidden', true);
        $row.find('.on-default').removeAttr('hidden');
      },
      handleTooltip: function handleTooltip($row) {
        var $tooltip = $row.find('[data-toggle="tooltip"]');
        $tooltip.tooltip();
      }
    };
    $$$1(function () {
      EditableTable.initialize();
    });
  }).apply(undefined, [jQuery]);
});