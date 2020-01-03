/**
 * FormValidation (http://formvalidation.io)
 * The best jQuery plugin to validate form fields. Support Bootstrap, Foundation, Pure, SemanticUI, UIKit and custom frameworks
 *
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */

/**
 * This class supports validating Foundation v6+ form (http://foundation.zurb.com/)
 */
/* global Foundation: false */
(function($) {
    FormValidation.Framework.Foundation = function(element, options) {
        options = $.extend(true, {
            button: {
                selector: '[type="submit"]:not([formnovalidate])',
                // The class for disabled button
                // http://foundation.zurb.com/docs/components/buttons.html#button-colors
                disabled: 'disabled'
            },
            err: {
                // http://foundation.zurb.com/sites/docs/abide.html
                clazz: 'form-error',
                parent: '^.*((small|medium|large)-[0-9]+)\\s.*(columns).*$'
            },
            // Foundation doesn't support feedback icon
            icon: {
                valid: null,
                invalid: null,
                validating: null,
                feedback: 'fv-control-feedback'
            },
            row: {
                selector: '.row',
                valid: 'fv-has-success',
                invalid: 'fv-has-error',
                feedback: 'fv-has-feedback'
            }
        }, options);

        FormValidation.Base.apply(this, [element, options]);
    };

    FormValidation.Framework.Foundation.prototype = $.extend({}, FormValidation.Base.prototype, {
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
                row     = this.options.fields[field].row || this.options.row.selector;

            if ('checkbox' === type || 'radio' === type) {
                var $next = $icon.next();
                if ($next.is('label')) {
                    $icon.insertAfter($next);
                }
            }
        },

        /**
         * Create a tooltip or popover
         * It will be shown when focusing on the field
         *
         * @see http://foundation.zurb.com/sites/docs/tooltip.html
         * @param {jQuery} $field The field element
         * @param {String} message The message
         * @param {String} type Can be 'tooltip' or 'popover'
         */
        _createTooltip: function($field, message, type) {
            var $icon = $field.data('fv.icon');
            if ($icon) {
                var tooltip = $icon.data('fv.foundation.tooltip');
                if (tooltip) {
                    tooltip.destroy();
                }

                $icon.css('cursor', 'pointer')
                     .off('.zf.tooltip')
                     .data('fv.foundation.tooltip', new Foundation.Tooltip($icon, {
                         templateClasses: 'fv-foundation-tooltip',
                         tipText: message
                     }));
            }
        },

        /**
         * Destroy the tooltip or popover
         *
         * @param {jQuery} $field The field element
         * @param {String} type Can be 'tooltip' or 'popover'
         */
        _destroyTooltip: function($field, type) {
            var $icon = $field.data('fv.icon');
            if ($icon) {
                $icon.removeAttr('title')
                     .removeAttr('data-tooltip')
                     .css('cursor', '')
                     .off('.zf.tooltip');

                var tooltip = $icon.data('fv.foundation.tooltip');
                if (tooltip) {
                    tooltip.destroy();
                    $icon.removeData('fv.foundation.tooltip');
                }
            }
        },

        /**
         * Hide a tooltip or popover
         *
         * @param {jQuery} $field The field element
         * @param {String} type Can be 'tooltip' or 'popover'
         */
        _hideTooltip: function($field, type) {
            var $icon = $field.data('fv.icon');
            if ($icon) {
                $icon.css('cursor', '');
                var tooltip = $icon.data('fv.foundation.tooltip');
                if (tooltip) {
                    tooltip.hide();
                }
            }
        },

        /**
         * Show a tooltip or popover
         *
         * @param {jQuery} $field The field element
         * @param {String} type Can be 'tooltip' or 'popover'
         */
        _showTooltip: function($field, type) {
            var $icon = $field.data('fv.icon');
            if ($icon) {
                $icon.css('cursor', 'pointer');
                var tooltip = $icon.data('fv.foundation.tooltip');
                if (tooltip) {
                    tooltip.show();
                }
            }
        }
    });
}(jQuery));
