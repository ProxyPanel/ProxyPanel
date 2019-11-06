(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/pages/code-editor", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.pagesCodeEditor = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);

  var _items;

  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Treeview
  // ---------

  (function () {
    var data = [{
      text: 'assets',
      href: '#assets',
      state: {
        expanded: false
      },
      nodes: [{
        text: 'css',
        href: '#css',
        nodes: [{
          text: 'bootstrap.css',
          href: '#bootstrap.css',
          icon: 'fa fa-file-code-o'
        }, {
          text: 'site.css',
          href: '#site.css',
          icon: 'fa fa-file-code-o'
        }]
      }, {
        text: 'fonts',
        href: '#fonts',
        nodes: [{
          text: 'font-awesome',
          href: '#font-awesome'
        }, {
          text: 'web-icons',
          href: '#web-icons'
        }]
      }, {
        text: 'images',
        href: '#images',
        nodes: [{
          text: 'logo.png',
          href: '#logo.png',
          icon: 'fa fa-file-photo-o'
        }, {
          text: 'bg.png',
          href: '#bg.png',
          icon: 'fa fa-file-photo-o'
        }]
      }]
    }, {
      text: 'grunt',
      href: '#grunt',
      state: {
        expanded: false
      },
      nodes: [{
        text: 'autoprefixer.js',
        href: '#autoprefixer.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'clean.js',
        href: '#clean.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'concat.js',
        href: '#concat.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'csscomb.js',
        href: '#csscomb.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'cssmin.js',
        href: '#cssmin.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'less.js',
        href: '#less.js',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'uglify.js',
        href: '#uglify.js',
        icon: 'fa fa-file-code-o'
      }]
    }, {
      text: 'html',
      href: '#html',
      state: {
        expanded: true
      },
      nodes: [{
        text: 'blog.html',
        href: '#blog.html',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'docs.html',
        href: '#docs.html',
        icon: 'fa fa-file-code-o'
      }, {
        text: 'index.html',
        href: '#index.html',
        state: {
          selected: true
        },
        icon: 'fa fa-file-code-o'
      }]
    }, {
      text: 'media',
      href: '#media',
      state: {
        expanded: false
      },
      nodes: [{
        text: 'audio.mp3',
        href: '#audio.mp3',
        icon: 'fa fa-file-audio-o'
      }, {
        text: 'video.mp4',
        href: '#video.mp4',
        icon: 'fa fa-file-video-o'
      }]
    }, {
      text: 'Gruntfile.js',
      href: '#Gruntfile.js',
      icon: 'fa fa-file-code-o'
    }, {
      text: 'bower.json',
      href: '#bower.json',
      icon: 'fa fa-file-code-o'
    }, {
      text: 'README.pdf',
      href: '#README.pdf',
      icon: 'fa fa-file-pdf-o'
    }, {
      text: 'package.json',
      href: '#package.json',
      icon: 'fa fa-file-code-o'
    }];
    var defaults = Plugin.getDefaults("treeview");

    var options = _jquery.default.extend({}, defaults, {
      levels: 1,
      color: false,
      backColor: false,
      borderColor: false,
      onhoverColor: false,
      selectedColor: false,
      selectedBackColor: false,
      searchResultColor: false,
      searchResultBackColor: false,
      data: data,
      highlightSelected: true
    });

    (0, _jquery.default)('#filesTree').treeview(options);
  })(); // Codemirror
  // ----------


  CodeMirror.fromTextArea(document.getElementById('code'), {
    lineNumbers: !0,
    theme: 'eclipse',
    mode: 'text/html',
    scrollbarStyle: "simple"
  }); // Contextmenu
  // -----------

  _jquery.default.contextMenu({
    selector: '#filesTree',
    // callback: function(key, options) {
    //   var m = "clicked: " + key;
    //   window.console && console.log(m) || alert(m);
    // },
    items: (_items = {
      "rename": {
        name: "Rename ...",
        icon: function icon() {
          return 'context-menu-icon context-menu-extend-icon wb-pencil';
        }
      },
      "search": {
        name: "Find in...",
        icon: function icon() {
          return 'context-menu-icon context-menu-extend-icon wb-search';
        }
      },
      "sep1": "---------",
      "new": {
        name: "New File",
        icon: function icon() {
          return 'context-menu-icon context-menu-extend-icon wb-file';
        }
      },
      "new-folder": {
        name: "New Folder",
        icon: function icon() {
          return 'context-menu-icon context-menu-extend-icon wb-folder';
        }
      }
    }, babelHelpers.defineProperty(_items, "sep1", "---------"), babelHelpers.defineProperty(_items, "delete", {
      name: "Delete",
      icon: function icon() {
        return 'context-menu-icon context-menu-extend-icon wb-close';
      }
    }), _items)
  });
});