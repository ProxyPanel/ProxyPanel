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
                                <button class="btn btn-primary"
                                        onclick="showDeployModal({{ $auth->node->id }}, {{ $auth->node->type }}, '{{ $auth->key }}', '{{ $auth->node->type_label }}', '{{ $auth->node->host }}')">
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
        <div id="deployModalBody"></div>
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
        function showDeployModal(nodeId, nodeType, nodeKey, typeLabel, nodeHost) {
            // 设置模态框标题
            $('#deployModalTitle').text(`{{ trans('admin.node.auth.deploy.title', ['type_label' => '${typeLabel}']) }}`);

            const webApi = '{{ sysConfig('web_api_url') ?: sysConfig('website_url') }}';

            // 定义节点配置
            const nodeConfigs = {
                1: { // 默认节点 (VNET)
                    name: 'VNET',
                    scriptUrl: 'https://bit.ly/3828OP1',
                    commands: {
                        update: '{{ trans('admin.node.auth.deploy.same') }}',
                        uninstall: 'curl -L -s https://bit.ly/3828OP1 | bash -s -- --remove',
                        start: 'systemctl start vnet',
                        stop: 'systemctl stop vnet',
                        restart: 'systemctl restart vnet',
                        status: 'systemctl status vnet',
                        recent_logs: 'journalctl -x -n 300 --no-pager -u vnet',
                        real_time_logs: 'journalctl -u vnet -f'
                    }
                },
                2: { // V2Ray 节点
                    scripts: [{
                            name: 'VNET-V2Ray',
                            scriptUrl: 'https://bit.ly/3oO3HZy',
                            commands: {
                                update: '{{ trans('admin.node.auth.deploy.same') }}',
                                uninstall: 'curl -L -s https://bit.ly/3oO3HZy | bash -s -- --remove',
                                start: 'systemctl start vnet-v2ray',
                                stop: 'systemctl stop vnet-v2ray',
                                status: 'systemctl status vnet-v2ray',
                                recent_logs: 'journalctl -x -n 300 --no-pager -u vnet-v2ray',
                                real_time_logs: 'journalctl -u vnet-v2ray -f'
                            }
                        },
                        {
                            name: 'V2Ray-Poseidon',
                            scriptUrl: 'https://bit.ly/2HswWko',
                            commands: {
                                update: 'curl -L -s https://bit.ly/2HswWko | bash',
                                uninstall: 'curl -L -s https://mrw.so/5IHPR4 | bash',
                                start: 'systemctl start v2ray',
                                stop: 'systemctl stop v2ray',
                                status: 'systemctl status v2ray',
                                recent_logs: 'journalctl -x -n 300 --no-pager -u v2ray',
                                real_time_logs: 'journalctl -u v2ray -f'
                            }
                        }
                    ]
                },
                3: { // Trojan 节点
                    name: 'Trojan-Poseidon',
                    scriptUrl: 'https://mrw.so/6cMfGy',
                    requireHost: true,
                    commands: {
                        update: 'curl -L -s https://mrw.so/6cMfGy | bash',
                        uninstall: 'curl -L -s https://mrw.so/5ulpvu | bash',
                        start: 'systemctl start trojanp',
                        stop: 'systemctl stop trojanp',
                        status: 'systemctl status trojanp',
                        recent_logs: 'journalctl -x -n 300 --no-pager -u trojanp',
                        real_time_logs: 'journalctl -u trojanp -f'
                    }
                }
            };

            let content = '';

            if (nodeType === 2) {
                // V2Ray 节点(特殊处理，有两个脚本)
                const config = nodeConfigs[2];
                config.scripts.forEach(script => {
                    content += `
                        <div class="alert alert-info text-break">
                            <div class="text-center red-700 mb-5">${script.name}</div>
                            (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\<br>
                            && curl -L -s ${script.scriptUrl} \\<br>
                            | WEB_API="${webApi}" \\<br>
                            NODE_ID=${nodeId} \\<br>
                            NODE_KEY=${nodeKey} \\<br>
                            ${script.name.includes('Trojan') && nodeHost ? `NODE_HOST=${nodeHost} \\<br>` : ''}
                            bash
                            <br><br>
                            <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                            {{ trans('admin.node.auth.deploy.update') }}: ${script.commands.update}
                            <br>
                            {{ trans('admin.node.auth.deploy.uninstall') }}: ${script.commands.uninstall}
                            <br>
                            {{ trans('admin.node.auth.deploy.start') }}: ${script.commands.start}
                            <br>
                            {{ trans('admin.node.auth.deploy.stop') }}: ${script.commands.stop}
                            <br>
                            {{ trans('admin.node.auth.deploy.status') }}: ${script.commands.status}
                            <br>
                            {{ trans('admin.node.auth.deploy.recent_logs') }}: ${script.commands.recent_logs}
                            <br>
                            {{ trans('admin.node.auth.deploy.real_time_logs') }}: ${script.commands.real_time_logs}
                        </div>
                    `;
                });
            } else if (nodeType === 3) {
                // Trojan 节点
                const config = nodeConfigs[3];
                if (!nodeHost) {
                    let url = jsRoute('{{ route('admin.node.edit', 'PLACEHOLDER') }}', nodeId)
                    content = `<h3>{!! trans('admin.node.auth.deploy.trojan_hint', ['url' => '${url}']) !!}</h3>`;
                } else {
                    content = `
                        <div class="alert alert-info text-break">
                            <div class="text-center red-700 mb-5">${config.name}</div>
                            (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\<br>
                            && curl -L -s ${config.scriptUrl} \\<br>
                            | WEB_API="${webApi}" \\<br>
                            NODE_ID=${nodeId} \\<br>
                            NODE_KEY=${nodeKey} \\<br>
                            NODE_HOST=${nodeHost} \\<br>
                            bash
                            <br><br>
                            <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                            {{ trans('admin.node.auth.deploy.update') }}: ${config.commands.update}
                            <br>
                            {{ trans('admin.node.auth.deploy.uninstall') }}: ${config.commands.uninstall}
                            <br>
                            {{ trans('admin.node.auth.deploy.start') }}: ${config.commands.start}
                            <br>
                            {{ trans('admin.node.auth.deploy.stop') }}: ${config.commands.stop}
                            <br>
                            {{ trans('admin.node.auth.deploy.status') }}: ${config.commands.status}
                            <br>
                            {{ trans('admin.node.auth.deploy.recent_logs') }}: ${config.commands.recent_logs}
                            <br>
                            {{ trans('admin.node.auth.deploy.real_time_logs') }}: ${config.commands.real_time_logs}
                        </div>
                    `;
                }
            } else {
                // 默认节点 (VNET)
                const config = nodeConfigs[1];
                content = `
                    <div class="alert alert-info text-break">
                        <div class="text-center red-700 mb-5">${config.name}</div>
                        (yum install curl 2> /dev/null || apt install curl 2> /dev/null) \\<br>
                        && curl -L -s ${config.scriptUrl} \\<br>
                        | WEB_API="${webApi}" \\<br>
                        NODE_ID=${nodeId} \\<br>
                        NODE_KEY=${nodeKey} \\<br>
                        bash
                        <br><br>
                        <div class="text-center red-700 mb-5">{{ trans('admin.node.auth.deploy.command') }}</div>
                        {{ trans('admin.node.auth.deploy.update') }}: ${config.commands.update}
                        <br>
                        {{ trans('admin.node.auth.deploy.uninstall') }}: ${config.commands.uninstall}
                        <br>
                        {{ trans('admin.node.auth.deploy.start') }}: ${config.commands.start}
                        <br>
                        {{ trans('admin.node.auth.deploy.stop') }}: ${config.commands.stop}
                        <br>
                        {{ trans('admin.node.auth.deploy.restart') }}: ${config.commands.restart}
                        <br>
                        {{ trans('admin.node.auth.deploy.status') }}: ${config.commands.status}
                        <br>
                        {{ trans('admin.node.auth.deploy.recent_logs') }}: ${config.commands.recent_logs}
                        <br>
                        {{ trans('admin.node.auth.deploy.real_time_logs') }}: ${config.commands.real_time_logs}
                    </div>
                `;
            }

            $('#deployModalBody').html(content);
            $('#deployModal').modal('show');
        }
    </script>
@endpush
