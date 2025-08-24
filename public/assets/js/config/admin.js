/**
 * 自动填充表单字段
 * @param {Object} data - 要填充的数据对象
 * @param {Object} options - 配置选项
 * @param {string} options.formSelector - 表单选择器，默认为 'form'
 * @param {Array} options.skipFields - 跳过的字段名
 */
function autoPopulateForm(data, options = {}) {
    if (!data) return;

    const defaults = {
        formSelector: "form", skipFields: []
    };

    const settings = { ...defaults, ...options };

    // 获取表单内的所有输入元素
    const $form = $(settings.formSelector);
    const $inputs = $form.find("input, select, textarea");

    $inputs.each(function() {
        const $element = $(this);
        const name = $element.attr("name");
        const id = $element.attr("id");

        // 使用 name 作为主要查找键，id 作为备选
        const fieldKey = name || id;

        // 跳过没有名称或ID的元素，以及明确指定跳过的字段
        if (!fieldKey || settings.skipFields.includes(fieldKey)) {
            return; // continue to next element
        }

        // 处理数组字段（如 roles[]）
        const cleanFieldKey = fieldKey.replace(/\[\]$/, "");

        // 从数据对象中获取对应值
        let value = getObjectValue(data, cleanFieldKey);

        // 如果找不到值，尝试使用原始字段名
        if (value === undefined && cleanFieldKey !== fieldKey) {
            value = getObjectValue(data, fieldKey);
        }

        if (value !== undefined) {
            // 根据元素类型设置值
            setElementValue($element, value);
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
    const $form = typeof formSelector === "string" ? $(formSelector) : formSelector;
    const defaults = {
        excludeFields: [],
        removeEmpty: false,
    };

    const settings = { ...defaults, ...options };
    const formData = {};

    // 收集所有表单元素的值
    $form.find('input:not([hidden]), select, textarea').each(function() {
        const $element = $(this);
        const name = $element.attr('name');
        const type = $element.attr('type');
        const tagName = $element.prop('tagName').toLowerCase();

        // 跳过没有名称的字段和排除的字段
        if (!name || settings.excludeFields.includes(name)) {
            return;
        }

        // 处理数组字段
        const isArrayField = name.endsWith('[]');
        const fieldName = isArrayField ? name.slice(0, -2) : name;

        let value;

        // 标准值收集
        switch (tagName) {
            case 'input':
                switch (type) {
                    case 'checkbox':
                        if (isArrayField) {
                            if (!formData[fieldName]) formData[fieldName] = [];
                            if ($element.is(':checked')) {
                                formData[fieldName].push($element.val());
                            }
                        } else if($element.attr("data-plugin") === "switchery"){
                            value = $element.is(':checked') ? 1 : 0;
                        }
                        else{
                            value = $element.is(':checked') ? $element.val() : null;
                        }
                        break;
                    case 'radio':
                        if ($element.is(':checked')) {
                            value = $element.val();
                        }
                        break;
                    default:
                        // 特殊处理 datepicker 元素
                        if ($element.attr("data-plugin") === "datepicker" || $element.parent().attr("data-plugin") === "datepicker" || $element.parent().hasClass("input-daterange")) {
                            value = formatDateToYMD($element.datepicker('getDate'));
                        } else if ($element.attr("data-plugin") === "asColorPicker") {
                            // asColorPicker 取值
                            value = $element.asColorPicker('val');
                        } else {
                            value = $element.val();
                        }
                }
                break;

            case 'select':
                if ($element.prop('multiple')) {
                    value = $element.val() || [];
                } else {
                    value = $element.val();
                }
                break;

            case 'textarea':
                value = $element.val();
                break;
        }

        // 处理数组字段
        if (isArrayField) {
            if (!formData[fieldName]) formData[fieldName] = [];
            if (value !== undefined && value !== null) {
                if (Array.isArray(value)) {
                    formData[fieldName] = [...formData[fieldName], ...value];
                } else {
                    formData[fieldName].push(value);
                }
            }
        } else if (value !== undefined) {
            // 避免覆盖已设置的值（如radio按钮）
            if (formData[fieldName] === undefined || type !== 'radio' || value !== null) {
                formData[fieldName] = value;
            }
        }
    });

    // 去除空值
    if (settings.removeEmpty) {
        return Object.fromEntries(
            Object.entries(formData).filter(([_, value]) => {
                if (Array.isArray(value)) {
                    return value.length > 0;
                }
                return value !== "" && value !== null && value !== undefined;
            })
        );
    }

    return formData;
}


/**
 * 格式化日期为 Y-m-d 格式
 */
function formatDateToYMD(date) {
    if (!date) {
        return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * 从嵌套对象中获取值
 * @param {Object} obj - 源对象
 * @param {string} path - 属性路径，支持点号分隔
 * @returns {*} 属性值或 undefined
 */
function getObjectValue(obj, path) {
    if (!obj || !path) return undefined;

    // 处理点号分隔的路径
    const keys = path.split(".");
    let current = obj;

    for (let i = 0; i < keys.length; i++) {
        if (current === null || current === undefined) {
            return undefined;
        }
        current = current[keys[i]];
    }

    return current;
}

/**
 * 设置元素值
 * @param {jQuery} $element - jQuery元素对象
 * @param {*} value - 要设置的值
 */
function setElementValue($element, value) {
    const type = $element.attr("type");
    const tagName = $element.prop("tagName").toLowerCase();

    switch (tagName) {
        case "input":
            switch (type) {
                case "radio":
                    $element.filter(`[value="${value}"]`).click();
                    break;
                case "checkbox":
                    if (Array.isArray(value)) {
                        $element.each(function() {
                            const $this = $(this);
                            const isChecked = value.includes($this.val());
                            if ($this.is(':checked') !== isChecked) {
                                $this.click();
                            }
                        });
                    } else {
                        const shouldBeChecked = value === true || value === 1 || value === "1";
                        if ($element.is(':checked') !== shouldBeChecked) {
                            $element.click();
                        }
                    }
                    break;
                default:
                    // 特殊处理 datepicker 元素
                    if ($element.attr("data-plugin") === "datepicker" || $element.parent().attr("data-plugin") === "datepicker" || $element.parent().hasClass("input-daterange")) {
                        $element.datepicker("setDate", new Date(value));
                        return;
                    }
                    
                    // 特殊处理 asColorPicker 元素
                    if ($element.attr("data-plugin") === "asColorPicker") {
                        $element.asColorPicker('val', value);
                        return;
                    }

                    $element.val(value);
            }
            break;

        case "select":
            if ($element.attr("data-plugin") === "multiSelect") {
                $element.multiSelect('select', value);
            }else if ($element.attr("data-plugin") === "selectpicker") {
                $element.selectpicker("val", value);
                $element.selectpicker("refresh");
            } else {
                $element.val(value);
            }
            break;

        case "textarea":
            $element.val(value);
            break;
    }
}

