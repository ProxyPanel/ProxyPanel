/**
 * ProxyPanel 通用JavaScript函数
 */

const jsRoute = (template, id) => template.replace("PLACEHOLDER", id);

/**
 * 基础AJAX请求 - 仅提供最基础的功能
 * @param {Object} options - 请求选项
 * @param {string} options.url - 请求URL
 * @param {string} options.method - HTTP方法 (GET, POST, PUT, DELETE, PATCH)
 * @param {Object} options.data - 请求数据
 * @param {string} options.dataType - 预期服务器响应数据类型
 * @param {function} options.beforeSend - 请求发送前回调
 * @param {function} options.success - 请求成功回调
 * @param {function} options.error - 请求失败回调
 * @param {function} options.complete - 请求完成后回调(无论成功失败)
 */
function ajaxRequest(options) {
    // 默认值
    const defaults = {
        method: "GET", dataType: "json", data: {}
    };
    const s = { ...defaults, ...options };

    if (["POST", "PUT", "DELETE", "PATCH"].includes(s.method)) {
        if (typeof CSRF_TOKEN !== "undefined" && (!s.data || !s.data._token)) {
            s.data = { ...(s.data || {}), _token: CSRF_TOKEN };
        }
    }

    // loading 包装（在这里集中处理，避免重复）
    if (s.loadingSelector) {
        const origBefore = s.beforeSend;
        s.beforeSend = function (xhr) {
            $(s.loadingSelector).show();
            if (typeof origBefore === "function") origBefore(xhr);
        };
        const origComplete = s.complete;
        s.complete = function (xhr, status) {
            $(s.loadingSelector).hide();
            if (typeof origComplete === "function") origComplete(xhr, status);
        };
    }

    return $.ajax({
        url: s.url,
        method: s.method,
        data: s.data,
        dataType: s.dataType,
        beforeSend: s.beforeSend,
        success: s.success,
        error: s.error,
        complete: s.complete
    });
}

function ajaxMethod(method, url, data = {}, options = {}) {
    if (!options.success) options.success = ret => handleResponse(ret);
    return ajaxRequest({ url, method, data, ...options });
}

const ajaxGet    = (url, data = {}, options = {}) => ajaxRequest({url:url, data: data, ...options});
const ajaxPost   = (url, data = {}, options = {}) => ajaxMethod("POST", url, data, options);
const ajaxPut    = (url, data = {}, options = {}) => ajaxMethod("PUT", url, data, options);
const ajaxDelete = (url, data = {}, options = {}) => ajaxMethod("DELETE", url, data, options);
const ajaxPatch  = (url, data = {}, options = {}) => ajaxMethod("PATCH", url, data, options);

/**
 * 处理加载指示器的辅助函数
 * @param {Object} settings - AJAX设置对象
 * @param {string} settings.loadingSelector - 加载指示器选择器 (提供该参数即表示需要显示加载指示器)
 * @returns {Object} 修改后的设置对象
 */
function handleLoadingIndicator(settings) {
    // 如果提供了loadingSelector，则显示加载指示器
    if (settings.loadingSelector) {
        const originalBeforeSend = settings.beforeSend;
        settings.beforeSend = function (xhr) {
            $(settings.loadingSelector).show();
            if (originalBeforeSend) originalBeforeSend(xhr);
        };

        const originalComplete = settings.complete;
        settings.complete = function (xhr, status) {
            $(settings.loadingSelector).hide();
            if (originalComplete) originalComplete(xhr, status);
        };
    }
    return settings;
}

/**
 * 显示确认对话框
 * @param {Object} options - 对话框选项
 * @param {string} options.title - 对话框标题
 * @param {string} options.text - 对话框文本内容
 * @param {string} options.html - 对话框HTML内容 (优先级高于text)
 * @param {string} options.icon - 图标类型 (success, error, warning, info, question)
 * @param {string} options.cancelButtonText - 取消按钮文本
 * @param {string} options.confirmButtonText - 确认按钮文本
 * @param {function} options.onConfirm - 确认回调函数
 * @param {function} options.onCancel - 取消回调函数
 */
