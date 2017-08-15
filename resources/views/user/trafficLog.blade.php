@extends('user.layouts')

@section('css')
@endsection

@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('user/trafficLog')}}">流量日志</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light portlet-fit bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-settings font-dark"></i>
                            <span class="caption-subject font-dark sbold uppercase">流量日志</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div id="chart" class="chart"> </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/flot/jquery.flot.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        var ChartsFlotcharts = function() {
            return {
                init: function() {
                    App.addResizeHandler(function() {
                        Charts.initPieCharts();
                    });
                },

                initCharts: function() {
                    if (!jQuery.plot) {
                        return;
                    }

                    function chart() {
                        if ($('#chart').size() != 1) {
                            return;
                        }

                        var chartData = [
                            @if (!empty($trafficList))
                                @foreach ($trafficList as $key => $vo)
                                    [{{$key + 1}}, {{$vo->total}}],
                                @endforeach
                            @endif
                        ];

                        var plot = $.plot($("#chart"), [
                                {data: chartData, label: "30日流量走势", lines: {lineWidth: 1}, shadowSize: 0},
                            ], {
                                series: {
                                    lines: {
                                        show: true,
                                        lineWidth: 2,
                                        fill: true,
                                        fillColor: {
                                            colors: [{
                                                opacity: 0.05
                                            }, {
                                                opacity: 0.01
                                            }]
                                        }
                                    },
                                    points: {
                                        show: true,
                                        radius: 3,
                                        lineWidth: 1
                                    },
                                    shadowSize: 2
                                },
                                grid: {
                                    hoverable: true,
                                    clickable: true,
                                    tickColor: "#eee",
                                    borderColor: "#eee",
                                    borderWidth: 1
                                },
                                colors: ["#d12610", "#37b7f3", "#52e136"],
                                xaxis: {
                                    ticks: 11,
                                    tickDecimals: 0,
                                    tickColor: "#eee",
                                },
                                yaxis: {
                                    ticks: 11,
                                    tickDecimals: 0,
                                    tickColor: "#eee",
                                }
                            });


                        function showTooltip(x, y, contents) {
                            $('<div id="tooltip">' + contents + '</div>').css({
                                position: 'absolute',
                                display: 'none',
                                top: y + 5,
                                left: x + 15,
                                border: '1px solid #333',
                                padding: '4px',
                                color: '#fff',
                                'border-radius': '3px',
                                'background-color': '#333',
                                opacity: 0.80
                            }).appendTo("body").fadeIn(200);
                        }

                        var previousPoint = null;
                        $("#chart").bind("plothover", function(event, pos, item) {
                            $("#x").text(pos.x.toFixed(2));
                            $("#y").text(pos.y.toFixed(2));

                            if (item) {
                                if (previousPoint != item.dataIndex) {
                                    previousPoint = item.dataIndex;

                                    $("#tooltip").remove();
                                    var x = item.datapoint[0].toFixed(2),
                                        y = item.datapoint[1].toFixed(2);

                                    showTooltip(item.pageX, item.pageY, item.series.label + ": " + y + 'M');
                                }
                            } else {
                                $("#tooltip").remove();
                                previousPoint = null;
                            }
                        });
                    }

                    chart();
                }
            };
        }();

        jQuery(document).ready(function() {
            ChartsFlotcharts.init();
            ChartsFlotcharts.initCharts();
        });
    </script>
@endsection