/**
 * ProxyPanel 通用JavaScript函数
 */

/* 辅助：替换路由模板中的 PLACEHOLDER */
const jsRoute = (template, id) => template.replace(id ? "PLACEHOLDER" : "/PLACEHOLDER", id || "");



/* -----------------------
   小工具 / 辅助函数
   ----------------------- */

/** 统一弹窗封装（基于 SweetAlert2） */
function showAlert(options) {
    // options 直接传给 swal.fire；返回 Promise
    return swal.fire(options);
}

/** 将 errors 对象转换为 <ul> HTML 字符串 */
function buildErrorHtml(errors) {
    let errorStr = "";
    Object.values(errors).forEach(values => {
        values.forEach(v => {
            errorStr += `<li>${v}</li>`;
        });
    });
    return `<ul>${errorStr}</ul>`;
}

/* -----------------------
   AJAX 核心
   ----------------------- */

/**
 * 基础AJAX请求 - 返回 jQuery jqXHR
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
    // 简化对象合并
    const settings = Object.assign({
        method: "GET",
        dataType: "json",
        data: {}
    }, options);

    // CSRF 自动注入（只在写方法上）
    if (["POST", "PUT", "DELETE", "PATCH"].includes(settings.method.toUpperCase()) &&
        typeof CSRF_TOKEN !== "undefined" &&
        !(settings.data && settings.data._token)) {
        settings.data = Object.assign({}, settings.data || {}, { _token: CSRF_TOKEN });
    }

    // loading 包装（如果提供 loadingSelector）
    if (settings.loadingSelector) {
        const origBefore = settings.beforeSend;
        const origComplete = settings.complete;

        settings.beforeSend = function (xhr, opts) {
            try { $(settings.loadingSelector).show(); } catch (e) { /* ignore */ }
            if (origBefore) origBefore.call(this, xhr, opts);
        };

        settings.complete = function (xhr, status) {
            try { $(settings.loadingSelector).hide(); } catch (e) { /* ignore */ }
            if (origComplete) origComplete.call(this, xhr, status);
        };
    }

    return $.ajax(settings);
}

/**
 * ajaxMethod - 为带有默认 success(handleResponse) 的方法提供便利
 */
function ajaxMethod(method, url, data = {}, options = {}) {
    const opts = {...options};
    opts.success = opts.success ?? (ret => handleResponse(ret));
    return ajaxRequest({url, method, data, ...opts});
}

const createAjaxMethod = (method) => (url, data = {}, options = {}) => ajaxMethod(method, url, data, options);

const ajaxGet = (url, data = {}, options = {}) => ajaxRequest({url, data, ...options});
const ajaxPost = createAjaxMethod("POST");
const ajaxPut = createAjaxMethod("PUT");
const ajaxDelete = createAjaxMethod("DELETE");
const ajaxPatch = createAjaxMethod("PATCH");

/* -----------------------
   通用弹窗 / 提示
   ----------------------- */

/**
 * 显示确认对话框（基于 swal.fire）
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
    const {onConfirm, onCancel, ...alertOptions} = {
        icon: "question",
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonText: i18n('btn.close'),
        confirmButtonText: i18n('btn.confirm'),
        ...options
    };

    alertOptions.title = alertOptions.title || i18n('confirm_title');

    if (!alertOptions.html && !alertOptions.text) {
        alertOptions.text = i18n('confirm_action');
    }

    showAlert(alertOptions).then((result) => {
        if (result.value && typeof onConfirm === "function") {
            onConfirm(result);
        } else if (!result.value && typeof onCancel === "function") {
            onCancel(result);
        }
    });
}

/**
 * 显示操作结果提示
 * @param {string} options.title - 提示标题
 * @param {string} options.message - 提示消息
 * @param {string} options.icon - 图标类型 (success, error, warning, info)
 * @param {boolean} options.autoClose - 是否自动关闭
 * @param {number} options.timer - 自动关闭时间 (毫秒)
 * @param {boolean} options.showConfirmButton - 是否显示确认按钮
 * @param {string} options.html - HTML内容
 * @param {function} options.callback - 关闭后回调
 */
