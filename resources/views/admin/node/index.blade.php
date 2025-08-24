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
                                <button class="btn btn-info" type="button" onclick="reload(0)">
                                    <i class="icon wb-reload" id="reload_0" aria-hidden="true"></i> {{ trans('admin.node.reload_all') }}
                                </button>
                            @endif
                        @endcan
                        @can('admin.node.geo')
                            <button class="btn btn-outline-default" type="button" onclick="refreshGeo(0)">
                                <i class="icon wb-map" id="geo_0" aria-hidden="true"></i> {{ trans('admin.node.refresh_geo_all') }}
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
                                        <x-ui.dropdown-item id="geo{{ $node->id }}" url="javascript:refreshGeo('{{ $node->id }}')" icon="wb-map"
                                                            :text="trans('admin.node.refresh_geo')" />
                                    @endcan
                                    @can('admin.node.check')
                                        <x-ui.dropdown-item id="node_{{ $node->id }}" url="javascript:checkNode('{{ $node->id }}')" icon="wb-signal"
                                                            :text="trans('admin.node.connection_test')" />
                                    @endcan
                                    @if ($node->type === 4)
                                        @can('admin.node.reload')
                                            <hr />
                                            <x-ui.dropdown-item id="reload_{{ $node->id }}" url="javascript:reload('{{ $node->id }}')" icon="wb-reload"
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
                                            <x-ui.dropdown-item id="geo_{{ $childNode->id }}" url="javascript:refreshGeo('{{ $childNode->id }}')" icon="wb-map"
                                                                :text="trans('admin.node.refresh_geo')" />
                                        @endcan
                                        @can('admin.node.check')
                                            <x-ui.dropdown-item id="node_{{ $childNode->id }}" url="javascript:checkNode('{{ $childNode->id }}')" icon="wb-signal"
                                                                :text="trans('admin.node.connection_test')" />
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
@endsection
@push('javascript')
    <script>
        @can('admin.node.check')
            function checkNode(id) { // 节点连通性测试
                const $element = $(`#node_${id}`);

                ajaxPost(jsRoute('{{ route('admin.node.check', 'PLACEHOLDER') }}', id), {}, {
                    beforeSend: function() {
                        $element.removeClass("wb-signal").addClass("wb-loop icon-spin");
                    },
                    success: function(ret) {
                        if (ret.status === "success") {
                            let str = "";
                            for (let i in ret.message) {
                                str += "<tr><td>" + i + "</td><td>" + ret.message[i][0] + "</td><td>" + ret.message[i][1] +
                                    "</td></tr>";
                            }
                            showMessage({
                                title: ret.title,
                                html: "<table class=\"my-20\"><thead class=\"thead-default\"><tr><th> IP </th><th> ICMP </th> <th> TCP </th></thead><tbody>" +
                                    str + "</tbody></table>",
                                autoClose: false
                            });
                        } else {
                            showMessage({
                                title: ret.title,
                                message: ret.message,
                                icon: "error"
                            });
                        }
                    },
                    complete: function() {
                        $element.removeClass("wb-loop icon-spin").addClass("wb-signal");
                    }
                });
            }
        @endcan

        @can('admin.node.reload')
            function reload(id) { // 发送节点重载请求
                const $element = $(`#reload_${id}`);

                showConfirm({
                    text: '{{ trans('admin.node.reload_confirm') }}',
                    onConfirm: function() {
                        ajaxPost(jsRoute('{{ route('admin.node.reload', 'PLACEHOLDER') }}', id), {}, {
                            beforeSend: function() {
                                $element.removeClass("wb-reload").addClass("wb-loop icon-spin");
                            },
                            complete: function() {
                                $element.removeClass("wb-loop icon-spin").addClass("wb-reload");
                            }
                        });
                    }
                });
            }
        @endcan

        @can('admin.node.geo')
            function refreshGeo(id) { // 刷新节点地理信息
                const $element = $(`#geo_${id}`);

                ajaxGet(jsRoute('{{ route('admin.node.geo', 'PLACEHOLDER') }}', id), {}, {
                    beforeSend: function() {
                        $element.removeClass("wb-map").addClass("wb-loop icon-spin");
                    },
                    complete: function() {
                        $element.removeClass("wb-loop icon-spin").addClass("wb-map");
                    }
                });
            }
        @endcan
    </script>
@endpush
