@extends('admin.table_layouts')
@push('css')
    <style>
        .deploy-card {
            margin-bottom: 15px;
            border: 1px solid #e4eaec;
            border-radius: 3px;
            background: #fff;
        }

        .deploy-card:last-child {
            margin-bottom: 0;
        }

        .deploy-card__head {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #e4eaec;
            background: #f6f8fa;
        }

        .deploy-card__head > i {
            margin-right: 8px;
            font-size: 16px;
            color: #607d8b;
        }

        .deploy-card__name {
            font-size: 15px;
            font-weight: 600;
            color: #324157;
        }

        .deploy-card__body {
            padding: 0 15px 15px;
        }

        .deploy-cmd {
            display: grid;
            grid-template-columns: 9em minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px dashed #e9ecef;
        }

        .deploy-card__body > .deploy-cmd:last-child {
            border-bottom: none;
        }

        .deploy-cmd__label {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #607d8b;
        }

        .deploy-cmd__label > i {
            margin-right: 6px;
            font-size: 14px;
        }

        .deploy-cmd__code {
            margin: 0;
            padding: 9px 12px;
            border: 1px solid #e4eaec;
            border-radius: 2px;
            background: #f6f8fa;
            color: #324157;
            font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12.5px;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .deploy-cmd--primary .deploy-cmd__code {
            padding: 12px;
            border-color: #cad1d8;
            background: #fff;
            font-size: 13px;
        }

        .deploy-cmd__note {
            font-size: 13px;
            color: #a3afb7;
        }

        .deploy-copy {
            white-space: nowrap;
        }

        .deploy-group {
            display: flex;
            align-items: center;
            margin: 15px 0 10px;
            font-size: 12px;
            color: #a3afb7;
        }

        .deploy-group > i {
            margin-right: 6px;
        }

        .deploy-group::after {
            content: "";
            flex: 1;
            height: 1px;
            margin-left: 10px;
            background: #e9ecef;
        }

        .deploy-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .deploy-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid #e4eaec;
            border-radius: 2px;
            background: #fff;
            color: #607d8b;
            font-size: 12.5px;
            line-height: 1.4;
            cursor: pointer;
        }

        .deploy-chip:hover {
            border-color: #cad1d8;
            background: #f6f8fa;
        }

        .deploy-chip > i {
            margin-right: 6px;
        }

        .deploy-chip code {
            margin-left: 6px;
            padding: 0;
            background: none;
            color: #a3afb7;
            font-size: 12px;
        }

        .deploy-empty {
            padding: 15px 0;
            font-size: 13px;
            color: #a3afb7;
        }
    </style>
