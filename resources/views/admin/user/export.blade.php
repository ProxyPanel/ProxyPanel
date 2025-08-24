@extends('admin.table_layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-admin.table-panel :title="trans('admin.user.proxies_config', ['username' => $user->username])" :theads="[
            '#',
            trans('model.node.attribute'),
            trans('admin.node.info.extend'),
            trans('model.node.domain'),
            trans('model.node.ipv4'),
            trans('admin.user.proxy_info'),
        ]" :count="trans('admin.node.counts', ['num' => $nodeList->total()])" :pagination="$nodeList->links()">
            <x-slot:tbody>
                @foreach ($nodeList as $node)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @can('admin.node.edit')
                                <a href="{{ route('admin.node.edit', $node) }}" target="_blank"> {{ $node->name }} </a>
                            @else
                                {{ $node->name }}
                            @endcan
                        </td>
                        <td>
                            @isset($node->profile['passwd'])
                                {{-- 单端口 --}}
                                <span class="badge badge-lg badge-info"><i class="fa-solid fa-arrows-left-right-to-line" aria-hidden="true"></i></span>
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
                        </td>
                        <td>{{ $node->server }}</td>
                        <td>{{ $node->ip }}</td>
                        <td>
                            @can('admin.user.exportProxy')
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{ $node->id }}','code')">
                                        <i class="fa-solid fa-code" id="code{{ $node->id }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{ $node->id }}','qrcode')">
                                        <i class="fa-solid fa-qrcode" id="qrcode{{ $node->id }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{ $node->id }}','text')">
                                        <i class="fa-solid fa-list" id="text{{ $node->id }}"></i>
                                    </button>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-admin.table-panel>
    </div>
@endsection
@push('javascript')
    <script src="/assets/custom/easy.qrcode.min.js" type="text/javascript"></script>
    @can('admin.user.exportProxy')
        <script>
            function getInfo(id, type) {
                const oldClass = $(`#${type}${id}`).attr("class");

                const requestOptions = {
                    beforeSend: function() {
                        $(`#${type}${id}`).removeAttr("class").addClass("icon wb-loop icon-spin");
                    },
                    complete: function() {
                        $(`#${type}${id}`).removeAttr("class").addClass(oldClass);
                    }
                };

                switch (type) {
                    case "code":
                        requestOptions.success = function(ret) {
                            if (ret.status === "success") {
                                swal.fire({
                                    html: "<textarea class=\"form-control\" rows=\"8\" readonly=\"readonly\">" + ret.data +
                                        "</textarea>" +
                                        "<a href=\"" + ret.data +
                                        '" class="btn btn-block btn-danger mt-4">{{ trans('common.open') }} ' +
                                        ret.title + "</a>",
                                    showConfirmButton: false
                                });
                            }
                        };
                        break;
                    case "qrcode":
                        requestOptions.success = function(ret) {
                            if (ret.status === "success") {
                                swal.fire({
                                    title: '{{ trans('user.scan_qrcode') }}',
                                    html: '<div id="qrcode"></div><button class="btn btn-block btn-outline-primary mt-4" onclick="Download()"> <i class="icon wb-download"></i> {{ trans('common.download') }}</button>',
                                    onBeforeOpen: () => {
                                        new QRCode(document.getElementById("qrcode"), {
                                            text: ret.data
                                        });
                                    },
                                    showConfirmButton: false
                                });
                            }
                        };
                        break;
                    case "text":
                        requestOptions.success = function(ret) {
                            if (ret.status === "success") {
                                swal.fire({
                                    title: '{{ trans('user.node.info') }}',
                                    html: "<textarea class=\"form-control\" rows=\"12\" readonly=\"readonly\">" + ret.data +
                                        "</textarea>",
                                    showConfirmButton: false
                                });
                            }
                        };
                        break;
                    default:
                        requestOptions.success = function(ret) {
                            if (ret.status === "success") {
                                swal.fire({
                                    title: ret.title,
                                    text: ret.data
                                });
                            }
                        };
                }

                ajaxPost('{{ route('admin.user.exportProxy', $user) }}', {
                    id: id,
                    type: type
                }, requestOptions);
            }

            function Download() {
                const canvas = document.getElementsByTagName("canvas")[0];
                canvas.toBlob((blob) => {
                    let link = document.createElement("a");
                    link.download = "qr.png";

                    let reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onload = () => {
                        link.href = reader.result;
                        link.click();
                    };
                }, "image/png");
            }
        </script>
    @endcan
@endpush
