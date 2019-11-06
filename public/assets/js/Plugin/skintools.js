(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/Plugin/skintools", [], factory);
  } else if (typeof exports !== "undefined") {
    factory();
  } else {
    var mod = {
      exports: {}
    };
    factory();
    global.PluginSkintools = mod.exports;
  }
})(this, function () {
  "use strict";

  if (window.localStorage) {
    var getLevel = function getLevel(url, tag) {
      var arr = url.split('/').reverse();
      var level;
      var path = '';

      for (var i = 0; i < arr.length; i++) {
        if (arr[i] === tag) {
          level = i;
        }
      }

      for (var m = 1; m < level; m++) {
        path += '../';
      }

      return path;
    };

    var layout = 'iconbar';
    var settingsName = "remark.".concat(layout, ".skinTools");
    var settings = localStorage.getItem(settingsName);

    if (settings) {
      if (settings[0] === '{') {
        settings = JSON.parse(settings);
      }

      if (settings.primary && settings.primary !== 'primary') {
        var head = document.head;
        var link = document.createElement('link');
        link.type = 'text/css';
        link.rel = 'stylesheet';
        link.href = "".concat(getLevel(window.location.pathname, layout), "assets/skins/").concat(settings.primary, ".css");
        link.id = 'skinStyle';
        head.appendChild(link);
      }

      if (settings.sidebar && settings.sidebar === 'dark') {
        var menubarFn = setInterval(function () {
          var menubar = document.getElementsByClassName('site-menubar');

          if (menubar.length > 0) {
            clearInterval(menubarFn);
            menubar[0].className += ' site-menubar-dark';
          }
        }, 5);
      }

      var navbarFn = setInterval(function () {
        var navbar = document.getElementsByClassName('site-navbar');

        if (navbar.length > 0) {
          clearInterval(navbarFn);

          if (settings.navbar && settings.navbar !== 'primary') {
            navbar[0].className += " bg-".concat(settings.navbar, "-600");
          }

          if (settings.navbarInverse && settings.navbarInverse !== 'false') {
            navbar[0].className += ' navbar-inverse';
          }
        }
      }, 5);
    }

    if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', function () {
        var $body = $(document.body);
        var $doc = $(document);
        var $win = $(window);
        var Storage = {
          set: function set(key, value) {
            if (!window.localStorage) {
              return null;
            }

            if (!key || !value) {
              return null;
            }

            if (Object(value) === value) {
              value = JSON.stringify(value);
            }

            localStorage.setItem(key, value);
          },
          get: function get(key) {
            if (!window.localStorage) {
              return null;
            }

            var value = localStorage.getItem(key);

            if (!value) {
              return null;
            }

            if (value[0] === '{') {
              value = JSON.parse(value);
            }

            return value;
          }
        };
        var Skintools = {
          tpl: '<div class="site-skintools">' + '<div class="site-skintools-inner">' + '<div class="site-skintools-toggle">' + '<i class="icon wb-settings primary-600"></i>' + '</div>' + '<div class="site-skintools-content">' + '<div class="nav-tabs-horizontal">' + '<ul role="tablist" class="nav nav-tabs nav-tabs-line">' + '<li role="presentation" class="nav-item"><a class="nav-link active" role="tab" aria-controls="skintoolsSidebar" href="#skintoolsSidebar" data-toggle="tab" aria-expanded="true">Sidebar</a></li>' + '<li class="nav-item" role="presentation"><a class="nav-link" role="tab" aria-controls="skintoolsNavbar" href="#skintoolsNavbar" data-toggle="tab" aria-expanded="false">Navbar</a></li>' + '<li class="nav-item" role="presentation"><a class="nav-link" role="tab" aria-controls="skintoolsPrimary" href="#skintoolsPrimary" data-toggle="tab" aria-expanded="false">Primary</a></li>' + '</ul>' + '<div class="tab-content">' + '<div role="tabpanel" id="skintoolsSidebar" class="tab-pane active"></div>' + '<div role="tabpanel" id="skintoolsNavbar" class="tab-pane"></div>' + '<div role="tabpanel" id="skintoolsPrimary" class="tab-pane"></div>' + '<button class="btn btn-outline btn-block btn-primary mt-20" id="skintoolsReset" type="button">Reset</button>' + '</div>' + '</div>' + '</div>' + '</div>' + '</div>',
          skintoolsSidebar: ['dark', 'light'],
          skintoolsNavbar: ['primary', 'brown', 'cyan', 'green', 'grey', 'indigo', 'orange', 'pink', 'purple', 'red', 'teal', 'yellow'],
          navbarSkins: 'bg-primary-600 bg-brown-600 bg-cyan-600 bg-green-600 bg-grey-600 bg-indigo-600 bg-orange-600 bg-pink-600 bg-purple-600 bg-red-600 bg-teal-600 bg-yellow-700',
          skintoolsPrimary: ['primary', 'brown', 'cyan', 'green', 'grey', 'indigo', 'orange', 'pink', 'purple', 'red', 'teal', 'yellow'],
          storageKey: settingsName,
          defaultSettings: {
            sidebar: 'light',
            navbar: 'indigo',
            navbarInverse: 'true',
            primary: 'indigo'
          },
          init: function init() {
            var self = this;
            this.path = getLevel(window.location.pathname, layout);
            this.overflow = false;
            this.$siteSidebar = $('.site-menubar');
            this.$siteNavbar = $('.site-navbar');
            this.$container = $(this.tpl);
            this.$toggle = $('.site-skintools-toggle', this.$container);
            this.$content = $('.site-skintools-content', this.$container);
            this.$tabContent = $('.tab-content', this.$container);
            this.$sidebar = $('#skintoolsSidebar', this.$content);
            this.$navbar = $('#skintoolsNavbar', this.$content);
            this.$primary = $('#skintoolsPrimary', this.$content);
            this.build(this.$sidebar, this.skintoolsSidebar, 'skintoolsSidebar', 'radio', 'Sidebar Skins');
            this.build(this.$navbar, ['inverse'], 'skintoolsNavbar', 'checkbox', 'Navbar Type');
            this.build(this.$navbar, this.skintoolsNavbar, 'skintoolsNavbar', 'radio', 'Navbar Skins');
            this.build(this.$primary, this.skintoolsPrimary, 'skintoolsPrimary', 'radio', 'Primary Skins');
            this.$container.appendTo($body);
            this.$toggle.on('click', function () {
              self.$container.toggleClass('is-open');
            });
            $('#skintoolsSidebar input').on('click', function () {
              self.sidebarEvents(this);
            });
            $('#skintoolsNavbar input').on('click', function () {
              self.navbarEvents(this);
            });
            $('#skintoolsPrimary input').on('click', function () {
              self.primaryEvents(this);
            });
            $('#skintoolsReset').on('click', function () {
              self.reset();
            });
            this.initLocalStorage();
          },
          initLocalStorage: function initLocalStorage() {
            var self = this;
            this.settings = Storage.get(this.storageKey);

            if (this.settings === null) {
              this.settings = $.extend(true, {}, this.defaultSettings);
              Storage.set(this.storageKey, this.settings);
            }

            if (this.settings && $.isPlainObject(this.settings)) {
              $.each(this.settings, function (n, v) {
                switch (n) {
                  case 'sidebar':
                    $("input[value=\"".concat(v, "\"]"), self.$sidebar).prop('checked', true);
                    self.sidebarImprove(v);
                    break;

                  case 'navbar':
                    $("input[value=\"".concat(v, "\"]"), self.$navbar).prop('checked', true);
                    self.navbarImprove(v);
                    break;

                  case 'navbarInverse':
                    var flag = v !== 'false';
                    $('input[value="inverse"]', self.$navbar).prop('checked', flag);
                    self.navbarImprove('inverse', flag);
                    break;

                  case 'primary':
                    $("input[value=\"".concat(v, "\"]"), self.$primary).prop('checked', true);
                    self.primaryImprove(v);
                    break;
                }
              });
            }
          },
          updateSetting: function updateSetting(item, value) {
            this.settings[item] = value;
            Storage.set(this.storageKey, this.settings);
          },
          title: function title(content) {
            return $("<h4 class=\"site-skintools-title\">".concat(content, "</h4>"));
          },
          item: function item(type, name, id, content) {
            var item = "<div class=\"".concat(type, "-custom ").concat(type, "-").concat(content, "\"><input id=\"").concat(id, "\" type=\"").concat(type, "\" name=\"").concat(name, "\" value=\"").concat(content, "\"><label for=\"").concat(id, "\">").concat(content, "</label></div>");
            return $(item);
          },
          build: function build($wrap, data, name, type, title) {
            if (title) {
              this.title(title).appendTo($wrap);
            }

            for (var i = 0; i < data.length; i++) {
              this.item(type, name, "".concat(name, "-").concat(data[i]), data[i]).appendTo($wrap);
            }
          },
          sidebarEvents: function sidebarEvents(self) {
            var val = $(self).val();
            this.sidebarImprove(val);
            this.updateSetting('sidebar', val);
          },
          navbarEvents: function navbarEvents(self) {
            var val = $(self).val();
            var checked = $(self).prop('checked');
            this.navbarImprove(val, checked);

            if (val === 'inverse') {
              this.updateSetting('navbarInverse', checked.toString());
            } else {
              this.updateSetting('navbar', val);
            }
          },
          primaryEvents: function primaryEvents(self) {
            var val = $(self).val();
            this.primaryImprove(val);
            this.updateSetting('primary', val);
          },
          sidebarImprove: function sidebarImprove(val) {
            if (val === 'light') {
              // this.$siteSidebar.removeClass('site-menubar-dark');
              this.$siteSidebar.removeClass('site-menubar-dark').addClass("site-menubar-".concat(val));
            } else if (val === 'dark') {
              // this.$siteSidebar.addClass('site-menubar-' + val);
              this.$siteSidebar.removeClass('site-menubar-light');
            }
          },
          navbarImprove: function navbarImprove(val, checked) {
            if (val === 'inverse') {
              checked ? this.$siteNavbar.addClass('navbar-inverse') : this.$siteNavbar.removeClass('navbar-inverse');
            } else {
              var bg = "bg-".concat(val, "-600");

              if (val === 'yellow') {
                bg = 'bg-yellow-700';
              }

              if (val === 'primary') {
                bg = '';
              }

              this.$siteNavbar.removeClass(this.navbarSkins).addClass(bg);
            }
          },
          primaryImprove: function primaryImprove(val) {
            var $link = $('#skinStyle', $('head'));
            var href = "".concat(this.path, "assets/skins/").concat(val, ".css");

            if (val === 'primary') {
              $link.remove();
              return;
            }

            if ($link.length === 0) {
              $('head').append("<link id=\"skinStyle\" href=\"".concat(href, "\" rel=\"stylesheet\" type=\"text/css\"/>"));
            } else {
              $link.attr('href', href);
            }
          },
          reset: function reset() {
            localStorage.clear();
            this.initLocalStorage();
          }
        };
        Skintools.init();
      });
    }
  }
});