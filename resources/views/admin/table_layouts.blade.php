@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <style>
        #swal2-content {
            display: grid !important;
        }

        .table a {
            text-decoration: none;
        }

        .table .th-inner a {
            color: #76838f;
        }
    </style>
    @stack('css')
@endsection
@section('javascript')
    <script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script>
        $("form:not(.modal-body form)").on("submit", function() {
            $(this).find("input:not([type=\"submit\"]), select").filter(function() {
                return this.value === "";
            }).prop("disabled", true);

            setTimeout(function() {
                $(this).find(":disabled").prop("disabled", false);
            }, 0);
        });

        $("select").not(".modal-body select").on("change", function() {
            $(this).closest("form").trigger("submit");
        });

        function autoInitSelectpickers() {
            $('select[data-plugin="selectpicker"]').each(function() {
                const $select = $(this);
                const fieldName = $select.attr('name').replace('[]', ''); // 处理多选字段
                const queryValue = getUrlParameter(fieldName);

                if (queryValue) {
                    $select.selectpicker("val", queryValue);
                }
            });
        }

        // 获取 URL 参数的辅助函数
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        $(document).ready(function() {
            autoInitSelectpickers();
        });


        // 使用事件委托处理所有删除按钮点击
        document.addEventListener('click', function(e) {
            // 检查被点击的元素是否是删除按钮或包含在删除按钮内
            const deleteButton = e.target.closest('[data-action="delete"]');

            if (deleteButton) {
                e.preventDefault();

                // 查找包含 data-delete-config 的最近父元素
                const container = deleteButton.closest('[data-delete-config]');
                if (!container) {
                    console.error('Delete button must be inside an element with data-delete-config attribute');
                    return;
                }

                try {
                    // 解析配置（处理HTML转义字符）
                    const configStr = container.getAttribute('data-delete-config');
                    const config = JSON.parse(configStr);

                    // 获取 ID 和名称（优先使用手动指定的值，否则从表格中自动获取）
                    const id = deleteButton.getAttribute('data-id') || getCellValue(deleteButton, config.idColumn || 0);
                    const name = deleteButton.getAttribute('data-name') || getCellValue(deleteButton, config.nameColumn || 1);
                    const attribute = deleteButton.getAttribute('data-attribute') || config.attribute;

                    // 验证必要参数
                    if (!id || !name) {
                        console.error('No ID/name found for delete action');
                        return;
                    }

                    // 构建 URL 并执行删除
                    const url = config.url.replace('PLACEHOLDER', id);
                    confirmDelete(url, name, attribute, config.options || {});

                } catch (error) {
                    console.error('Error processing delete action:', error);
                }
            }
        });

        // 获取表格单元格值的辅助函数
        function getCellValue(button, columnIndex) {
            const row = button.closest('tr');
            if (!row) return null;

            const cells = row.querySelectorAll('td');
            if (cells.length <= columnIndex) return null;

            const cell = cells[columnIndex];
            // 首先检查单元格内是否有输入元素
            const input = cell.querySelector('input, select, textarea');
            return input ? input.value.trim() : cell.textContent.trim();
        }
    </script>
    @stack('javascript')
@endsection
