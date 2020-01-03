/**
 * FormValidation (http://formvalidation.io)
 * The best jQuery plugin to validate form fields. Support Bootstrap, Foundation, Pure, SemanticUI, UIKit and custom frameworks
 *
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */

/**
 * This class supports validating Pure framework (http://purecss.io/)
 */
(function($) {
    FormValidation.Framework.Pure = function(element, options) {
        options = $.extend(true, {
            button: {
                selector: '[type="submit"]:not([formnovalidate])',
                // The class of disabled button
                // http://purecss.io/buttons/#disabled-buttons
                disabled: 'pure-button-disabled'
            },
            err: {
                clazz: 'fv-help-block',
                parent: '^.*pure-control-group.*$'
            },
            // Pure doesn't support feedback icon
            icon: {
                valid: null,
                invalid: null,
                validating: null,
                feedback: 'fv-control-feedback'
            },
            row: {
                // http://purecss.io/forms/#aligned-form
                selector: '.pure-control-group',
                valid: 'fv-has-success',
                invalid: 'fv-has-error',
                feedback: 'fv-has-feedback'
            }
        }, options);

        FormValidation.Base.apply(this, [element, options]);
    };

    FormValidation.Framework.Pure.prototype = $.extend({}, FormValidation.Base.prototype, {
        /**
         * Specific framework might need to adjust the icon position
         *
         * @param {jQuery} $field The field element
         * @param {jQuery} $icon The icon element
         */
        _fixIcon: function($field, $icon) {
            var ns      = this._namespace,
                type    = $field.attr('type'),
                field   = $field.attr('data-' + ns + '-field'),
                $parent = $field.parent();

            if ('checkbox' === type || 'radio' === type) {
                if ($parent.is('label')) {
                    $icon.insertAfter($parent);
                }
            }
        }
    });
}(jQuery));
