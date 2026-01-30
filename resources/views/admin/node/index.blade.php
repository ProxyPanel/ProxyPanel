@extends('admin.table_layouts')
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
                                    <i class="icon wb-reload" id="reload_0" aria-hidden="true"></i> {{ trans('admin.node.reload_all') }}
                                </button>
                            @endif
                        @endcan
                        @can('admin.node.geo')
                            <button class="btn btn-outline-default" type="button" onclick="handleNodeAction('geo')">
                                <i class="icon wb-map" id="geo_0" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo_all') }}
                            </button>
                        @endcan
                        @can('admin.node.check')
                            <button class="btn btn-outline-primary" type="button" onclick="handleNodeAction('check')">
                                <i class="icon wb-signal" id="check_all_nodes" aria-hidden="true"></i> {{ trans('admin.node.connection_test_all') }}
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
                                        <x-ui.dropdown-item color="red-700" url="javascript:(0)" attribute="data-action=delete" icon="wb-trash" :text="trans('common.delete')" />
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
                                            <x-ui.dropdown-item color="red-700" url="javascript:(0)" attribute="data-action=delete" icon="wb-trash" :text="trans('common.delete')" />
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
@endsection
@push('javascript')
    @vite(['resources/js/app.js'])
    <script>
        window.i18n.extend({
            'broadcast': {
                'error': '{{ trans('common.error') }}',
                'websocket_unavailable': '{{ trans('common.broadcast.websocket_unavailable') }}',
                'websocket_disconnected': '{{ trans('common.broadcast.websocket_disconnected') }}',
                'setup_failed': '{{ trans('common.broadcast.setup_failed') }}',
                'disconnect_failed': '{{ trans('common.broadcast.disconnect_failed') }}'
            }
        });
        // 全局状态
        const state = {
            actionType: null, // 'check' | 'geo' | 'reload'
            actionId: null, // 当前操作针对的节点 id（null/'' 表示批量）
            results: {}, // 按 nodeId 存储节点信息与已收到的数据
            finished: {}, // 标记 nodeId 是否完成
            spinnerFallbacks: {} // 防止无限 spinner 的后备定时器
        };

        const networkStatus = @json(trans('admin.network_status'));

        // 配置表：保留原按钮 id 规则 & 原模态结构
        const ACTION_CFG = {
            check: {
                icon: 'wb-signal',
                routeTpl: '{{ route('admin.node.check', 'PLACEHOLDER') }}',
                event: '.node.actions',
                modal: '#nodeCheckModal',
                btnSelector: (id) => id ? $(`#node_${id}`) : $('#check_all_nodes'),
                buildUI: buildCheckUI,
                updateUI: updateCheckUI,
                isNodeDone: function(node) {
                    // node.ips 是 array，node.data 是按 ip 存放结果
                    if (!Array.isArray(node.ips)) return !!node.data; // 没有 ip 列表的认为收到数据就算
                    const got = Object.keys(node.data || {}).length;
                    return got >= node.ips.length;
                },
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.connection_test')]) }}'
            },
            geo: {
                icon: 'wb-map',
                routeTpl: '{{ route('admin.node.geo', 'PLACEHOLDER') }}',
                event: '.node.actions',
                modal: '#nodeGeoRefreshModal',
                btnSelector: (id) => id ? $(`#geo_${id}`) : $('#geo_0'),
                buildUI: buildGeoUI,
                updateUI: updateGeoUI,
                isNodeDone: function(node) {
                    return !!(node.data && Object.keys(node.data).length > 0);
                },
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.refresh_geo')]) }}'
            },
            reload: {
                icon: 'wb-reload',
                routeTpl: '{{ route('admin.node.reload', 'PLACEHOLDER') }}',
                event: '.node.actions',
                modal: '#nodeReloadModal',
                btnSelector: (id) => id ? $(`#reload_${id}`) : $(`#reload_0`),
                buildUI: buildReloadUI,
                updateUI: updateReloadUI,
                isNodeDone: function(node) {
                    // 重载有 list 或 error 认为完成
                    return !!(node.data && (Array.isArray(node.data.list) || Array.isArray(node.data.error) || node.data.list || node.data.error));
                },
                successMsg: '{{ trans('common.completed_item', ['attribute' => trans('admin.node.reload')]) }}'
            }
        };

        // 统一设置 spinner（显示/隐藏）
        function setSpinner($el, iconClass, on) {
            if (!$el || !$el.length) return;
            if (on) {
                $el.removeClass(iconClass).addClass('wb-loop icon-spin');
            } else {
                $el.removeClass('wb-loop icon-spin').addClass(iconClass);
            }
        }

        // 启动后备定时器（防止 spinner 卡住）
        function startSpinnerFallback(key, $el, iconClass) {
            clearSpinnerFallback(key);
            state.spinnerFallbacks[key] = setTimeout(() => {
                setSpinner($el, iconClass, false);
                toastr.warning('{{ trans('A Timeout Occurred') }}');
                delete state.spinnerFallbacks[key];
            }, 120000); // 2 分钟兜底
        }

        function clearSpinnerFallback(key) {
            if (state.spinnerFallbacks[key]) {
                clearTimeout(state.spinnerFallbacks[key]);
                delete state.spinnerFallbacks[key];
            }
        }

        // 通用操作入口
        function handleNodeAction(type, id) {
            const cfg = ACTION_CFG[type];
            if (!cfg) return;

            const $btn = cfg.btnSelector(id);
            const channelName = id ? `node.${type}.${id}` : `node.${type}.all`;
            const routeTpl = cfg.routeTpl;

            // 如果相同操作正在进行并且已有结果缓存，则仅打开 modal（不重复发起）
            if (state.actionType === type && String(state.actionId) === String(id) && Object.keys(state.results).length > 0) {
                $(cfg.modal).modal('show');
                return;
            }

            // 开始新操作：清理之前的连接/缓存
            state.actionType = type;
            state.actionId = id;
            state.results = {};
            state.finished = {};

            // 启动 spinner（保持加载直到我们检测到完成）
            setSpinner($btn, cfg.icon, true);
            // 启动后备定时器
            const fallbackKey = `${type}_${id ?? 'all'}`;
            startSpinnerFallback(fallbackKey, $btn, cfg.icon);

            // 使用统一的广播管理器订阅频道
            const success = window.broadcastingManager.subscribe(
                channelName,
                cfg.event,
                (e) => handleResult(e.data || e, type, id, $btn)
            );

            if (!success) {
                // 订阅失败：恢复按钮状态
                setSpinner($btn, cfg.icon, false);
                clearSpinnerFallback(fallbackKey);
                return;
            }

            // 触发后端接口（Ajax）
            ajaxPost(jsRoute(routeTpl, id), {}, {
                beforeSend: function() {
                    // spinner 已经设置
                },
                success: function(ret) {
                    // 不在此处处理最终结果，交由广播处理（避免 race）
                },
                error: function(xhr, status, error) {
                    if (!window.broadcastingManager.isConnected()) {
                        window.broadcastingManager.handleError(i18n('broadcast.websocket_unavailable'));
                    } else {
                        showMessage({
                            title: '{{ trans('common.error') }}',
                            message: `{{ trans('common.request_failed') }} ${error}: ${xhr?.responseJSON?.exception}`,
                            icon: 'error',
                            showConfirmButton: true
                        });
                    }
                    // 出错时恢复 spinner
                    setSpinner($btn, cfg.icon, false);
                    clearSpinnerFallback(fallbackKey);
                }
            });
        }

        // 处理广播数据的统一入口
        function handleResult(e, type, id, $btn) {
            const cfg = ACTION_CFG[type];
            if (!cfg) return;

            // 如果包含 nodeList：构建初始 UI 框架
            if (e.nodeList) {
                Object.keys(e.nodeList).forEach(nodeId => {
                    const nodeInfo = e.nodeList[nodeId];
                    state.results[nodeId] = {
                        name: (typeof nodeInfo === 'string') ? nodeInfo : (nodeInfo.name || ''),
                        ips: (nodeInfo.ips && Array.isArray(nodeInfo.ips)) ? nodeInfo.ips : (nodeInfo.ips || []),
                        data: {}
                    };
                });
                // 构建并显示 modal
                cfg.buildUI();
                return;
            }

            // 处理详细数据
            try {
                const nodeId = e.nodeId;
                if (!nodeId || !state.results[nodeId]) return;

                if (type === 'check' && (e.icmp !== undefined || e.tcp !== undefined)) {
                    if (!state.results[nodeId].data[e.ip]) {
                        state.results[nodeId].data[e.ip] = {};
                    }
                    state.results[nodeId].data[e.ip] = {
                        icmp: e.icmp,
                        tcp: e.tcp
                    };
                    cfg.updateUI(nodeId, e);
                } else if (type === 'geo' && e) {
                    state.results[nodeId].data = e;
                    cfg.updateUI(nodeId, e);
                } else if (type === 'reload' && e) {
                    state.results[nodeId].data = e;
                    cfg.updateUI(nodeId, e);
                }

                // 检查是否所有节点都完成
                const allDone = Object.keys(state.results).length > 0 &&
                    Object.keys(state.results).every(nodeId => cfg.isNodeDone(state.results[nodeId]));

                if (allDone) {
                    const fallbackKey = `${type}_${id ?? 'all'}`;
                    setSpinner($btn, cfg.icon, false);
                    clearSpinnerFallback(fallbackKey);
                    toastr.success(cfg.successMsg);
                }
            } catch (err) {
                console.error('handleResult error', err);
            }
        }

        // check UI
        function buildCheckUI() {
            $('#nodeCheckModal').modal('show');
            const body = document.querySelector('#nodeCheckModal .modal-body');
            let html = '<div class="row">';
            const nodeIds = Object.keys(state.results);
            const columnClass = nodeIds.length > 1 ? 'col-md-6' : 'col-12';

            nodeIds.forEach(nodeId => {
                const node = state.results[nodeId];
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
                if (Array.isArray(node.ips)) {
                    node.ips.forEach(ip => {
                        html += `
                            <tr data-ip="${ip}">
                                <td>${ip}</td>
                                <td><i class="wb-loop icon-spin"></i></td>
                                <td><i class="wb-loop icon-spin"></i></td>
                            </tr>`;
                    });
                }
                html += `</tbody></table></div>`;
            });
            html += '</div>';
            body.innerHTML = html;
        }

        function updateCheckUI(nodeId, data) {
            try {
                // 使用 data-* 属性选择器定位元素
                const row = document.querySelector(`#nodeCheckModal div[data-node-id="${nodeId}"] tr[data-ip="${data.ip}"]`);
                if (!row) return;

                // 使用 nth-child 选择器定位 td 元素
                const icmpEl = row.querySelector('td:nth-child(2)');
                const tcpEl = row.querySelector('td:nth-child(3)');

                if (icmpEl) icmpEl.innerHTML = networkStatus[data.icmp] || networkStatus[4];
                if (tcpEl) tcpEl.innerHTML = networkStatus[data.tcp] || networkStatus[4];
            } catch (e) {}
        }

        // geo UI
        function buildGeoUI() {
            $('#nodeGeoRefreshModal').modal('show');
            const body = document.querySelector('#nodeGeoRefreshModal .modal-body');
            let html = `<table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('validation.attributes.name') }}</th>
                                    <th>{{ trans('common.status.attribute') }}</th>
                                    <th>{{ trans('validation.attributes.message') }}</th>
                                </tr>
                            </thead>
                            <tbody>`;

            Object.keys(state.results).forEach(nodeId => {
                const node = state.results[nodeId];
                html += `
                    <tr data-node-id="${nodeId}">
                        <td>${node.name}</td>
                        <td><i class="wb-loop icon-spin"></i></td>
                        <td><i class="wb-loop icon-spin"></i></td>
                    </tr>`;
            });
            html += '</tbody></table></div>';
            body.innerHTML = html;
        }

        function updateGeoUI(nodeId, data) {
            try {
                const row = document.querySelector(`#nodeGeoRefreshModal tr[data-node-id="${nodeId}"]`);
                if (!row) return;

                const statusEl = row.querySelector('td:nth-child(2)');
                const infoEl = row.querySelector('td:nth-child(3)');
                if (!statusEl || !infoEl) return;

                let status = '❌';
                let info = data.error || '-';

                if (!data.error && Array.isArray(data.original) && Array.isArray(data.update)) {
                    const filteredOriginal = data.original.filter(v => v !== null);
                    const filteredUpdate = data.update.filter(v => v !== null);
                    const isSame = filteredOriginal.length === filteredUpdate.length &&
                        filteredOriginal.every((val, idx) => {
                            const n1 = typeof val === 'number' ? val : parseFloat(val);
                            const n2 = typeof filteredUpdate[idx] === 'number' ? filteredUpdate[idx] : parseFloat(filteredUpdate[idx]);
                            if (!isNaN(n1) && !isNaN(n2)) return Math.abs(n1 - n2) < 1e-2;
                            return val === filteredUpdate[idx];
                        });
                    status = '✔️';
                    info = isSame ? '{{ trans('Not Modified') }}' :
                        `{{ trans('common.update') }}: [${filteredOriginal.join(', ') || '-'}] => [${filteredUpdate.join(', ') || '-'}]`;
                }

                statusEl.innerHTML = status;
                infoEl.innerHTML = info;
            } catch (e) {}
        }

        // reload UI
        function buildReloadUI() {
            $('#nodeReloadModal').modal('show');
            const body = document.querySelector('#nodeReloadModal .modal-body');
            let html = `<table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('validation.attributes.name') }}</th>
                            <th>{{ trans('common.status.attribute') }}</th>
                            <th>{{ trans('validation.attributes.message') }}</th>
                        </tr>
                    </thead>
                    <tbody>`;

            Object.keys(state.results).forEach(nodeId => {
                const node = state.results[nodeId];
                html += `<tr data-node-id="${nodeId}">
                <td>${node.name}</td>
                <td><i class="wb-loop icon-spin"></i></td>
                <td><i class="wb-loop icon-spin"></i></td>
            </tr>`;
            });
            html += '</tbody></table>';
            body.innerHTML = html;
        }

        function updateReloadUI(nodeId, data) {
            try {
                const row = document.querySelector(`#nodeReloadModal tr[data-node-id="${nodeId}"]`);
                if (!row) return;

                const statusEl = row.querySelector('td:nth-child(2)');
                const infoEl = row.querySelector('td:nth-child(3)');

                if (!statusEl || !infoEl) return;

                // 处理状态显示
                let status = '❌'; // 默认失败状态
                let info = '';

                if (!data.error || (Array.isArray(data.error) && data.error.length === 0)) {
                    status = '✔️';
                } else if (Array.isArray(data.error) && data.error.length > 0) {
                    // 有错误信息
                    info = `{{ trans('common.error') }}: ${data.error.join(', ')}`;
                }

                statusEl.innerHTML = status;
                infoEl.innerHTML = info;
            } catch (e) {}
        }

        @can('admin.node.reload')
            function reload(id = null) {
                showConfirm({
                    text: '{{ trans('admin.node.reload_confirm') }}',
                    onConfirm: function() {
                        handleNodeAction('reload', id);
                    }
                });
            }
        @endcan
    </script>
@endpush
