(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Location", ["exports", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site);
    global.AppLocation = mod.exports;
  }
})(this, function (_exports, _Site2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppLocation = void 0;
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
    function Markers(friends, map) {
      babelHelpers.classCallCheck(this, Markers);
      this.friends = friends;
      this.map = map;
      this.allMarkers = [];
      this.handleMarkers();
    }

    babelHelpers.createClass(Markers, [{
      key: "handleMarkers",
      value: function handleMarkers() {
        /* add markercluster Plugin */
        // this mapbox's Plugins,you can get it to here ==> [ https://github.com/Leaflet/Leaflet.markercluster.git ]
        var markers = new L.markerClusterGroup({
          removeOutsideVisibleBounds: false,
          polygonOptions: {
            color: '#444444'
          }
        });

        for (var i = 0; i < this.friends.length; i++) {
          var path = void 0;
          var x = void 0;

          if (i % 2 === 0) {
            x = Number(Math.random());
          } else {
            x = -1 * Math.random();
          }

          var markerLatlng = L.latLng(37.769 + Math.random() / 170 * x, -122.446 + Math.random() / 150 * x);
          path = $(this.friends[i]).find('img').attr('src');
          var divContent = "<div class='in-map-markers'><div class='friend-icon'><img src='".concat(path, "' /></div></div>");
          var friendImg = L.divIcon({
            html: divContent,
            iconAnchor: [0, 0],
            className: ''
          });
          /* create new marker and add to map */

          var popupInfo = "<div class='friend-popup-info'><div class='detail'>info</div><h3>".concat($(this.friends[i]).find('.friend-name').html(), "</h3><p>").concat($(this.friends[i]).find('.friend-title').html(), "</p></div><i class='icon wb-chevron-right-mini'></i>");
          var marker = L.marker(markerLatlng, {
            title: $(this.friends[i]).find('friend-name').html(),
            icon: friendImg
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

        this.map.addLayer(markers);
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

  var AppLocation =
  /*#__PURE__*/
  function (_Site) {
    babelHelpers.inherits(AppLocation, _Site);

    function AppLocation() {
      babelHelpers.classCallCheck(this, AppLocation);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppLocation).apply(this, arguments));
    }

    babelHelpers.createClass(AppLocation, [{
      key: "initialize",
      value: function initialize() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppLocation.prototype), "initialize", this).call(this);
        this.window = $(window);
        this.$listItem = $('.app-location .page-aside .list-group');
        this.$allFriends = $('.app-location .friend-info');
        this.allFriends = this.getAllFriends();
        this.mapbox = new Map();
        this.map = this.mapbox.getMap();
        this.markers = new Markers(this.$allFriends, this.map);
        this.allMarkers = this.markers.getAllMarkers();
        this.markersInMap = null;
        this.friendNum = null; // states

        this.states = {
          mapChanged: true,
          listItemActive: false
        };
      }
    }, {
      key: "process",
      value: function process() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppLocation.prototype), "process", this).call(this);
        this.handleResize();
        this.steupListItem();
        this.steupMapChange();
        this.handleSearch();
      }
    }, {
      key: "getDefaultState",
      value: function getDefaultState() {
        return Object.assign(babelHelpers.get(babelHelpers.getPrototypeOf(AppLocation.prototype), "getDefaultState", this).call(this), {
          mapChange: true,
          listItemActive: false
        });
      }
    }, {
      key: "mapChange",
      value: function mapChange(change) {
        if (change) {
          console.log('map change');
        } else {
          var friendsInList = [];
          this.markersInMap = this.markers.getMarkersInMap();

          for (var i = 0; i < this.allMarkers.length; i++) {
            if (this.markersInMap.indexOf(i) === -1) {
              $(this.allFriends[i]).hide();
            } else {
              $(this.allFriends[i]).show();
              friendsInList.push($(this.allFriends[i]));
            }
          }

          this.friendsInList = friendsInList;
        }

        this.states.mapChanged = change;
      }
    }, {
      key: "listItemActive",
      value: function listItemActive(active) {
        if (active) {
          this.map.panTo(this.allMarkers[this.friendNum].getLatLng());
          this.allMarkers[this.friendNum].openPopup();
        } else {
          console.log('listItem unactive');
        }

        this.states.listItemActived = active;
      }
    }, {
      key: "getAllFriends",
      value: function getAllFriends() {
        var allFriends = [];
        this.$allFriends.each(function () {
          allFriends.push(this);
        });
        return allFriends;
      }
    }, {
      key: "steupListItem",
      value: function steupListItem() {
        var _this = this;

        var self = this;
        this.$allFriends.on('click', function () {
          $('.list-inline').on('click', function (event) {
            event.stopPropagation();
          });
          self.friendNum = self.allFriends.indexOf(this);
          self.listItemActive(true);
        });
        this.$allFriends.on('mouseup', function () {
          _this.listItemActive(false);
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
      key: "handleResize",
      value: function handleResize() {
        var _this3 = this;

        this.window.on('resize', function () {
          _this3.mapbox.handleMapHeight();
        });
      }
    }, {
      key: "handleSearch",
      value: function handleSearch() {
        var self = this;
        $('.search-friends input').on('focus', function () {
          $(this).on('keyup', function () {
            var inputName = $('.search-friends input').val();

            for (var i = 0; i < self.friendsInList.length; i++) {
              var friendName = self.friendsInList[i].find('.friend-name').html();

              if (inputName.length <= friendName.length) {
                for (var j = 1; j <= inputName.length; j++) {
                  if (inputName.substring(0, j).toLowerCase() === friendName.substring(0, j).toLowerCase()) {
                    self.friendsInList[i].show();
                  } else {
                    self.friendsInList[i].hide();
                  }
                }
              } else {
                self.friendsInList[i].hide();
              }
            }

            if (inputName === '') {
              for (var _i = 0; _i < self.friendsInList.length; _i++) {
                self.friendsInList[_i].show();
              }
            }
          });
        });
        $('.search-friends input').on('focusout', function () {
          $(this).off('keyup');
        });
      }
    }]);
    return AppLocation;
  }(_Site2.default);

  _exports.AppLocation = AppLocation;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppLocation();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppLocation;
  _exports.default = _default;
});