function showMessage(options = {}) {
    // 确认按钮显示逻辑：手动设置 > 自动关闭时隐藏 > 默认显示
    const showConfirmButton = options.showConfirmButton !== undefined 
        ? options.showConfirmButton 
        : false;

    const explicitAutoClose = options.autoClose;
    const hasTimer = options.timer !== undefined;
    const disableAutoClose = showConfirmButton === true;
    
    const isAutoClose = explicitAutoClose !== undefined 
        ? explicitAutoClose 
        : (hasTimer ? true : (!disableAutoClose));
    
    const timerValue = hasTimer 
        ? options.timer 
        : (isAutoClose ? 1500 : null);

    const alertOptions = {
        title: options.title || options.message,
        icon: options.icon || "info",
        html: options.html,
        showConfirmButton: showConfirmButton,
        ...(timerValue && isAutoClose && {timer: timerValue}),
        ...(options.title && options.message && !options.html && {text: options.message})
    };

    showAlert(alertOptions).then(() => {
        if (typeof options.callback === "function") options.callback();
    });
}

/* -----------------------
   通用错误处理
   ----------------------- */

/**
 * handleErrors - 处理 xhr 错误（422 验证错误 / 其它错误）
 * options: { validation: 'field'|'element'|'swal', default: 'swal'|'field'|'element', form, element, onError }
 * @param {Object} xhr - AJAX响应对象
 * @param {Object} options - 错误处理选项
 * @param {string} options.validation - 验证错误显示类型: 'field', 'element', 'swal'
 * @param {string} options.default - 默认错误显示类型: 'swal'(默认), 'field', 'element'
 * @param {string|Object} options.form - 表单选择器或jQuery对象 (type='field'时使用)
 * @param {string} options.element - 错误信息显示元素的选择器 (type='element'时使用)
 * @param {function} options.onError - 自定义错误处理回调
 */
function handleErrors(xhr, options = {}) {
    const settings = Object.assign({validation: 'field', default: 'swal'}, options);

    if (typeof settings.onError === "function") {
        return settings.onError(xhr);
    }

    // 验证错误 422
    if (xhr.status === 422 && xhr.responseJSON?.errors) {
        const errors = xhr.responseJSON.errors;

        switch (settings.validation) {
            case 'field':
                if (settings.form) {
                    const $form = typeof settings.form === "string" ? $(settings.form) : settings.form;
                    $form.find(".is-invalid").removeClass("is-invalid");
                    $form.find(".invalid-feedback").remove();

                    Object.keys(errors).forEach(field => {
                        const $field = $form.find(`[name="${field}"]`);
                        if ($field.length) {
                            $field.addClass("is-invalid");
                            const errorMessage = errors[field][0];
                            const $feedback = $("<div>").addClass("invalid-feedback").text(errorMessage);
                            $field.after($feedback);
                        }
                    });

                    const $firstError = $form.find(".is-invalid").first();
                    if ($firstError.length) {
                        $("html, body").animate({scrollTop: $firstError.offset().top - 100}, 500);
                    }
                } else {
                    // 如果没有提供 form，回退到 swal 显示
                    showMessage({title: xhr.responseJSON.message || i18n('operation_failed'), html: buildErrorHtml(errors), icon: "error"});
                }
                break;

            case 'element':
                if (settings.element) {
                    $(settings.element).html(buildErrorHtml(errors)).show();
                } else {
                    showMessage({title: xhr.responseJSON.message || i18n('operation_failed'), html: buildErrorHtml(errors), icon: "error"});
                }
                break;

            case 'swal':
            default:
                showMessage({
                    title: xhr.responseJSON.message || i18n('operation_failed'),
                    html: buildErrorHtml(errors),
                    icon: "error"
                });
                break;
        }
        return true;
    }

    // 其它错误
    const errorMessage = xhr.responseJSON?.message || xhr.statusText || i18n('request_failed');
    
    // 提取公共的 showMessage 调用
    const showMessageOptions = {title: errorMessage, icon: "error"};
    
    switch (settings.default) {
        case 'element':
            if (settings.element) {
                $(settings.element).html(errorMessage).show();
            } else {
                showMessage(showMessageOptions);
            }
            break;

        case 'field':
            if (settings.form) {
                showMessage(showMessageOptions);
            } else {
                showMessage(showMessageOptions);
            }
            break;

        case 'swal':
        default:
            showMessage(showMessageOptions);
            break;
    }

    return false;
}

