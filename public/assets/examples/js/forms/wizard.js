(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/forms/wizard", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsWizard = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Wizard Form
  // -------------------

  (function () {
    // set up formvalidation
    (0, _jquery.default)('#exampleAccountForm').formValidation({
      framework: 'bootstrap',
      fields: {
        username: {
          validators: {
            notEmpty: {
              message: 'The username is required'
            },
            stringLength: {
              min: 6,
              max: 30,
              message: 'The username must be more than 6 and less than 30 characters long'
            },
            regexp: {
              regexp: /^[a-zA-Z0-9_\.]+$/,
              message: 'The username can only consist of alphabetical, number, dot and underscore'
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: 'The password is required'
            },
            different: {
              field: 'username',
              message: 'The password cannot be the same as username'
            }
          }
        }
      },
      err: {
        clazz: 'text-help'
      },
      row: {
        invalid: 'has-danger'
      }
    });
    (0, _jquery.default)("#exampleBillingForm").formValidation({
      framework: 'bootstrap',
      fields: {
        number: {
          validators: {
            notEmpty: {
              message: 'The credit card number is required' // creditCard: {
              //   message: 'The credit card number is not valid'
              // }

            }
          }
        },
        cvv: {
          validators: {
            notEmpty: {
              message: 'The CVV number is required' // cvv: {
              //   creditCardField: 'number',
              //   message: 'The CVV number is not valid'
              // }

            }
          }
        }
      },
      err: {
        clazz: 'text-help'
      },
      row: {
        invalid: 'has-danger'
      }
    }); // init the wizard

    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      buttonsAppendTo: '.panel-body'
    });

    var wizard = (0, _jquery.default)("#exampleWizardForm").wizard(options).data('wizard'); // setup validator
    // http://formvalidation.io/api/#is-valid

    wizard.get("#exampleAccount").setValidator(function () {
      var fv = (0, _jquery.default)("#exampleAccountForm").data('formValidation');
      fv.validate();

      if (!fv.isValid()) {
        return false;
      }

      return true;
    });
    wizard.get("#exampleBilling").setValidator(function () {
      var fv = (0, _jquery.default)("#exampleBillingForm").data('formValidation');
      fv.validate();

      if (!fv.isValid()) {
        return false;
      }

      return true;
    });
  })(); // Example Wizard Form Container
  // -----------------------------
  // http://formvalidation.io/api/#is-valid-container


  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      onInit: function onInit() {
        (0, _jquery.default)('#exampleFormContainer').formValidation({
          framework: 'bootstrap',
          fields: {
            username: {
              validators: {
                notEmpty: {
                  message: 'The username is required'
                }
              }
            },
            password: {
              validators: {
                notEmpty: {
                  message: 'The password is required'
                }
              }
            },
            number: {
              validators: {
                notEmpty: {
                  message: 'The credit card number is not valid'
                }
              }
            },
            cvv: {
              validators: {
                notEmpty: {
                  message: 'The CVV number is required'
                }
              }
            }
          },
          err: {
            clazz: 'text-help'
          },
          row: {
            invalid: 'has-danger'
          }
        });
      },
      validator: function validator() {
        var fv = (0, _jquery.default)('#exampleFormContainer').data('formValidation');
        var $this = (0, _jquery.default)(this); // Validate the container

        fv.validateContainer($this);
        var isValidStep = fv.isValidContainer($this);

        if (isValidStep === false || isValidStep === null) {
          return false;
        }

        return true;
      },
      onFinish: function onFinish() {// $('#exampleFormContainer').submit();
      },
      buttonsAppendTo: '.panel-body'
    });

    (0, _jquery.default)("#exampleWizardFormContainer").wizard(options);
  })(); // Example Wizard Pager
  // --------------------------


  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      step: '.wizard-pane',
      templates: {
        buttons: function buttons() {
          var options = this.options;
          var html = '<div class="btn-group btn-group-sm">' + '<a class="btn btn-default btn-outline" href="#' + this.id + '" data-wizard="back" role="button">' + options.buttonLabels.back + '</a>' + '<a class="btn btn-success btn-outline float-right" href="#' + this.id + '" data-wizard="finish" role="button">' + options.buttonLabels.finish + '</a>' + '<a class="btn btn-default btn-outline float-right" href="#' + this.id + '" data-wizard="next" role="button">' + options.buttonLabels.next + '</a>' + '</div>';
          return html;
        }
      },
      buttonLabels: {
        next: '<i class="icon wb-chevron-right" aria-hidden="true"></i>',
        back: '<i class="icon wb-chevron-left" aria-hidden="true"></i>',
        finish: '<i class="icon wb-check" aria-hidden="true"></i>'
      },
      buttonsAppendTo: '.panel-actions'
    });

    (0, _jquery.default)("#exampleWizardPager").wizard(options);
  })(); // Example Wizard Progressbar
  // --------------------------


  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      step: '.wizard-pane',
      onInit: function onInit() {
        this.$progressbar = this.$element.find('.progress-bar').addClass('progress-bar-striped');
      },
      onBeforeShow: function onBeforeShow(step) {
        step.$element.tab('show');
      },
      onFinish: function onFinish() {
        this.$progressbar.removeClass('progress-bar-striped').addClass('progress-bar-success');
      },
      onAfterChange: function onAfterChange(prev, step) {
        var total = this.length();
        var current = step.index + 1;
        var percent = current / total * 100;
        this.$progressbar.css({
          width: percent + '%'
        }).find('.sr-only').text(current + '/' + total);
      },
      buttonsAppendTo: '.panel-body'
    });

    (0, _jquery.default)("#exampleWizardProgressbar").wizard(options);
  })(); // Example Wizard Tabs
  // -------------------


  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      step: '> .nav > li > a',
      onBeforeShow: function onBeforeShow(step) {
        step.$element.tab('show');
      },
      classes: {
        step: {
          //done: 'color-done',
          error: 'color-error'
        }
      },
      onFinish: function onFinish() {
        alert('finish');
      },
      buttonsAppendTo: '.tab-content'
    });

    (0, _jquery.default)("#exampleWizardTabs").wizard(options);
  })(); // Example Wizard Accordion
  // ------------------------


  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery.default.extend(true, {}, defaults, {
      step: '.panel-title[data-toggle="collapse"]',
      classes: {
        step: {
          //done: 'color-done',
          error: 'color-error'
        }
      },
      templates: {
        buttons: function buttons() {
          return '<div class="panel-footer">' + defaults.templates.buttons.call(this) + '</div>';
        }
      },
      onBeforeShow: function onBeforeShow(step) {
        step.$pane.collapse('show');
      },
      onBeforeHide: function onBeforeHide(step) {
        step.$pane.collapse('hide');
      },
      onFinish: function onFinish() {
        alert('finish');
      },
      buttonsAppendTo: '.panel-collapse'
    });

    (0, _jquery.default)("#exampleWizardAccordion").wizard(options);
  })();
});