(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/charts/chartist", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.chartsChartist = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Chartist Css Animation
  // ------------------------------

  (function () {
    var cssAnimationData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      series: [[1, 2, 2.7, 0, 3, 5, 3, 4, 8, 10, 12, 7], [0, 1.2, 2, 7, 2.5, 9, 5, 8, 9, 11, 14, 4], [10, 9, 8, 6.5, 6.8, 6, 5.4, 5.3, 4.5, 4.4, 3, 2.8]]
    };
    var cssAnimationResponsiveOptions = [[// Foundation.media_queries.small,
    {
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value, index) {
          // Interpolation function causes only every 2nd label to be displayed
          if (index % 2 !== 0) {
            return false;
          } else {
            return value;
          }
        }
      }
    }]];
    new Chartist.Line('#exampleLineAnimation', cssAnimationData, null, cssAnimationResponsiveOptions);
  })(); // Example Chartist Simple Line
  // ----------------------------


  (function () {
    new Chartist.Line('#exampleSimpleLine', {
      labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
      series: [[12, 9, 7, 8, 5], [2, 1, 3.5, 7, 3], [1, 3, 4, 5, 6]]
    }, {
      fullWidth: true,
      chartPadding: {
        right: 40
      }
    });
  })(); // Example Chartist Line Scatter
  // -----------------------------


  (function () {
    var ctScatterTimes = function ctScatterTimes(n) {
      return Array.apply(null, new Array(n));
    };

    var ctScatterData = ctScatterTimes(52).map(Math.random).reduce(function (data, rnd, index) {
      data.labels.push(index + 1);
      data.series.forEach(function (series) {
        series.push(Math.random() * 100);
      });
      return data;
    }, {
      labels: [],
      series: ctScatterTimes(4).map(function () {
        return [];
      })
    });
    var ctScatterOptions = {
      showLine: false,
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value, index) {
          return index % 13 === 0 ? 'W' + value : null;
        }
      }
    };
    var ctScatterResponsiveOptions = [['screen and (min-width: 640px)', {
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value, index) {
          return index % 4 === 0 ? 'W' + value : null;
        }
      }
    }]];
    new Chartist.Line('#exampleLineScatter', ctScatterData, ctScatterOptions, ctScatterResponsiveOptions);
  })(); // Example Chartist Line Chart With Tooltips
  // -----------------------------------------


  (function () {
    new Chartist.Line('#exampleTooltipsLine', {
      labels: ['1', '2', '3', '4', '5', '6'],
      series: [{
        name: 'Fibonacci sequence',
        data: [1, 2, 3, 5, 8, 13]
      }, {
        name: 'Golden section',
        data: [1, 1.618, 2.618, 4.236, 6.854, 11.09]
      }]
    }, {
      plugins: [Chartist.plugins.tooltip()]
    });
    var $ctTooltipsChart = (0, _jquery.default)('#exampleTooltipsLine');
  })(); // Example Chartist Line Chart With Area
  // -------------------------------------


  (function () {
    new Chartist.Line('#exampleAreaLine', {
      labels: [1, 2, 3, 4, 5, 6, 7, 8],
      series: [[5, 9, 7, 8, 5, 3, 5, 4]]
    }, {
      low: 0,
      showArea: true
    });
  })(); // Example Chartist Bi-Polar Line
  // ------------------------------


  (function () {
    new Chartist.Line('#exampleOnlyArea', {
      labels: [1, 2, 3, 4, 5, 6, 7, 8],
      series: [[1, 2, 3, 1, -2, 0, 1, 0], [-2, -1, -2, -1, -2.5, -1, -2, -1], [0, 0, 0, 1, 2, 2.5, 2, 1], [2.5, 2, 1, 0.5, 1, 0.5, -1, -2.5]]
    }, {
      high: 3,
      low: -3,
      showArea: true,
      showLine: false,
      showPoint: false,
      fullWidth: true,
      axisX: {
        showLabel: false,
        showGrid: false
      }
    });
  })(); // Example Chartist Advanced Smil Animations
  // -----------------------------------------


  (function () {
    var animationsChart = new Chartist.Line('#exampleLineAnimations', {
      labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
      series: [[12, 9, 7, 8, 5, 4, 6, 2, 3, 3, 4, 6], [4, 5, 3, 7, 3, 5, 5, 3, 4, 4, 5, 5], [5, 3, 4, 5, 6, 3, 3, 4, 5, 6, 3, 4], [3, 4, 5, 6, 7, 6, 4, 5, 6, 7, 6, 3]]
    }, {
      low: 0
    }); // // Let's put a sequence number aside so we can use it in the event callbacks

    var seq = 0,
        delays = 80,
        durations = 500; // Once the chart is fully created we reset the sequence

    animationsChart.on('created', function () {
      seq = 0;
    }); // // On each drawn element by Chartist we use the Chartist.Svg API to trigger SMIL animations

    animationsChart.on('draw', function (data) {
      seq++;

      if (data.type === 'line') {
        // If the drawn element is a line we do a simple opacity fade in. This could also be achieved using CSS3 animations.
        data.element.animate({
          opacity: {
            // The delay when we like to start the animation
            begin: seq * delays + 1000,
            // Duration of the animation
            dur: durations,
            // The value where the animation should start
            from: 0,
            // The value where it should end
            to: 1
          }
        });
      } else if (data.type === 'label' && data.axis === 'x') {
        data.element.animate({
          y: {
            begin: seq * delays,
            dur: durations,
            from: data.y + 100,
            to: data.y,
            // We can specify an easing function from Chartist.Svg.Easing
            easing: 'easeOutQuart'
          }
        });
      } else if (data.type === 'label' && data.axis === 'y') {
        data.element.animate({
          x: {
            begin: seq * delays,
            dur: durations,
            from: data.x - 100,
            to: data.x,
            easing: 'easeOutQuart'
          }
        });
      } else if (data.type === 'point') {
        data.element.animate({
          x1: {
            begin: seq * delays,
            dur: durations,
            from: data.x - 10,
            to: data.x,
            easing: 'easeOutQuart'
          },
          x2: {
            begin: seq * delays,
            dur: durations,
            from: data.x - 10,
            to: data.x,
            easing: 'easeOutQuart'
          },
          opacity: {
            begin: seq * delays,
            dur: durations,
            from: 0,
            to: 1,
            easing: 'easeOutQuart'
          }
        });
      } else if (data.type === 'grid') {
        // Using data.axis we get x or y which we can use to construct our animation definition objects
        var pos1Animation = {
          begin: seq * delays,
          dur: durations,
          from: data[data.axis.units.pos + '1'] - 30,
          to: data[data.axis.units.pos + '1'],
          easing: 'easeOutQuart'
        };
        var pos2Animation = {
          begin: seq * delays,
          dur: durations,
          from: data[data.axis.units.pos + '2'] - 100,
          to: data[data.axis.units.pos + '2'],
          easing: 'easeOutQuart'
        };
        var ctAnimations = {};
        ctAnimations[data.axis.units.pos + '1'] = pos1Animation;
        ctAnimations[data.axis.units.pos + '2'] = pos2Animation;
        ctAnimations.opacity = {
          begin: seq * delays,
          dur: durations,
          from: 0,
          to: 1,
          easing: 'easeOutQuart'
        };
        data.element.animate(ctAnimations);
      }
    }); // For the sake of the example we update the chart every time it's created with a delay of 10 seconds

    animationsChart.on('created', function () {
      if (window.__exampleAnimateTimeout) {
        clearTimeout(window.__exampleAnimateTimeout);
        window.__exampleAnimateTimeout = null;
      }

      window.__exampleAnimateTimeout = setTimeout(animationsChart.update.bind(animationsChart), 12000);
    });
  })(); // Example Chartist Svg Path Animation
  // -----------------------------------


  (function () {
    //ct-path-animation
    var pathAnimationChart = new Chartist.Line('#examplePathAnimation', {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      series: [[1, 5, 2, 5, 4, 3], [2, 3, 4, 8, 1, 2], [5, 4, 3, 2, 1, 0.5]]
    }, {
      low: 0,
      showArea: true,
      showPoint: false,
      fullWidth: true
    });
    pathAnimationChart.on('draw', function (data) {
      if (data.type === 'line' || data.type === 'area') {
        data.element.animate({
          d: {
            begin: 2000 * data.index,
            dur: 2000,
            from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
            to: data.path.clone().stringify(),
            easing: Chartist.Svg.Easing.easeOutQuint
          }
        });
      }
    });
  })(); // Example Chartist Line Interpolation
  // -----------------------------------


  (function () {
    var smoothingChart = new Chartist.Line('#exampleSmoothingLine', {
      labels: [1, 2, 3, 4, 5],
      series: [[1, 5, 10, 0, 1], [10, 15, 0, 1, 2]]
    }, {
      // Remove this configuration to see that chart rendered with cardinal spline interpolation
      // Sometimes, on large jumps in data values, it's better to use simple smoothing.
      lineSmooth: Chartist.Interpolation.simple({
        divisor: 2
      }),
      fullWidth: true,
      chartPadding: {
        right: 20
      },
      low: 0
    });
  })(); // Example Chartist Bi-Polar Bar
  // -----------------------------


  (function () {
    var biPolarData = {
      labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10'],
      series: [[1, 2, 4, 8, 6, -2, -1, -4, -6, -2]]
    };
    var biPolarOptions = {
      high: 10,
      low: -10,
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value, index) {
          return index % 2 === 0 ? value : null;
        }
      }
    };
    new Chartist.Bar('#exampleBiPolarBar', biPolarData, biPolarOptions);
  })(); // Example Chartist Overlapping Bars
  // ---------------------------------


  (function () {
    var overlappingData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      series: [[5, 4, 3, 7, 5, 10, 3, 4, 8, 10, 6, 8], [3, 2, 9, 5, 4, 6, 4, 6, 7, 8, 7, 4]]
    };
    var overlappingOptions = {
      seriesBarDistance: 10
    };
    var overlappingResponsiveOptions = [['screen and (max-width: 640px)', {
      seriesBarDistance: 5,
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value) {
          return value[0];
        }
      }
    }]];
    new Chartist.Bar('#exampleOverlappingBar', overlappingData, overlappingOptions, overlappingResponsiveOptions);
  })(); // Example Chartist Add Peak Circles
  // ---------------------------------


  (function () {
    // Create a simple bi-polar bar chart
    var peakCirclesChart = new Chartist.Bar('#examplePeakCirclesBar', {
      labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10'],
      series: [[1, 2, 4, 8, 6, -2, -1, -4, -6, -2]]
    }, {
      high: 10,
      low: -10,
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value, index) {
          return index % 2 === 0 ? value : null;
        }
      }
    }); // Listen for draw events on the bar chart

    peakCirclesChart.on('draw', function (data) {
      // If this draw event is of type bar we can use the data to create additional content
      if (data.type === 'bar') {
        // We use the group element of the current series to append a simple circle with the bar peek coordinates and a circle radius that is depending on the value
        data.group.append(new Chartist.Svg('circle', {
          cx: data.x2,
          cy: data.y2,
          r: Math.abs(Chartist.getMultiValue(data.value)) * 2 + 5
        }, 'ct-slice-pie'));
      }
    });
  })(); // Example Chartist Multi-Line Labels
  // ----------------------------------


  (function () {
    new Chartist.Bar('#exampleMultiLabelsBar', {
      labels: ['First quarter of the year', 'Second quarter of the year', 'Third quarter of the year', 'Fourth quarter of the year'],
      series: [[60000, 40000, 80000, 70000], [40000, 30000, 70000, 65000], [8000, 3000, 10000, 6000]]
    }, {
      seriesBarDistance: 10,
      axisX: {
        offset: 60
      },
      axisY: {
        offset: 80,
        labelInterpolationFnc: function labelInterpolationFnc(value) {
          return value + ' CHF';
        },
        scaleMinSpace: 15
      }
    });
  })(); // Example Chartist Stacked Bar Chart
  // ----------------------------------


  (function () {
    new Chartist.Bar('#exampleStackedBar', {
      labels: ['Q1', 'Q2', 'Q3', 'Q4'],
      series: [[800000, 1200000, 1400000, 1300000], [200000, 400000, 500000, 300000], [100000, 200000, 400000, 600000]]
    }, {
      stackBars: true,
      axisY: {
        labelInterpolationFnc: function labelInterpolationFnc(value) {
          return value / 1000 + 'k';
        }
      }
    }).on('draw', function (data) {
      if (data.type === 'bar') {
        data.element.attr({
          style: 'stroke-width: 30px'
        });
      }
    });
  })(); // Example Chartist Horizontal Bar
  // -------------------------------


  (function () {
    new Chartist.Bar('#exampleHorizontalBar', {
      labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      series: [[5, 4, 3, 7, 5, 10, 3], [3, 2, 9, 5, 4, 6, 4]]
    }, {
      seriesBarDistance: 10,
      reverseData: true,
      horizontalBars: true,
      axisY: {
        offset: 70
      }
    });
  })(); // Example Chartist Extreme Responsive
  // -----------------------------------


  (function () {
    new Chartist.Bar('#exampleResponsiveBar', {
      labels: ['Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4'],
      series: [[5, 4, 3, 7], [3, 2, 9, 5], [1, 5, 8, 4], [2, 3, 4, 6], [4, 1, 2, 1]]
    }, {
      // Default mobile configuration
      stackBars: true,
      axisX: {
        labelInterpolationFnc: function labelInterpolationFnc(value) {
          return value.split(/\s+/).map(function (word) {
            return word[0];
          }).join('');
        }
      },
      axisY: {
        offset: 20
      }
    }, [// Options override for media > 400px
    ['screen and (min-width: 480px)', {
      reverseData: true,
      horizontalBars: true,
      axisX: {
        labelInterpolationFnc: Chartist.noop
      },
      axisY: {
        offset: 60
      }
    }], // Options override for media > 800px
    ['screen and (min-width: 992px)', {
      stackBars: false,
      seriesBarDistance: 10
    }], // Options override for media > 1000px
    ['screen and (min-width: 1200px)', {
      reverseData: false,
      horizontalBars: false,
      seriesBarDistance: 15
    }]]);
  })(); // Example Chartist Simple Pie
  // ---------------------------


  (function () {
    var simplePiedata = {
      series: [5, 3, 4]
    };

    var simplePieSum = function simplePieSum(a, b) {
      return a + b;
    };

    new Chartist.Pie('#exampleSimplePie', simplePiedata, {
      labelInterpolationFnc: function labelInterpolationFnc(value) {
        return Math.round(value / simplePiedata.series.reduce(simplePieSum) * 100) + '%';
      }
    });
  })(); // Example Chartist Pie Chart Labels
  // ---------------------------------


  (function () {
    var labelsPieData = {
      labels: ['Bananas', 'Apples', 'Grapes'],
      series: [20, 15, 40]
    };
    var labelsPieOptions = {
      labelInterpolationFnc: function labelInterpolationFnc(value) {
        return value[0];
      }
    };
    var labelsPieResponsiveOptions = [['screen and (min-width: 640px)', {
      chartPadding: 30,
      labelOffset: 100,
      labelDirection: 'explode',
      labelInterpolationFnc: function labelInterpolationFnc(value) {
        return value;
      }
    }], ['screen and (min-width: 1024px)', {
      labelOffset: 80,
      chartPadding: 20
    }]];
    new Chartist.Pie('#exampleLabelsPie', labelsPieData, labelsPieOptions, labelsPieResponsiveOptions);
  })(); // Example Chartist Gauge Pie
  // --------------------------


  (function () {
    new Chartist.Pie('#exampleGaugePie', {
      series: [20, 10, 30, 40]
    }, {
      donut: true,
      donutWidth: 60,
      startAngle: 270,
      total: 200,
      showLabel: false
    });
  })();
});