@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <form class="form-row">
                    <x-admin.filter.selectpicker class="col-xxl-2 col-md-3 col-sm-4" name="node_id" :title="trans('admin.logs.user_traffic.choose_node')" :options="$nodes" />
                    <div class="form-group col-xxl-1 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-md-30">
                <div class="row pb-20" style="height:calc(100% - 322px);">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_site_flow') }}</div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="row">
                            @isset($data['avgDaily30d'])
                                <div class="col-sm-6">
                                    <div class="counter counter-md">
                                        <div class="counter-number-group text-nowrap">
                                            <span class="counter-number">{{ $data['avgDaily30d'] }}</span>
                                            <span class="counter-number-related">GiB</span>
                                        </div>
                                        <div class="counter-label blue-grey-400">{{ trans('admin.report.avg_traffic_30d') }}</div>
                                    </div>
                                </div>
                            @endisset
                            @isset($data['nodePct30d'])
                                <div class="col-sm-6">
                                    <div class="counter counter-md">
                                        <div class="counter-number-group text-nowrap">
                                            <span class="counter-number">{{ $data['nodePct30d'] }}</span>
                                            <span class="counter-number-related">%</span>
                                        </div>
                                        <div class="counter-label blue-grey-400">{{ trans('admin.report.sum_traffic_30d') }}</div>
                                    </div>
                                </div>
                            @endisset
                        </div>
                    </div>
                </div>
                <canvas id="days"></canvas>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.monthly_site_flow') }}</div>
                    </div>
                </div>
                <canvas id="months"></canvas>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.annually_site_flow') }}</div>
                    </div>
                </div>
                <canvas id="years"></canvas>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script type="text/javascript">
        function label_callbacks(titleLabel, valueLabel = ' GiB') {
            return {
                mode: 'index',
                intersect: false,
                callbacks: {
                    title: function(context) {
                        return context[0].label + titleLabel;
                    },
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('{{ str_replace('_', '-', app()->getLocale()) }}').format(
                                context.parsed.y) + valueLabel;
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
                            font: {
                                size: 14
                            },
                        },
                    },
                    tooltip: label_callbacks(label || ''),
                },
            };
        }

        function create_area_dataset(label, data, color) {
            return {
                label: label,
                backgroundColor: color,
                borderColor: color,
                data: data,
                fill: {
                    target: 'origin',
                    above: color.replace(')', ', 0.5)'), // Change opacity
                },
                tension: 0.4,
            };
        }

        function create_chart(elementId, type, labels, datasets, options) {
            new Chart(document.getElementById(elementId), {
                type: type,
                data: {
                    labels: labels,
                    datasets: datasets,
                },
                options: options,
            });
        }

        const daysLabels = @json($data['days']);
        const currentMonthData = @json($data['currentMonth']);
        const lastMonthData = @json($data['lastMonth']);
        const yearsLabels = @json($data['months']);
        const currentYearData = @json($data['currentYear']);
        const lastYearData = @json($data['lastYear']);
        const yearlyFlowsLabels = @json(array_keys($data['yearlyFlows']));
        const yearlyFlowsData = @json(array_values($data['yearlyFlows']));

        create_chart('days', 'line', daysLabels, [
            create_area_dataset('{{ trans('admin.report.current_month') }}', currentMonthData, 'rgba(184, 215, 255)'),
            create_area_dataset('{{ trans('admin.report.last_month') }}', lastMonthData, 'rgba(146, 240, 230)'),
        ], common_options(' {{ trans_choice('common.days.attribute', 2) }}'));

        create_chart('months', 'line', yearsLabels, [
            create_area_dataset('{{ trans('admin.report.current_year') }}', currentYearData, 'rgba(184, 215, 255)'),
            create_area_dataset('{{ trans('admin.report.last_year') }}', lastYearData, 'rgba(146, 240, 230)'),
        ], common_options());

        create_chart('years', 'line', yearlyFlowsLabels, [
            create_area_dataset('{{ ucfirst(trans('validation.attributes.year')) }}', yearlyFlowsData, 'rgba(184, 215, 255)'),
        ], {
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
                tooltip: label_callbacks(' {{ ucfirst(trans('validation.attributes.year')) }}', ' TiB'),
            },
        });

        $(document).ready(function() {
            $('#node_id').val({{ Request::query('node_id') }});
        });
    </script>
@endsection
