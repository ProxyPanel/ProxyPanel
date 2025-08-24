@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <x-ui.panel :title="trans('admin.monitor.node')">
            <x-slot:alert>
                <x-alert type="info">
                    <h4 class="block">{{ $nodeName }}
                        <small class="pl-10">{{ $nodeServer }}</small>
                    </h4>
                    {!! trans('admin.monitor.hint') !!}
                </x-alert>
            </x-slot:alert>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="dailyChart" role="img" aria-label="{{ trans('admin.monitor.daily_chart') }}"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="monthlyChart" role="img" aria-label="{{ trans('admin.monitor.monthly_chart') }}"></canvas>
                </div>
            </div>
        </x-ui.panel>
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
                                return this.getLabelForValue(value) + ' ' + tail;
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
                                return context[0].label + ' ' + tail;
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
                datasets: [{
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
            options: common_options(@json(trans_choice('common.hour', 2))),
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: datasets(@json($monthDays), @json($trafficDaily)),
            options: common_options(@json(trans_choice('common.days.attribute', 2))),
        });
    </script>
@endsection
