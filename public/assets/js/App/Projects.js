(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/App/Projects", ["exports", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site);
    global.AppProjects = mod.exports;
  }
})(this, function (_exports, _Site2) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.run = run;
  _exports.getInstance = getInstance;
  _exports.default = _exports.AppProjects = void 0;
  _Site2 = babelHelpers.interopRequireDefault(_Site2);

  var AppProjects =
  /*#__PURE__*/
  function (_Site) {
    babelHelpers.inherits(AppProjects, _Site);

    function AppProjects() {
      babelHelpers.classCallCheck(this, AppProjects);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppProjects).apply(this, arguments));
    }

    babelHelpers.createClass(AppProjects, [{
      key: "initialize",
      value: function initialize() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppProjects.prototype), "initialize", this).call(this);
        this.handleSelective();
      }
    }, {
      key: "process",
      value: function process() {
        babelHelpers.get(babelHelpers.getPrototypeOf(AppProjects.prototype), "process", this).call(this);
        this.handleProject();
      }
    }, {
      key: "handleSelective",
      value: function handleSelective() {
        var members = [{
          id: 'uid_1',
          name: 'Herman Beck',
          img: '../../../../global/portraits/1.jpg'
        }, {
          id: 'uid_2',
          name: 'Mary Adams',
          img: '../../../../global/portraits/2.jpg'
        }, {
          id: 'uid_3',
          name: 'Caleb Richards',
          img: '../../../../global/portraits/3.jpg'
        }, {
          id: 'uid_4',
          name: 'June Lane',
          img: '../../../../global/portraits/4.jpg'
        }];
        var selected = [{
          id: 'uid_1',
          name: 'Herman Beck',
          img: '../../../../global/portraits/1.jpg'
        }, {
          id: 'uid_2',
          name: 'Caleb Richards',
          img: '../../../../global/portraits/2.jpg'
        }];
        $('.plugin-selective').selective({
          namespace: 'addMember',
          local: members,
          selected: selected,
          buildFromHtml: false,
          tpl: {
            optionValue: function optionValue(data) {
              return data.id;
            },
            frame: function frame() {
              return "<div class=\"".concat(this.namespace, "\">\n            ").concat(this.options.tpl.items.call(this), "\n          <div class=\"").concat(this.namespace, "-trigger\">\n            ").concat(this.options.tpl.triggerButton.call(this), "\n          <div class=\"").concat(this.namespace, "-trigger-dropdown\">\n            ").concat(this.options.tpl.list.call(this), "\n          </div>\n          </div>\n          </div>");
            },
            triggerButton: function triggerButton() {
              return "<div class=\"".concat(this.namespace, "-trigger-button\"><i class=\"wb-plus\"></i></div>");
            },
            listItem: function listItem(data) {
              return "<li class=\"".concat(this.namespace, "-list-item\"><img class=\"avatar\" src=\"").concat(data.img, "\">").concat(data.name, "</li>");
            },
            item: function item(data) {
              return "<li class=\"".concat(this.namespace, "-item\"><img class=\"avatar\" src=\"").concat(data.img, "\">").concat(this.options.tpl.itemRemove.call(this), "</li>");
            },
            itemRemove: function itemRemove() {
              return "<span class=\"".concat(this.namespace, "-remove\"><i class=\"wb-minus-circle\"></i></span>");
            },
            option: function option(data) {
              return "<option value=\"".concat(this.options.tpl.optionValue.call(this, data), "\">").concat(data.name, "</option>");
            }
          }
        });
      }
    }, {
      key: "handleProject",
      value: function handleProject() {
        $(document).on('click', '[data-tag=project-delete]', function (e) {
          bootbox.dialog({
            message: 'Do you want to delete the project?',
            buttons: {
              success: {
                label: 'Delete',
                className: 'btn-danger',
                callback: function callback() {// $(e.target).closest('.list-group-item').remove();
                }
              }
            }
          });
        });
      }
    }]);
    return AppProjects;
  }(_Site2.default);

  _exports.AppProjects = AppProjects;
  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppProjects();
    }

    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  var _default = AppProjects;
  _exports.default = _default;
});