function showConfirm(options) {
    // 默认值
    const defaults = {
        icon: "question",
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonText: typeof TRANS !== "undefined" ? TRANS.btn.close : "Cancel",
        confirmButtonText: typeof TRANS !== "undefined" ? TRANS.btn.confirm : "Confirm"
    };

    // 如果没有提供title，使用默认title
    if (!options.title) {
        options.title = typeof TRANS !== "undefined" ? TRANS.confirm_title : "Confirm";
    }

    // 如果没有提供文本内容，使用默认文本
    if (!options.html && !options.text) {
        options.text = typeof TRANS !== "undefined" ? TRANS.confirm_action : "Are you sure you want to perform this action?";
    }

    // 保存回调函数并从选项中移除它们，因为SweetAlert2不接受这些参数
    const onConfirm = options.onConfirm;
    const onCancel = options.onCancel;
    delete options.onConfirm;
    delete options.onCancel;

    // 合并默认值和用户选项
    const alertOptions = {...defaults, ...options};

    swal.fire(alertOptions).then((result) => {
        if (result.value && typeof onConfirm === "function") {
            onConfirm(result);
        } else if (!result.value && typeof onCancel === "function") {
            onCancel(result);
        }
    });
}

/**
 * 显示操作结果提示
 * @param {Object} options - 提示选项
 * @param {string} options.title - 提示标题
 * @param {string} options.message - 提示消息
 * @param {string} options.icon - 图标类型 (success, error, warning, info)
 * @param {boolean} options.autoClose - 是否自动关闭
 * @param {number} options.timer - 自动关闭时间 (毫秒)
 * @param {boolean} options.showConfirmButton - 是否显示确认按钮
 * @param {string} options.html - HTML内容
 * @param {function} options.callback - 关闭后回调
 */
function showMessage(options) {
    const alertOptions = {
        title: options.title || options.message,
        icon: options.icon || "info",
        html: options.html,
        showConfirmButton: options.showConfirmButton !== undefined ? options.showConfirmButton : !options.autoClose
    };

    // 修改逻辑：如果autoClose没有被明确设置为false，并且showConfirmButton没有被明确设置为true，则自动关闭
    if (options.autoClose !== false && options.showConfirmButton !== true) {
        alertOptions.timer = options.timer || 1500;
    }   

    // 如果同时提供了title和message，并且没有html，则将message作为html内容显示
    if (options.title && options.message && !options.html) {
        alertOptions.text = options.message;
    }

    swal.fire(alertOptions).then(() => {
        if (typeof options.callback === "function") {
            options.callback();
        }
    });
}

/**
 * 通用错误处理函数，支持三种错误显示方式
 * @param {Object} xhr - AJAX响应对象
 * @param {Object} options - 错误处理选项
 * @param {string} options.validation - 验证错误显示类型: 'field', 'element', 'swal'
 * @param {string} options.default - 默认错误显示类型: 'swal'(默认), 'field', 'element'
 * @param {string|Object} options.form - 表单选择器或jQuery对象 (type='field'时使用)
 * @param {string} options.element - 错误信息显示元素的选择器 (type='element'时使用)
 * @param {function} options.onError - 自定义错误处理回调
 */
