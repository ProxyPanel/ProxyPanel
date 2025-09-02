/** 判断字段是否数组（以 [] 结尾）并返回标准名字 */
function normalizeFieldName(name) {
    if (!name) return {isArray: false, base: name};
    if (name.endsWith('[]')) return {isArray: true, base: name.slice(0, -2)};
    return {isArray: false, base: name};
}

/** 格式化 Date 为 Y-m-d */
function formatDateToYMD(date) {
    if (!date) return '';
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/**
 * 从嵌套对象中获取值
 * @param {Object} obj - 源对象
 * @param {string} path - 属性路径，支持点号分隔
 * @returns {*} 属性值或 undefined
 */
function getObjectValue(obj, path) {
    if (!obj || !path) return undefined;
    return path.split('.').reduce((cur, key) => (cur !== null && cur !== undefined) ? cur[key] : undefined, obj);
}

/**
 * 自动填充表单字段
 * @param {Object} data - 数据对象
 * @param {Object} options - 配置选项
 * @param {string} options.formSelector - 表单选择器，默认为 'form'
 * @param {Array} options.skipFields - 跳过的字段名
 */
function autoPopulateForm(data, options = {}) {
    if (!data) return;

    const settings = {formSelector: 'form', skipFields: [], ...options};
    const $form = $(settings.formSelector);
    if (!$form.length) return;

    // 查询所有 input/select/textarea（包含可能是同名的多元素）
    $form.find('input, select, textarea').each(function () {
        const $el = $(this);
        const name = $el.attr('name') || $el.attr('id');

        if (!name || settings.skipFields.includes(name)) return;

        const {isArray, base} = normalizeFieldName(name);

        // 优先使用无 [] 的字段名去 data 中取值；若不存在且原名不是相同，则尝试原名
        let value = getObjectValue(data, base);
        if (value === undefined && base !== name) {
            value = getObjectValue(data, name);
        }

        if (value !== undefined) {
            const tag = $el.prop('tagName').toLowerCase();
            const type = $el.attr('type');
            const plugin = $el.attr('data-plugin');

            if (tag === 'input') {
                if (type === 'radio') {
                    // $el 可能是选组：将匹配 value 的那项触发 click（保持原来用 click 的行为）
                    $el.filter(`[value="${value}"]`).each(function () {
                        const $this = $(this);
                        if (!$this.is(':checked')) $this.click();
                    });
                    return;
                }

                if (type === 'checkbox') {
                    if (Array.isArray(value)) {
                        // 对应多个 checkbox（数组值）
                        $el.each(function () {
                            const $this = $(this);
                            const should = value.includes($this.val());
                            if ($this.is(':checked') !== should) $this.click();
                        });
                    } else {
                        // 单一 checkbox（switchery 等插件映射 1/0）
                        const shouldBeChecked = (value === true || value === 1 || value === '1' || value === 'true');
                        $el.each(function () {
                            const $this = $(this);
                            if ($this.is(':checked') !== shouldBeChecked) $this.click();
                        });
                    }
                    return;
                }

                // 非选择类 input
                if (plugin === 'datepicker') {
                    // 设置日期，若 value 为空则传 null 以清除
                    try {
                        $el.datepicker('setDate', value ? new Date(value) : null);
                    } catch (e) {
                        // 忽略插件异常
                    }
                    return;
                }
                if (plugin === 'asColorPicker') {
                    try {
                        $el.asColorPicker('val', value);
                    } catch (e) { }
                    return;
                }

                $el.val(value);
                return;
            }

            if (tag === 'select') {
                if (plugin === 'multiSelect') {
                    try { $el.multiSelect('select', value); } catch (e) { $el.val(value); }
                    return;
                }
                if (plugin === 'selectpicker') {
                    try {
                        $el.selectpicker('val', value);
                        $el.selectpicker('refresh');
                    } catch (e) {
                        $el.val(value);
                    }
                    return;
                }
                $el.val(value);
                return;
            }

            if (tag === 'textarea') {
                $el.val(value);
                return;
            }
        }
    });
}

/**
 * 收集表单数据
 * @param {string|Object} formSelector - 表单选择器或jQuery对象
 * @param {Object} options - 配置选项
 * @param {Array} options.excludeFields - 排除的字段
 * @param {Array} options.removeEmpty - 过滤掉空字符串
 * @returns {Object} 表单数据对象
 */
function collectFormData(formSelector, options = {}) {
    const $form = (typeof formSelector === 'string') ? $(formSelector) : formSelector;
    if (!$form || !$form.length) return {};

    const settings = {excludeFields: [], removeEmpty: false, ...options};
    const formData = {};

    // 查找非 hidden 的 input/select/textarea（但还要跳过 data-hidden / [hidden] 或父元素 data-hidden）
    $form.find('input:not([hidden]), select:not([hidden]), textarea:not([hidden])').each(function () {
        const $el = $(this);
        const name = $el.attr('name');
        const type = $el.attr('type');
        const tag = $el.prop('tagName').toLowerCase();
        const {isArray, base} = normalizeFieldName(name);

        if (!name || settings.excludeFields.includes(base)) return;

        // 跳过通过 hide() / data-hidden 或父元素 data-hidden 隐藏的元素
        if ($el.is('[hidden], [data-hidden]') || $el.closest('[data-hidden]').length > 0) return;

        let value;
        const plugin = $el.attr('data-plugin');

        if (tag === 'input') {
            if (type === 'checkbox') {
                if (isArray) {
                    // collect all checked ones by pushing to array
                    if (!$el.is(':checked')) {
                        // 不勾选时不推入
                    } else {
                        if (!formData[base]) formData[base] = [];
                        formData[base].push($el.val());
                    }
                    return; // 已在数组处理，不继续后续赋值逻辑
                }

                // 非数组 checkbox：可能是 switchery (取 1/0)，或普通单选取值/否则 null
                if (plugin === 'switchery') {
                    value = $el.is(':checked') ? 1 : 0;
                } else {
                    value = $el.is(':checked') ? $el.val() : null;
                }
            } else if (type === 'radio') {
                // 仅在 checked 时读取值；避免覆盖其他同名 radio
                if ($el.is(':checked')) value = $el.val();
                else return;
            } else {
                // 其他 input，特殊插件处理
                if (plugin === 'datepicker' || $el.parent().hasClass('input-daterange')) {
                    value = formatDateToYMD($el.datepicker('getDate'));
                } else if (plugin === 'asColorPicker') {
                    value = $el.asColorPicker('val');
                } else {
                    value = $el.val();
                }
            }
        } else if (tag === 'select') {
            value = $el.prop('multiple') ? ($el.val() || []) : $el.val();
        } else if (tag === 'textarea') {
            value = $el.val();
        }

        // 将值写入 formData（注意 radio 与其他覆盖逻辑）
        if (isArray) {
            if (!formData[base]) formData[base] = [];
            if (value !== undefined && value !== null) {
                formData[base].push(...(Array.isArray(value) ? value : [value]));
            }
        } else if (value !== undefined) {
            // 保持原逻辑：避免 radio 被未选覆盖（radio 在未选时直接 return）
            if (formData[base] === undefined || type !== 'radio' || value !== null) {
                formData[base] = value;
            }
        }
    });

    // removeEmpty 过滤空字符串 / null / undefined / 空数组
    if (settings.removeEmpty) {
        return Object.fromEntries(Object.entries(formData).filter(([_, v]) => {
            if (Array.isArray(v)) return v.length > 0;
            return v !== "" && v !== null && v !== undefined;
        }));
    }

    return formData;
}

/* -----------------------
   jQuery hide/show/toggle 拦截（保留原有行为）
   ----------------------- */

(function ($) {
    const origHide = $.fn.hide;
    const origShow = $.fn.show;
    const origToggle = $.fn.toggle;

    // 仅在实际状态变化时修改属性（减少 DOM 写入）
    function setDataHiddenIfChanged($els, hidden) {
        const attrVal = hidden ? 'true' : null;
        $els.each(function () {
            const cur = this.getAttribute('data-hidden');
            if (cur !== attrVal) {
                if (attrVal === null) this.removeAttribute('data-hidden');
                else this.setAttribute('data-hidden', 'true');
            }
        });
    }

    // hide -> 执行原 hide 后标记为隐藏
    $.fn.hide = function () {
        const res = origHide.apply(this, arguments);
        // 被隐藏 -> data-hidden = 'true'
        setDataHiddenIfChanged(this, true);
        return res;
    };

    // show -> 执行原 show 后移除标记
    $.fn.show = function () {
        const res = origShow.apply(this, arguments);
        // 被显示 -> 移除 data-hidden
        setDataHiddenIfChanged(this, false);
        return res;
    };

    // toggle 需要处理两种情况：传入布尔或不传
    $.fn.toggle = function (state) {
        if (typeof state === 'boolean') {
            // 如果有布尔参数，原生方法会按 state 显示/隐藏
            const res = origToggle.call(this, state);
            // state === true -> show -> hidden = false
            setDataHiddenIfChanged(this, !state);
            return res;
        }

        // 无参数：调用原始 toggle，然后依据最终可见性标记
        const res = origToggle.apply(this, arguments);
        // 逐项检查最终是否可见（避免计算样式多次：一次查询 .is(':visible')）
        this.each(function () {
            const $el = $(this);
            // :visible 计算开销可接受（仅在 toggle 时），并能反映 CSS/display/class 的最终结果
            const isVisible = $el.is(':visible');
            setDataHiddenIfChanged($el, !isVisible);
        });
        return res;
    };
})(jQuery);

