(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/charts/chartjs", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.chartsChartjs = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });
  Chart.defaults.global.responsive = true; // Example Chartjs Line
  // --------------------

  (function () {
    var lineChartData = {
      labels: ["January", "February", "March", "April", "May", "June", "July"],
      datasets: [{
        label: "First",
        fill: true,
        backgroundColor: "rgba(204, 213, 219, .1)",
        borderColor: Config.colors("blue-grey", 300),
        pointRadius: 4,
        borderDashOffset: 2,
        pointBorderColor: "#fff",
        pointBackgroundColor: Config.colors("blue-grey", 300),
        pointHoverBackgroundColor: "#fff",
        pointHoverBorderColor: Config.colors("blue-grey", 300),
        data: [65, 59, 80, 81, 56, 55, 40]
      }, {
        label: "Second",
        fill: true,
        backgroundColor: "rgba(98, 168, 234, .1)",
        borderColor: Config.colors("primary", 600),
        pointRadius: 4,
        borderDashOffset: 2,
        pointBorderColor: "#fff",
        pointBackgroundColor: Config.colors("primary", 600),
        pointHoverBackgroundColor: "#fff",
        pointHoverBorderColor: Config.colors("primary", 600),
        data: [28, 48, 40, 19, 86, 27, 90]
      }]
    };
    var myLine = new Chart(document.getElementById("exampleChartjsLine").getContext("2d"), {
      type: 'line',
      data: lineChartData,
      options: {
        responsive: true,
        scales: {
          xAxes: [{
            display: true
          }],
          yAxes: [{
            display: true
          }]
        }
      }
    });
  })(); // Example Chartjs Bar
  // --------------------


  (function () {
    var barChartData = {
      labels: ["January", "February", "March", "April", "May", "June", "July"],
      datasets: [{
        label: "First",
        backgroundColor: "rgba(204, 213, 219, .2)",
        borderColor: Config.colors("blue-grey", 300),
        hoverBackgroundColor: "rgba(204, 213, 219, .3)",
        borderWidth: 2,
        data: [65, 45, 75, 50, 60, 45, 55]
      }, {
        label: "Second",
        backgroundColor: "rgba(98, 168, 234, .2)",
        borderColor: Config.colors("primary", 600),
        hoverBackgroundColor: "rgba(98, 168, 234, .3)",
        borderWidth: 2,
        data: [30, 20, 40, 25, 45, 35, 40]
      }]
    };
    var myBar = new Chart(document.getElementById("exampleChartjsBar").getContext("2d"), {
      type: 'bar',
      data: barChartData,
      options: {
        responsive: true,
        scales: {
          xAxes: [{
            display: true
          }],
          yAxes: [{
            display: true
          }]
        }
      }
    });
  })(); // Example Chartjs Radar
  // --------------------


  (function () {
    var radarChartData = {
      labels: ["Eating", "Drinking", "Sleeping", "Designing", "Coding", "Partying", "Running"],
      pointLabelFontSize: 14,
      datasets: [{
        label: "First",
        pointRadius: 4,
        borderDashOffset: 2,
        backgroundColor: "rgba(98, 168, 234, .15)",
        borderColor: "rgba(0,0,0,0)",
        pointBackgroundColor: Config.colors("primary", 600),
        pointBorderColor: "#fff",
        pointHoverBackgroundColor: "#fff",
        pointHoverBorderColor: Config.colors("primary", 600),
        data: [65, 59, 90, 81, 56, 55, 40]
      }, {
        label: "Second",
        pointRadius: 4,
        borderDashOffset: 2,
        backgroundColor: "rgba(250,122,122,0.25)",
        borderColor: "rgba(0,0,0,0)",
        pointBackgroundColor: Config.colors("red", 500),
        pointBorderColor: "#fff",
        pointHoverBackgroundColor: "#fff",
        pointHoverBorderColor: Config.colors("red", 500),
        data: [28, 48, 40, 19, 96, 27, 100]
      }]
    };
    var myRadar = new Chart(document.getElementById("exampleChartjsRadar").getContext("2d"), {
      type: 'radar',
      data: radarChartData,
      options: {
        responsive: true,
        scale: {
          ticks: {
            beginAtZero: true
          }
        }
      }
    });
  })(); // Example Chartjs Ploar Area
  // --------------------------


  (function () {
    var chartData = {
      datasets: [{
        data: [300, 200, 150, 100],
        backgroundColor: [Config.colors("red", 400), Config.colors("green", 400), Config.colors("yellow", 400), Config.colors("blue", 400)],
        label: 'My dataset' // for legend

      }],
      labels: ["Red", "Green", "Yellow", "Blue"]
    };
    var myPolarArea = new Chart(document.getElementById("exampleChartjsPloarArea").getContext("2d"), {
      data: chartData,
      type: "polarArea",
      options: {
        responsive: true,
        elements: {
          arc: {
            borderColor: "#ffffff"
          }
        }
      }
    });
  })(); // Example Chartjs Pie
  // -------------------


  (function () {
    var pieData = {
      labels: ["Red", "Blue", "Yellow"],
      datasets: [{
        data: [300, 50, 100],
        backgroundColor: [Config.colors("red", 400), Config.colors("green", 400), Config.colors("yellow", 400)],
        hoverBackgroundColor: [Config.colors("red", 600), Config.colors("green", 600), Config.colors("yellow", 600)]
      }]
    };
    var myPie = new Chart(document.getElementById("exampleChartjsPie").getContext("2d"), {
      type: 'pie',
      data: pieData,
      options: {
        responsive: true
      }
    });
  })(); // Example Chartjs Donut
  // ---------------------


  (function () {
    var doughnutData = {
      labels: ["Red", "Blue", "Yellow"],
      datasets: [{
        data: [300, 50, 100],
        backgroundColor: [Config.colors("red", 400), Config.colors("green", 400), Config.colors("yellow", 400)],
        hoverBackgroundColor: [Config.colors("red", 600), Config.colors("green", 600), Config.colors("yellow", 600)]
      }]
    };
    var myDoughnut = new Chart(document.getElementById("exampleChartjsDonut").getContext("2d"), {
      type: 'doughnut',
      data: doughnutData,
      options: {
        responsive: true
      }
    });
  })();
});