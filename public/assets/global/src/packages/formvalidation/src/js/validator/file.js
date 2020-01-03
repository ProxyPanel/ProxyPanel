/**
 * file validator
 *
 * @link        http://formvalidation.io/validators/file/
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
(function($) {
    FormValidation.I18n = $.extend(true, FormValidation.I18n || {}, {
        'en_US': {
            file: {
                'default': 'Please choose a valid file'
            }
        }
    });

    FormValidation.Validator.file = {
        Error: {
            EXTENSION: 'EXTENSION',
            MAX_FILES: 'MAX_FILES',
            MAX_SIZE: 'MAX_SIZE',
            MAX_TOTAL_SIZE: 'MAX_TOTAL_SIZE',
            MIN_FILES: 'MIN_FILES',
            MIN_SIZE: 'MIN_SIZE',
            MIN_TOTAL_SIZE: 'MIN_TOTAL_SIZE',
            TYPE: 'TYPE'
        },

        html5Attributes: {
            extension: 'extension',
            maxfiles: 'maxFiles',
            minfiles: 'minFiles',
            maxsize: 'maxSize',
            minsize: 'minSize',
            maxtotalsize: 'maxTotalSize',
            mintotalsize: 'minTotalSize',
            message: 'message',
            type: 'type'
        },

        /**
         * Validate upload file. Use HTML 5 API if the browser supports
         *
         * @param {FormValidation.Base} validator The validator plugin instance
         * @param {jQuery} $field Field element
         * @param {Object} options Can consist of the following keys:
         * - extension: The allowed extensions, separated by a comma
         * - maxFiles: The maximum number of files
         * - minFiles: The minimum number of files
         * - maxSize: The maximum size in bytes
         * - minSize: The minimum size in bytes
         * - maxTotalSize: The maximum size in bytes for all files
         * - minTotalSize: The minimum size in bytes for all files
         * - message: The invalid message
         * - type: The allowed MIME type, separated by a comma
         * @returns {Boolean|Object}
         */
        validate: function(validator, $field, options, validatorName) {
            var value = validator.getFieldValue($field, validatorName);
            if (value === '') {
                return true;
            }

            var ext,
                extensions = options.extension ? options.extension.toLowerCase().split(',') : null,
                types      = options.type      ? options.type.toLowerCase().split(',')      : null,
                html5      = (window.File && window.FileList && window.FileReader);

            if (html5) {
                // Get FileList instance
                var files     = $field.get(0).files,
                    total     = files.length,
                    totalSize = 0;

                // Check the maxFiles
                if (options.maxFiles && total > parseInt(options.maxFiles, 10)) {
                    return {
                        valid: false,
                        error: this.Error.MAX_FILES
                    };
                }

                // Check the minFiles
                if (options.minFiles && total < parseInt(options.minFiles, 10)) {
                    return {
                        valid: false,
                        error: this.Error.MIN_FILES
                    };
                }

                var metaData = {};
                for (var i = 0; i < total; i++) {
                    totalSize += files[i].size;
                    ext        = files[i].name.substr(files[i].name.lastIndexOf('.') + 1);
                    metaData   = {
                        file: files[i],
                        size: files[i].size,
                        ext: ext,
                        type: files[i].type
                    };

                    // Check the minSize
                    if (options.minSize && files[i].size < parseInt(options.minSize, 10)) {
                       return {
                           valid: false,
                           error: this.Error.MIN_SIZE,
                           metaData: metaData
                       };
                    }

                    // Check the maxSize
                    if (options.maxSize && files[i].size > parseInt(options.maxSize, 10)) {
                        return {
                            valid: false,
                            error: this.Error.MAX_SIZE,
                            metaData: metaData
                        };
                    }

                    // Check file extension
                    if (extensions && $.inArray(ext.toLowerCase(), extensions) === -1) {
                        return {
                            valid: false,
                            error: this.Error.EXTENSION,
                            metaData: metaData
                        };
                    }

                    // Check file type
                    if (files[i].type && types && $.inArray(files[i].type.toLowerCase(), types) === -1) {
                        return {
                            valid: false,
                            error: this.Error.TYPE,
                            metaData: metaData
                        };
                    }
                }

                // Check the maxTotalSize
                if (options.maxTotalSize && totalSize > parseInt(options.maxTotalSize, 10)) {
                    return {
                        valid: false,
                        error: this.Error.MAX_TOTAL_SIZE,
                        metaData: {
                            totalSize: totalSize
                        }
                    };
                }

                // Check the minTotalSize
                if (options.minTotalSize && totalSize < parseInt(options.minTotalSize, 10)) {
                    return {
                        valid: false,
                        error: this.Error.MIN_TOTAL_SIZE,
                        metaData: {
                            totalSize: totalSize
                        }
                    };
                }
            } else {
                // Check file extension
                ext = value.substr(value.lastIndexOf('.') + 1);
                if (extensions && $.inArray(ext.toLowerCase(), extensions) === -1) {
                    return {
                        valid: false,
                        error: this.Error.EXTENSION,
                        metaData: {
                            ext: ext
                        }
                    };
                }
            }

            return true;
        }
    };
}(jQuery));
