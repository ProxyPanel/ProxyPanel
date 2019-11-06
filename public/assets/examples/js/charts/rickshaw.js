(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/charts/rickshaw", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.chartsRickshaw = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Lines
  // -------------

  (function () {
    var seriesData = [[], [], []];
    var random = new Rickshaw.Fixtures.RandomData(150);

    for (var i = 0; i < 150; i++) {
      random.addData(seriesData);
    }

    var $element = (0, _jquery.default)('#exampleChart');
    var graph = new Rickshaw.Graph({
      element: $element.get(0),
      width: $element.width(),
      height: 300,
      renderer: 'line',
      series: [{
        color: Config.colors("primary", 500),
        data: seriesData[0],
        name: 'New York'
      }, {
        color: Config.colors("red", 500),
        data: seriesData[1],
        name: 'London'
      }, {
        color: Config.colors("green", 500),
        data: seriesData[2],
        name: 'Tokyo'
      }]
    });
    graph.render();
    setInterval(function () {
      random.removeData(seriesData);
      random.addData(seriesData);
      graph.update();
    }, 2000);
    var hoverDetail = new Rickshaw.Graph.HoverDetail({
      graph: graph
    });
    var legend = new Rickshaw.Graph.Legend({
      graph: graph,
      element: document.getElementById('exampleChartLegend')
    });
    var shelving = new Rickshaw.Graph.Behavior.Series.Toggle({
      graph: graph,
      legend: legend
    });
    var axes = new Rickshaw.Graph.Axis.Time({
      graph: graph
    });
    axes.render();
    (0, _jquery.default)(window).on('resize', function () {
      graph.configure({
        width: $element.width()
      });
      graph.render();
    });
  })(); // Example Scatter Plot
  // --------------------


  (function () {
    var seriesData = [[], [], []];
    var random = new Rickshaw.Fixtures.RandomData(150);

    for (var i = 0; i < 150; i++) {
      random.addData(seriesData);
    }

    var $element = (0, _jquery.default)('#exampleScatterChart');
    var graph = new Rickshaw.Graph({
      element: $element.get(0),
      width: $element.width(),
      height: 300,
      renderer: 'scatterplot',
      series: [{
        color: Config.colors("primary", 500),
        data: seriesData[0],
        name: 'New York'
      }, {
        color: Config.colors("red", 500),
        data: seriesData[1],
        name: 'London'
      }, {
        color: Config.colors("green", 500),
        data: seriesData[2],
        name: 'Tokyo'
      }]
    });
    graph.render();
    var hoverDetail = new Rickshaw.Graph.HoverDetail({
      graph: graph
    });
    var legend = new Rickshaw.Graph.Legend({
      graph: graph,
      element: document.getElementById('exampleScatterLegend')
    });
    var shelving = new Rickshaw.Graph.Behavior.Series.Toggle({
      graph: graph,
      legend: legend
    });
    (0, _jquery.default)(window).on('resize', function () {
      graph.configure({
        width: $element.width()
      });
      graph.render();
    });
  })(); // Example Stacked Bars
  // --------------------


  (function () {
    var seriesData = [[], [], []];
    var random = new Rickshaw.Fixtures.RandomData(150);

    for (var i = 0; i < 150; i++) {
      random.addData(seriesData);
    }

    var $element = (0, _jquery.default)('#exampleStackedChart');
    var graph = new Rickshaw.Graph({
      element: $element.get(0),
      width: $element.width(),
      height: 300,
      renderer: 'bar',
      series: [{
        color: Config.colors("primary", 700),
        data: seriesData[0],
        name: 'New York'
      }, {
        color: Config.colors("primary", 500),
        data: seriesData[1],
        name: 'London'
      }, {
        color: Config.colors("primary", 300),
        data: seriesData[2],
        name: 'Tokyo'
      }]
    });
    graph.render();
    setInterval(function () {
      random.removeData(seriesData);
      random.addData(seriesData);
      graph.update();
    }, 2000);
    var hoverDetail = new Rickshaw.Graph.HoverDetail({
      graph: graph
    });
    var legend = new Rickshaw.Graph.Legend({
      graph: graph,
      element: document.getElementById('exampleStackedLegend')
    });
    var shelving = new Rickshaw.Graph.Behavior.Series.Toggle({
      graph: graph,
      legend: legend
    });
    (0, _jquery.default)(window).on('resize', function () {
      graph.configure({
        width: $element.width()
      });
      graph.render();
    });
  })(); // Example Area
  // ------------


  (function () {
    var seriesData = [[], [], []];
    var random = new Rickshaw.Fixtures.RandomData(150);

    for (var i = 0; i < 150; i++) {
      random.addData(seriesData);
    }

    var $element = (0, _jquery.default)('#exampleAreaChart');
    var graph = new Rickshaw.Graph({
      element: $element.get(0),
      width: $element.width(),
      height: 300,
      renderer: 'area',
      stroke: true,
      series: [{
        color: Config.colors("purple", 700),
        data: seriesData[0],
        name: 'New York'
      }, {
        color: Config.colors("purple", 500),
        data: seriesData[1],
        name: 'London'
      }, {
        color: Config.colors("purple", 300),
        data: seriesData[2],
        name: 'Tokyo'
      }]
    });
    graph.render();
    setInterval(function () {
      random.removeData(seriesData);
      random.addData(seriesData);
      graph.update();
    }, 2000);
    var hoverDetail = new Rickshaw.Graph.HoverDetail({
      graph: graph
    });
    var legend = new Rickshaw.Graph.Legend({
      graph: graph,
      element: document.getElementById('exampleAreaLegend')
    });
    var shelving = new Rickshaw.Graph.Behavior.Series.Toggle({
      graph: graph,
      legend: legend
    });
    (0, _jquery.default)(window).on('resize', function () {
      graph.configure({
        width: $element.width()
      });
      graph.render();
    });
  })(); // Example Multiple Renderers
  // ---------------------------


  (function () {
    var seriesData = [[], [], [], [], []];
    var random = new Rickshaw.Fixtures.RandomData(50);

    for (var i = 0; i < 75; i++) {
      random.addData(seriesData);
    }

    var $element = (0, _jquery.default)('#exampleMultipleChart');
    var graph = new Rickshaw.Graph({
      element: $element.get(0),
      width: $element.width(),
      height: 300,
      renderer: 'multi',
      dotSize: 5,
      series: [{
        name: 'temperature',
        data: seriesData.shift(),
        color: Config.colors("green", 500),
        renderer: 'stack'
      }, {
        name: 'heat index',
        data: seriesData.shift(),
        color: Config.colors("cyan", 500),
        renderer: 'stack'
      }, {
        name: 'dewpoint',
        data: seriesData.shift(),
        color: Config.colors("blue", 500),
        renderer: 'scatterplot'
      }, {
        name: 'pop',
        data: seriesData.shift().map(function (d) {
          return {
            x: d.x,
            y: d.y / 4
          };
        }),
        color: Config.colors("indigo", 500),
        renderer: 'bar'
      }, {
        name: 'humidity',
        data: seriesData.shift().map(function (d) {
          return {
            x: d.x,
            y: d.y * 1.5
          };
        }),
        renderer: 'line',
        color: Config.colors("red", 500)
      }]
    });
    var slider = new Rickshaw.Graph.RangeSlider.Preview({
      graph: graph,
      element: document.querySelector('#exampleMultipleSlider')
    });
    graph.render();
    var detail = new Rickshaw.Graph.HoverDetail({
      graph: graph
    });
    var legend = new Rickshaw.Graph.Legend({
      graph: graph,
      element: document.querySelector('#exampleMultipleLegend')
    });
    var highlighter = new Rickshaw.Graph.Behavior.Series.Highlight({
      graph: graph,
      legend: legend,
      disabledColor: function disabledColor() {
        return 'rgba(0, 0, 0, 0.2)';
      }
    });
    var highlighter = new Rickshaw.Graph.Behavior.Series.Toggle({
      graph: graph,
      legend: legend
    });
    (0, _jquery.default)(window).on('resize', function () {
      graph.configure({
        width: $element.width()
      });
      graph.render();
    });
  })();
});