function handleErrors(xhr, options = {}) {
    const defaults = {
        validation: 'field', // 验证错误默认使用字段显示
        default: 'swal' // 其他错误默认使用swal显示
    };

    const settings = {...defaults, ...options};

    // 如果有自定义错误处理回调，优先执行
    if (typeof settings.onError === "function") {
        return settings.onError(xhr);
    }

    // 处理验证错误 (422)
    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
        const errors = xhr.responseJSON.errors;

        switch (settings.validation) {
            case 'field':
                // 在表单字段上显示错误 (添加is-invalid类和错误信息)
                if (settings.form) {
                    const $form = typeof settings.form === "string" ? $(settings.form) : settings.form;
                    // 清除之前的错误状态
                    $form.find(".is-invalid").removeClass("is-invalid");
                    $form.find(".invalid-feedback").remove();

                    // 显示每个字段的错误
                    Object.keys(errors).forEach(field => {
                        const $field = $form.find(`[name="${field}"]`);
                        if ($field.length) {
                            // 添加错误样式
                            $field.addClass("is-invalid");

                            // 添加错误消息
                            const errorMessage = errors[field][0];
                            const $feedback = $("<div>").addClass("invalid-feedback").text(errorMessage);
                            $field.after($feedback);
                        }
                    });

                    // 滚动到第一个错误
                    const $firstError = $form.find(".is-invalid").first();
                    if ($firstError.length) {
                        $("html, body").animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 500);
                    }
                }
                break;

            case 'element':
                // 在指定元素中显示错误列表
                if (settings.element) {
                    let errorStr = "";
                    $.each(errors, function (index, values) {
                        // values 是一个数组，可能包含多个错误消息
                        $.each(values, function (i, value) {
                            errorStr += "<li>" + value + "</li>";
                        });
                    });
                    $(settings.element).html("<ul>" + errorStr + "</ul>").show();
                }
                break;

            case 'swal':
            default:
                // 使用swal显示错误
                let errorStr = "";
                $.each(errors, function (index, values) {
                    // values 是一个数组，可能包含多个错误消息
                    $.each(values, function (i, value) {
                        errorStr += "<li>" + value + "</li>";
                    });
                });
                showMessage({
                    title: xhr.responseJSON.message || (typeof TRANS !== "undefined" ? TRANS.operation_failed : "Operation failed"), html: "<ul>" + errorStr + "</ul>", icon: "error"
                });
                break;
        }
        return true;
    }

    // 处理其他类型的错误
    const errorMessage = xhr.responseJSON?.message || xhr.statusText || (typeof TRANS !== "undefined" ? TRANS.request_failed : "Request failed");

    switch (settings.default) {
        case 'element':
            if (settings.element) {
                $(settings.element).html(errorMessage).show();
            }
            break;

        case 'field':
            // 对于非验证错误，如果指定了表单，可以在表单顶部显示错误
            if (settings.form) {
                const $form = typeof settings.form === "string" ? $(settings.form) : settings.form;
                // 可以根据需要实现特定的显示方式
                showMessage({
                    title: errorMessage, icon: "error"
                });
            }
            break;

        case 'swal':
        default:
            showMessage({
                title: errorMessage, icon: "error"
            });
            break;
    }

    return false;
}

/**
 * 处理AJAX响应结果
 * @param {Object} response - AJAX响应
 * @param {Object} options - 处理选项
 * @param {boolean} options.reload - 成功后是否刷新页面
 * @param {string} options.redirectUrl - 成功后重定向URL
 * @param {function} options.onSuccess - 成功回调
 * @param {function} options.onError - 错误回调
 * @param {boolean} options.showMessage - 是否显示消息提示
 * @returns {Object} 原始响应
 */
function handleResponse(response, options = {}) {
    const defaults = {
        reload: true, showMessage: true
    };

    const settings = {...defaults, ...options};

    if (response.status === "success") {
        if (settings.showMessage) {
            showMessage({
                title: response.message || (typeof TRANS !== "undefined" ? TRANS.operation_success : "Operation successful"), icon: "success", showConfirmButton: false, callback: function () {
                    if (typeof settings.onSuccess === "function") {
                        settings.onSuccess(response);
                    } else if (settings.redirectUrl) {
                        window.location.href = settings.redirectUrl;
                    } else if (settings.reload) {
                        window.location.reload();
                    }
                }
            });
        } else {
            if (typeof settings.onSuccess === "function") {
                settings.onSuccess(response);
            } else if (settings.redirectUrl) {
                window.location.href = settings.redirectUrl;
            } else if (settings.reload) {
                window.location.reload();
            }
        }
    } else {
        if (settings.showMessage) {
            showMessage({
                title: response.message || (typeof TRANS !== "undefined" ? TRANS.operation_failed : "Operation failed"), icon: "error", showConfirmButton: true, callback: function () {
                    if (typeof settings.onError === "function") {
                        settings.onError(response);
                    }
                }
            });
        } else if (typeof settings.onError === "function") {
            settings.onError(response);
        }
    }

    return response;
}

/**
 * 重置搜索表单
 */
function resetSearchForm() {
    window.location.href = window.location.href.split("?")[0];
}

/**
 * 初始化表单选择器变化时自动提交
 * @param {string} formSelector - 表单选择器
 * @param {string} excludeSelector - 排除的选择器
 */
function initAutoSubmitSelects(formSelector = "form:not(.modal-body form)", excludeSelector = ".modal-body select") {
    $(formSelector).on("submit", function () {
        const $form = $(this);
        $form.find("input:not([type=\"submit\"]), select").filter(function () {
            return this.value === "";
        }).prop("disabled", true);

        // 恢复 disabled 要使用闭包 $form
        setTimeout(function () {
            $form.find(":disabled").prop("disabled", false);
        }, 0);
    });

    // 只对非排除选择器的 select 绑定 change 自动提交
    $(`select`).not(excludeSelector).on("change", function () {
        $(this).closest("form").trigger("submit");
    });
}

