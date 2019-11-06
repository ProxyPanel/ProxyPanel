(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/dashboard/v1", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.dashboardV1 = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)(); // Widget Linearea Color
    // ---------------------

    (function () {
      var timeline_labels = [];
      var timeline_data1 = [];
      var timeline_data2 = [];
      var timeline_data3 = [];
      var totalPoints = 20;
      var updateInterval = 2000;
      var now = new Date().getTime();

      function GetData() {
        timeline_labels.shift();
        timeline_data1.shift();
        timeline_data2.shift();
        timeline_data3.shift();

        while (timeline_data1.length < totalPoints) {
          var x = Math.random() * 100 + 800;
          var y = Math.random() * 100 + 400;
          var z = Math.random() * 100 + 200;
          timeline_labels.push(now += updateInterval);
          timeline_data1.push(x);
          timeline_data2.push(y);
          timeline_data3.push(z);
        }
      }

      var timlelineData = {
        labels: timeline_labels,
        series: [timeline_data1, timeline_data2, timeline_data3]
      };
      var timlelineData = {
        labels: timeline_labels,
        series: [timeline_data1, timeline_data2, timeline_data3]
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
        },
        plugins: [Chartist.plugins.tooltip()]
      };
      new Chartist.Line("#widgetLineareaColor .ct-chart", timlelineData, timelineOptions);

      function update() {
        GetData();
        new Chartist.Line("#widgetLineareaColor .ct-chart", timlelineData, timelineOptions);
        setTimeout(update, updateInterval);
      }

      update();
    })(); // Widget Stacked Bar
    // ------------------


    (function () {
      var timeline_labels = [];
      var timeline_data1 = [];
      var timeline_data2 = [];
      var totalPoints = 30;
      var updateInterval = 2500;
      var now = new Date().getTime();

      function GetData() {
        timeline_labels.shift();
        timeline_data1.shift();
        timeline_data2.shift();

        while (timeline_data1.length < totalPoints) {
          var x = Math.floor(Math.random() * 100) + 800;
          var y = Math.floor(Math.random() * 100) + 600;
          timeline_labels.push(now += updateInterval);
          timeline_data1.push(x);
          timeline_data2.push(y);
        }
      }

      var timlelineData = {
        labels: timeline_labels,
        series: [timeline_data1, timeline_data2]
      };
      var timlelineData = {
        labels: timeline_labels,
        series: [timeline_data1, timeline_data2]
      };
      var timelineOptions = {
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
        },
        plugins: [Chartist.plugins.tooltip()]
      };
      new Chartist.Bar("#widgetStackedBar .ct-chart", timlelineData, timelineOptions);

      function update() {
        GetData();
        new Chartist.Bar("#widgetStackedBar .ct-chart", timlelineData, timelineOptions);
        setTimeout(update, updateInterval);
      }

      update();
    })(); // Widget Statistic
    // ----------------


    (function () {
      (function () {
        var defaults = Plugin.getDefaults('vectorMap');
        var options = $$$1.extend({}, defaults, {
          map: "au_mill",
          markers: [{
            latLng: [-33.55, 150.53],
            name: '1,512 Visits'
          }, {
            latLng: [-37.5, 144.58],
            name: '940 Visits'
          }, {
            latLng: [-31.58, 115.49],
            name: '340 Visits'
          }],
          markerStyle: {
            initial: {
              r: 6,
              fill: Config.colors("blue-grey", 600),
              stroke: Config.colors("blue-grey", 600),
              "stroke-width": 6,
              "stroke-opacity": 0.6
            },
            hover: {
              r: 10,
              fill: Config.colors("blue-grey", 500),
              "stroke-width": 0
            }
          }
        }, true);
        $$$1('#widgetJvmap').vectorMap(options);
      })();
    })(); // Widget Linepoint
    // ----------------


    (function () {
      new Chartist.Line("#widgetLinepoint .ct-chart", {
        labels: ['1', '2', '3', '4', '5', '6', '7', '8'],
        series: [[1, 1.5, 0.5, 2, 1, 2.5, 1.5, 2]]
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
    })(); // Widget Sale Bar
    // ---------------


    (function () {
      new Chartist.Bar("#widgetSaleBar .ct-chart", {
        labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'],
        series: [[50, 90, 100, 90, 110, 100, 120, 130, 115, 95, 80, 85, 100, 140, 130, 120]]
      }, {
        low: 0,
        fullWidth: true,
        chartPadding: {
          top: 0,
          right: 20,
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
    })(); // Widget Overall Views
    // --------------------


    (function () {
      new Chartist.Bar("#widgetOverallViews .small-bar-one", {
        labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
        series: [[120, 60, 100, 50, 40, 120, 80, 130]]
      }, {
        low: 0,
        fullWidth: true,
        chartPadding: {
          top: -10,
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
      new Chartist.Bar("#widgetOverallViews .small-bar-two", {
        labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
        series: [[50, 90, 30, 90, 130, 40, 120, 90]]
      }, {
        low: 0,
        fullWidth: true,
        chartPadding: {
          top: -10,
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
      new Chartist.Line("#widgetOverallViews .line-chart", {
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
    })(); // Widget Timeline
    // ---------------


    (function () {
      var timeline_labels = [];
      var timeline_data1 = [];
      var timeline_data2 = [];
      var totalPoints = 20;
      var updateInterval = 1000;
      var now = new Date().getTime();

      function GetData() {
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
        },
        plugins: [Chartist.plugins.tooltip()]
      };
      new Chartist.Line("#widgetTimeline .ct-chart", timlelineData, timelineOptions);

      function update() {
        GetData();
        new Chartist.Line("#widgetTimeline .ct-chart", timlelineData, timelineOptions);
        setTimeout(update, updateInterval);
      }

      update();
    })();

    (function () {
      var snow = new Skycons({
        "color": Config.colors("blue-grey", 500)
      });
      snow.set(document.getElementById("widgetSnow"), "snow");
      snow.play();
      var sunny = new Skycons({
        "color": Config.colors("blue-grey", 700)
      });
      sunny.set(document.getElementById("widgetSunny"), "clear-day");
      sunny.play();
    })(); // Widget Linepoint
    // ----------------


    (function () {
      new Chartist.Line("#widgetLinepointDate .ct-chart", {
        labels: ['1', '2', '3', '4', '5', '6', '7', '8'],
        series: [[36, 45, 28, 19, 39, 46, 35, 13]]
      }, {
        low: 0,
        showArea: false,
        showPoint: true,
        showLine: true,
        fullWidth: true,
        lineSmooth: false,
        chartPadding: {
          top: 5,
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
        },
        plugins: [Chartist.plugins.tooltip()]
      });
    })();
  });
});