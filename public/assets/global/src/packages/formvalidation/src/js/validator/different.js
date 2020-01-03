/**
 * different validator
 *
 * @link        http://formvalidation.io/validators/different/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            different: {
                'default': 'Please enter a different value'
            }
        }
    });

    FormValidation.Validator.different = {
        html5Attributes: {
            message: 'message',
            field: 'field'
        },

        /**
         * Bind the validator on the live change of the field to compare with current one
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Consists of the following key:
         * - field: The name of field that will be used to compare with current one
         */
        init: function(validator, $field, options, validatorName) {
            var fields = options.field.split(',');
            for (var i = 0; i < fields.length; i++) {
                var compareWith = validator.getFieldElements($.trim(fields[i]));
                validator.onLiveChange(compareWith, 'live_' + validatorName, function() {
                    var status = validator.getStatus($field, validatorName);
                    if (status !== validator.STATUS_NOT_VALIDATED) {
                        validator.revalidateField($field);
                    }
                });
            }
        },

        /**
         * Unbind the validator on the live change of the field to compare with current one
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Consists of the following key:
         * - field: The name of field that will be used to compare with current one
         */
        destroy: function(validator, $field, options, validatorName) {
            var fields = options.field.split(',');
            for (var i = 0; i < fields.length; i++) {
                var compareWith = validator.getFieldElements($.trim(fields[i]));
                validator.offLiveChange(compareWith, 'live_' + validatorName);
            }
        },

        /**
         * Return true if the input value is different with given field's value
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Consists of the following key:
         * - field: The name of field that will be used to compare with current one
         * - message: The invalid message
         * @returns {Boolean}
         */
        validate: function(validator, $field, options, validatorName) {
            var value = validator.getFieldValue($field, validatorName);
            if (value === '') {
                return true;
            }

            var fields  = options.field.split(','),
                isValid = true;

            for (var i = 0; i < fields.length; i++) {
                var compareWith = validator.getFieldElements($.trim(fields[i]));
                if (compareWith == null || compareWith.length === 0) {
                    continue;
                }

                var compareValue = validator.getFieldValue(compareWith, validatorName);
                if (value === compareValue) {
                    isValid = false;
                } else if (compareValue !== '') {
                    validator.updateStatus(compareWith, validator.STATUS_VALID, validatorName);
                }
            }

            return isValid;
        }
    };
}(jQuery));
