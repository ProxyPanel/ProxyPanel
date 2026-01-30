@extends('user.layouts')
@section('css')
    <link href="/assets/global/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/webui-popover/webui-popover.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/jvectormap/jquery-jvectormap.min.css" rel="stylesheet">
    <style>
        .flag-icon-rounded {
            border-radius: 50%;
            background-size: cover;
            height: 100%;
            width: auto;
            aspect-ratio: 1 / 1;
        }
    </style>
@endsection
@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-9">
                <div class="card card-inverse card-shadow bg-white map">
                    <div class="card-block h-450">
                        <div class="h-p100" id="world-map"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="row map">
                    <div class="col-md-12">
                        <div class="card card-block p-20 bg-indigo-500">
                            <div class="counter counter-lg counter-inverse">
                                <div class="counter-label text-uppercase font-size-16">{{ trans('user.account.level') }}</div>
                                <div class="counter-number-group">
                                    <span class="counter-icon"><i class="icon wb-user-circle" aria-hidden="true"></i></span>
                                    <span class="counter-number ml-10">{{ auth()->user()->level }}</span>
                                </div>
                                <div class="counter-label text-uppercase font-size-16">{{ auth()->user()->level_name }}</div>
                            </div>
                        </div>
                    </div>
                    @if (auth()->user()->user_group_id)
                        <div class="col-md-12">
                            <div class="card card-block p-30 bg-indigo-500">
                                <div class="counter counter-lg counter-inverse">
                                    <div class="counter-label text-uppercase font-size-16">{{ trans('user.account.group') }}</div>
                                    <div class="counter-number-group">
                                        <span class="counter-icon"><i class="icon wb-globe" aria-hidden="true"></i></span>
                                        <span class="counter-number ml-10">{{ auth()->user()->userGroup->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <div class="card card-block p-30 bg-indigo-500">
                            <div class="counter counter-lg counter-inverse">
                                <div class="counter-label text-uppercase font-size-16">{{ trans('user.account.speed_limit') }}</div>
                                <div class="counter-number-group">
                                    <span class="counter-icon"><i class="icon wb-signal" aria-hidden="true"></i></span>
                                    <span class="counter-number ml-10">{{ auth()->user()->speed_limit ?: trans('common.unlimited') }}</span>
                                </div>
                                <div class="counter-label font-size-16">Mbps</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @foreach ($nodes as $node)
                <div class="col-xxl-3 col-xl-4 col-sm-6">
                    <div class="card card-inverse card-shadow bg-white node-card">
                        <div class="card-block p-30 row">
                            <div class="col-3">
                                <i class="fi fi-{{ $node->country_code }} flag-icon-rounded" aria-hidden="true"></i>
                            </div>
                            <div class="col-9 text-break text-right">
                                <p class="font-size-20 blue-600">
                                    <span class="float-left badge badge-round badge-default">{{ $node->level_table->name }}</span>
                                    @if ($node->offline && !$node->relay_node_id)
                                        <i class="red-600 icon wb-warning" data-content="{{ trans('user.node.unstable') }}" data-trigger="hover"
                                           data-toggle="popover" data-placement="top"></i>
                                    @endif
                                    @if ($node->traffic_rate !== 1.0)
                                        <i class="green-600 icon wb-info-circle" data-content="{{ trans('user.node.rate', ['ratio' => $node->traffic_rate]) }}"
                                           data-trigger="hover" data-toggle="popover" data-placement="top"></i>
                                    @endif
                                    {{ $node->name }}
                                </p>
                                <blockquote>
                                    @foreach ($node->label_names->take(3) as $label_name)
                                        <span class="badge badge-lg badge-round badge-info">{{ $label_name }}</span>
                                    @endforeach
                                    @if ($node->label_names->count() > 3)
                                        <i class="icon wb-more-horizontal" data-content="{{ $node->label_names->join(', ') }}" data-trigger="hover"
                                           data-toggle="popover" data-placement="top"></i>
                                    @endif
                                    <br>
                                    {{ $node->description }}
                                </blockquote>
                                <div>
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
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/matchheight/jquery.matchHeight-min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/matchheight.js" type="text/javascript"></script>
    <script src="/assets/custom/easy.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/webui-popover.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="/assets/custom/maps/jquery-jvectormap-world-mill-cn.js"></script>

    <script type="text/javascript">
        $(function() {
            $("#world-map").vectorMap({
                map: "world_mill",
                scaleColors: ["#C8EEFF", "#0071A4"],
                normalizeFunction: "polynomial",
                zoomAnimate: true,
                hoverOpacity: 0.7,
                hoverColor: false,
                regionStyle: {
                    initial: {
                        fill: "#3E8EF7"
                    },
                    hover: {
                        fill: "#589FFC"
                    },
                    selected: {
                        fill: "#0B69E3"
                    },
                    selectedHover: {
                        fill: "#589FFC"
                    }
                },
                markerStyle: {
                    initial: {
                        r: 3,
                        fill: "#FF4C52",
                        "stroke-width": 0
                    },
                    hover: {
                        r: 6,
                        stroke: "#FF4C52",
                        "stroke-width": 0
                    }
                },
                backgroundColor: "#fff",
                markers: [
                    @foreach ($nodesGeo as $geo => $name)
                        {
                            latLng: [{{ $geo }}],
                            name: '{{ $name }}'
                        },
                    @endforeach
                ]
            });
            $(".node-card").matchHeight();
            $(".map").matchHeight();
        });

        function getInfo(id, type) {
            const oldClass = $(`#${type}${id}`).attr("class");
            const iconElement = $(`#${type}${id}`);

            ajaxPost(jsRoute('{{ route('node.show', 'PLACEHOLDER') }}', id), {
                type: type
            }, {
                beforeSend: function() {
                    iconElement.removeClass().addClass("icon wb-loop icon-spin");
                },
                success: function(ret) {
                    if (ret.status === "success") {
                        switch (type) {
                            case "code":
                                swal.fire({
                                    html: "<textarea class=\"form-control\" rows=\"8\" readonly=\"readonly\">" + ret.data + "</textarea>" +
                                        "<a href=\"" + ret.data + '" class="btn btn-block btn-danger mt-4">{{ trans('common.open') }}' +
                                        ret.title + "</a>",
                                    showConfirmButton: false
                                });
                                break;
                            case "qrcode":
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
                                break;
                            case "text":
                                swal.fire({
                                    title: '{{ trans('user.node.info') }}',
                                    html: "<textarea class=\"form-control\" rows=\"12\" readonly=\"readonly\">" + ret.data + "</textarea>",
                                    showConfirmButton: false
                                });
                                break;
                            default:
                                swal.fire({
                                    title: ret.title,
                                    text: ret.data,
                                    icon: "error"
                                });
                        }
                    }
                },
                complete: function() {
                    iconElement.removeClass().addClass(oldClass);
                }
            });
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
@endsection
