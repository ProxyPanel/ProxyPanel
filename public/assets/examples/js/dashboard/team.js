(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/dashboard/team", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.dashboardTeam = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Top Line Chart With Tooltips
  // ----------------------------

  (function () {
    var options = {
      showArea: true,
      low: 0,
      high: 1000,
      height: 453,
      fullWidth: true,
      axisX: {
        offset: 30
      },
      axisY: {
        offset: 30,
        labelInterpolationFnc: function labelInterpolationFnc(value) {
          if (value === 0) {
            return null;
          }

          return value;
        },
        scaleMinSpace: 50
      },
      chartPadding: {
        bottom: 12,
        left: 10
      },
      plugins: [Chartist.plugins.tooltip()]
    }; // team total completed data

    var labelList = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'];
    var series1List = {
      name: 'series-1',
      data: [0, 180, 600, 980, 850, 600, 300, 350, 600, 200, 630]
    };
    var series2List = {
      name: 'series-2',
      data: [0, 100, 520, 810, 620, 500, 630, 400, 380, 405, 210]
    };

    var newScoreLineChart = function newScoreLineChart(chartId, labelList, series1List, series2List, options) {
      var lineChart = new Chartist.Line(chartId, {
        labels: labelList,
        series: [series1List, series2List]
      }, options); //start create

      lineChart.on('draw', function (data) {
        var elem, parent;

        if (data.type === 'point') {
          elem = data.element;
          parent = new Chartist.Svg(elem._node.parentNode);
          parent.elem('line', {
            x1: data.x,
            y1: data.y,
            x2: data.x + 0.01,
            y2: data.y,
            "class": 'ct-point-content'
          });
        }
      });
    };

    newScoreLineChart("#teamCompletedWidget .ct-chart", labelList, series1List, series2List, options);
  })(); // item dialog
  // -----------


  (function () {
    //handleSelective
    var handleSelective = function handleSelective(handleSelectiveItem) {
      var member = [{
        id: 'uid_1',
        name: 'Herman Beck',
        avatar: '../../../global/portraits/1.jpg'
      }, {
        id: 'uid_2',
        name: 'Mary Adams',
        avatar: '../../../global/portraits/2.jpg'
      }, {
        id: 'uid_3',
        name: 'Caleb Richards',
        avatar: '../../../global/portraits/3.jpg'
      }, {
        id: 'uid_4',
        name: 'June Lane',
        avatar: '../../../global/portraits/4.jpg'
      }];
      var items = handleSelectiveItem;
      (0, _jquery.default)('.plugin-selective').selective({
        namespace: 'addMember',
        local: member,
        selected: items,
        buildFromHtml: false,
        tpl: {
          optionValue: function optionValue(data) {
            return data.id;
          },
          frame: function frame() {
            return '<div class="' + this.namespace + '">' + this.options.tpl.items.call(this) + '<div class="' + this.namespace + '-trigger">' + this.options.tpl.triggerButton.call(this) + '<div class="' + this.namespace + '-trigger-dropdown">' + this.options.tpl.list.call(this) + '</div>' + '</div>' + '</div>';
          },
          triggerButton: function triggerButton() {
            return '<div class="' + this.namespace + '-trigger-button"><i class="wb-plus"></i></div>';
          },
          listItem: function listItem(data) {
            return '<li class="' + this.namespace + '-list-item"><img class="avatar" src="' + data.avatar + '">' + data.name + '</li>';
          },
          item: function item(data) {
            return '<li class="' + this.namespace + '-item"><img class="avatar" src="' + data.avatar + '" title="' + data.name + '">' + this.options.tpl.itemRemove.call(this) + '</li>';
          },
          itemRemove: function itemRemove() {
            return '<span class="' + this.namespace + '-remove"><i class="wb-minus-circle"></i></span>';
          },
          option: function option(data) {
            return '<option value="' + this.options.tpl.optionValue.call(this, data) + '">' + data.name + '</option>';
          }
        }
      });
    }; // add Item Dialog


    (0, _jquery.default)('#addNewItemBtn').on('click', function () {
      //default handleSelectiveItem for add dialog
      var handleSelectiveItem = [{
        id: 'uid_1',
        name: 'Herman Beck',
        avatar: '../../../global/portraits/1.jpg'
      }, {
        id: 'uid_2',
        name: 'Caleb Richards',
        avatar: '../../../global/portraits/2.jpg'
      }];
      handleSelective(handleSelectiveItem);
      (0, _jquery.default)('#addtodoItemForm').modal('show');
    }); // edit Item Dialog

    (0, _jquery.default)("#toDoListWidget .list-group-item input").on('click', function (e) {
      e.stopPropagation();
    });
    (0, _jquery.default)('#toDoListWidget .list-group-item').on('click', function () {
      var oldTitle = (0, _jquery.default)(this).find(".item-title").text();
      var dueDate = (0, _jquery.default)(this).find(".item-due-date > span").text();

      if (dueDate === "No due date") {
        dueDate = null;
      } else {
        dueDate = "8/25/2015";
      }

      (0, _jquery.default)("#editTitle").val(oldTitle);
      (0, _jquery.default)("#editDueDate").val(dueDate);
      var handleSelectiveItem = [];
      handleSelective(handleSelectiveItem);
      (0, _jquery.default)('#edittodoItemForm').modal('show');
    });
  })();
});