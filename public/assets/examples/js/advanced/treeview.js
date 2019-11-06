(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/advanced/treeview", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.advancedTreeview = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  window.getExampleTreeview = function () {
    return [{
      text: 'Parent 1',
      href: '#parent1',
      tags: ['4'],
      nodes: [{
        text: 'Child 1',
        href: '#child1',
        tags: ['2'],
        nodes: [{
          text: 'Grandchild 1',
          href: '#grandchild1',
          tags: ['0']
        }, {
          text: 'Grandchild 2',
          href: '#grandchild2',
          tags: ['0']
        }]
      }, {
        text: 'Child 2',
        href: '#child2',
        tags: ['0']
      }]
    }, {
      text: 'Parent 2',
      href: '#parent2',
      tags: ['0']
    }, {
      text: 'Parent 3',
      href: '#parent3',
      tags: ['0']
    }, {
      text: 'Parent 4',
      href: '#parent4',
      tags: ['0']
    }, {
      text: 'Parent 5',
      href: '#parent5',
      tags: ['0']
    }];
  };

  var defaults = Plugin.getDefaults("treeview"); // Example TreeView Json Data
  // --------------------------

  (function () {
    var json = '[' + '{' + '"text": "Parent 1",' + '"nodes": [' + '{' + '"text": "Child 1",' + '"nodes": [' + '{' + '"text": "Grandchild 1"' + '},' + '{' + '"text": "Grandchild 2"' + '}' + ']' + '},' + '{' + '"text": "Child 2"' + '}' + ']' + '},' + '{' + '"text": "Parent 2"' + '},' + '{' + '"text": "Parent 3"' + '},' + '{' + '"text": "Parent 4"' + '},' + '{' + '"text": "Parent 5"' + '}' + ']';

    var json_options = _jquery.default.extend({}, defaults, {
      data: json
    });

    (0, _jquery.default)('#exampleJsonData').treeview(json_options);
  })(); // Example TreeView Searchable
  // ---------------------------


  (function () {
    var options = _jquery.default.extend({}, defaults, {
      data: getExampleTreeview()
    });

    var $searchableTree = (0, _jquery.default)('#exampleSearchableTree').treeview(options);
    (0, _jquery.default)('#inputSearchable').on('keyup', function (e) {
      var pattern = (0, _jquery.default)(e.target).val();
      var results = $searchableTree.treeview('search', [pattern, {
        'ignoreCase': true,
        'exactMatch': false
      }]);
    });
  })(); // Example TreeView Expandible
  // ---------------------------


  (function () {
    var options = _jquery.default.extend({}, defaults, {
      data: getExampleTreeview()
    }); // Expandible


    var $expandibleTree = (0, _jquery.default)('#exampleExpandibleTree').treeview(options); // Expand/collapse all

    (0, _jquery.default)('#exampleExpandAll').on('click', function (e) {
      $expandibleTree.treeview('expandAll', {
        levels: '99'
      });
    });
    (0, _jquery.default)('#exampleCollapseAll').on('click', function (e) {
      $expandibleTree.treeview('collapseAll');
    });
  })(); // Example TreeView Events
  // -----------------------


  (function () {
    // Events
    var events_toastr = function events_toastr(msg) {
      toastr.info(msg, '', {
        iconClass: 'toast-just-text toast-info',
        positionClass: 'toast-bottom-right',
        containertId: 'toast-bottom-right'
      });
    };

    var options = _jquery.default.extend({}, defaults, {
      data: getExampleTreeview(),
      onNodeCollapsed: function onNodeCollapsed(event, node) {
        events_toastr(node.text + ' was collapsed');
      },
      onNodeExpanded: function onNodeExpanded(event, node) {
        events_toastr(node.text + ' was expanded');
      },
      onNodeSelected: function onNodeSelected(event, node) {
        events_toastr(node.text + ' was selected');
      },
      onNodeUnselected: function onNodeUnselected(event, node) {
        events_toastr(node.text + ' was unselected');
      }
    });

    (0, _jquery.default)('#exampleEvents').treeview(options);
  })(); // Example jstree use JSON format
  // ------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_3').jstree({
      'core': {
        'data': [{
          'text': 'Simple root node',
          "icon": "wb-folder"
        }, {
          'text': 'Root node 2',
          "icon": "wb-folder",
          'state': {
            'opened': false,
            'selected': true
          },
          'children': [{
            'text': 'Child 1',
            "icon": "wb-folder"
          }, {
            'text': 'Child 2',
            "icon": "wb-folder"
          }]
        }]
      }
    });
  })(); // Example jstree use AJAX
  // ------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_4').jstree({
      'core': {
        'data': {
          "url": "../../assets/data/treeview_jstree.json",
          "dataType": "json"
        }
      }
    });
  })(); // Example jstree use checkbox Plugin
  // ------------------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_5').jstree({
      'core': {
        'data': [{
          'text': 'Simple root node',
          "icon": "wb-folder"
        }, {
          'text': 'Root node 2',
          "icon": "wb-folder",
          'state': {
            'opened': true,
            'selected': true
          },
          'children': [{
            'text': 'Child 1',
            "icon": "wb-folder"
          }, {
            'text': 'Child 2',
            "icon": "wb-folder"
          }]
        }]
      },
      'plugins': ['checkbox']
    });
  })(); // Example jstree use Contextmenu Plugin
  // ------------------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_6').jstree({
      'core': {
        "check_callback": true,
        'data': [{
          'text': 'Simple root node',
          "icon": "wb-folder"
        }, {
          'text': 'Root node 2',
          "icon": "wb-folder",
          'state': {
            'opened': true,
            'selected': true
          },
          'children': [{
            'text': 'Child 1',
            "icon": "wb-folder"
          }, {
            'text': 'Child 2',
            "icon": "wb-folder"
          }]
        }]
      },
      'plugins': ['contextmenu']
    });
  })(); // Example jstree use Search Plugin
  // --------------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_7').jstree({
      'core': {
        'data': [{
          'text': 'Simple root node',
          "icon": "wb-folder"
        }, {
          'text': 'Root node 2',
          "icon": "wb-folder",
          'state': {
            'opened': true,
            'selected': true
          },
          'children': [{
            'text': 'Child 1',
            "icon": "wb-folder"
          }, {
            'text': 'Child 2',
            "icon": "wb-folder"
          }]
        }]
      },
      'plugins': ['search']
    });
    var to = false;
    (0, _jquery.default)('#jstreeSearch').keyup(function () {
      if (to) {
        clearTimeout(to);
      }

      to = setTimeout(function () {
        var v = (0, _jquery.default)('#jstreeSearch').val();
        (0, _jquery.default)('#jstreeExample_7').jstree(true).search(v);
      }, 250);
    });
  })(); // Example jstree use Drag & drop Plugin
  // -------------------------------------


  (function () {
    (0, _jquery.default)('#jstreeExample_8').jstree({
      'core': {
        "check_callback": true,
        'data': [{
          'text': 'Simple root node',
          "icon": "wb-folder"
        }, {
          'text': 'Root node 2',
          "icon": "wb-folder",
          'state': {
            'opened': true,
            'selected': true
          },
          'children': [{
            'text': 'Child 1',
            "icon": "wb-folder"
          }, {
            'text': 'Child 2',
            "icon": "wb-folder"
          }]
        }]
      },
      'plugins': ['dnd']
    });
  })();
});