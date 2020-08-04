(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/dashboard/ecommerce", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.dashboardEcommerce = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // ScoreChart
  // ------------------------------

  (function () {
    var scoreChart = function scoreChart(id, labelList, series1List, series2List) {
      var scoreChart = new Chartist.Line('#' + id, {
        labels: labelList,
        series: [series1List, series2List]
      }, {
        lineSmooth: Chartist.Interpolation.simple({
          divisor: 2
        }),
        fullWidth: true,
        chartPadding: {
          right: 25
        },
        series: {
          "series-1": {
            showArea: true
          },
          "series-2": {
            showArea: true
          }
        },
        axisX: {
          showGrid: false
        },
        axisY: {
          labelInterpolationFnc: function labelInterpolationFnc(value) {
            return value / 1000 + 'K';
          },
          scaleMinSpace: 40
        },
        plugins: [Chartist.plugins.tooltip()],
        low: 0,
        height: 300
      });
      scoreChart.on('created', function (data) {
        var defs = data.svg.querySelector('defs') || data.svg.elem('defs');
        var width = data.svg.width();
        var height = data.svg.height();
        var filter = defs.elem('filter', {
          x: 0,
          y: "-10%",
          id: 'shadow' + id
        }, '', true);
        filter.elem('feGaussianBlur', {
          in: "SourceAlpha",
          stdDeviation: "8",
          result: 'offsetBlur'
        });
        filter.elem('feOffset', {
          dx: "0",
          dy: "10"
        });
        filter.elem('feBlend', {
          in: "SourceGraphic",
          mode: "multiply"
        });
        return defs;
      }).on('draw', function (data) {
        if (data.type === 'line') {
          data.element.attr({
            filter: 'url(#shadow' + id + ')'
          });
        } else if (data.type === 'point') {
          var parent = new Chartist.Svg(data.element._node.parentNode);
          parent.elem('line', {
            x1: data.x,
            y1: data.y,
            x2: data.x + 0.01,
            y2: data.y,
            "class": 'ct-point-content'
          });
        }

        if (data.type === 'line' || data.type === 'area') {
          data.element.animate({
            d: {
              begin: 1000 * data.index,
              dur: 1000,
              from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
              to: data.path.clone().stringify(),
              easing: Chartist.Svg.Easing.easeOutQuint
            }
          });
        }
      });
    };

    var DayLabelList = ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th"];
    var DaySeries1List = {
      name: "series-1",
      data: [2400, 4500, 5000, 6100, 7700, 5000, 1700]
    };
    var DaySeries2List = {
      name: "series-2",
      data: [1300, 4100, 8000, 4000, 6600, 5600, 3800]
    };
    var WeekLabelList = ["W1", "W2", "W4", "W5", "W6", "W7", "W8"];
    var WeekSeries1List = {
      name: "series-1",
      data: [18000, 37000, 41000, 29000, 58000, 75000, 43000]
    };
    var WeekSeries2List = {
      name: "series-2",
      data: [30000, 20000, 43000, 68000, 72000, 53000, 66000]
    };
    var MonthLabelList = ["AUG", "SEP", "OTC", "NOV", "DEC", "JAN", "FEB"];
    var MonthSeries1List = {
      name: "series-1",
      data: [100000, 500000, 300000, 700000, 100000, 200000, 700000]
    };
    var MonthSeries2List = {
      name: "series-2",
      data: [300000, 400000, 200000, 600000, 800000, 600000, 300000]
    };

    var createChart = function createChart(button) {
      var btn = button || (0, _jquery.default)("#ecommerceChartView .chart-action").find(".active");
      var chartId = btn.attr("href");

      switch (chartId) {
        case "#scoreLineToDay":
          scoreChart("scoreLineToDay", DayLabelList, DaySeries1List, DaySeries2List);
          break;

        case "#scoreLineToWeek":
          scoreChart("scoreLineToWeek", WeekLabelList, WeekSeries1List, WeekSeries2List);
          break;

        case "#scoreLineToMonth":
          scoreChart("scoreLineToMonth", MonthLabelList, MonthSeries1List, MonthSeries2List);
          break;
      }
    };

    createChart();
    (0, _jquery.default)(".chart-action li a").on("click", function () {
      createChart((0, _jquery.default)(this));
    });
  })(); // barChart
  // ------------------------------


  (function () {
    var barChart = new Chartist.Bar('.barChart', {
      labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY'],
      series: [[630, 700, 500, 400, 780], [400, 800, 700, 500, 700]]
    }, {
      axisX: {
        showGrid: false
      },
      axisY: {
        showGrid: false,
        scaleMinSpace: 30
      },
      height: 220,
      seriesBarDistance: 24
    });
    barChart.on('draw', function (data) {
      if (data.type === 'bar') {
        // $("#ecommerceRevenue .ct-labels").attr('transform', 'translate(0 15)');
        var parent = new Chartist.Svg(data.element._node.parentNode);
        parent.elem('line', {
          x1: data.x1,
          x2: data.x2,
          y1: data.y2,
          y2: 0,
          "class": 'ct-bar-fill'
        });
      }
    });
  })();
});