/**
 * 复制文本到剪贴板
 * @param {string} text - 要复制的文本
 * @param {Object} options - 选项
 * @param {boolean} options.showMessage - 是否显示消息提示
 * @param {string} options.successMessage - 复制成功消息
 * @param {string} options.errorMessage - 复制失败消息
 * @param {function} options.onSuccess - 复制成功回调
 * @param {function} options.onError - 复制失败回调
 * @returns {boolean} 是否复制成功
 */
function copyToClipboard(text, options = {}) {
    const defaults = {
        showMessage: true,
        successMessage: typeof TRANS !== "undefined" ? TRANS.copy.success : "Copy successful",
        errorMessage: typeof TRANS !== "undefined" ? TRANS.copy.failed : "Copy failed, please copy manually"
    };

    const settings = {...defaults, ...options};

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            if (settings.showMessage) {
                showMessage({
                    title: settings.successMessage, icon: "success", autoClose: true
                });
            }
            if (typeof settings.onSuccess === "function") {
                settings.onSuccess();
            }
        }).catch(err => {
            console.error("Copy failed: ", err);
            if (settings.showMessage) {
                showMessage({
                    title: settings.errorMessage, icon: "error"
                });
            }
            if (typeof settings.onError === "function") {
                settings.onError(err);
            }
        });
        return true;
    } else {
        const textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.style.position = "fixed";
        textarea.style.opacity = 0;
        document.body.appendChild(textarea);
        textarea.select();

        let success = false;
        try {
            success = document.execCommand("copy");
            if (success && settings.showMessage) {
                showMessage({
                    title: settings.successMessage, icon: "success", autoClose: true
                });
            }
            if (success && typeof settings.onSuccess === "function") {
                settings.onSuccess();
            }
        } catch (err) {
            console.error("Unable to copy text: ", err);
            if (settings.showMessage) {
                showMessage({
                    title: settings.errorMessage, icon: "error"
                });
            }
            if (typeof settings.onError === "function") {
                settings.onError(err);
            }
        }

        document.body.removeChild(textarea);
        return success;
    }
}

/**
 * 通用删除确认功能
 * @param {string} url - 删除请求的URL
 * @param {string} attribute - 要删除的实体属性名称
 * @param {string} name - 要删除项目的ID或名称
 * @param {Object} options - 附加选项
 * @param {string} options.title - 自定义标题
 * @param {string} options.text - 自定义文本内容
 * @param {string} options.html - 自定义HTML内容
 * @param {string} options.icon - 自定义图标 (success, error, warning, info, question)
 * @param {function} options.callback - 成功后的回调函数 (等同于onSuccess)
 * @param {function} options.onSuccess - 成功后的回调函数
 * @param {function} options.onError - 错误后的回调函数
 * @param {boolean} options.reload - 成功后是否刷新页面
 * @param {string} options.redirectUrl - 成功后重定向URL
 */
function confirmDelete(url, name, attribute, options = {}) {
    // 获取翻译文本
    const defaults = {
        titleMessage: typeof TRANS !== "undefined" ? TRANS.warning : "Warning",
    };

    let text = options.text;
    if (!text && typeof TRANS !== "undefined" && TRANS.confirm && TRANS.confirm.delete) {
        text = TRANS.confirm.delete.replace("{attribute}", attribute || "").replace("{name}", name || "");
    } else if (!text) {
        text = typeof TRANS !== "undefined" ? TRANS.confirm_delete.replace("{attribute}", attribute || "").replace("{name}", name || "") : `Are you sure you want to delete {attribute} [{name}]?`.replace("{attribute}", attribute || "").replace("{name}", name || "");
    }

    // 显示确认对话框
    showConfirm({
        title: options.title || defaults.titleMessage, icon: options.icon || "warning", text: text, html: options.html, onConfirm: function () {
            // 使用ajaxDelete函数发送删除请求
            ajaxDelete(url,{}, {
                success: function (response) {
                    // 使用通用响应处理
                    handleResponse(response, {
                        reload: options.reload !== false, redirectUrl: options.redirectUrl, onSuccess: options.callback || options.onSuccess, onError: options.onError
                    });
                }
            });
        }
    });
}