@endpush
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
                            <x-admin.row-actions>

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
                            </x-admin.row-actions>
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>

    <!-- 部署教程模态框模板 -->
    <x-ui.modal id="deployModal" tabindex="-1" size="lg" position="center" :keyboard="false" focus="input:first">
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
            const $content = $('#deployContent').empty();

            if (config.hint) {
                // 提示里带 <a> 指向设置页，整串由服务端 trans 生成
                $content.append($('<div class="alert alert-warning deploy-hint">').html(config.hint));

                return;
            }

            config.forEach(backend => $content.append(renderBackend(backend)));
        }

        // 命令键 → 呈现；group 为 service 的收进工具条，其余逐行显示。新增键要同步补 admin.node.auth.deploy 的词条，否则整条被滤掉
        const COMMAND_META = {
            install: {label: '{{ trans('admin.node.auth.deploy.install') }}', icon: 'wb-download', tone: 'primary'},
            update: {label: '{{ trans('admin.node.auth.deploy.update') }}', icon: 'wb-reload'},
            uninstall: {label: '{{ trans('admin.node.auth.deploy.uninstall') }}', icon: 'wb-trash', tone: 'danger'},
            start: {label: '{{ trans('admin.node.auth.deploy.start') }}', icon: 'wb-play', group: 'service'},
            stop: {label: '{{ trans('admin.node.auth.deploy.stop') }}', icon: 'wb-pause', group: 'service'},
            restart: {label: '{{ trans('admin.node.auth.deploy.restart') }}', icon: 'wb-loop', group: 'service'},
            status: {label: '{{ trans('admin.node.auth.deploy.status') }}', icon: 'wb-eye', group: 'service'},
            recent_logs: {label: '{{ trans('admin.node.auth.deploy.recent_logs') }}', icon: 'wb-list-bulleted', group: 'service'},
            real_time_logs: {label: '{{ trans('admin.node.auth.deploy.real_time_logs') }}', icon: 'wb-code-working', group: 'service'}
        };

        const HINT_SAME = '{{ trans('admin.node.auth.deploy.same') }}';
        const LABEL_COPY = '{{ trans('common.copy.attribute') }}';
        const LABEL_NO_COMMAND = '{{ trans('admin.node.auth.deploy.no_command') }}';
        const LABEL_SERVICE = '{{ trans('admin.node.auth.deploy.service_control') }}';

        function renderBackend(backend) {
            const entries = Object.entries(backend.commands || {});
            const known = entries.filter(([key]) => COMMAND_META[key]);
            const service = known.filter(([key]) => COMMAND_META[key].group === 'service');
            const lifecycle = known.filter(([key]) => COMMAND_META[key].group !== 'service');

            const $body = $('<div class="deploy-card__body">');
            lifecycle.forEach(([key, value]) => $body.append(renderCommandRow(COMMAND_META[key], value)));

            if (service.length) {
                const $chips = $('<div class="deploy-chips">');
                service.forEach(([key, value]) => $chips.append(renderChip(COMMAND_META[key], value)));
                $body.append(
                    $('<div class="deploy-group">')
                        .append($('<i class="icon wb-settings">'))
                        .append($('<span>').text(LABEL_SERVICE))
                ).append($chips);
            }

            if (!known.length) {
                $body.append($('<div class="deploy-empty">').text(LABEL_NO_COMMAND));
            }

            return $('<div class="deploy-card">')
                .append(
                    $('<div class="deploy-card__head">')
                        .append($('<i class="icon wb-desktop">'))
                        .append($('<span class="deploy-card__name">').text(backend.name))
                )
                .append($body);
        }

        function renderCommandRow(meta, value) {
            const $label = $('<span class="deploy-cmd__label">')
                .append($('<i class="icon ' + meta.icon + '">'))
                .append($('<span>').text(meta.label));
            if (meta.tone) {
                $label.addClass('text-' + meta.tone);
            }

            const $row = $('<div class="deploy-cmd">');
            if (meta.tone) {
                $row.addClass('deploy-cmd--' + meta.tone);
            }
            $row.append($label);

            // 「同上级配置」是给运维看的说明，不是命令，不能进剪贴板
            if (value === HINT_SAME) {
                return $row.append($('<div class="deploy-cmd__note">').text(value));
            }

            const $copy = $('<button type="button">')
                .attr('title', LABEL_COPY)
                .addClass('deploy-copy btn btn-sm ' + (meta.tone === 'primary' ? 'btn-primary' : 'btn-outline-secondary'))
                .data('cmd', value)
                .append($('<i class="icon wb-copy">'));
            if (meta.tone === 'primary') {
                $copy.append($('<span>').text(' ' + LABEL_COPY));
            }

            return $row.append($('<pre class="deploy-cmd__code">').text(value)).append($copy);
        }

        function renderChip(meta, value) {
            return $('<button type="button" class="deploy-chip">')
                .attr('title', LABEL_COPY)
                .data('cmd', value)
                .append($('<i class="icon ' + meta.icon + '">'))
                .append($('<span>').text(meta.label))
                .append($('<code>').text(value));
        }

        // 命令文本走 data 而不是拼 HTML：里面有节点密钥与主机名
        $(document).on('click', '#deployContent .deploy-copy, #deployContent .deploy-chip', function() {
            copyToClipboard($(this).data('cmd'));
        });
    </script>
@endpush
