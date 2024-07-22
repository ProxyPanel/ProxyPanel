@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container-fluid w-xl-p75 w-xxl-p100">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <form class="form-row">
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <select class="form-control show-tick" id="hour_date" name="hour_date" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('admin.report.select_hourly_date') }}" onchange="cleanSubmit(this.form);this.form.submit()">
                            @foreach ($hour_dates as $date)
                                <option value="{{ $date }}">{{ $date }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <select class="form-control show-tick" id="nodes" name="nodes[]" data-plugin="selectpicker" data-style="btn-outline btn-primary"
                                title="{{ trans('admin.logs.user_traffic.choose_node') }}" multiple>
                            @foreach ($nodes as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-6 col-sm-12">
                        <div class="input-group input-daterange" data-plugin="datepicker">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                            </div>
                            <input class="form-control" name="start" type="text" value="{{ Request::query('start') }}" autocomplete="off" />
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ trans('common.to') }}</span>
                            </div>
                            <input class="form-control" name="end" type="text" value="{{ Request::query('end') }}" autocomplete="off" />
                        </div>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @isset($data)
            <div class="row">
                <div class="col-md-12 col-xxl-7 card card-shadow">
                    <div class="card-block p-30">
                        <div class="row pb-20">
                            <div class="col-md-8 col-sm-6">
                                <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.hourly_traffic') }}</div>
                            </div>
                        </div>
                        <canvas id="hourlyBar"></canvas>
                    </div>
                </div>
                <div class="col-md-12 col-xxl-5 card card-shadow">
                    <div class="card-block p-30">
                        <div class="row pb-20">
                            <div class="col-md-8 col-sm-6">
                                <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_distribution') }}</div>
                            </div>
                        </div>
                        <canvas id="dailyPie"></canvas>
                    </div>
                </div>
                <div class="col-12 offset-xxl-2 col-xxl-8 card card-shadow">
                    <div class="card-block p-30">
                        <div class="row pb-20">
                            <div class="col-md-8 col-sm-6">
                                <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_traffic') }}</div>
                            </div>
                        </div>
                        <canvas id="dailyBar"></canvas>
                    </div>
                </div>
            </div>
        @endisset
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
    <script src="/assets/global/vendor/chart-js/chartjs-plugin-datalabels.min.js"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js"></script>
    <script src="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        const nodeData = @json($data);
        const nodeColorMap = generateNodeColorMap(nodeData.nodes);

        function resetSearchForm() {
            window.location.href = window.location.href.split('?')[0];
        }

        function initDatepicker() {
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                startDate: nodeData.start_date,
                endDate: new Date(),
            });
        }

        function cleanSubmit(form) {
            $(form).find('input:not([type="submit"]), select').filter(function() {
                return this.value === "";
            }).prop('disabled', true);

            setTimeout(function() {
                $(form).find(':disabled').prop('disabled', false);
            }, 0);
        }

        function handleFormSubmit() {
            $('form').on('submit', function() {
                cleanSubmit(this);
            });
        }

        function initSelectors() {
            $('#nodes').selectpicker('val', @json(Request::query('nodes')));
            $('#hour_date').selectpicker('val', @json(Request::query('hour_date')));
            $('.input-daterange').datepicker('update', @json(Request::query('start')), @json(Request::query('end')));
        }

        function optimizeDatasets(datasets) {
            const dataByDate = datasets.reduce((acc, dataset) => {
                dataset.data.forEach(item => {
                    acc[item.time] = acc[item.time] || [];
                    acc[item.time].push({
                        id: dataset.label,
                        total: parseFloat(item.total)
                    });
                });
                return acc;
            }, {});

            const allNodeIds = datasets.map(d => d.label);
            const optimizedData = Object.entries(dataByDate).map(([date, dayData]) => {
                const total = dayData.reduce((sum, item) => sum + item.total, 0);
                const filledData = allNodeIds.map(id => {
                    const nodeData = dayData.find(item => item.id === id);
                    return nodeData ? nodeData.total : 0;
                });
                return {
                    time: date,
                    data: filledData,
                    total
                };
            });

            return datasets.map((dataset, index) => ({
                ...dataset,
                data: optimizedData.map(day => ({
                    time: day.time,
                    total: day.data[index]
                }))
            }));
        }

        function createBarChart(elementId, labels, datasets, labelTail, unit = 'MiB') {
            const optimizedDatasets = optimizeDatasets(datasets);
            new Chart(document.getElementById(elementId), {
                type: 'bar',
                data: {
                    labels: optimizedDatasets[0].data.map(d => d.time),
                    datasets: optimizedDatasets
                },
                plugins: [ChartDataLabels],
                options: {
                    parsing: {
                        xAxisKey: 'time',
                        yAxisKey: 'total'
                    },
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true
                        }
                    },
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 14
                                }
                            },
                        },
                        tooltip: label_callbacks(labelTail, unit),
                        datalabels: {
                            display: true,
                            align: 'end',
                            anchor: 'end',
                            formatter: (value, context) => {
                                if (context.datasetIndex === context.chart.data.datasets.length - 1) {
                                    let total = context.chart.data.datasets.reduce((sum, dataset) => sum + dataset.data[context.dataIndex].total, 0);
                                    return total.toFixed(2) + unit;
                                }
                                return null;
                            },
                        },
                    },
                },
            });
        }

        function label_callbacks(tail, unit) {
            return {
                mode: 'index',
                intersect: false,
                callbacks: {
                    title: context => `${context[0].label} ${tail}`,
                    label: context => {
                        const dataset = context.dataset;
                        const value = dataset.data[context.dataIndex]?.total || context.parsed.y;
                        return `${dataset.label || ''}: ${value.toFixed(2)} ${unit}`;
                    }
                }
            };
        }

        function generateNodeColorMap(nodeNames) {
            return Object.fromEntries(Object.entries(nodeNames).map(([id, name]) => [id, getRandomColor(name)]));
        }

        function getRandomColor(name) {
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            const hue = (hash % 360 + Math.random() * 50) % 360;
            const saturation = 50 + (hash % 30);
            const lightness = 40 + (hash % 20);
            return `hsla(${hue}, ${saturation}%, ${lightness}%, 0.55)`;
        }

        function generateDatasets(flows) {
            const dataByNode = flows.reduce((acc, flow) => {
                acc[flow.id] = acc[flow.id] || [];
                acc[flow.id].push({
                    time: flow.time,
                    total: parseFloat(flow.total),
                    name: flow.name
                });
                return acc;
            }, {});

            return Object.entries(dataByNode).map(([nodeId, data]) => ({
                label: data[0].name,
                backgroundColor: nodeColorMap[nodeId],
                borderColor: nodeColorMap[nodeId],
                data,
                fill: true,
            }));
        }

        function createDoughnutChart(elementId, labels, data, colors, date) {
            Chart.register({
                id: 'totalLabel',
                beforeDraw(chart) {
                    const {
                        ctx,
                        chartArea,
                        data
                    } = chart;
                    if (!chartArea || !data.datasets.length) return;

                    const total = data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                    if (typeof total !== 'number') return;

                    const {
                        width,
                        height,
                        top,
                        left
                    } = chartArea;
                    const text = `${date}\n\n${total.toFixed(2)} GiB`;
                    ctx.save();
                    ctx.font = 'bold 32px Roboto';
                    ctx.fillStyle = 'black';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    const lineHeight = 40;
                    text.split('\n').forEach((line, index) => {
                        ctx.fillText(line, left + width / 2, top + height / 2 - lineHeight / 2 + index * lineHeight);
                    });
                    ctx.restore();
                },
            });

            new Chart(document.getElementById(elementId), {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: tooltipItem => {
                                    const dataset = tooltipItem.dataset;
                                    const currentValue = dataset.data[tooltipItem.dataIndex];
                                    const label = tooltipItem.label || '';
                                    return `${label}: ${currentValue.toFixed(2)} G`;
                                },
                            },
                        },
                        datalabels: {
                            color: '#fff',
                            formatter: (value, context) => {
                                const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                                const percentage = (value / total * 100).toFixed(1);
                                const label = context.chart.data.labels[context.dataIndex];
                                return percentage > 1 ? `${label} ${value.toFixed(2)}G ${percentage}%` : '';
                            },
                            anchor: "center",
                            rotation: function(ctx) {
                                const valuesBefore = ctx.dataset.data.slice(0, ctx.dataIndex).reduce((a, b) => a + b, 0);
                                const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const rotation = ((valuesBefore + ctx.dataset.data[ctx.dataIndex] / 2) / sum * 360);
                                return rotation < 180 ? rotation - 90 : rotation + 90;
                            },
                            font: {
                                weight: 'bold',
                                size: 16,
                                family: 'Roboto'
                            },
                        },
                    },
                },
                plugins: [ChartDataLabels, 'totalLabel'],
            });
        }

        function generatePieData(flows) {
            return {
                labels: flows.map(flow => flow.name),
                data: flows.map(flow => flow.total),
                colors: flows.map(flow => nodeColorMap[flow.id]),
            };
        }

        function initCharts() {
            createBarChart('hourlyBar', nodeData.hours, generateDatasets(nodeData.hourlyFlows), @json(trans_choice('common.hour', 2)), ' GiB');
            createBarChart('dailyBar', '', generateDatasets(nodeData.dailyFlows), '', ' GiB');

            const lastDate = nodeData.dailyFlows[nodeData.dailyFlows.length - 1].time;
            const lastDayData = nodeData.dailyFlows.filter(flow => flow.time === lastDate);
            const {
                labels,
                data,
                colors
            } = generatePieData(lastDayData);
            createDoughnutChart('dailyPie', labels, data, colors, lastDate);
        }

        $(document).ready(function() {
            initDatepicker();
            handleFormSubmit();
            initSelectors();
            initCharts();
        });
    </script>
@endsection
