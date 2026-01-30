@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.menu.node.auth')" :theads="[
            trans('model.node.id'),
            trans('model.common.type'),
            trans('model.node.name'),
            trans('model.node.domain'),
            trans('model.node_auth.key'),
            trans('model.node_auth.secret'),
            trans('common.action'),
        ]" :count="trans('admin.node.auth.counts', ['num' => $authorizations->total()])" :pagination="$authorizations->links()" :delete-config="['url' => route('admin.node.auth.destroy', 'PLACEHOLDER'), 'attribute' => trans('model.node_auth.attribute'), 'nameColumn' => 2]">
            @can('admin.node.auth.store')
                <x-slot:actions>
                    <button class="btn btn-primary" onclick="addAuth()">
                        <i class="icon wb-plus" aria-hidden="true"></i> {{ trans('common.add') }}
                    </button>
                </x-slot:actions>
            @endcan
            <x-slot:tbody>
                @foreach ($authorizations as $auth)
                    <tr>
                        <td> {{ $auth->node_id }} </td>
                        <td> {{ $auth->node->type_label }} </td>
                        <td> {{ Str::limit($auth->node->name, 20) }} </td>
                        <td> {{ $auth->node->host }} </td>
                        <td><span class="badge badge-lg badge-info"> {{ $auth->key }} </span></td>
                        <td><span class="badge badge-lg badge-info"> {{ $auth->secret }} </span></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary" onclick="showDeployModal({{ $auth->node->id }}, '{{ $auth->node->type_label }}')">
                                    <i class="icon wb-code" aria-hidden="true"></i> {{ trans('admin.node.auth.deploy.attribute') }}
                                </button>
                                @can('admin.node.auth.update')
                                    <button class="btn btn-danger" onclick="refreshAuth('{{ $auth->id }}')">
                                        <i class="icon wb-reload" aria-hidden="true"></i> {{ trans('admin.node.auth.reset_auth') }}
                                    </button>
                                @endcan
                                @can('admin.node.auth.destroy')
                                    <button class="btn btn-primary" data-action="delete">
                                        <i class="icon wb-trash" aria-hidden="true"></i> {{ trans('common.delete') }}
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    <!-- 部署教程模态框模板 -->
    <x-ui.modal id="deployModal" tabindex="-1" size="simple" position="center" :keyboard="false" focus="input:first">
        <x-slot:title>
            <span id="deployModalTitle"></span>
        </x-slot:title>
        <div id="deployModalBody">
            <div class="text-center" id="loadingSpinner" style="display: none;">
                <i class="icon wb-loop icon-spin"></i> {{ trans('common.loading') }}
            </div>
            <div id="deployContent"></div>
        </div>
    </x-ui.modal>
@endsection
@push('javascript')
    <script>
        // 生成授权KEY
        @can('admin.node.auth.store')
            function addAuth() {
                showConfirm({
                    title: '{{ trans('admin.hint') }}',
                    text: '{{ trans('admin.node.auth.generating_all') }}',
                    icon: 'info',
                    onConfirm: function() {
                        ajaxPost('{{ route('admin.node.auth.store') }}');
                    }
                });
            }
        @endcan

        @can('admin.node.auth.update')
            // 重置授权认证KEY
            function refreshAuth(id) {
                showConfirm({
                    title: '{{ trans('admin.hint') }}',
                    text: '{{ trans('admin.confirm.continues') }}',
                    icon: 'info',
                    onConfirm: function() {
                        ajaxPut(jsRoute('{{ route('admin.node.auth.update', 'PLACEHOLDER') }}', id));
                    }
                });
            }
        @endcan

        // 显示部署教程模态框
        function showDeployModal(nodeId, typeLabel) {
            // 设置模态框标题
            $('#deployModalTitle').text(jsRoute('{{ trans('admin.node.auth.deploy.title', ['type_label' => 'PLACEHOLDER']) }}', typeLabel));

            // 显示加载动画
            $('#loadingSpinner').show();
            $('#deployContent').html('');

            // 从后端获取部署配置
            $.ajax({
                url: jsRoute('{{ route('admin.node.deployment', 'PLACEHOLDER') }}', nodeId),
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        renderDeploymentConfig(response.data);
                    } else {
                        $('#deployContent').html('<div class="alert alert-danger">{{ trans('common.error') }}</div>');
                    }
                },
                error: function() {
                    $('#deployContent').html('<div class="alert alert-danger">{{ trans('common.error') }}</div>');
                },
                complete: function() {
                    $('#loadingSpinner').hide();
                }
            });

            $('#deployModal').modal('show');
        }

        // 渲染部署配置
        function renderDeploymentConfig(config) {
            let content = '';

            if (config.requires_host) {
                content = `<h3>{!! trans('admin.node.auth.deploy.trojan_hint', ['url' => '${config.edit_url}']) !!}</h3>`;
            } else {
                config.forEach(script => {
                    content += `
                            <div class="alert alert-info text-break">
                                <div class="text-center red-700 mb-5">${script.name}</div>
                                ${formatCommand(script?.commands?.install)}
                                ${script.commands ? `<div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>${renderCommands(script.commands)}`: ''}
                            </div>
                        `;
                });
            }

            $('#deployContent').html(content);
        }

        // 动态渲染命令列表
        function renderCommands(commands) {
            let commandsHtml = '';
            const commandLabels = {
                update: '{{ trans('admin.node.auth.deploy.update') }}',
                uninstall: '{{ trans('admin.node.auth.deploy.uninstall') }}',
                start: '{{ trans('admin.node.auth.deploy.start') }}',
                stop: '{{ trans('admin.node.auth.deploy.stop') }}',
                restart: '{{ trans('admin.node.auth.deploy.restart') }}',
                status: '{{ trans('admin.node.auth.deploy.status') }}',
                recent_logs: '{{ trans('admin.node.auth.deploy.recent_logs') }}',
                real_time_logs: '{{ trans('admin.node.auth.deploy.real_time_logs') }}'
            };

            for (const [command, value] of Object.entries(commands)) {
                if (commandLabels[command] && value) {
                    commandsHtml += `${commandLabels[command]}: ${value}<br>`;
                }
            }

            return commandsHtml;
        }

        // 格式化命令显示
        function formatCommand(command) {
            if (!command) {
                return ''
            }
            // 使用 <pre> 标签保持原始格式
            return `<pre class="p-0 border-0">${command}</pre>`;
        }
    </script>
@endpush
