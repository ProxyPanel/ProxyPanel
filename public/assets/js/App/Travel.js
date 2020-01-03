(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Travel", ["exports", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site);
    global.AppTravel = mod.exports;
  }
})(this, function (_exports, _Site2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppTravel = void 0;
  _Site2 = babelHelpers.interopRequireDefault(_Site2);

  var Map =
  /*#__PURE__*/
  function () {
    function Map() {
      babelHelpers.classCallCheck(this, Map);
      this.window = $(window);
      this.$siteNavbar = $('.site-navbar');
      this.$siteFooter = $('.site-footer');
      this.$pageMain = $('.page-main');
      this.handleMapHeight();
    }

    babelHelpers.createClass(Map, [{
      key: "handleMapHeight",
      value: function handleMapHeight() {
        var footerH = this.$siteFooter.outerHeight();
        var navbarH = this.$siteNavbar.outerHeight();
        var mapH = this.window.height() - navbarH - footerH;
        this.$pageMain.outerHeight(mapH);
      }
    }, {
      key: "getMap",
      value: function getMap() {
        var mapLatlng = L.latLng(37.769, -122.446); // this accessToken, you can get it to here ==> [ https://www.mapbox.com ]

        L.mapbox.accessToken = 'pk.eyJ1IjoiYW1hemluZ3N1cmdlIiwiYSI6ImNpaDVubzBoOTAxZG11dGx4OW5hODl2b3YifQ.qudwERFDdMJhFA-B2uO6Rg';
        return L.mapbox.map('map', 'mapbox.light').setView(mapLatlng, 18);
      }
    }]);
    return Map;
  }();

  var Markers =
  /*#__PURE__*/
  function () {
    function Markers(spots, hotels, reviews, map) {
      babelHelpers.classCallCheck(this, Markers);
      this.spots = spots;
      this.hotels = hotels;
      this.reviews = reviews;
      this.map = map;
      this.markers = null;
      this.allMarkers = [];
      this.addMarkersByOption('spots');
    }

    babelHelpers.createClass(Markers, [{
      key: "deleteMarkers",
      value: function deleteMarkers() {
        this.map.removeLayer(this.markers);
        this.markers = null;
        this.allMarkers.length = 0;
      }
    }, {
      key: "addMarkersByOption",
      value: function addMarkersByOption(option) {
        /* add markercluster Plugin */
        // this mapbox's Plugins,you can get it to here ==> [ https://github.com/Leaflet/Leaflet.markercluster.git ]
        this.markers = new L.MarkerClusterGroup({
          removeOutsideVisibleBounds: false,
          polygonOptions: {
            color: '#444'
          }
        });
        this.initMarkers(this.markers, this["".concat(option)]);
        this.map.addLayer(this.markers);
      }
    }, {
      key: "initMarkers",
      value: function initMarkers(markers, items) {
        for (var i = 0; i < items.length; i++) {
          var path = void 0;
          var x = void 0;

          if (i % 2 === 0) {
            x = Number(Math.random());
          } else {
            x = -1 * Math.random();
          }

          var markerLatlng = L.latLng(37.769 + Math.random() / 170 * x, -122.446 + Math.random() / 150 * x);
          path = $(items[i]).find('img').attr('src');
          var divContent = "<div class='in-map-markers'>\n                          <div class='marker-icon'>\n                            <img src='".concat(path, "'/>\n                          </div>\n                        </div>");
          var itemImg = L.divIcon({
            html: divContent,
            iconAnchor: [0, 0],
            className: ''
          });
          /* create new marker and add to map */

          var itemName = $(items[i]).find('.item-name').html();
          var itemTitle = $(items[i]).find('.item-title').html();
          var popupInfo = "<div class='marker-popup-info'>\n                        <div class='detail'>info</div>\n                        <h3>".concat(itemName, "</h3>\n                        <p>").concat(itemTitle, "</p>\n                      </div>\n                      <i class='icon wb-chevron-right-mini'>\n                      </i>");
          var marker = L.marker(markerLatlng, {
            title: itemName,
            icon: itemImg
          }).bindPopup(popupInfo, {
            closeButton: false
          });
          markers.addLayer(marker);
          this.allMarkers.push(marker);
          marker.on('popupopen', function () {
            this._icon.className += ' marker-active';
            this.setZIndexOffset(999);
          });
          marker.on('popupclose', function () {
            if (this._icon) {
              this._icon.className = 'leaflet-marker-icon leaflet-zoom-animated leaflet-clickable';
            }

            this.setZIndexOffset(450);
          });
        }
      }
    }, {
      key: "getAllMarkers",
      value: function getAllMarkers() {
        return this.allMarkers;
      }
    }, {
      key: "getMarkersInMap",
      value: function getMarkersInMap() {
        var inMapMarkers = [];
        var allMarkers = this.getAllMarkers();
        /* Get the object of all Markers in the map view */

        for (var i = 0; i < allMarkers.length; i++) {
          if (this.map.getBounds().contains(allMarkers[i].getLatLng())) {
            inMapMarkers.push(allMarkers.indexOf(allMarkers[i]));
          }
        }

        return inMapMarkers;
      }
    }]);
    return Markers;
  }();

  var AppTravel =
  /*#__PURE__*/
  function (_Site) {
    babelHelpers.inherits(AppTravel, _Site);

    function AppTravel() {
      babelHelpers.classCallCheck(this, AppTravel);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppTravel).apply(this, arguments));
    }

    babelHelpers.createClass(AppTravel, [{
      key: "initialize",
      value: function initialize() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppTravel.prototype), "initialize", this).call(this);
        this.window = $(window);
        this.$pageAside = $('.page-aside');
        this.$allSpots = $('.app-travel .spot-info');
        this.allSpots = this.getAllListItems(this.$allSpots);
        this.$allHotels = $('.app-travel .hotel-info');
        this.allHotels = this.getAllListItems(this.$allHotels);
        this.$allReviews = $('.app-travel .review-info');
        this.allReviews = this.getAllListItems(this.$allReviews);
        this.mapbox = new Map();
        this.map = this.mapbox.getMap();
        this.markers = new Markers(this.$allSpots, this.$allHotels, this.$allReviews, this.map);
        this.allMarkers = this.markers.getAllMarkers();
        this.markersInMap = null;
        this.spotsNum = null;
        this.hotelsNum = null;
        this.reviewsNum = null; // states

        this.states = {
          mapChange: true,
          listItemActive: false,
          optionChange: 'spots'
        };
      }
    }, {
      key: "process",
      value: function process() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppTravel.prototype), "process", this).call(this);
        this.handleResize();
        this.steupListItem('spots');
        this.steupListItem('hotels');
        this.steupListItem('reviews');
        this.steupMapChange();
        this.setupTabChange();
        this.handleSwitchClick();
        this.handleSpotAction();
      }
    }, {
      key: "getAllListItems",
      value: function getAllListItems($allListItems) {
        var allListItems = [];
        $allListItems.each(function () {
          allListItems.push(this);
        });
        return allListItems;
      } // getDefaultState() {
      //   return Object.assign(super.getDefaultState(), {
      //     mapChange: true,
      //     listItemActive: false,
      //     optionChange: 'spots'
      //   });
      // }

    }, {
      key: "optionChange",
      value: function optionChange(change) {
        var self = this;
        this.states.optionChange = change;

        if (change) {
          console.log('tab change');

          if (self.markers.markers) {
            self.markers.deleteMarkers();
          }

          var tabOption = self.states.optionChange; // spots,hotels,reviews

          self.markers.addMarkersByOption(tabOption);
          self.changeListItemsByOption(tabOption);
        }
      }
    }, {
      key: "mapChange",
      value: function mapChange(change) {
        if (change) {
          console.log('map change');
        } else {
          var tabOption = this.states.optionChange;
          this.changeListItemsByOption(tabOption);
        }

        this.states.mapChange = change;
      }
    }, {
      key: "listItemActive",
      value: function listItemActive(active) {
        if (active) {
          var tabOption = this.states.optionChange;
          this.changeMapOnListActiveByOption(tabOption);
        } else {
          console.log('listItem unactive');
        }

        this.states.listItemActive = active;
      } // change list when map change

    }, {
      key: "changeListItems",
      value: function changeListItems(allListItems) {
        var itemsInList = [];
        this.markersInMap = this.markers.getMarkersInMap();

        for (var i = 0; i < this.allMarkers.length; i++) {
          if (this.markersInMap.indexOf(i) === -1) {
            $(allListItems[i]).hide();
          } else {
            $(allListItems[i]).show();
            itemsInList.push($(allListItems[i]));
          }
        }

        return itemsInList;
      }
    }, {
      key: "onSpotsListChange",
      value: function onSpotsListChange(spotsItemsInList) {
        $('.clearfix.hidden-xl-down').remove();

        for (var i = 0; i < spotsItemsInList.length; i++) {
          if (i > 0 && (i + 1) % 2 === 0) {
            var $clear = $('<div></div>').addClass('clearfix hidden-xl-down');
            spotsItemsInList[i].after($clear);
          }
        }
      }
    }, {
      key: "onReviewsListChange",
      value: function onReviewsListChange(reviewsItemsInList) {
        var $lastReview = $('.last-review');

        if ($lastReview.length > 0) {
          $lastReview.removeClass('last-review');
        }

        var length = reviewsItemsInList.length;

        if (length > 0) {
          reviewsItemsInList[length - 1].addClass('last-review');
        }
      }
    }, {
      key: "changeListItemsByOption",
      value: function changeListItemsByOption(option) {
        var optionString = option.substring(0, 1).toUpperCase() + option.substring(1);
        var itemsInList = this.changeListItems(this["all".concat(optionString)]);
        this["on".concat(optionString, "ListChange")] ? this["on".concat(optionString, "ListChange")](itemsInList) : '';
        this.window.trigger('resize');
      } // end change list when map change
      // change map on list change

    }, {
      key: "changeMapOnListActive",
      value: function changeMapOnListActive(num) {
        this.map.panTo(this.allMarkers[num].getLatLng());
        this.allMarkers[num].openPopup();
      }
    }, {
      key: "changeMapOnListActiveByOption",
      value: function changeMapOnListActiveByOption(option) {
        this.changeMapOnListActive(this["".concat(option, "Num")]);
      } // end change map on list change
      // bind

    }, {
      key: "steupListItem",
      value: function steupListItem(option) {
        var _this = this;

        var self = this;
        var optionString = option.substring(0, 1).toUpperCase() + option.substring(1);
        this["$all".concat(optionString)].on('click', function () {
          $('.rating').on('click', function (event) {
            event.stopPropagation();
          });
          self["".concat(option, "Num")] = self["all".concat(optionString)].indexOf(this);
          self.listItemActive(true);
        });
        this["$all".concat(optionString)].on('mouseup', function () {
          _this.listItemActive(false);
        });
      }
    }, {
      key: "setupTabChange",
      value: function setupTabChange() {
        var self = this;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          var href = $(e.target).attr('href'); // #spots,#travels,#reviews

          if (href) {
            var option = href.substring(1);
            self.optionChange("".concat(option));
          } // e.relatedTarget; /* previous active tab */

        });
      }
    }, {
      key: "steupMapChange",
      value: function steupMapChange() {
        var _this2 = this;

        this.map.on('viewreset move', function () {
          _this2.mapChange(true);
        });
        this.map.on('ready blur moveend dragend zoomend', function () {
          _this2.mapChange(false);
        });
      }
    }, {
      key: "handleSwitchClick",
      value: function handleSwitchClick() {
        var self = this;
        $(document).on('click', '.page-aside .page-aside-switch', function (event) {
          if (self.$pageAside.hasClass('open')) {
            var tabOption = self.states.optionChange;
            self.changeListItemsByOption(tabOption);
          } else {
            event.stopPropagation();
          }
        });
      }
    }, {
      key: "handleResize",
      value: function handleResize() {
        var _this3 = this;

        this.window.on('resize', function () {
          _this3.mapbox.handleMapHeight();
        });
      }
    }, {
      key: "handleSpotAction",
      value: function handleSpotAction() {
        $(document).on('click', '.card-actions', function () {
          var $this = $(this);
          $this.toggleClass('active');
        });
      } // end bind

    }]);
    return AppTravel;
  }(_Site2.default);

  _exports.AppTravel = AppTravel;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppTravel();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppTravel;
  _exports.default = _default;
});