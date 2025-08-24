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
                    <x-admin.filter.selectpicker class="col-xxl-2 col-md-3 col-sm-4" name="nodes" :title="trans('admin.logs.user_traffic.choose_node')" :multiple="true" :options="$nodes" />
                    <div class="form-group col-xxl-1 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @isset($data)
            <div class="row mx-0">
                <div class="col-md-12 col-xxl-7 card card-shadow">
                    <div class="card-block p-md-30">
                        <div class="row pb-20">
                            <div class="col-md-4 col-sm-6">
                                <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.hourly_traffic') }}</div>
                            </div>
                            <div class="col-md-8 col-sm-6">
                                <form class="form-row float-right">
                                    <div class="form-group">
                                        <select class="form-control show-tick" id="hour_date" name="hour_date" data-plugin="selectpicker"
                                                data-style="btn-outline btn-primary" title="{{ trans('admin.report.select_hourly_date') }}">
                                            @foreach ($hour_dates as $date)
                                                <option value="{{ $date }}">{{ $date }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <canvas id="hourlyBar"></canvas>
                    </div>
                </div>
                <div class="col-md-12 col-xxl-5 card card-shadow">
                    <div class="card-block p-md-30">
                        <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_distribution') }}</div>
                        <div class="d-flex justify-content-around">
                            <canvas id="dailyPie"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 offset-xxl-1 col-xxl-10 card card-shadow">
                    <div class="card-block p-md-30">
                        <div class="row pb-20">
                            <div class="col-md-4 col-sm-6">
                                <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_traffic') }}</div>
                            </div>
                            <div class="col-md-8 col-sm-6">
                                <form class="form-row float-right" onsubmit="handleFormSubmit(event, this);">
                                    <div class="form-group">
                                        <div class="input-group input-daterange" data-plugin="datepicker">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                                            </div>
                                            <input class="form-control" name="start" type="text"
                                                   value="{{ Request::query('start', now()->startOfMonth()->toDateString()) }}" autocomplete="off" />
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ trans('common.to') }}</span>
                                            </div>
                                            <input class="form-control" name="end" type="text" value="{{ Request::query('end', now()->toDateString()) }}"
                                                   autocomplete="off" />
                                            <div class="input-group-addon">
                                                <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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
    @if (app()->getLocale() !== 'en')
        <script src="/assets/global/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.{{ str_replace('_', '-', app()->getLocale()) }}.min.js" charset="UTF-8">
        </script>
    @endif
    <script src="/assets/global/js/Plugin/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        const nodeData = @json($data);

        const getRandomColor = (name) => {
            const hash = name.split("").reduce((acc, char) => char.charCodeAt(0) + ((acc << 5) - acc), 0);
            const hue = (hash % 360 + Math.random() * 50) % 360;
            const saturation = 50 + (hash % 30);
            const lightness = 40 + (hash % 20);
            return `hsla(${hue}, ${saturation}%, ${lightness}%, 0.55)`;
        };

        const generateNodeColorMap = (nodeNames) =>
            Object.fromEntries(Object.entries(nodeNames).map(([id, name]) => [id, getRandomColor(name)]));

        const optimizeDatasets = (datasets) => {
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
            const optimizedData = Object.entries(dataByDate).map(([date, dayData]) => ({
                time: date,
                data: allNodeIds.map(id => dayData.find(item => item.id === id)?.total || 0),
                total: dayData.reduce((sum, item) => sum + item.total, 0)
            }));

            return datasets.map((dataset, index) => ({
                ...dataset,
                data: optimizedData.map(day => ({
                    time: day.time,
                    total: day.data[index]
                }))
            }));
        };

        const generateDatasets = (flows, nodeColorMap) =>
            Object.entries(flows.reduce((acc, flow) => {
                acc[flow.id] = acc[flow.id] || [];
                acc[flow.id].push({
                    time: flow.time,
                    total: parseFloat(flow.total),
                    name: flow.name
                });
                return acc;
            }, {})).map(([nodeId, data]) => ({
                label: data[0].name,
                backgroundColor: nodeColorMap[nodeId],
                borderColor: nodeColorMap[nodeId],
                data,
                fill: true
            }));

        const createBarChart = (elementId, labels, datasets, labelTail, unit = "GiB") => {
            const optimizedDatasets = optimizeDatasets(datasets);
            new Chart(document.getElementById(elementId), {
                type: "bar",
                data: {
                    labels: labels || optimizedDatasets[0]?.data.map(d => d.time),
                    datasets: optimizedDatasets
                },
                plugins: [ChartDataLabels],
                options: {
                    parsing: {
                        xAxisKey: "time",
                        yAxisKey: "total"
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
                                pointStyle: "circle",
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                            callbacks: {
                                title: context => `${context[0].label} ${labelTail}`,
                                label: context => {
                                    const dataset = context.dataset;
                                    const value = dataset.data[context.dataIndex]?.total || context.parsed.y;
                                    return `${dataset.label || ""}: ${value.toFixed(2)} ${unit}`;
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            align: "end",
                            anchor: "end",
                            formatter: (value, context) => {
                                if (context.datasetIndex === context.chart.data.datasets.length - 1) {
                                    let total = context.chart.data.datasets.reduce((sum, dataset) => sum + dataset.data[context.dataIndex].total,
                                        0);
                                    return total.toFixed(2) + unit;
                                }
                                return null;
                            }
                        }
                    }
                }
            });
        };

        const createDoughnutChart = (elementId, labels, data, colors, date) => {
            Chart.register({
                id: "totalLabel",
                beforeDraw(chart) {
                    const {
                        ctx,
                        chartArea,
                        data
                    } = chart;
                    if (!chartArea || !data.datasets.length) return;

                    const total = data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                    if (typeof total !== "number") return;

                    const {
                        width,
                        height,
                        top,
                        left
                    } = chartArea;
                    const text = `${date}\n${total.toFixed(2)} GiB`;
                    ctx.save();
                    ctx.font = "bold 32px Roboto";
                    ctx.fillStyle = "black";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    const lineHeight = 40;
                    text.split("\n").forEach((line, index) => {
                        ctx.fillText(line, left + width / 2, top + height / 2 - lineHeight / 2 + index * lineHeight);
                    });
                    ctx.restore();
                }
            });

            new Chart(document.getElementById(elementId), {
                type: "doughnut",
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
                                    const label = tooltipItem.label || "";
                                    return `${label}: ${currentValue.toFixed(2)} G`;
                                }
                            }
                        },
                        datalabels: {
                            color: "#fff",
                            formatter: (value, context) => {
                                const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                                const percentage = (value / total * 100).toFixed(1);
                                const label = context.chart.data.labels[context.dataIndex];
                                return percentage > 1 ? `${label} ${percentage}%` : "";
                            },
                            anchor: "center",
                            rotation: function(ctx) {
                                const valuesBefore = ctx.dataset.data.slice(0, ctx.dataIndex).reduce((a, b) => a + b, 0);
                                const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const rotation = ((valuesBefore + ctx.dataset.data[ctx.dataIndex] / 2) / sum * 360);
                                return rotation < 180 ? rotation - 90 : rotation + 90;
                            },
                            font: {
                                weight: "bold",
                                size: 16,
                                family: "Roboto"
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels, "totalLabel"]
            });
        };

        const generatePieData = (flows, nodeColorMap) => {
            return {
                labels: flows.map(flow => flow.name),
                data: flows.map(flow => flow.total),
                colors: flows.map(flow => nodeColorMap[flow.id])
            };
        };

        const initCharts = () => {
            if (nodeData) {
                const nodeColorMap = generateNodeColorMap(nodeData.nodes);
                createBarChart("hourlyBar", nodeData.hours.map(String), generateDatasets(nodeData.hourlyFlows, nodeColorMap), @json(trans_choice('common.hour', 2)),
                    "GiB");
                createBarChart("dailyBar", "", generateDatasets(nodeData.dailyFlows, nodeColorMap), "", "GiB");

                const lastDate = nodeData.dailyFlows[nodeData.dailyFlows.length - 1].time;
                const lastDayData = nodeData.dailyFlows.filter(flow => flow.time === lastDate);
                const {
                    labels,
                    data,
                    colors
                } = generatePieData(lastDayData, nodeColorMap);
                createDoughnutChart("dailyPie", labels, data, colors, lastDate);
            }
        };

        const handleFormSubmit = (event, form) => {
            event.preventDefault();
            let urlParams = new URLSearchParams(window.location.search);
            let formData = new FormData(form);

            for (let [key, value] of formData.entries()) {
                value ? urlParams.set(key, value) : urlParams.delete(key);
            }

            window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
        };

        const resetSearchForm = () => {
            window.location.href = window.location.href.split("?")[0];
        };

        document.addEventListener("DOMContentLoaded", () => {
            initCharts();

            const hourDateSelect = document.getElementById("hour_date");
            if (hourDateSelect) {
                hourDateSelect.addEventListener("change", (event) => handleFormSubmit(event, event.target.form));
                $(hourDateSelect).selectpicker("val", new URLSearchParams(window.location.search).get("hour_date") || @json(now()->toDateString()));
            }

            $(".input-daterange").datepicker({
                startDate: nodeData.start_date,
                endDate: new Date()
            });
        });
    </script>
@endsection
