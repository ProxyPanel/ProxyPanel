@extends('user.layouts')

@section('css')
@endsection

@section('title', trans('home.panel'))
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="note note-info">
                    <p> {{trans('home.traffic_log_tips')}} </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div id="chart1" style="width: auto;height:450px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div id="chart2" style="width: auto;height:450px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/echarts/echarts.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        var myChart = echarts.init(document.getElementById('chart1'));

        option = {
            title: {
                text: '{{trans('home.traffic_log_30days')}}',
                subtext: '{{trans('home.traffic_log_unit')}}'
            },
            tooltip: {
                trigger: 'axis'
            },
            toolbox: {
                show: true,
                feature: {
                    saveAsImage: {}
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30']
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '{value} M'
                }
            },
            series: [
                @if(!empty($trafficDaily))
                {
                    name:'{{trans('home.traffic_log_keywords')}}',
                    type:'line',
                    data:[{!! $trafficDaily !!}],
                    markPoint: {
                        data: [
                            {type: 'max', name: '{{trans('home.traffic_log_max')}}'}
                        ]
                    }
                }
                @endif
            ]
        };

        myChart.setOption(option);
    </script>
    <script type="text/javascript">
        var myChart = echarts.init(document.getElementById('chart2'));

        option = {
            title: {
                text: '{{trans('home.traffic_log_24hours')}}',
                subtext: '{{trans('home.traffic_log_unit')}}'
            },
            tooltip: {
                trigger: 'axis'
            },
            toolbox: {
                show: true,
                feature: {
                    saveAsImage: {}
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24']
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '{value} M'
                }
            },
            series: [
                @if(!empty($trafficHourly))
                {
                    name:'{{trans('home.traffic_log_keywords')}}',
                    type:'line',
                    data:[{!! $trafficHourly !!}],
                    markPoint: {
                        data: [
                            {type: 'max', name: '{{trans('home.traffic_log_max')}}'}
                        ]
                    }
                }
                @endif
            ]
        };

        myChart.setOption(option);
    </script>
@endsection