@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">月流水账簿</div>
                    </div>
                </div>
                <canvas id="days"></canvas>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">年流水账簿</div>
                    </div>
                </div>
                <canvas id="months"></canvas>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">历史流水账簿</div>
                    </div>
                </div>
                <canvas id="years"></canvas>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
    <script type="text/javascript">
        function label_callbacks(tail) {
            return {
                mode: 'index',
                intersect: false,
                callbacks: {
                    title: function(context) {
                        return context[0].label + tail;
                    },
                    label: function(context) {
                        let label = context.dataset.label || '';

                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('ch-CN', {style: 'currency', currency: 'CNY'}).format(context.parsed.y);
                        }
                        return label;
                    },
                },
            };
        }

        function common_options(label) {
            return {
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        min: 0,
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {size: 14},
                        },
                    },
                    tooltip: label_callbacks(label),
                },
            };
        }

        function area_a(label, data) {
            return {
                label: label,
                backgroundColor: 'rgba(184, 215, 255)',
                borderColor: 'rgba(184, 215, 255)',
                data: data,
                fill: {
                    target: 'origin',
                    above: 'rgba(184, 215, 255, 0.5)',
                },
                tension: 0.4,
            };
        }

        function area_b(label, data) {
            return {
                label: label,
                backgroundColor: 'rgba(146, 240, 230)',
                borderColor: 'rgba(146, 240, 230)',
                data: data,
                fill: {
                    target: 'origin',
                    above: 'rgba(146, 240, 230, 0.5)',
                },
                tension: 0.4,
            };
        }

        new Chart(document.getElementById('days'), {
            type: 'line',
            data: {
                labels: @json($data['days']),
                datasets: [area_a(' 本 月 ',@json($data['currentMonth'])), area_b(' 上 月 ',@json($data['lastMonth']))],
            },
            options: common_options(' 日'),
        });

        new Chart(document.getElementById('months'), {
            type: 'line',
            data: {
                labels: @json($data['years']),
                datasets: [area_a(' 今 年 ',@json($data['currentYear'])), area_b(' 去 年 ',@json($data['lastYear']))],
            },
            options: common_options(' 月'),
        });

        new Chart(document.getElementById('years'), {
            type: 'line',
            data: {
                labels: @json(array_keys($data['ordersByYear'])),
                datasets: [
                    {
                        backgroundColor: 'rgba(184, 215, 255)',
                        borderColor: 'rgba(184, 215, 255)',
                        data: @json(array_values($data['ordersByYear'])),
                        fill: {target: 'origin'},
                        tension: 0.4,
                    }],
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        min: 0,
                    },
                },
                plugins: {
                    legend: false,
                    tooltip: label_callbacks(' 年'),
                },
            },
        });
    </script>
@endsection
