@extends('admin.table_layouts')
@push('css')
    <link href="/assets/custom/range.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.user.list')" :theads="[
            'id' => '#',
            trans('model.user.username'),
            'credit' => trans('model.user.credit'),
            'port' => trans('model.user.port'),
            trans('model.subscribe.code'),
            trans('model.user.traffic_used'),
            't' => trans('common.latest_at'),
            'expired_at' => trans('common.expired_at'),
            trans('common.account'),
            trans('model.user.service'),
            trans('common.action'),
        ]" :count="trans('admin.user.counts', ['num' => $userList->total()])" :pagination="$userList->links()" :delete-config="['url' => route('admin.user.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.user.attribute')]">
            @canany(['admin.user.batch', 'admin.user.create'])
                <x-slot:actions>
                    @can('admin.user.batch')
                        <button class="btn btn-outline-default" onclick="batchAddUsers()">
                            <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.generate') }}
                        </button>
                    @endcan
                    @can('admin.user.create')
                        <a class="btn btn-outline-primary" href="{{ route('admin.user.create') }}">
                            <i class="icon wb-user-add" aria-hidden="true"></i> {{ trans('common.add') }}
                        </a>
                    @endcan
                </x-slot:actions>
            @endcanany

            <x-slot:filters>
                <x-admin.filter.input class="col-md-1 col-sm-4" name="id" type="number" :placeholder="trans('model.user.id')" />
                <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="username" :placeholder="trans('model.user.username')" />
                <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="wechat" :placeholder="trans('model.user.wechat')" />
                <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="qq" type="number" :placeholder="trans('model.user.qq')" />
                <x-admin.filter.input class="col-xxl-1 col-md-2 col-sm-4" name="port" type="number" :placeholder="trans('model.user.port')" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="user_group_id" :title="trans('model.user_group.attribute')" :options="$userGroups" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="level" :title="trans('model.common.level')" :options="$levels" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="status" :title="trans('model.user.account_status')" :options="[-1 => trans('common.status.banned'), 0 => trans('common.status.inactive'), 1 => trans('common.status.normal')]" />
                <x-admin.filter.selectpicker class="col-xxl-1 col-md-3 col-4" name="enable" :title="trans('model.user.proxy_status')" :options="[1 => trans('common.status.enabled'), 0 => trans('common.status.banned')]" />
            </x-slot:filters>
            <x-slot:tbody>
                @foreach ($userList as $user)
                    <tr class="{{ $user->ban_time ? 'table-danger' : '' }}">
                        <td> {{ $user->id }} </td>
                        <td> {{ $user->username }} </td>
                        <td> {{ $user->credit }} </td>
                        <td>
                            {!! $user->port ?: '<span class="badge badge-lg badge-danger"> ' . trans('common.none') . ' </span>' !!}
                        </td>
                        <td>
                            <a class="copySubscribeLink" data-clipboard-text="{{ $user->sub_url }}" href="javascript:">{{ $user->subscribe->code }}</a>
                        </td>
                        <td> {{ formatBytes($user->used_traffic) }} / {{ $user->transfer_enable_formatted }} </td>
                        <td> {{ $user->t ? date('Y-m-d H:i', $user->t) : trans('common.status.unused') }} </td>
                        <td>
                            @if ($user->expiration_status !== 3)
                                <span class="badge badge-lg badge-{{ ['danger', 'warning', 'default'][$user->expiration_status] }}">
                                    {{ $user->expiration_date }} </span>
                            @else
                                {{ $user->expiration_date }}
                            @endif
                        </td>
                        <td>
                            @if ($user->status > 0)
                                <span class="badge badge-lg badge-primary">
                                    <i class="wb-check" aria-hidden="true"></i>
                                </span>
                            @elseif ($user->status < 0)
                                <span class="badge badge-lg badge-danger">
                                    <i class="wb-close" aria-hidden="true"></i>
                                </span>
                            @else
                                <span class="badge badge-lg badge-default">
                                    <i class="wb-minus" aria-hidden="true"></i>
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-lg badge-{{ $user->enable ? 'info' : 'danger' }}">
                                <i class="wb-{{ $user->enable ? 'check' : 'close' }}" aria-hidden="true"></i>
                            </span>
                        </td>
                        <td>
                            @canany(['admin.user.edit', 'admin.user.destroy', 'admin.user.export', 'admin.user.monitor', 'admin.user.online', 'admin.user.reset',
                                'admin.user.switch'])
                                <x-ui.dropdown>
                                    @can('admin.user.edit')
                                        <x-ui.dropdown-item :url="route('admin.user.edit', ['user' => $user->id, Request::getQueryString()])" icon="wb-edit" :text="trans('common.edit')" />
                                    @endcan
                                    @can('admin.user.destroy')
                                        <x-ui.dropdown-item color="red-600" url="javascript:(0)" attribute="data-action=delete" icon="wb-trash" :text="trans('common.delete')" />
                                    @endcan
                                    @can('admin.user.export')
                                        <x-ui.dropdown-item :url="route('admin.user.export', $user)" icon="wb-code" :text="trans('admin.user.proxy_info')" />
                                    @endcan
                                    @can('admin.user.monitor')
                                        <x-ui.dropdown-item :url="route('admin.user.monitor', $user)" icon="wb-stats-bars" :text="trans('admin.user.traffic_monitor')" />
                                    @endcan
                                    @can('admin.user.online')
                                        <x-ui.dropdown-item :url="route('admin.user.online', $user)" icon="wb-cloud" :text="trans('admin.user.online_monitor')" />
                                    @endcan
                                    @can('admin.user.reset')
                                        <x-ui.dropdown-item url="javascript:resetTraffic('{{ $user->id }}','{{ $user->username }}')" icon="wb-reload"
                                                            :text="trans('admin.user.reset_traffic')" />
                                    @endcan
                                    @can('admin.user.switch')
                                        <x-ui.dropdown-item url="javascript:switchToUser('{{ $user->id }}')" icon="wb-user" :text="trans('admin.user.user_view')" />
                                    @endcan
                                    @can('admin.user.VNetInfo')
                                        <x-ui.dropdown-item id="vent_{{ $user->id }}" url="javascript:VNetInfo('{{ $user->id }}')" icon="wb-link-broken"
                                                            :text="trans('admin.user.connection_test')" />
                                    @endcan
                                </x-ui.dropdown>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    <!-- 用户VNet检测结果模态框 -->
    <x-ui.modal id="userVNetCheckModal" :title="trans('admin.user.connection_test')" size="lg">
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
        @can('admin.user.batch')
            function batchAddUsers() { // 批量生成账号
                showConfirm({
                    title: '{{ trans('admin.user.bulk_account_quantity') }}',
                    input: "range",
                    inputAttributes: {
                        min: 1,
                        max: 10
                    },
                    inputValue: 1,
                    onConfirm: function(result) {
                        if (result.value) {
                            ajaxPost('{{ route('admin.user.batch') }}', {
                                amount: result.value
                            });
                        }
                    }
                });
            }
        @endcan

        @can('admin.user.reset')
            function resetTraffic(id, username) { // 重置流量
                showConfirm({
                    title: '{{ trans('common.warning') }}',
                    text: '{{ trans('admin.user.reset_confirm') }}'.replace('{username}', username),
                    icon: 'warning',
                    onConfirm: function() {
                        ajaxPost(jsRoute('{{ route('admin.user.reset', 'PLACEHOLDER') }}', id));
                    }
                });
            }
        @endcan

        @can('admin.user.switch')
            function switchToUser(id) { // 切换用户身份
                ajaxPost(jsRoute('{{ route('admin.user.switch', 'PLACEHOLDER') }}', id), {}, {
                    success: function(ret) {
                        handleResponse(ret, {
                            redirectUrl: '/'
                        });
                    }
                });
            }
        @endcan

        @can('admin.user.VNetInfo')
            // 全局状态
            const userVNetState = {
                results: {} // 按 nodeId 存储节点信息与已收到的数据
            };

            // 构建并显示模态框
            function buildVNetCheckUI() {
                const body = document.querySelector('#userVNetCheckModal .modal-body');
                let html = `<table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('model.node.attribute') }}</th>
                                    <th>{{ trans('common.status.attribute') }}</th>
                                </tr>
                            </thead>
                            <tbody>`;

                Object.keys(userVNetState.results).forEach(nodeId => {
                    const node = userVNetState.results[nodeId];
                    html += `
                    <tr data-node-id="${nodeId}">
                        <td>${node.name}</td>
                        <td><i class="wb-loop icon-spin"></i></td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                body.innerHTML = html;
            }

            // 更新模态框中的节点状态
            function updateVNetCheckUI(nodeId, available) {
                try {
                    const row = document.querySelector(`#userVNetCheckModal tr[data-node-id="${nodeId}"]`);
                    if (!row) return;

                    const statusEl = row.querySelector('td:nth-child(2)');
                    if (statusEl) {
                        statusEl.innerHTML = available ? '✔️' : '❌';
                    }
                } catch (e) {}
            }

            // 处理广播数据
            function handleVNetResult(e) {
                // 如果包含 nodeList：构建初始 UI 框架
                if (e.data && e.data.nodeList) {
                    $('#userVNetCheckModal').modal('show');
                    userVNetState.results = {};

                    Object.keys(e.data.nodeList).forEach(nodeId => {
                        const nodeName = e.data.nodeList[nodeId];
                        userVNetState.results[nodeId] = {
                            name: nodeName,
                            available: null // 检查中
                        };
                    });

                    // 构建并显示 modal
                    buildVNetCheckUI();
                    return;
                }

                // 处理详细数据
                try {
                    const nodeId = e.data.nodeId;
                    if (!nodeId || !userVNetState.results[nodeId]) return;

                    userVNetState.results[nodeId].available = e.data.available;
                    updateVNetCheckUI(nodeId, e.data.available);
                } catch (err) {
                    console.error('handleVNetResult error', err);
                }
            }

            function VNetInfo(id) { // 节点连通性测试
                const $triggerElement = $(`#vent_${id}`);
                const channelName = `user.check.${id}`;

                // 清理之前的连接
                window.broadcastingManager.unsubscribe(channelName);
                userVNetState.results = {};

                // 启动 spinner
                $triggerElement.removeClass("wb-link-broken").addClass("wb-loop icon-spin");

                // 使用统一的广播管理器订阅频道
                const success = window.broadcastingManager.subscribe(
                    channelName,
                    '.user.vnet.tasks',
                    (e) => handleVNetResult(e)
                );

                if (!success) {
                    // 订阅失败：恢复按钮状态
                    $triggerElement.removeClass("wb-loop icon-spin").addClass("wb-link-broken");
                    return;
                }

                // 触发后端接口（Ajax）
                ajaxPost(jsRoute('{{ route('admin.user.VNetInfo', 'PLACEHOLDER') }}', id), {}, {
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
                        $triggerElement.removeClass("wb-loop icon-spin").addClass("wb-link-broken");
                    },
                    complete: function() {
                        // 不在这里恢复按钮状态，而是等所有广播完成后再恢复
                    }
                });
            }
        @endcan

        $(document).on('click', '.copySubscribeLink', function(e) {
            e.preventDefault();
            copyToClipboard($(this).data('clipboard-text'));
        });
    </script>
@endpush
