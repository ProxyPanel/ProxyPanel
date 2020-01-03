/**
 * numeric validator
 *
 * @link        http://formvalidation.io/validators/numeric/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            numeric: {
                'default': 'Please enter a valid float number'
            }
        }
    });

    FormValidation.Validator.numeric = {
        html5Attributes: {
            message: 'message',
            separator: 'separator',     // deprecated
            thousandsseparator: 'thousandsSeparator',
            decimalseparator: 'decimalSeparator'
        },

        enableByHtml5: function($field) {
            return ('number' === $field.attr('type')) && ($field.attr('step') !== undefined) && ($field.attr('step') % 1 !== 0);
        },

        /**
         * Validate decimal number
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Consist of key:
         * - message: The invalid message
         * - thousandsSeparator: The thousands separator. It's empty by default
         * - separator, decimalSeparator: The decimal separator. It's '.' by default
         * The separator option is deprecated and should be replaced with decimalSeparator
         * @returns {Boolean}
         */
        validate: function(validator, $field, options, validatorName) {
            if (this.enableByHtml5($field) && $field.get(0).validity && $field.get(0).validity.badInput === true) {
                return false;
            }

            var value = validator.getFieldValue($field, validatorName);
            if (value === '') {
                return true;
            }

            var decimalSeparator   = options.separator || options.decimalSeparator || '.',
                thousandsSeparator = options.thousandsSeparator || '';

            // Support preceding zero numbers such as .5, -.5
            if (value.substr(0, 1) === decimalSeparator) {
                value = '0' + decimalSeparator + value.substr(1);
            } else if (value.substr(0, 2) === '-' + decimalSeparator) {
                value = '-0' + decimalSeparator + value.substr(2);
            }

            decimalSeparator   = (decimalSeparator   === '.') ? '\\.' : decimalSeparator;
            thousandsSeparator = (thousandsSeparator === '.') ? '\\.' : thousandsSeparator;

            var testRegexp         = new RegExp('^-?[0-9]{1,3}(' + thousandsSeparator + '[0-9]{3})*(' + decimalSeparator + '[0-9]+)?$'),
                thousandsReplacer  = new RegExp(thousandsSeparator, 'g');

            if (!testRegexp.test(value)) {
                return false;
            }

            // Replace thousands separator with blank
            if (thousandsSeparator) {
                value = value.replace(thousandsReplacer, '');
            }
            // Replace decimal separator with a dot
            if (decimalSeparator) {
                value = value.replace(decimalSeparator, '.');
            }

            return !isNaN(parseFloat(value)) && isFinite(value);
        }
    };
}(jQuery));
