(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define("/forms/validation", ["jquery", "Site"], factory);
  } else if (typeof exports !== "undefined") {
    factory(require("jquery"), require("Site"));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsValidation = mod.exports;
  }
})(this, function (_jquery, _Site) {
  "use strict";

  _jquery = babelHelpers.interopRequireDefault(_jquery);
  (0, _jquery.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  }); // Example Validataion Full
  // ------------------------

  (function () {
    (0, _jquery.default)('#exampleFullForm').formValidation({
      framework: "bootstrap4",
      button: {
        selector: '#validateButton1',
        disabled: 'disabled'
      },
      icon: null,
      fields: {
        username: {
          validators: {
            notEmpty: {
              message: 'The username is required'
            },
            stringLength: {
              min: 6,
              max: 30
            },
            regexp: {
              regexp: /^[a-zA-Z0-9]+$/
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: 'The username is required'
            },
            emailAddress: {
              message: 'The email address is not valid'
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: 'The password is required'
            },
            stringLength: {
              min: 8
            }
          }
        },
        birthday: {
          validators: {
            notEmpty: {
              message: 'The birthday is required'
            },
            date: {
              format: 'YYYY/MM/DD'
            }
          }
        },
        github: {
          validators: {
            url: {
              message: 'The url is not valid'
            }
          }
        },
        skills: {
          validators: {
            notEmpty: {
              message: 'The skills is required'
            },
            stringLength: {
              max: 300
            }
          }
        },
        porto_is: {
          validators: {
            notEmpty: {
              message: 'Please specify at least one'
            }
          }
        },
        'for[]': {
          validators: {
            notEmpty: {
              message: 'Please specify at least one'
            }
          }
        },
        company: {
          validators: {
            notEmpty: {
              message: 'Please company'
            }
          }
        },
        browsers: {
          validators: {
            notEmpty: {
              message: 'Please specify at least one browser you use daily for development'
            }
          }
        }
      },
      err: {
        clazz: 'invalid-feedback'
      },
      control: {
        // The CSS class for valid control
        valid: 'is-valid',
        // The CSS class for invalid control
        invalid: 'is-invalid'
      },
      row: {
        invalid: 'has-danger'
      }
    });
  })(); // Example Validataion Constraints
  // -------------------------------


  (function () {
    (0, _jquery.default)('#exampleConstraintsForm, #exampleConstraintsFormTypes').formValidation({
      framework: "bootstrap4",
      icon: null,
      fields: {
        type_email: {
          validators: {
            emailAddress: {
              message: 'The email address is not valid'
            }
          }
        },
        type_url: {
          validators: {
            url: {
              message: 'The url is not valid'
            }
          }
        },
        type_digits: {
          validators: {
            digits: {
              message: 'The value is not digits'
            }
          }
        },
        type_numberic: {
          validators: {
            integer: {
              message: 'The value is not an number'
            }
          }
        },
        type_phone: {
          validators: {
            phone: {
              message: 'The value is not an phone(US)'
            }
          }
        },
        type_credit_card: {
          validators: {
            creditCard: {
              message: 'The credit card number is not valid'
            }
          }
        },
        type_date: {
          validators: {
            date: {
              format: 'YYYY/MM/DD'
            }
          }
        },
        type_color: {
          validators: {
            color: {
              type: ['hex', 'hsl', 'hsla', 'keyword', 'rgb', 'rgba'],
              // The default value for type
              message: 'The value is not valid color'
            }
          }
        },
        type_ip: {
          validators: {
            ip: {
              ipv4: true,
              ipv6: true,
              message: 'The value is not valid ip(v4 or v6)'
            }
          }
        }
      },
      err: {
        clazz: 'invalid-feedback'
      },
      control: {
        // The CSS class for valid control
        valid: 'is-valid',
        // The CSS class for invalid control
        invalid: 'is-invalid'
      },
      row: {
        invalid: 'has-danger'
      }
    });
  })(); // Example Validataion Standard Mode
  // ---------------------------------


  (function () {
    (0, _jquery.default)('#exampleStandardForm').formValidation({
      framework: "bootstrap4",
      button: {
        selector: '#validateButton2',
        disabled: 'disabled'
      },
      icon: null,
      fields: {
        standard_fullName: {
          validators: {
            notEmpty: {
              message: 'The full name is required and cannot be empty'
            }
          }
        },
        standard_email: {
          validators: {
            notEmpty: {
              message: 'The email address is required and cannot be empty'
            },
            emailAddress: {
              message: 'The email address is not valid'
            }
          }
        },
        standard_content: {
          validators: {
            notEmpty: {
              message: 'The content is required and cannot be empty'
            },
            stringLength: {
              max: 500,
              message: 'The content must be less than 500 characters long'
            }
          }
        }
      },
      err: {
        clazz: 'invalid-feedback'
      },
      control: {
        // The CSS class for valid control
        valid: 'is-valid',
        // The CSS class for invalid control
        invalid: 'is-invalid'
      },
      row: {
        invalid: 'has-danger'
      }
    });
  })(); // Example Validataion Summary Mode
  // -------------------------------


  (function () {
    (0, _jquery.default)('.summary-errors').hide();
    (0, _jquery.default)('#exampleSummaryForm').formValidation({
      framework: "bootstrap4",
      button: {
        selector: '#validateButton3',
        disabled: 'disabled'
      },
      icon: null,
      fields: {
        summary_fullName: {
          validators: {
            notEmpty: {
              message: 'The full name is required and cannot be empty'
            }
          }
        },
        summary_email: {
          validators: {
            notEmpty: {
              message: 'The email address is required and cannot be empty'
            },
            emailAddress: {
              message: 'The email address is not valid'
            }
          }
        },
        summary_content: {
          validators: {
            notEmpty: {
              message: 'The content is required and cannot be empty'
            },
            stringLength: {
              max: 500,
              message: 'The content must be less than 500 characters long'
            }
          }
        }
      },
      err: {
        clazz: 'invalid-feedback'
      },
      control: {
        // The CSS class for valid control
        valid: 'is-valid',
        // The CSS class for invalid control
        invalid: 'is-invalid'
      },
      row: {
        invalid: 'has-danger'
      }
    }).on('success.form.fv', function (e) {
      // Reset the message element when the form is valid
      (0, _jquery.default)('.summary-errors').html('');
    }).on('err.field.fv', function (e, data) {
      // data.fv     --> The FormValidation instance
      // data.field  --> The field name
      // data.element --> The field element
      (0, _jquery.default)('.summary-errors').show(); // Get the messages of field

      var messages = data.fv.getMessages(data.element); // Remove the field messages if they're already available

      (0, _jquery.default)('.summary-errors').find('li[data-field="' + data.field + '"]').remove(); // Loop over the messages

      for (var i in messages) {
        // Create new 'li' element to show the message
        (0, _jquery.default)('<li/>').attr('data-field', data.field).wrapInner((0, _jquery.default)('<a/>').attr('href', 'javascript: void(0);') // .addClass('alert alert-danger alert-dismissible')
        .html(messages[i]).on('click', function (e) {
          // Focus on the invalid field
          data.element.focus();
        })).appendTo('.summary-errors > ul');
      } // Hide the default message
      // $field.data('fv.messages') returns the default element containing the messages


      data.element.data('fv.messages').find('.invalid-feedback[data-fv-for="' + data.field + '"]').hide();
    }).on('success.field.fv', function (e, data) {
      // Remove the field messages
      (0, _jquery.default)('.summary-errors > ul').find('li[data-field="' + data.field + '"]').remove();

      if ((0, _jquery.default)('#exampleSummaryForm').data('formValidation').isValid()) {
        (0, _jquery.default)('.summary-errors').hide();
      }
    });
  })();
});