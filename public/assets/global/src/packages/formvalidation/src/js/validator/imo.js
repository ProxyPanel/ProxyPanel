/**
 * imo validator
 *
 * @link        http://formvalidation.io/validators/imo/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            imo: {
                'default': 'Please enter a valid IMO number'
            }
        }
    });

    FormValidation.Validator.imo = {
        /**
         * Validate IMO (International Maritime Organization)
         *
         * @see http://en.wikipedia.org/wiki/IMO_Number
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Can consist of the following keys:
         * - message: The invalid message
         * @returns {Boolean}
         */
        validate: function(validator, $field, options, validatorName) {
            var value = validator.getFieldValue($field, validatorName);
            if (value === '') {
                return true;
            }

            if (!/^IMO \d{7}$/i.test(value)) {
                return false;
            }
            
            // Grab just the digits
            var sum    = 0,
                digits = value.replace(/^.*(\d{7})$/, '$1');
            for (var i = 6; i >= 1; i--) {
                sum += (digits.slice((6 - i), -i) * (i + 1));
            }

            return sum % 10 === parseInt(digits.charAt(6), 10);
        }
    };
}(jQuery));
