@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">节点流量</h2>
            </div>
            <div class="alert alert-info alert-dismissible">
                <button class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span><span class="sr-only">{{trans('common.close')}}</span>
                </button>
                <h4 class="block">{{$nodeName}}
                    <small class="pl-10">{{$nodeServer}}</small>
                </h4>
                <strong>提示：</strong> 如果无统计数据，请检查定时任务是否正常。
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="dailyChart" aria-label="小时流量图" role="img"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="monthlyChart" aria-label="月流量图" role="img"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
    <script>
        function common_options(tail) {
            return {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                return this.getLabelForValue(value) + tail;
                            },
                        },
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        ticks: {
                            callback: function(value) {
                                return this.getLabelForValue(value) + ' GB';
                            },
                        },
                        grid: {
                            display: false,
                        },
                        min: 0,
                    },

                },
                plugins: {
                    legend: false,
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label + tail;
                            },
                            label: function(context) {
                                return context.parsed.y + ' GB';
                            },
                        },
                    },
                },
            };
        }

        function datasets(label, data) {
            return {
                labels: label,
                datasets: [
                    {
                        backgroundColor: 'rgba(184, 215, 255)',
                        borderColor: 'rgba(184, 215, 255)',
                        data: data,
                        tension: 0.4,
                    }],
            };
        }

        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: datasets(@json($dayHours), @json($trafficHourly)),
            options: common_options(' {{trans_choice('validation.attributes.hour', 2)}}'),
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: datasets(@json($monthDays), @json($trafficDaily)),
            options: common_options(' {{trans_choice('validation.attributes.day', 2)}}'),
        });
    </script>
@endsection
