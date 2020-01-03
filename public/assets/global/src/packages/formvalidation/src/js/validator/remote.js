/**
 * remote validator
 *
 * @link        http://formvalidation.io/validators/remote/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            remote: {
                'default': 'Please enter a valid value'
            }
        }
    });

    FormValidation.Validator.remote = {
        priority: 1000,

        html5Attributes: {
            async: 'async',
            crossdomain: 'crossDomain',
            data: 'data',
            datatype: 'dataType',
            delay: 'delay',
            message: 'message',
            name: 'name',
            type: 'type',
            url: 'url',
            validkey: 'validKey'
        },

        /**
         * Destroy the timer when destroying the FormValidation (using validator.destroy() method)
         */
        destroy: function(validator, $field, options, validatorName) {
            var ns    = validator.getNamespace(),
                timer = $field.data(ns + '.' + validatorName + '.timer');
            if (timer) {
                clearTimeout(timer);
                $field.removeData(ns + '.' + validatorName + '.timer');
            }
        },

        /**
         * Request a remote server to check the input value
         *
         * @param {FormValidation.Base} validator Plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Can consist of the following keys:
         * - async {Boolean} [optional] Indicate the Ajax request will be asynchronous or not. It's true by default
         * - crossDomain {Boolean} [optional]
         * - data {Object|Function} [optional]: By default, it will take the value
         *  {
         *      <fieldName>: <fieldValue>
         *  }
         * - dataType {String} [optional]: The type of data which is returned by remote server.
         * It can be json (default), text, script
         * - delay {Number} [optional]
         * - headers {String[]} [optional]: Additional headers
         * - message {String} [optional]: The invalid message
         * - name {String} [optional]: Override the field name for the request.
         * - type {String} [optional] Can be GET or POST (default)
         * - url {String|Function}
         * - validKey {String} [optional]: The valid key. It's "valid" by default
         * This is useful when connecting to external remote server or APIs provided by 3rd parties
         * @returns {Deferred}
         */
        validate: function(validator, $field, options, validatorName) {
            var ns    = validator.getNamespace(),
                value = validator.getFieldValue($field, validatorName),
                dfd   = new $.Deferred();
            if (value === '') {
                dfd.resolve($field, validatorName, { valid: true });
                return dfd;
            }
            var name     = $field.attr('data-' + ns + '-field'),
                data     = options.data || {},
                url      = options.url,
                validKey = options.validKey || 'valid';

            // Support dynamic data
            if ('function' === typeof data) {
                data = data.call(this, validator, $field, value);
            }

            // Parse string data from HTML5 attribute
            if ('string' === typeof data) {
                data = JSON.parse(data);
            }

            // Support dynamic url
            if ('function' === typeof url) {
                url = url.call(this, validator, $field, value);
            }

            data[options.name || name] = value;

            var ajaxOptions = {
                async: options.async === null || options.async === true || options.async === 'true',
                data: data,
                dataType: options.dataType || 'json',
                headers: options.headers || {},
                type: options.type || 'GET',
                url: url
            };
            if (options.crossDomain !== null) {
                ajaxOptions.crossDomain = (options.crossDomain === true || options.crossDomain === 'true');
            }

            function runCallback() {
                var xhr = $.ajax(ajaxOptions);

                xhr
                    .success(function(response) {
                        response.valid = (response[validKey] === true || response[validKey] === 'true')
                                        ? true
                                        : (response[validKey] === false || response[validKey] === 'false' ? false : null);
                        dfd.resolve($field, validatorName, response);
                    })
                    .error(function(response) {
                        dfd.resolve($field, validatorName, {
                            valid: false
                        });
                    });

                dfd.fail(function() {
                    xhr.abort();
                });

                return dfd;
            }
            
            if (options.delay) {
                // Since the form might have multiple fields with the same name
                // I have to attach the timer to the field element
                if ($field.data(ns + '.' + validatorName + '.timer')) {
                    clearTimeout($field.data(ns + '.' + validatorName + '.timer'));
                }

                $field.data(ns + '.' + validatorName + '.timer', setTimeout(runCallback, options.delay));
                return dfd;
            } else {
                return runCallback();
            }
        }
    };
}(jQuery));
