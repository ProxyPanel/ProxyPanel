@extends('user.layouts')
@section('css')
    <script src="//at.alicdn.com/t/font_682457_e6aq10jsbq0yhkt9.js" type="text/javascript"></script>
    <link href="/assets/global/fonts/font-awesome/font-awesome.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/webui-popover/webui-popover.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/global/vendor/jvectormap/jquery-jvectormap.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
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
                        <div class="card card-block p-20  bg-indigo-500">
                            <div class="counter counter-lg counter-inverse">
                                <div class="counter-label text-uppercase font-size-16">账号等级</div>
                                <div class="counter-number-group">
                                    <span class="counter-icon"><i class="icon wb-user-circle" aria-hidden="true"></i></span>
                                    <span class="counter-number ml-10">{{Auth::getUser()->level}}</span>
                                </div>
                                <div class="counter-label text-uppercase font-size-16">{{Auth::getUser()->level_name}}</div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::getUser()->group_id)
                        <div class="col-md-12">
                            <div class="card card-block p-20 bg-indigo-500">
                                <div class="counter counter-lg counter-inverse">
                                    <div class="counter-label text-uppercase font-size-16">所属分组</div>
                                    <div class="counter-number-group">
                                        <span class="counter-icon"><i class="icon wb-globe" aria-hidden="true"></i></span>
                                        <span class="counter-number ml-10">{{Auth::getUser()->group->name}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <div class="card card-block p-20 bg-indigo-500">
                            <div class="counter counter-lg counter-inverse">
                                <div class="counter-label text-uppercase font-size-16">限速</div>
                                <div class="counter-number-group">
                                    <span class="counter-icon"><i class="icon wb-signal" aria-hidden="true"></i></span>
                                    <span class="counter-number ml-10">{{Auth::getUser()->speed_limit ?? '无限制'}}</span>
                                </div>
                                <div class="counter-label font-size-16">Mbps</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @foreach($nodeList as $node)
                <div class="col-xxl-3 col-xl-4 col-sm-6">
                    <div class="card card-inverse card-shadow bg-white node">
                        <div class="card-block p-30 row">
                            <div class="col-4">
                                <svg class="w-p100 text-center" aria-hidden="true">
                                    <use xlink:href="@if($node->country_code)#icon-{{$node->country_code}}@else #icon-un @endif"></use>
                                </svg>
                            </div>
                            <div class="col-8 text-break text-right">
                                <p class="font-size-20 blue-600">
                                    <span class="badge badge-pill up m-0 badge-default">{{$node->level_table->name}}</span>
                                    @if($node->offline)
                                        <i class="red-600 icon wb-warning" data-content="线路波动/维护中" data-trigger="hover" data-toggle="popover" data-placement="top"></i>
                                    @endif
                                    @if($node->traffic_rate != 1)
                                        <i class="green-600 icon wb-info-circle" data-content="{{$node->traffic_rate}} 倍流量消耗" data-trigger="hover" data-toggle="popover"
                                           data-placement="top"></i>
                                    @endif
                                    {{$node->name}}
                                </p>
                                <blockquote>
                                    @foreach($node->labels as $label)
                                        <span class="badge badge-pill font-size-10 up m-0 badge-info">{{$label->label->name}}</span>
                                    @endforeach
                                    <br>
                                    {{$node->description}}
                                </blockquote>
                                <p class="font-size-14">
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{$node->id}}','code')">
                                        <i id="code{{$node->id}}" class="icon fa-code"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{$node->id}}','qrcode')">
                                        <i id="qrcode{{$node->id}}" class="icon fa-qrcode"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="getInfo('{{$node->id}}','text')">
                                        <i id="text{{$node->id}}" class="icon fa-list"></i>
                                    </button>
                                </p>
                                <p class="text-muted">
                                    <span>电信： {{$node->ct>0 ?$node->ct.' ms' :'无数据'}} </span>
                                    <span>联通： {{$node->cu>0 ?$node->cu.' ms' :'无数据'}} </span>
                                    <br>
                                    <span>移动： {{$node->cm>0 ?$node->cm.' ms' :'无数据'}} </span>
                                    <span>香港： {{$node->hk>0 ?$node->hk.' ms' :'无数据'}} </span>
                                </p>
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
    <script src="/assets/custom/jquery-qrcode/jquery.qrcode.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/webui-popover.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="/assets/custom/maps/jquery-jvectormap-world-mill-cn.js"></script>

    <script type="text/javascript">
      $(function() {
        $('#world-map').vectorMap({
          map: 'world_mill',
          scaleColors: ['#C8EEFF', '#0071A4'],
          normalizeFunction: 'polynomial',
          zoomAnimate: true,
          hoverOpacity: 0.7,
          hoverColor: false,
          regionStyle: {
            initial: {
              fill: '#3E8EF7',
            },
            hover: {
              fill: '#589FFC',
            },
            selected: {
              fill: '#0B69E3',
            },
            selectedHover: {
              fill: '#589FFC',
            },
          },
          markerStyle: {
            initial: {
              r: 3,
              fill: '#FF4C52',
              'stroke-width': 0,
            },
            hover: {
              r: 6,
              stroke: '#FF4C52',
              'stroke-width': 0,
            },
          },
          backgroundColor: '#fff',
          markers: [
                  @foreach($nodesGeo as $name => $geo)
            {
              latLng: [{{$name}}], name: '{{$geo}}',
            },
              @endforeach
          ],
        });
        $('.node').matchHeight();
        $('.map').matchHeight();
      });

      function getInfo(id, type) {
        const oldClass = $('#' + type + id).attr('class');
        $.ajax({
          method: 'POST',
          url: '{{route('node')}}',
          data: {_token: '{{csrf_token()}}', id: id, type: type},
          beforeSend: function() {
            $('#' + type + id).removeAttr('class').addClass('icon wb-loop icon-spin');
          },
          success: function(ret) {
            if (ret.status === 'success') {
              switch (type) {
                case 'code':
                  swal.fire({
                    html: '<textarea class="form-control" rows="8" readonly="readonly">' + ret.data + '</textarea>' +
                        '<a href="' + ret.data + '" class="btn btn-danger btn-block mt-10">打开' + ret.title + '</a>',
                    showConfirmButton: false,
                  });
                  break;
                case 'qrcode':
                  swal.fire({
                    title: '{{trans('home.scan_qrcode')}}',
                    html: '<div id="qrcode"></div>',
                    onBeforeOpen: () => {
                      $('#qrcode').qrcode({text: ret.data});
                    },
                    showConfirmButton: false,
                  });
                  break;
                case 'text':
                  swal.fire({
                    title: '{{trans('home.setting_info')}}',
                    html: '<textarea class="form-control" rows="12" readonly="readonly">' + ret.data + '</textarea>',
                    showConfirmButton: false,
                  });
                  break;
                default:
                  swal.fire({title: ret.title, text: ret.data, icon: 'error'});
              }
            }
          },
          complete: function() {
            $('#' + type + id).removeAttr('class').addClass(oldClass);
          },
        });
      }
    </script>
@endsection
