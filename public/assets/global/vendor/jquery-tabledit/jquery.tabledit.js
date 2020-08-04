/*!
 * Tabledit v1.2.3 (https://github.com/markcell/jQuery-Tabledit)
 * Copyright (c) 2015 Celso Marques
 * Licensed under MIT (https://github.com/markcell/jQuery-Tabledit/blob/master/LICENSE)
 */

/**
 * @description Inline editor for HTML tables compatible with Bootstrap
 * @version 1.2.3
 * @author Celso Marques
 */

if (typeof jQuery === 'undefined') {
  throw new Error('Tabledit requires jQuery library.');
}

(function($) {
    'use strict';

    $.fn.Tabledit = function(options) {
        if (!this.is('table')) {
            throw new Error('Tabledit only works when applied to a table.');
        }

        var $table = this;

        var defaults = {
            url: window.location.href,
            inputClass: 'form-control input-sm',
            toolbarClass: 'btn-toolbar',
            groupClass: 'btn-group btn-group-sm',
            dangerClass: 'danger',
            warningClass: 'warning',
            mutedClass: 'text-muted',
            eventType: 'click',
            rowIdentifier: 'id',
            hideIdentifier: false,
            autoFocus: true,
            editButton: true,
            deleteButton: true,
            saveButton: true,
            restoreButton: true,
            buttons: {
                edit: {
                    class: 'btn btn-sm btn-default',
                    html: '<span class="glyphicon glyphicon-pencil"></span>',
                    action: 'edit'
                },
                delete: {
                    class: 'btn btn-sm btn-default',
                    html: '<span class="glyphicon glyphicon-trash"></span>',
                    action: 'delete'
                },
                save: {
                    class: 'btn btn-sm btn-success',
                    html: 'Save'
                },
                restore: {
                    class: 'btn btn-sm btn-warning',
                    html: 'Restore',
                    action: 'restore'
                },
                confirm: {
                    class: 'btn btn-sm btn-danger',
                    html: 'Confirm'
                }
            },
            onDraw: function() { return; },
            onSuccess: function() { return; },
            onFail: function() { return; },
            onAlways: function() { return; },
            onAjax: function() { return; }
        };

        var settings = $.extend(true, defaults, options);

        var $lastEditedRow = 'undefined';
        var $lastDeletedRow = 'undefined';
        var $lastRestoredRow = 'undefined';

        /**
         * Draw Tabledit structure (identifier column, editable columns, toolbar column).
         *
         * @type {object}
         */
        var Draw = {
            columns: {
                identifier: function() {
                    // Hide identifier column.
                    if (settings.hideIdentifier) {
                        $table.find('th:nth-child(' + parseInt(settings.columns.identifier[0]) + 1 + '), tbody td:nth-child(' + parseInt(settings.columns.identifier[0]) + 1 + ')').hide();
                    }

                    var $td = $table.find('tbody td:nth-child(' + (parseInt(settings.columns.identifier[0]) + 1) + ')');

                    $td.each(function() {
                        // Create hidden input with row identifier.
                        var span = '<span class="tabledit-span tabledit-identifier">' + $(this).text() + '</span>';
                        var input = '<input class="tabledit-input tabledit-identifier" type="hidden" name="' + settings.columns.identifier[1] + '" value="' + $(this).text() + '" disabled>';

                        // Add elements to table cell.
                        $(this).html(span + input);

                        // Add attribute "id" to table row.
                        $(this).parent('tr').attr(settings.rowIdentifier, $(this).text());
                    });
                },
                editable: function() {
                    for (var i = 0; i < settings.columns.editable.length; i++) {
                        var $td = $table.find('tbody td:nth-child(' + (parseInt(settings.columns.editable[i][0]) + 1) + ')');

                        $td.each(function() {
                            // Get text of this cell.
                            var text = $(this).text();

                            // Add pointer as cursor.
                            if (!settings.editButton) {
                                $(this).css('cursor', 'pointer');
                            }

                            // Create span element.
                            var span = '<span class="tabledit-span">' + text + '</span>';

                            // Check if exists the third parameter of editable array.
                            if (typeof settings.columns.editable[i][2] !== 'undefined') {
                                // Create select element.
                                var input = '<select class="tabledit-input ' + settings.inputClass + '" name="' + settings.columns.editable[i][1] + '" style="display: none;" disabled>';

                                // Create options for select element.
                                $.each(jQuery.parseJSON(settings.columns.editable[i][2]), function(index, value) {
                                    if (text === value) {
                                        input += '<option value="' + index + '" selected>' + value + '</option>';
                                    } else {
                                        input += '<option value="' + index + '">' + value + '</option>';
                                    }
                                });

                                // Create last piece of select element.
                                input += '</select>';
                            } else {
                                // Create text input element.
                                var input = '<input class="tabledit-input ' + settings.inputClass + '" type="text" name="' + settings.columns.editable[i][1] + '" value="' + $(this).text() + '" style="display: none;" disabled>';
                            }

                            // Add elements and class "view" to table cell.
                            $(this).html(span + input);
                            $(this).addClass('tabledit-view-mode');
                       });
                    }
                },
                toolbar: function() {
                    if (settings.editButton || settings.deleteButton) {
                        var editButton = '';
                        var deleteButton = '';
                        var saveButton = '';
                        var restoreButton = '';
                        var confirmButton = '';

                        // Add toolbar column header if not exists.
                        if ($table.find('th.tabledit-toolbar-column').length === 0) {
                            $table.find('tr:first').append('<th class="tabledit-toolbar-column"></th>');
                        }

                        // Create edit button.
                        if (settings.editButton) {
                            editButton = '<button type="button" class="tabledit-edit-button ' + settings.buttons.edit.class + '" style="float: none;">' + settings.buttons.edit.html + '</button>';
                        }

                        // Create delete button.
                        if (settings.deleteButton) {
                            deleteButton = '<button type="button" class="tabledit-delete-button ' + settings.buttons.delete.class + '" style="float: none;">' + settings.buttons.delete.html + '</button>';
                            confirmButton = '<button type="button" class="tabledit-confirm-button ' + settings.buttons.confirm.class + '" style="display: none; float: none;">' + settings.buttons.confirm.html + '</button>';
                        }

                        // Create save button.
                        if (settings.editButton && settings.saveButton) {
                            saveButton = '<button type="button" class="tabledit-save-button ' + settings.buttons.save.class + '" style="display: none; float: none;">' + settings.buttons.save.html + '</button>';
                        }

                        // Create restore button.
                        if (settings.deleteButton && settings.restoreButton) {
                            restoreButton = '<button type="button" class="tabledit-restore-button ' + settings.buttons.restore.class + '" style="display: none; float: none;">' + settings.buttons.restore.html + '</button>';
                        }

                        var toolbar = '<div class="tabledit-toolbar ' + settings.toolbarClass + '" style="text-align: left;">\n\
                                           <div class="' + settings.groupClass + '" style="float: none;">' + editButton + deleteButton + '</div>\n\
                                           ' + saveButton + '\n\
                                           ' + confirmButton + '\n\
                                           ' + restoreButton + '\n\
                                       </div></div>';

                        // Add toolbar column cells.
                        $table.find('tbody>tr').append('<td style="white-space: nowrap; width: 1%;">' + toolbar + '</td>');
                    }
                }
            }
        };

        /**
         * Change to view mode or edit mode with table td element as parameter.
         *
         * @type object
         */
        var Mode = {
            view: function(td) {
                // Get table row.
                var $tr = $(td).parent('tr');
                // Disable identifier.
                $(td).parent('tr').find('.tabledit-input.tabledit-identifier').prop('disabled', true);
                // Hide and disable input element.
                $(td).find('.tabledit-input').blur().hide().prop('disabled', true);
                // Show span element.
                $(td).find('.tabledit-span').show();
                // Add "view" class and remove "edit" class in td element.
                $(td).addClass('tabledit-view-mode').removeClass('tabledit-edit-mode');
                // Update toolbar buttons.
                if (settings.editButton) {
                    $tr.find('button.tabledit-save-button').hide();
                    $tr.find('button.tabledit-edit-button').removeClass('active').blur();
                }
            },
            edit: function(td) {
                Delete.reset(td);
                // Get table row.
                var $tr = $(td).parent('tr');
                // Enable identifier.
                $tr.find('.tabledit-input.tabledit-identifier').prop('disabled', false);
                // Hide span element.
                $(td).find('.tabledit-span').hide();
                // Get input element.
                var $input = $(td).find('.tabledit-input');
                // Enable and show input element.
                $input.prop('disabled', false).show();
                // Focus on input element.
                if (settings.autoFocus) {
                    $input.focus();
                }
                // Add "edit" class and remove "view" class in td element.
                $(td).addClass('tabledit-edit-mode').removeClass('tabledit-view-mode');
                // Update toolbar buttons.
                if (settings.editButton) {
                    $tr.find('button.tabledit-edit-button').addClass('active');
                    $tr.find('button.tabledit-save-button').show();
                }
            }
        };

        /**
         * Available actions for edit function, with table td element as parameter or set of td elements.
         *
         * @type object
         */
        var Edit = {
            reset: function(td) {
                $(td).each(function() {
                    // Get input element.
                    var $input = $(this).find('.tabledit-input');
                    // Get span text.
                    var text = $(this).find('.tabledit-span').text();
                    // Set input/select value with span text.
                    if ($input.is('select')) {
                        $input.find('option').filter(function() {
                            return $.trim($(this).text()) === text;
                        }).attr('selected', true);
                    } else {
                        $input.val(text);
                    }
                    // Change to view mode.
                    Mode.view(this);
                });
            },
            submit: function(td) {
                // Send AJAX request to server.
                var ajaxResult = ajax(settings.buttons.edit.action);

                if (ajaxResult === false) {
                    return;
                }

                $(td).each(function() {
                    // Get input element.
                    var $input = $(this).find('.tabledit-input');
                    // Set span text with input/select new value.
                    if ($input.is('select')) {
                        $(this).find('.tabledit-span').text($input.find('option:selected').text());
                    } else {
                        $(this).find('.tabledit-span').text($input.val());
                    }
                    // Change to view mode.
                    Mode.view(this);
                });

                // Set last edited column and row.
                $lastEditedRow = $(td).parent('tr');
            }
        };

        /**
         * Available actions for delete function, with button as parameter.
         *
         * @type object
         */
        var Delete = {
            reset: function(td) {
                // Reset delete button to initial status.
                $table.find('.tabledit-confirm-button').hide();
                // Remove "active" class in delete button.
                $table.find('.tabledit-delete-button').removeClass('active').blur();
            },
            submit: function(td) {
                Delete.reset(td);
                // Enable identifier hidden input.
                $(td).parent('tr').find('input.tabledit-identifier').attr('disabled', false);
                // Send AJAX request to server.
                var ajaxResult = ajax(settings.buttons.delete.action);
                // Disable identifier hidden input.
                $(td).parents('tr').find('input.tabledit-identifier').attr('disabled', true);

                if (ajaxResult === false) {
                    return;
                }

                // Add class "deleted" to row.
                $(td).parent('tr').addClass('tabledit-deleted-row');
                // Hide table row.
                $(td).parent('tr').addClass(settings.mutedClass).find('.tabledit-toolbar button:not(.tabledit-restore-button)').attr('disabled', true);
                // Show restore button.
                $(td).find('.tabledit-restore-button').show();
                // Set last deleted row.
                $lastDeletedRow = $(td).parent('tr');
            },
            confirm: function(td) {
                // Reset all cells in edit mode.
                $table.find('td.tabledit-edit-mode').each(function() {
                    Edit.reset(this);
                });
                // Add "active" class in delete button.
                $(td).find('.tabledit-delete-button').addClass('active');
                // Show confirm button.
                $(td).find('.tabledit-confirm-button').show();
            },
            restore: function(td) {
                // Enable identifier hidden input.
                $(td).parent('tr').find('input.tabledit-identifier').attr('disabled', false);
                // Send AJAX request to server.
                var ajaxResult = ajax(settings.buttons.restore.action);
                // Disable identifier hidden input.
                $(td).parents('tr').find('input.tabledit-identifier').attr('disabled', true);

                if (ajaxResult === false) {
                    return;
                }

                // Remove class "deleted" to row.
                $(td).parent('tr').removeClass('tabledit-deleted-row');
                // Hide table row.
                $(td).parent('tr').removeClass(settings.mutedClass).find('.tabledit-toolbar button').attr('disabled', false);
                // Hide restore button.
                $(td).find('.tabledit-restore-button').hide();
                // Set last restored row.
                $lastRestoredRow = $(td).parent('tr');
            }
        };

        /**
         * Send AJAX request to server.
         *
         * @param {string} action
         */
        function ajax(action)
        {
            var serialize = $table.find('.tabledit-input').serialize() + '&action=' + action;

            var result = settings.onAjax(action, serialize);

            if (result === false) {
                return false;
            }

            var jqXHR = $.post(settings.url, serialize, function(data, textStatus, jqXHR) {
                if (action === settings.buttons.edit.action) {
                    $lastEditedRow.removeClass(settings.dangerClass).addClass(settings.warningClass);
                    setTimeout(function() {
                        //$lastEditedRow.removeClass(settings.warningClass);
                        $table.find('tr.' + settings.warningClass).removeClass(settings.warningClass);
                    }, 1400);
                }

                settings.onSuccess(data, textStatus, jqXHR);
            }, 'json');

            jqXHR.fail(function(jqXHR, textStatus, errorThrown) {
                if (action === settings.buttons.delete.action) {
                    $lastDeletedRow.removeClass(settings.mutedClass).addClass(settings.dangerClass);
                    $lastDeletedRow.find('.tabledit-toolbar button').attr('disabled', false);
                    $lastDeletedRow.find('.tabledit-toolbar .tabledit-restore-button').hide();
                } else if (action === settings.buttons.edit.action) {
                    $lastEditedRow.addClass(settings.dangerClass);
                }

                settings.onFail(jqXHR, textStatus, errorThrown);
            });

            jqXHR.always(function() {
                settings.onAlways();
            });

            return jqXHR;
        }

        Draw.columns.identifier();
        Draw.columns.editable();
        Draw.columns.toolbar();

        settings.onDraw();

        if (settings.deleteButton) {
            /**
             * Delete one row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-delete-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Get current state before reset to view mode.
                    var activated = $(this).hasClass('active');

                    var $td = $(this).parents('td');

                    Delete.reset($td);

                    if (!activated) {
                        Delete.confirm($td);
                    }

                    event.handled = true;
                }
            });

            /**
             * Delete one row (confirm).
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-confirm-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    var $td = $(this).parents('td');

                    Delete.submit($td);

                    event.handled = true;
                }
            });
        }

        if (settings.restoreButton) {
            /**
             * Restore one row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-restore-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    Delete.restore($(this).parents('td'));

                    event.handled = true;
                }
            });
        }

        if (settings.editButton) {
            /**
             * Activate edit mode on all columns.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-edit-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    var $button = $(this);

                    // Get current state before reset to view mode.
                    var activated = $button.hasClass('active');

                    // Change to view mode columns that are in edit mode.
                    Edit.reset($table.find('td.tabledit-edit-mode'));

                    if (!activated) {
                        // Change to edit mode for all columns in reverse way.
                        $($button.parents('tr').find('td.tabledit-view-mode').get().reverse()).each(function() {
                            Mode.edit(this);
                        });
                    }

                    event.handled = true;
                }
            });

            /**
             * Save edited row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-save-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Submit and update all columns.
                    Edit.submit($(this).parents('tr').find('td.tabledit-edit-mode'));

                    event.handled = true;
                }
            });
        } else {
            /**
             * Change to edit mode on table td element.
             *
             * @param {object} event
             */
            $table.on(settings.eventType, 'tr:not(.tabledit-deleted-row) td.tabledit-view-mode', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Reset all td's in edit mode.
                    Edit.reset($table.find('td.tabledit-edit-mode'));

                    // Change to edit mode.
                    Mode.edit(this);

                    event.handled = true;
                }
            });

            /**
             * Change event when input is a select element.
             */
            $table.on('change', 'select.tabledit-input:visible', function() {
                if (event.handled !== true) {
                    // Submit and update the column.
                    Edit.submit($(this).parent('td'));

                    event.handled = true;
                }
            });

            /**
             * Click event on document element.
             *
             * @param {object} event
             */
            $(document).on('click', function(event) {
                var $editMode = $table.find('.tabledit-edit-mode');
                // Reset visible edit mode column.
                if (!$editMode.is(event.target) && $editMode.has(event.target).length === 0) {
                    Edit.reset($table.find('.tabledit-input:visible').parent('td'));
                }
            });
        }

        /**
         * Keyup event on document element.
         *
         * @param {object} event
         */
        $(document).on('keyup', function(event) {
            // Get input element with focus or confirmation button.
            var $input = $table.find('.tabledit-input:visible');
            var $button = $table.find('.tabledit-confirm-button');

            if ($input.length > 0) {
                var $td = $input.parents('td');
            } else if ($button.length > 0) {
                var $td = $button.parents('td');
            } else {
                return;
            }

            // Key?
            switch (event.keyCode) {
                case 9:  // Tab.
                    if (!settings.editButton) {
                        Edit.submit($td);
                        Mode.edit($td.closest('td').next());
                    }
                    break;
                case 13: // Enter.
                    Edit.submit($td);
                    break;
                case 27: // Escape.
                    Edit.reset($td);
                    Delete.reset($td);
                    break;
            }
        });

        return this;
    };
}(jQuery));
