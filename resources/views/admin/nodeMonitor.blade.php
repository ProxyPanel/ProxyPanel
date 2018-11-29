@extends('admin.layouts')
@section('css')
@endsection
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="note note-info">
                    <h3 class="block">{{$nodeName}}<small style="padding-left:10px;">{{$nodeServer}}</small></h3>
                    <p> 提示：月流量统计不会统计当天，日流量统计不会统计当前小时；如果无统计数据，请检查定时任务是否正常。 </p>
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
                text: '今日流量',
                subtext: '单位 / G'
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
                data: ['0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23']
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '{value} G'
                }
            },
            series: [
                @if(!empty($trafficHourly))
                    {
                        name:'{{$trafficHourly['nodeName']}}',
                        type:'line',
                        data:[{!! $trafficHourly['hourlyData'] !!}],
                        markPoint: {
                            data: [
                                {type: 'max', name: '最大值'}
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
                text: '本月流量',
                subtext: '单位 / G'
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
                data: [{!! $monthDays !!}]
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '{value} G'
                }
            },
            series: [
                @if(!empty($trafficDaily))
                    {
                        name:'{{$trafficDaily['nodeName']}}',
                        type:'line',
                        data:[{!! $trafficDaily['dailyData'] !!}],
                        markPoint: {
                            data: [
                                {type: 'max', name: '最大值'}
                            ]
                        }
                    }
                @endif
            ]
        };

        myChart.setOption(option);
    </script>
@endsection