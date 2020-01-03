/**
 * promise validator
 *
 * @link        http://formvalidation.io/validators/promise/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            promise: {
                'default': 'Please enter a valid value'
            }
        }
    });

    FormValidation.Validator.promise = {
        priority: 999,

        html5Attributes: {
            message: 'message',
            promise: 'promise'
        },

        /**
         * Return result from a jQuery's Deferred object
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Can consist of the following keys:
         * - promise: The method that passes parameters:
         *      promise: function(fieldValue, validator, $field) {
         *          // fieldValue is the value of field
         *          // validator is instance of FormValidation.Base
         *          // $field is the field element
         *
         *          var dfd = new $.Deferred();
         *
         *          // Do something ...
         *
         *          // Resolve when particular task is done
         *          dfd.resolve({
         *              valid: true or false,       // Required
         *              message: 'Other message',   // Optional
         *              key: value                  // You can attach more data to reuse later
         *          });
         *
         *          // You can reject if there's error
         *          dfd.reject({
         *              message: 'Other message',   // Optional
         *              key: value                  // You can attach more data to reuse later
         *          });
         *
         *          return dfd.promise();
         *      }
         * - message: The invalid message
         * @returns {Deferred}
         */
        validate: function(validator, $field, options, validatorName) {
            var value   = validator.getFieldValue($field, validatorName),
                dfd     = new $.Deferred(),
                promise = FormValidation.Helper.call(options.promise, [value, validator, $field]);

            promise
                .done(function(result) {
                    dfd.resolve($field, validatorName, result);
                })
                .fail(function(result) {
                    result = result || {};
                    result.valid = false;
                    dfd.resolve($field, validatorName, result);
                });

            return dfd;
        }
    };
}(jQuery));
