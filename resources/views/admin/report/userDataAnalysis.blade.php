@extends('admin.layouts')
@section('content')
    <div class="page-content container">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input class="form-control" name="uid" type="number" value="{{ Request::query('uid') }}" placeholder="{{ trans('model.user.id') }}" />
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input class="form-control" name="username" type="text" value="{{ Request::query('username') }}"
                               placeholder="{{ trans('model.user.username') }}" />
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @isset($data)
            <div class="card card-shadow">
                <div class="card-block p-30">
                    <div class="row pb-20">
                        <div class="col-md-8 col-sm-6">
                            <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.hourly_traffic') }}</div>
                        </div>
                    </div>
                    <canvas id="hourlyBar"></canvas>
                </div>
            </div>
            <div class="card card-shadow">
                <div class="card-block p-30">
                    <div class="row pb-20">
                        <div class="col-md-8 col-sm-6">
                            <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_traffic') }}</div>
                        </div>
                    </div>
                    <canvas id="dailyBar"></canvas>
                </div>
            </div>
        @endisset
    </div>
@endsection
@section('javascript')
    @isset($data)
        <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
        <script src="/assets/global/vendor/chart-js/chartjs-plugin-datalabels.min.js"></script>
        <script type="text/javascript">
            const userData = @json($data);
            const nodeColorMap = generateNodeColorMap(userData.nodes); // 获取所有节点名称并生成颜色映射

            function resetSearchForm() {
                window.location.href = window.location.href.split('?')[0];
            }

            function handleFormSubmit() {
                $('form').on('submit', function() {
                    $(this).find('input, select').each(function() {
                        if (!$(this).val()) {
                            $(this).remove();
                        }
                    });
                });
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
                        labels: optimizedDatasets[0]?.data.map(d => d.time),
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

            // 创建图表
            function initCharts() {
                createBarChart('hourlyBar', userData.hours, generateDatasets(userData.hourlyFlows), @json(trans_choice('common.hour', 2)));
                createBarChart('dailyBar', userData.days, generateDatasets(userData.dailyFlows), @json(trans_choice('common.days.attribute', 2)));
            }

            $(document).ready(function() {
                handleFormSubmit();
                initCharts();
            });
        </script>
    @endisset
@endsection