/* -----------------------
   AJAX 响应处理
   ----------------------- */

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
    const settings = Object.assign({reload: true, showMessage: true}, options);

    if (response?.status === "success") {
        const successCallback = () => {
            if (settings.onSuccess) {
                settings.onSuccess(response);
            } else if (settings.redirectUrl) {
                window.location.href = settings.redirectUrl;
            } else if (settings.reload) {
                window.location.reload();
            }
        };

        if (settings.showMessage) {
            showMessage({
                title: response.message || i18n('operation_success'),
                icon: "success",
                showConfirmButton: false,
                callback: successCallback
            });
        } else {
            successCallback();
        }
    } else {
        const errorCallback = () => {
            if (settings.onError) settings.onError(response);
        };

        if (settings.showMessage) {
            showMessage({
                title: response.message || i18n('operation_failed'),
                icon: "error",
                showConfirmButton: true,
                callback: errorCallback
            });
        } else if (settings.onError) {
            settings.onError(response);
        }
    }

    return response;
}

/* -----------------------
   其他工具函数
   ----------------------- */

/** 重置搜索表单（清除查询参数） */
function resetSearchForm() {
    window.location.href = window.location.href.split("?")[0];
}

/**
 * 初始化表单内 select change 时自动提交
 * 默认：formSelector = "form:not(.modal-body form)"
 */
function initAutoSubmitSelects(formSelector = "form:not(.modal-body form)", excludeSelector = ".modal-body select") {
    // 在提交前禁用空值 input/select，防止空字符串参数传递
    $(formSelector).on("submit", function () {
        const $form = $(this);
        $form.find("input:not([type=\"submit\"]), select").filter(function () {
            return this.value === "";
        }).prop("disabled", true);

        // 提交后恢复 disabled
        setTimeout(() => {
            $form.find(":disabled").prop("disabled", false);
        }, 0);
    });

    // 仅绑定在指定表单内的 select
    $(formSelector).find("select").not(excludeSelector).on("change", function () {
        $(this).closest("form").trigger("submit");
    });
}

/**
 * 复制文本到剪贴板（优先使用 navigator.clipboard）
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
    const settings = Object.assign({
        showMessage: true,
        successMessage: i18n('copy.success'),
        errorMessage: i18n('copy.failed')
    }, options);

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            if (settings.showMessage) showMessage({title: settings.successMessage, icon: "success", autoClose: true});
            settings.onSuccess?.();
        }).catch(err => {
            console.error("Copy failed: ", err);
            if (settings.showMessage) showMessage({title: settings.errorMessage, icon: "error"});
            settings.onError?.(err);
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
            if (success && settings.showMessage) showMessage({title: settings.successMessage, icon: "success", autoClose: true});
            success && settings.onSuccess?.();
        } catch (err) {
            console.error("Unable to copy text: ", err);
            if (settings.showMessage) showMessage({title: settings.errorMessage, icon: "error"});
            settings.onError?.(err);
        }

        document.body.removeChild(textarea);
        return success;
    }
}

/* -----------------------
   通用删除确认
   ----------------------- */

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
    const defaults = {
        titleMessage: i18n('warning'),
    };

    let text = options.text;
    if (!text && !options.html) {
        text = i18n('confirm.delete').replace("{attribute}", attribute || "").replace("{name}", name || "");
    }

    showConfirm({
        title: options.title || defaults.titleMessage,
        icon: options.icon || "warning",
        text: text,
        html: options.html,
        onConfirm: () => {
            ajaxDelete(url, {}, {
                success: (response) => {
                    handleResponse(response, {
                        reload: options.reload !== false,
                        redirectUrl: options.redirectUrl,
                        onSuccess: options.callback || options.onSuccess,
                        onError: options.onError
                    });
                }
            });
        }
    });
}
