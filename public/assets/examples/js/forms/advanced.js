(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/forms/advanced", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsAdvanced = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Reset Current
  // ---------------------

  (function () {
    // Reset Current
    (0, _jquery.default)('#exampleTimeButton').on('click', function () {
      (0, _jquery.default)('#inputTextCurrent').timepicker('setTime', new Date());
    });
  })(); // Example inline datepicker
  // ---------------------


  (function () {
    // Reset Current
    (0, _jquery.default)('#inlineDatepicker').datepicker();
    (0, _jquery.default)("#inlineDatepicker").on("changeDate", function (event) {
      (0, _jquery.default)("#inputHiddenInline").val((0, _jquery.default)("#inlineDatepicker").datepicker('getFormattedDate'));
    });
  })(); // Example Tokenfield With Typeahead
  // ---------------------------------


  (function () {
    var engine = new Bloodhound({
      local: [{
        value: 'red'
      }, {
        value: 'blue'
      }, {
        value: 'green'
      }, {
        value: 'yellow'
      }, {
        value: 'violet'
      }, {
        value: 'brown'
      }, {
        value: 'purple'
      }, {
        value: 'black'
      }, {
        value: 'white'
      }],
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace
    }); // engine.initialize();

    (0, _jquery.default)('#inputTokenfieldTypeahead').tokenfield({
      typeahead: [null, {
        name: 'engine',
        displayKey: 'value',
        source: engine.ttAdapter()
      }]
    });
  })(); // Example Tokenfield Events
  // -------------------------


  (function () {
    (0, _jquery.default)('#inputTokenfieldEvents').on('tokenfield:createtoken', function (e) {
      var data = e.attrs.value.split('|');
      e.attrs.value = data[1] || data[0];
      e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0];
    }).on('tokenfield:createdtoken', function (e) {
      // Über-simplistic e-mail validation
      var re = /\S+@\S+\.\S+/;
      var valid = re.test(e.attrs.value);

      if (!valid) {
        (0, _jquery.default)(e.relatedTarget).addClass('invalid');
      }
    }).on('tokenfield:edittoken', function (e) {
      if (e.attrs.label !== e.attrs.value) {
        var label = e.attrs.label.split(' (');
        e.attrs.value = label[0] + '|' + e.attrs.value;
      }
    }).on('tokenfield:removedtoken', function (e) {
      if (e.attrs.length > 1) {
        var values = _jquery.default.map(e.attrs, function (attrs) {
          return attrs.value;
        });

        alert(e.attrs.length + ' tokens removed! Token values were: ' + values.join(', '));
      } else {
        alert('Token removed! Token value was: ' + e.attrs.value);
      }
    }).tokenfield();
  })(); // Example Tags Input Objects as tags
  // ----------------------------------


  (function () {
    var cities = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      prefetch: '../../assets/data/cities.json'
    });
    cities.initialize();

    var options = _jquery.default.extend(true, {}, Plugin.getDefaults("tagsinput"), {
      itemValue: 'value',
      itemText: 'text',
      typeaheadjs: [{
        hint: true,
        highlight: true,
        minLength: 1
      }, {
        name: 'cities',
        displayKey: 'text',
        source: cities.ttAdapter()
      }]
    });

    var $input = (0, _jquery.default)('#inputTagsObject');
    $input.tagsinput(options);
    $input.tagsinput('add', {
      "value": 1,
      "text": "Amsterdam",
      "continent": "Europe"
    });
    $input.tagsinput('add', {
      "value": 4,
      "text": "Washington",
      "continent": "America"
    });
    $input.tagsinput('add', {
      "value": 7,
      "text": "Sydney",
      "continent": "Australia"
    });
    $input.tagsinput('add', {
      "value": 10,
      "text": "Beijing",
      "continent": "Asia"
    });
    $input.tagsinput('add', {
      "value": 13,
      "text": "Cairo",
      "continent": "Africa"
    });
  })(); // Example Tags Input Categorizing
  // -------------------------------


  (function () {
    var cities = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      prefetch: '../../assets/data/cities.json'
    });
    cities.initialize();

    var options = _jquery.default.extend(true, {}, Plugin.getDefaults("tagsinput"), {
      tagClass: function tagClass(item) {
        switch (item.continent) {
          case 'Europe':
            return 'badge badge-primary';

          case 'America':
            return 'badge badge-danger';

          case 'Australia':
            return 'badge badge-success';

          case 'Africa':
            return 'badge badge-default';

          case 'Asia':
            return 'badge badge-warning';
        }
      },
      itemValue: 'value',
      itemText: 'text',
      typeaheadjs: [{
        hint: true,
        highlight: true,
        minLength: 1
      }, {
        name: 'cities',
        displayKey: 'text',
        source: cities.ttAdapter()
      }]
    });

    var $input = (0, _jquery.default)('#inputTagsCategorizing');
    $input.tagsinput(options);
    $input.tagsinput('add', {
      "value": 1,
      "text": "Amsterdam",
      "continent": "Europe"
    });
    $input.tagsinput('add', {
      "value": 4,
      "text": "Washington",
      "continent": "America"
    });
    $input.tagsinput('add', {
      "value": 7,
      "text": "Sydney",
      "continent": "Australia"
    });
    $input.tagsinput('add', {
      "value": 10,
      "text": "Beijing",
      "continent": "Asia"
    });
    $input.tagsinput('add', {
      "value": 13,
      "text": "Cairo",
      "continent": "Africa"
    });
  })(); // Example AsSpinner
  // -----------------


  (function () {
    // Custom Format
    var options = _jquery.default.extend({}, Plugin.getDefaults("asSpinner"), {
      format: function format(value) {
        return value + '%';
      }
    });

    (0, _jquery.default)('#inputSpinnerCustomFormat').asSpinner(options);
  })(); // Example Multi-Select
  // --------------------


  (function () {
    // for multi-select public methods example
    (0, _jquery.default)('.multi-select-methods').multiSelect();
    (0, _jquery.default)('#buttonSelectAll').click(function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('select_all');
      return false;
    });
    (0, _jquery.default)('#buttonDeselectAll').click(function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('deselect_all');
      return false;
    });
    (0, _jquery.default)('#buttonSelectSome').click(function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('select', ['Idaho', 'Montana', 'Arkansas']);
      return false;
    });
    (0, _jquery.default)('#buttonDeselectSome').click(function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('select', ['Idaho', 'Montana', 'Arkansas']);
      return false;
    });
    (0, _jquery.default)('#buttonRefresh').on('click', function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('refresh');
      return false;
    });
    (0, _jquery.default)('#buttonAdd').on('click', function () {
      (0, _jquery.default)('.multi-select-methods').multiSelect('addOption', {
        value: 42,
        text: 'test 42',
        index: 0
      });
      return false;
    });
  })(); // Example Typeahead
  // -----------------


  (function () {
    var states = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming']; // basic & Styled
    // --------------

    (function () {
      var substringMatcher = function substringMatcher(strs) {
        return function findMatches(q, cb) {
          var matches, substrRegex; // an array that will be populated with substring matches

          matches = []; // regex used to determine if a string contains the substring `q`

          substrRegex = new RegExp(q, 'i'); // iterate through the pool of strings and for any string that
          // contains the substring `q`, add it to the `matches` array

          _jquery.default.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
              matches.push(str);
            }
          });

          cb(matches);
        };
      };

      (0, _jquery.default)('#exampleTypeaheadBasic, #exampleTypeaheadStyle').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
      }, {
        name: 'states',
        source: substringMatcher(states)
      });
    })(); // bloodhound
    // ----------


    (function () {
      var states = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming']; // constructs the suggestion engine

      var state = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        // `states` is an array of state names defined in "The Basics"
        local: states
      });
      (0, _jquery.default)('#exampleTypeaheadBloodhound').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
      }, {
        name: 'states',
        source: state
      });
    })(); // Prefetch typeahead
    // ----------------


    (function () {
      var countries = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        // url points to a json file that contains an array of country names, see
        // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
        prefetch: '../../assets/data/countries.json'
      }); // passing in `null` for the `options` arguments will result in the default
      // options being used

      (0, _jquery.default)('#exampleTypeaheadPrefetch').typeahead(null, {
        name: 'countries',
        source: countries
      });
    })();
  })();
});