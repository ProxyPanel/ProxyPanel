@extends('admin.table_layouts')
@push('css')
    <style>
        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }

        .list-icons>li {
            border-bottom: 1px solid #e4eaec !important;
            padding: 5px 8px;
        }

        .list-icons>li:last-of-type {
            border-bottom: none !important;
        }

        .sub-container {
            border-left: 2px solid #e9ecef;
        }

        .sub-container>li {
            padding: 8px 10px;
            border-bottom: 1px dashed #e9ecef !important;
            font-size: 0.9em;
        }

        .operation-message {
            max-width: 60%;
            word-wrap: break-word;
            word-break: break-all;
            white-space: normal;
        }
    </style>
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.node.list')" :theads="[
            'ID',
            trans('model.common.type'),
            trans('model.node.name'),
            trans('model.node.domain'),
            'IP',
            trans('model.node.static'),
            trans('model.node.online_user'),
            trans('model.node.data_consume'),
            trans('model.node.data_rate'),
            trans('model.common.extend'),
            trans('common.status.attribute'),
            trans('common.action'),
        ]" :count="trans('admin.node.counts', ['num' => $nodeList->total()])" :pagination="$nodeList->links()" :delete-config="['url' => route('admin.node.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.node.attribute'), 'nameColumn' => 2]">
            @canany(['admin.node.reload', 'admin.node.geo', 'admin.node.create'])
                <x-slot:actions>
                    <div class="btn-group">
                        @can('admin.node.reload')
                            @if ($nodeList->where('type', 4)->count())
                                <button class="btn btn-info" type="button" onclick="reload()">
                                    <i class="icon wb-reload" id="reload_all" aria-hidden="true"></i> {{ trans('admin.node.reload_all') }}
                                </button>
                            @endif
                        @endcan
                        @can('admin.node.geo')
                            <button class="btn btn-outline-default" type="button" onclick="handleNodeAction('geo')">
                                <i class="icon wb-map" id="geo_all" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo_all') }}
                            </button>
                        @endcan
                        @can('admin.node.check')
                            <button class="btn btn-outline-primary" type="button" onclick="handleNodeAction('check')">
                                <i class="icon wb-signal" id="check_all" aria-hidden="true"></i> {{ trans('admin.node.connection_test_all') }}
                            </button>
                        @endcan
                        @can('admin.node.create')
                            <a class="btn btn-primary" href="{{ route('admin.node.create') }}">
                                <i class="icon wb-plus"></i> {{ trans('common.add') }}
                            </a>
                        @endcan
                    </div>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($nodeList as $node)
                    <tr>
                        <td> {{ $node->id }} </td>
                        <td> {{ $node->type_label }} </td>
                        <td> {{ $node->name }} </td>
                        <td> {{ $node->server }} </td>
                        <td> {{ $node->is_ddns ? trans('model.node.ddns') : $node->ip }} </td>
                        <td> {{ $node->uptime }} </td>
                        <td> {{ $node->online_users ?: '-' }} </td>
                        <td> {{ $node->transfer }} </td>
                        <td> {{ $node->traffic_rate }} </td>
                        <td>
                            @isset($node->profile['passwd'])
                                {{-- 单端口 --}}
                                <span class="badge badge-lg badge-info"><i class="fa-solid fa-1" aria-hidden="true"></i></span>
                            @endisset
                            @if ($node->is_display === 0)
                                {{-- 节点完全不可见 --}}
                                <span class="badge badge-lg badge-danger"><i class="icon wb-eye-close" aria-hidden="true"></i></span>
                            @elseif($node->is_display === 1)
                                {{-- 节点只在页面中显示 --}}
                                <span class="badge badge-lg badge-danger"><i class="fa-solid fa-link-slash" aria-hidden="true"></i></span>
                            @elseif($node->is_display === 2)
                                {{-- 节点只可被订阅到 --}}
                                <span class="badge badge-lg badge-danger"><i class="fa-solid fa-store-slash" aria-hidden="true"></i></span>
                            @endif
                            @if ($node->ip)
                                <span class="badge badge-md badge-info"><i class="fa-solid fa-4" aria-hidden="true"></i></span>
                            @endif
                            @if ($node->ipv6)
                                <span class="badge badge-md badge-info"><i class="fa-solid fa-6" aria-hidden="true"></i></span>
                            @endif
                        </td>
                        <td>
                            @if ($node->isOnline)
                                @if ($node->status)
                                    {{ $node->load }}
                                @else
                                    <i class="yellow-700 icon icon-spin fa-solid fa-gear" aria-hidden="true"></i>
                                @endif
                            @else
                                @if ($node->status)
                                    <i class="red-600 fa-solid fa-gear" aria-hidden="true"></i>
                                @else
                                    <i class="red-600 fa-solid fa-handshake-simple-slash" aria-hidden="true"></i>
                                @endif
                            @endif
                        </td>
                        <td>
                            @canany(['admin.node.edit', 'admin.node.clone', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.check',
                                'admin.node.reload'])
                                <x-ui.dropdown>
                                    @can('admin.node.edit')
                                        <x-ui.dropdown-item :url="route('admin.node.edit', [$node->id, 'page' => Request::query('page', 1)])" icon="wb-edit" :text="trans('common.edit')" />
                                    @endcan
                                    @can('admin.node.clone')
                                        <x-ui.dropdown-item :url="route('admin.node.clone', $node)" icon="wb-copy" :text="trans('admin.clone')" />
                                    @endcan
                                    @can('admin.node.destroy')
                                        <x-ui.dropdown-item color="red-700" url="javascript:destroy('{{ $node->id }}')" icon="wb-trash" :text="trans('common.delete')" />
                                    @endcan
                                    @can('admin.node.monitor')
                                        <x-ui.dropdown-item :url="route('admin.node.monitor', $node)" icon="wb-stats-bars" :text="trans('admin.node.traffic_monitor')" />
                                    @endcan
                                    <hr />
                                    @can('admin.node.geo')
                                        <x-ui.dropdown-item id="geo_{{ $node->id }}" url="javascript:handleNodeAction('geo', '{{ $node->id }}')" icon="wb-map"
                                                            :text="trans('admin.node.refresh_geo')" />
                                    @endcan
                                    @can('admin.node.check')
                                        <x-ui.dropdown-item id="node_{{ $node->id }}" url="javascript:handleNodeAction('check', '{{ $node->id }}')"
                                                            icon="wb-signal" :text="trans('admin.node.connection_test')" />
                                    @endcan
                                    @if ($node->type === 4)
                                        @can('admin.node.reload')
                                            <hr />
                                            <x-ui.dropdown-item id="reload_{{ $node->id }}" url="javascript:reload({{ $node->id }})" icon="wb-reload"
                                                                :text="trans('admin.node.reload')" />
                                        @endcan
                                    @endif
                                </x-ui.dropdown>
                            @endcan
                        </td>
                    </tr>
                    @foreach ($node->childNodes as $childNode)
                        <tr class="bg-blue-grey-200 grey-700 table-borderless">
                            <td></td>
                            <td><i class="float-left fa-solid fa-right-left" aria-hidden="true"></i>
                                <strong>{{ trans('model.node.transfer') }}</strong>
                            </td>
                            <td> {{ $childNode->name }} </td>
                            <td> {{ $childNode->server }} </td>
                            <td> {{ $childNode->is_ddns ? trans('model.node.ddns') : $childNode->ip }} </td>
                            <td colspan="2">
                                @if ($childNode->is_display === 0)
                                    {{-- 节点完全不可见 --}}
                                    <span class="badge badge-lg badge-danger"><i class="icon wb-eye-close" aria-hidden="true"></i></span>
                                @elseif($childNode->is_display === 1)
                                    {{-- 节点只在页面中显示 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-link-slash" aria-hidden="true"></i></span>
                                @elseif($childNode->is_display === 2)
                                    {{-- 节点只可被订阅到 --}}
                                    <span class="badge badge-lg badge-danger"><i class="fa-solid fa-store-slash" aria-hidden="true"></i></span>
                                @endif
                            </td>
                            <td colspan="2">
                                @if (!$childNode->status || !$node->status)
                                    <i class="red-600 fa-solid fa-handshake-simple-slash" aria-hidden="true"></i>
                                @endif
                            </td>
                            <td colspan="3">
                                @canany(['admin.node.edit', 'admin.node.clone', 'admin.node.destroy', 'admin.node.monitor', 'admin.node.geo', 'admin.node.check'])
                                    <x-ui.dropdown>
                                        @can('admin.node.edit')
                                            <x-ui.dropdown-item :url="route('admin.node.edit', [$childNode->id, 'page' => Request::query('page', 1)])" icon="wb-edit" :text="trans('common.edit')" />
                                        @endcan
                                        @can('admin.node.clone')
                                            <x-ui.dropdown-item :url="route('admin.node.clone', $childNode)" icon="wb-copy" :text="trans('admin.clone')" />
                                        @endcan
                                        @can('admin.node.destroy')
                                            <x-ui.dropdown-item color="red-700" url="javascript:destroy('{{ $childNode->id }}')" icon="wb-trash" :text="trans('common.delete')" />
                                        @endcan
                                        @can('admin.node.monitor')
                                            <x-ui.dropdown-item :url="route('admin.node.monitor', $childNode)" icon="wb-stats-bars" :text="trans('admin.node.traffic_monitor')" />
                                        @endcan
                                        <hr />
                                        @can('admin.node.geo')
                                            <x-ui.dropdown-item id="geo_{{ $childNode->id }}" url="javascript:handleNodeAction('geo', '{{ $childNode->id }}')"
                                                                icon="wb-map" :text="trans('admin.node.refresh_geo')" />
                                        @endcan
                                        @can('admin.node.check')
                                            <x-ui.dropdown-item id="node_{{ $childNode->id }}" url="javascript:handleNodeAction('check', '{{ $childNode->id }}')"
                                                                icon="wb-signal" :text="trans('admin.node.connection_test')" />
                                        @endcan
                                    </x-ui.dropdown>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    <!-- 节点检测结果模态框 -->
    <x-ui.modal id="nodeCheckModal" :title="trans('admin.node.connection_test')" size="lg">
    </x-ui.modal>

    <!-- 节点刷新地理位置结果模态框 -->
    <x-ui.modal id="nodeGeoRefreshModal" :title="trans('admin.node.refresh_geo')" size="lg">
    </x-ui.modal>

    <!-- 节点重载结果模态框 -->
    <x-ui.modal id="nodeReloadModal" :title="trans('admin.node.reload')" size="lg">
    </x-ui.modal>

    <!-- 节点删除结果模态框 -->
    <x-ui.modal id="nodeDeleteModal" :title="trans('admin.node.delete_operations')" size="lg">
    </x-ui.modal>
@endsection
@push('javascript')
    @vite(['resources/js/app.js'])
    <script>
        // 国际化配置
        window.i18n.extend({
            "broadcast": {
                "error": '{{ trans('common.error') }}',
                "websocket_unavailable": '{{ trans('common.broadcast.websocket_unavailable') }}',
                "websocket_disconnected": '{{ trans('common.broadcast.websocket_disconnected') }}',
                "setup_failed": '{{ trans('common.broadcast.setup_failed') }}',
                "disconnect_failed": '{{ trans('common.broadcast.disconnect_failed') }}'
            }
        });

        // 操作上下文管理 - 记录当前正在进行中的操作
        const actionContexts = {
            check: null,
            geo: null,
            reload: null,
            delete: null
        };

        // 网络状态映射
        const networkStatus = @json(trans('admin.network_status'));

        // 操作名称映射
        const operationNames = {
            "handle_ddns": '{{ trans('admin.node.operation.handle_ddns') }}',
            "delete_node": '{{ trans('admin.node.operation.delete_node') }}'
        };

        // 子操作名称映射
        const subOperationNames = {
            "destroy": '{{ trans('admin.node.operation.delete_domain_record') }}'
        };

        // 操作配置表
        const ACTION_CFG = {
            check: {
                icon: "wb-signal",
                routeTpl: '{{ route('admin.node.check', 'PLACEHOLDER') }}',
                modal: "#nodeCheckModal",
                btnSelector: (id) => id ? $(`#node_${id}`) : $("#check_all"),
                buildUI: buildCheckUI,
                updateUI: updateCheckUI,
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.connection_test')]) }}'
            },
            geo: {
                icon: "wb-map",
                routeTpl: '{{ route('admin.node.geo', 'PLACEHOLDER') }}',
                modal: "#nodeGeoRefreshModal",
                btnSelector: (id) => id ? $(`#geo_${id}`) : $("#geo_all"),
                buildUI: buildNodeTableUI,
                updateUI: updateNodeOperationUI,
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.refresh_geo')]) }}'
            },
            reload: {
                icon: "wb-reload",
                routeTpl: '{{ route('admin.node.reload', 'PLACEHOLDER') }}',
                modal: "#nodeReloadModal",
                btnSelector: (id) => id ? $(`#reload_${id}`) : $("#reload_all"),
                buildUI: buildNodeTableUI,
                updateUI: updateNodeOperationUI,
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.reload')]) }}'
            },
            delete: {
                icon: "wb-trash",
                routeTpl: '{{ route('admin.node.destroy', 'PLACEHOLDER') }}',
                modal: "#nodeDeleteModal",
                btnSelector: () => {},
                buildUI: buildDeleteUI,
                updateUI: updateDeleteUI,
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.delete_operations')]) }}'
            }
        };

        // 统一设置 spinner
        function setSpinner($el, iconClass, on = false) {
            if (!$el?.length) return;
            $el.removeClass(`${iconClass} wb-loop icon-spin`);
            $el.addClass(on ? "wb-loop icon-spin" : iconClass);
        }

        // 清理函数
        function cleanupActionContext(type) {
            const context = actionContexts[type];
            if (!context) return;
            window.broadcastingManager.unsubscribe(context.channel);
            actionContexts[type] = null;
        }

        // 通用操作入口
        function handleNodeAction(type, id) {
            const cfg = ACTION_CFG[type];
            const $btn = cfg.btnSelector(id);
            const channel = window.broadcastingManager.getChannelName(`node.${type}`, id);

            // 如果已有操作在进行中，直接显示 modal（不重新发起请求）
            if (actionContexts[type]) {
                $(cfg.modal).modal("show");
                return;
            }

            // 记录当前操作上下文
            actionContexts[type] = {
                actionId: id,
                channel: channel,
                $btn: $btn
            };

            setSpinner($btn, cfg.icon, true);

            // 订阅广播频道
            const success = window.broadcastingManager.subscribe(channel, ".node.actions", (e) => handleResult(type, id, e.data || e));

            if (!success) {
                setSpinner($btn, cfg.icon);
                actionContexts[type] = null;
                return;
            }

            const routeUrl = jsRoute(cfg.routeTpl, id);

            // AJAX 调用
            const ajaxOptions = {
                success: () => {},
                error: (xhr, status, error) => {
                    window.broadcastingManager.handleAjaxError(
                        '{{ trans('common.error') }}',
                        `{{ trans('common.request_failed') }} ${error}: ${xhr?.responseJSON?.exception}`
                    );
                    setSpinner($btn, cfg.icon);
                    cleanupActionContext(type);
                }
            };

            if (type === "delete") {
                ajaxDelete(routeUrl, {}, ajaxOptions);
            } else {
                ajaxPost(routeUrl, {}, ajaxOptions);
            }
        }

        // 处理广播数据
        function handleResult(type, id, e) {
            const cfg = ACTION_CFG[type];
            const context = actionContexts[type];

            if (!cfg || !context) return;

            if (e.list) {
                cfg.buildUI(e, type);
            } else {
                cfg.updateUI(e.node_id || context.actionId, e, type);

                // 检查是否所有操作都完成
                const modal = $(cfg.modal);
                if (modal.find(".icon-spin").length === 0) {
                    setSpinner(context.$btn, cfg.icon);

                    if (cfg.successMsg) {
                        toastr.success(cfg.successMsg);
                    }
                }
            }
        }

        function getStatusIcon(status) {
            return status === 1 ? `<i class="icon wb-check text-success"></i>` : `<i class="icon wb-close text-danger"></i>`;
        }

        // 通用UI构建函数
        function buildNodeTableUI(e, type) {
            const modalSelector = ACTION_CFG[type]?.modal;
            $(modalSelector).modal("show");

            let html = `<table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('validation.attributes.name') }}</th>
                                    <th>{{ trans('common.status.attribute') }}</th>
                                    <th>{{ trans('validation.attributes.message') }}</th>
                                </tr>
                            </thead>
                            <tbody>`;

            Object.entries(e.list).forEach(([nodeId, nodeName]) => {
                html += `<tr data-node-id="${nodeId}">
                            <td>${nodeName}</td>
                            <td><i class="wb-loop icon-spin"></i></td>
                            <td></td>
                        </tr>`;
            });

            document.querySelector(`${modalSelector} .modal-body`).innerHTML = html + "</tbody></table>";
        }

        // 通用节点操作UI更新函数
        function updateNodeOperationUI(nodeId, data, type) {
            const modalSelector = ACTION_CFG[type]?.modal;
            const row = document.querySelector(`${modalSelector} tr[data-node-id="${nodeId}"]`);
            if (!row) return;

            // 默认处理方式（适用于reload等简单操作）
            let info = data.message || "";

            // 特殊处理geo操作
            if (type === "geo" && data.status === 1 && data.original && data.update) {
                info = JSON.stringify(data.original) !== JSON.stringify(data.update) ?
                    `{{ trans('common.update') }}: [${data.original.join(", ")}] => [${data.update.join(", ")}]` : '{{ trans('Not Modified') }}';
            } else if (type === "reload") {
                if (info.message) {
                    info = info.message;
                } else {
                    info = '{{ trans('common.success_item', ['attribute' => trans('admin.node.operation.reload_node')]) }}: ' + data?.success.join(', ');
                    if (data.error && data.error.length > 0) {
                        info += ' | {{ trans('common.failed') }}: ' + data.error.join(', ');
                    }
                }
            }

            row.querySelector("td:nth-child(2)").innerHTML = getStatusIcon(data.status);
            row.querySelector("td:nth-child(3)").innerHTML = info;
        }

        // check UI
        function buildCheckUI(e) {
            $("#nodeCheckModal").modal("show");
            let html = `<div class="row">`;
            const columnClass = Object.keys(e.list).length > 1 ? "col-md-6" : "col-12";

            Object.entries(e.list).forEach(([nodeId, node]) => {
                html += `
                    <div class="${columnClass}" data-node-id="${nodeId}">
                        <h5>${node.name}</h5>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('user.attribute.ip') }}</th>
                                    <th>ICMP</th>
                                    <th>TCP</th>
                                </tr>
                            </thead>
                            <tbody>`;

                node.ips.forEach(ip => {
                    html += `
                        <tr data-ip="${ip}">
                            <td>${ip}</td>
                            <td><i class="wb-loop icon-spin"></i></td>
                            <td><i class="wb-loop icon-spin"></i></td>
                        </tr>`;
                });

                html += `</tbody></table></div>`;
            });
            document.querySelector("#nodeCheckModal .modal-body").innerHTML = html + "</div>";
        }

        function updateCheckUI(nodeId, data) {
            const row = document.querySelector(`#nodeCheckModal div[data-node-id="${nodeId}"] tr[data-ip="${data.ip}"]`);
            if (!row) return;

            row.querySelector("td:nth-child(2)").innerHTML = networkStatus[data.icmp] || networkStatus[4];
            row.querySelector("td:nth-child(3)").innerHTML = networkStatus[data.tcp] || networkStatus[4];
        }

        // delete UI
        function buildDeleteUI(e) {
            $("#nodeDeleteModal").modal("show");
            let html = '<ul class="list-icons">';

            // e.list 是数组形式: ['delete_node', 'handle_ddns']
            e.list.forEach(operation => {
                const operationName = operationNames[operation] || operation;
                html += `
                    <li class="d-flex justify-content-between align-items-center" data-operation="${operation}">
                        <i class="wb-loop icon-spin"></i>
                        <div class="flex-grow-1">
                            ${operationName}
                        </div>
                        <div class="operation-message text-muted small"></div>
                    </li>
                    <ul class="sub-container list-icons"></ul>`;
            });

            document.querySelector("#nodeDeleteModal .modal-body").innerHTML = html + '</ul>';
        }

        function updateDeleteUI(nodeId, data) {
            if (!data.operation) return;

            const $operationItem = $(`#nodeDeleteModal [data-operation="${data.operation}"]`);
            if (!$operationItem.length) return;

            if (!data.sub_operation || data.sub_operation === 'list') {
                $operationItem.find('i:first').replaceWith(getStatusIcon(data.status));
            }

            // 处理子操作（如 DDNS 操作）
            if (data.sub_operation) {
                handleDeleteSubOperation($operationItem, data);
            } else if (data.message) {
                $operationItem.find(".operation-message").text(data.message);
            }

            // 所有操作完成后显示按钮
            showDeleteCompletionButton();
        }

        // 处理删除操作的子操作
        function handleDeleteSubOperation($operationItem, data) {
            // 查找或创建子操作容器
            let $container = $operationItem.nextAll(`.sub-container`).first();

            if ($container.length === 0) return;

            // 特殊处理 DDNS 操作中的 IP 列表预显示
            if (data.delete) {
                data.delete.forEach(ip => {
                    createSubOperationItem($container, 'destroy', ip);
                });
            } else {
                const subOpKey = `${data.sub_operation}_${data.data || ''}`;
                // 更新或创建子操作项
                let $item = $container.find(`[data-sub-operation="${subOpKey}"]`);
                $item.find('i:first').replaceWith(getStatusIcon(data.status));
                if (data.message) {
                    $item.find('.operation-message').text(data.message);
                }
            }
        }

        // 创建删除操作的子操作项
        function createSubOperationItem($container, operation, data) {
            let key = operation + '_' + data;
            let $item = $container.find(`[data-sub-operation="${key}"]`);
            const opName = subOperationNames[operation] || operation;
            const displayText = data ? `${opName} (${data})` : opName;

            if ($item.length) return;

            $item = $(`
                <li class="d-flex justify-content-between align-items-center" data-sub-operation="${key}">
                    <i class="wb-loop icon-spin"></i>
                    <div class="flex-grow-1">
                        ${displayText}
                    </div>
                    <div class="operation-message text-muted small"></div>
                </li>
            `);
            $container.append($item);
        }

        // 显示删除完成确认按钮
        function showDeleteCompletionButton() {
            const $modal = $("#nodeDeleteModal");
            if ($modal.find(".icon-spin").length !== 0 || $modal.find(".modal-footer").length > 0) return;

            $modal.find(".modal-content").append(`
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('common.confirm') }}</button>
                </div>`);
        }

        @can('admin.node.reload')
            function reload(id = null) {
                if (actionContexts['reload']) {
                    $(ACTION_CFG['reload'].modal).modal("show");
                } else {
                    showConfirm({
                        title: '{{ trans('admin.node.reload_confirm') }}',
                        onConfirm: () => handleNodeAction("reload", id)
                    });
                }
            }
        @endcan

        @can('admin.node.destroy')
            function destroy(id = null) {
                const nodeName = $(`tr:has(td:first-child:contains('${id}')) td:nth-child(3)`).text().trim() || id || "";

                showConfirm({
                    title: '{{ trans('common.warning') }}',
                    text: i18n("confirm.delete")
                        .replace("{attribute}", '{{ trans('model.node.attribute') }}')
                        .replace("{name}", nodeName),
                    icon: "warning",
                    onConfirm: () => handleNodeAction("delete", id)
                });
            }
        @endcan

        // 检测、地理位置、重载 modal 的通用处理
        Object.keys(ACTION_CFG).forEach(type => {
            const modalSelector = ACTION_CFG[type].modal;
            $(document).on("hidden.bs.modal", modalSelector, function() {
                const context = actionContexts[type];
                const modalBody = document.querySelector(`${modalSelector} .modal-body`);
                const isLoading = modalBody && modalBody.querySelectorAll('.icon-spin').length > 0;

                if (!isLoading && context) {
                    cleanupActionContext(type);
                    // 清空 modal 内容
                    if (modalBody) {
                        modalBody.innerHTML = '';
                    }
                    if (type === 'delete') {
                        location.reload();
                    }
                }
            });
        });
    </script>
@endpush
