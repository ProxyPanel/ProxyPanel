(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/widgets/chart", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.widgetsChart = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  // Widget Chart
  (0, _jquery.default)(document).ready(function (jQuery) {
    (0, _Site.run)();
  }); // Chart Three Linearea
  // --------------------------

  (function () {
    new Chartist.Line('#chartThreeLinearea .ct-chart', {
      labels: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
      series: [[4, 4.5, 4.3, 4, 5, 6, 5.5], [3, 2.5, 3, 3.5, 4.2, 4, 5], [1, 2, 2.5, 2, 3, 2.8, 4]]
    }, {
      low: 0,
      showArea: true,
      showPoint: false,
      showLine: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart line Pie
  // --------------------------


  (function () {
    new Chartist.Line("#chartLinePie .chart-line", {
      labels: ['1', '2', '3', '4', '5', '6', '7', '8'],
      series: [[4, 5, 3, 6, 7, 5.5, 5.8, 4.6]]
    }, {
      low: 0,
      showArea: false,
      showPoint: true,
      showLine: true,
      fullWidth: true,
      lineSmooth: false,
      chartPadding: {
        top: 4,
        right: 4,
        bottom: -20,
        left: 4
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
    new Chartist.Pie('#chartLinePie .chart-pie', {
      series: [35, 65]
    }, {
      donut: true,
      donutWidth: 10,
      startAngle: 0,
      showLabel: false
    });
  })(); // Chart Bar Pie
  // ----------------------


  (function () {
    new Chartist.Bar("#chartBarPie .chart-bar", {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'],
      series: [[50, 90, 100, 90, 110, 100, 120, 130, 115, 95, 80, 85]]
    }, {
      low: 0,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
    new Chartist.Pie('#chartBarPie .chart-pie', {
      series: [70, 30]
    }, {
      donut: true,
      donutWidth: 10,
      startAngle: 0,
      showLabel: false
    });
  })(); // Chart Bar Stacked
  // -----------------------


  (function () {
    var stacked_bar = new Chartist.Bar('#chartBarStacked .ct-chart', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M'],
      series: [[11, 19, 17, 13, 2, 11, 26, 20, 27, 5, 22, 4], [6, 18, 7, 9, 26, 24, 3, 18, 28, 21, 19, 12], [9, 10, 22, 14, 23, 19, 15, 25, 28, 21, 17, 17]]
    }, {
      stackBars: true,
      fullWidth: true,
      seriesBarDistance: 0,
      chartPadding: {
        top: -10,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: true,
        showGrid: false,
        offset: 30
      },
      axisY: {
        showLabel: true,
        showGrid: true,
        offset: 30
      }
    });
  })(); // Chart Pie
  // -------------------


  (function () {
    new Chartist.Pie('#chartPie .ct-chart', {
      series: [35, 20, 45]
    }, {
      donut: true,
      donutWidth: 10,
      startAngle: 0,
      showLabel: false
    });
  })(); // Chart Bar Simple
  // -----------------------


  (function () {
    new Chartist.Bar("#chartBarSimple .ct-chart", {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'],
      series: [[6, 3, 2, 5, 4, 7, 5, 9, 4, 5, 4, 9, 8, 3, 6, 4, 8, 6, 8, 6, 4]]
    }, {
      low: 0,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Linearea Simple
  // --------------------------


  (function () {
    new Chartist.Line('#chartLineareaSimple .ct-chart', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'],
      series: [[1, 6, 4, 9, 1, 6, 4, 9, 8, 6, 5, 1, 4, 6, 4, 9, 1, 3, 1, 9]]
    }, {
      low: 0,
      showArea: true,
      showPoint: false,
      showLine: true,
      fullWidth: true,
      lineSmooth: false,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Linearea Withfooter
  // ----------------------------


  (function () {
    new Chartist.Line('#chartLineareaWithfooter .ct-chart', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G'],
      series: [[1, 6, 4, 9, 1, 6, 4, 9]]
    }, {
      low: 0,
      showArea: true,
      showPoint: false,
      showLine: true,
      fullWidth: true,
      lineSmooth: false,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Bar Withfooter
  // ------------------------


  (function () {
    new Chartist.Bar('#chartBarWithfooter .ct-chart', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'],
      series: [[160, 200, 150, 400, 460, 440, 240, 250, 50, 200, 360, 150, 380, 240, 460], [600 - 160, 600 - 200, 600 - 150, 600 - 400, 600 - 460, 600 - 440, 600 - 240, 600 - 250, 600 - 50, 600 - 200, 600 - 360, 600 - 150, 600 - 380, 600 - 240, 600 - 460]]
    }, {
      stackBars: true,
      fullWidth: true,
      seriesBarDistance: 0,
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Linebar Large
  // ----------------------


  (function () {
    new Chartist.Line("#chartLinebarLarge .chart-line", {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      series: [[20, 50, 70, 110, 100, 200, 230, 50, 80, 140, 130, 150]]
    }, {
      low: 0,
      showArea: false,
      showPoint: false,
      showLine: true,
      lineSmooth: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 10,
        bottom: 0,
        left: 10
      },
      axisX: {
        showLabel: true,
        showGrid: false,
        offset: 30
      },
      axisY: {
        showLabel: true,
        showGrid: true,
        offset: 30
      }
    });
    new Chartist.Bar('#chartLinebarLarge .chart-bar', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'],
      series: [[6, 3, 2, 5, 4, 7, 5, 9, 4, 5, 4, 9, 8, 3, 6, 4, 8, 6, 8, 6, 4, 3, 6, 4]]
    }, {
      stackBars: true,
      fullWidth: true,
      seriesBarDistance: 0,
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Line Time
  // -----------------------


  (function () {
    var line_time_labels = [];
    var line_time_data = [];
    var line_time_totalPoints = 100;
    var line_time_updateInterval = 1000;
    var line_time_now = new Date().getTime();

    function line_time_getData() {
      line_time_labels.shift();
      line_time_data.shift();

      while (line_time_data.length < line_time_totalPoints) {
        var x = Math.random() * 100;
        line_time_labels.push(line_time_now += line_time_updateInterval);
        line_time_data.push(x);
      }
    }

    var lineTime = {
      labels: line_time_labels,
      series: [line_time_data]
    };
    var lineTimeOptions = {
      low: 0,
      showArea: false,
      showPoint: false,
      showLine: true,
      lineSmooth: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    };
    new Chartist.Line("#chartLineTime .chart-line", lineTime, lineTimeOptions);

    function line_time_update() {
      line_time_getData();
      new Chartist.Line("#chartLineTime .chart-line", lineTime, lineTimeOptions);
      setTimeout(line_time_update, line_time_updateInterval);
    }

    line_time_update();
    new Chartist.Pie('#chartLineTime .chart-pie-left', {
      series: [50, 50]
    }, {
      donut: true,
      donutWidth: 10,
      startAngle: 0,
      showLabel: false
    });
    new Chartist.Pie('#chartLineTime .chart-pie-right', {
      series: [80, 20]
    }, {
      donut: true,
      donutWidth: 10,
      startAngle: 0,
      showLabel: false
    });
  })(); // Chart Barline Mix
  // -----------------------


  (function () {
    var mix_data = {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'],
      series: [[50, 90, 100, 90, 110, 100, 120, 130, 115, 95, 80, 85, 60, 100, 90]]
    };
    new Chartist.Bar("#chartBarlineMix .chart-bar", mix_data, {
      low: 0,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: -7,
        bottom: 0,
        left: -7
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
    new Chartist.Line("#chartBarlineMix .chart-line", mix_data, {
      low: 0,
      showArea: false,
      showPoint: false,
      showLine: true,
      lineSmooth: false,
      fullWidth: true,
      chartPadding: {
        top: 50,
        right: 4,
        bottom: 0,
        left: 4
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Barline Mix Two
  // ---------------------------


  (function () {
    new Chartist.Bar("#chartBarlineMixTwo .small-bar-one", {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [[50, 90, 100, 90, 110, 100, 120, 130]]
    }, {
      low: 0,
      fullWidth: true,
      chartPadding: {
        top: -10,
        right: 0,
        bottom: 0,
        left: 20
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
    new Chartist.Bar("#chartBarlineMixTwo .small-bar-two", {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [[50, 90, 100, 90, 110, 100, 120, 120]]
    }, {
      low: 0,
      fullWidth: true,
      chartPadding: {
        top: -10,
        right: 0,
        bottom: 0,
        left: 20
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
    new Chartist.Line("#chartBarlineMixTwo .line-chart", {
      labels: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
      series: [[20, 50, 70, 110, 100, 200, 230], [50, 80, 140, 130, 150, 110, 160]]
    }, {
      low: 0,
      showArea: false,
      showPoint: false,
      showLine: true,
      lineSmooth: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 10,
        bottom: 0,
        left: 10
      },
      axisX: {
        showLabel: true,
        showGrid: false,
        offset: 30
      },
      axisY: {
        showLabel: true,
        showGrid: true,
        offset: 30
      }
    });
  })(); // Chart Linearea Two
  // --------------------------


  (function () {
    new Chartist.Line('#charLineareaTwo .ct-chart', {
      labels: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
      series: [[0, 2.5, 2, 2.8, 2.6, 3.8, 0], [0, 1.4, 0.5, 2, 1.2, 0.9, 0]]
    }, {
      low: 0,
      showArea: true,
      showPoint: false,
      showLine: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 10,
        bottom: 0,
        left: 0
      },
      axisX: {
        showGrid: false,
        labelOffset: {
          x: -14,
          y: 0
        }
      },
      axisY: {
        labelOffset: {
          x: -10,
          y: 0
        },
        labelInterpolationFnc: function labelInterpolationFnc(num) {
          return num % 1 === 0 ? num : false;
        }
      }
    });
  })(); // Chart Linepoint
  // ---------------------


  (function () {
    new Chartist.Line("#chartLinepoint .ct-chart", {
      labels: ['1', '2', '3', '4', '5', '6'],
      series: [[1, 1.5, 0.5, 2, 2.5, 1.5]]
    }, {
      low: 0,
      showArea: false,
      showPoint: true,
      showLine: true,
      fullWidth: true,
      lineSmooth: false,
      chartPadding: {
        top: 10,
        right: -4,
        bottom: 10,
        left: -4
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })(); // Chart Timeline Two
  // -----------------------------


  (function () {
    var timeline_labels = [];
    var timeline_data1 = [];
    var timeline_data2 = [];
    var totalPoints = 20;
    var updateInterval = 1000;
    var now = new Date().getTime();

    function getData() {
      timeline_labels.shift();
      timeline_data1.shift();
      timeline_data2.shift();

      while (timeline_data1.length < totalPoints) {
        var x = Math.random() * 100 + 800;
        var y = Math.random() * 100 + 400;
        timeline_labels.push(now += updateInterval);
        timeline_data1.push(x);
        timeline_data2.push(y);
      }
    }

    var timlelineData = {
      labels: timeline_labels,
      series: [timeline_data1, timeline_data2]
    };
    var timelineOptions = {
      low: 0,
      showArea: true,
      showPoint: false,
      showLine: false,
      fullWidth: true,
      chartPadding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    };
    new Chartist.Line("#chartTimelineTwo .ct-chart", timlelineData, timelineOptions);

    function update() {
      getData();
      new Chartist.Line("#chartTimelineTwo .ct-chart", timlelineData, timelineOptions);
      setTimeout(update, updateInterval);
    }

    update();
  })(); // Chart Stacked Bar
  // ----------------------


  (function () {
    new Chartist.Bar('#chartStackedBar .ct-chart', {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
      series: [[50, 90, 100, 90, 110, 100, 120, 130, 115, 95, 80, 85, 100, 140, 130, 120, 135, 110, 120, 105, 100, 105, 90, 110, 100, 60], [150, 190, 200, 190, 210, 200, 220, 230, 215, 195, 180, 185, 200, 240, 230, 220, 235, 210, 220, 205, 200, 205, 190, 210, 200, 160]]
    }, {
      stackBars: true,
      fullWidth: true,
      seriesBarDistance: 0,
      chartPadding: {
        top: 0,
        right: 30,
        bottom: 30,
        left: 20
      },
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      }
    });
  })();
});