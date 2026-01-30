@extends('admin.layouts')
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/assets/global/vendor/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="page-content container">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <form class="form-row" onsubmit="handleFormSubmit(event, this);">
                    <x-admin.filter.input class="col-lg-1 col-md-2 col-sm-4" name="uid" type="number" :placeholder="trans('model.user.id')" />
                    <x-admin.filter.input class="col-xxl-2 col-md-3 col-sm-4" name="username" :placeholder="trans('model.user.username')" />
                    <div class="form-group col-xxl-1 col-md-3 col-4 btn-group">
                        <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                        <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @if (count($data) > 2)
            <div class="card card-shadow">
                <div class="card-block p-lg-30 p-10">
                    <div class="row pb-20">
                        <div class="col-md-4 col-sm-6">
                            <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.hourly_traffic') }}</div>
                        </div>
                        <div class="col-md-8 col-sm-6">
                            <form class="form-row float-right">
                                <div class="form-group">
                                    <select class="form-control show-tick" id="hour_date" name="hour_date" data-plugin="selectpicker"
                                            data-style="btn-outline btn-primary" title="{{ trans('admin.report.select_hourly_date') }}">
                                        @foreach ($data['hour_dates'] as $date)
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
            <div class="card card-shadow">
                <div class="card-block p-lg-30 p-10">
                    <div class="row pb-20">
                        <div class="col-md-4 col-sm-6">
                            <div class="blue-grey-700 font-size-26 font-weight-500">{{ trans('admin.report.daily_traffic') }}</div>
                        </div>
                        <div class="col-md-8 col-sm-6">
                            <form class="form-row float-right" onsubmit="handleFormSubmit(event, this);">
                                <div class="form-group">
                                    <div class="input-group input-daterange">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="icon wb-calendar" aria-hidden="true"></i></span>
                                        </div>
                                        <input class="form-control" name="start" data-plugin="datepicker" type="text"
                                               value="{{ Request::query('start', now()->startOfMonth()->toDateString()) }}" autocomplete="off" />
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ trans('common.to') }}</span>
                                        </div>
                                        <input class="form-control" name="end" data-plugin="datepicker" type="text"
                                               value="{{ Request::query('end', now()->toDateString()) }}" autocomplete="off" />
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
        @endif
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chart-js/chart.umd.min.js"></script>
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
        const userData = @json($data);

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

        const createBarChart = (elementId, labels, datasets, labelTail, unit = "MiB") => {
            const optimizedDatasets = optimizeDatasets(datasets);
            const isMobile = window.innerWidth <= 768;
            new Chart(document.getElementById(elementId), {
                type: "bar",
                data: {
                    labels: labels || optimizedDatasets[0]?.data.map(d => d.time),
                    datasets: optimizedDatasets
                },
                plugins: [ChartDataLabels],
                options: {
                    aspectRatio: isMobile ? 0.8 : 2,
                    parsing: {
                        xAxisKey: "time",
                        yAxisKey: "total"
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                font: {
                                    size: isMobile ? 10 : 12,
                                },
                            }
                        },
                        y: {
                            stacked: true,
                            grace: '10%', // 在 Y 轴最大值上方自动留出 20% 空间，防止数值贴边
                            ticks: {
                                font: {
                                    size: isMobile ? 10 : 12,
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: !isMobile, // 移动端隐藏图例
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: "circle",
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            mode: "index",
                            intersect: true,
                            filter: (item) => item.raw.total > 0.01,
                            itemSort: (a, b) => b.raw.total - a.raw.total,
                            bodyFont: {
                                size: isMobile ? 10 : 14,
                            },
                            callbacks: {
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw.total.toFixed(2)} ${unit}`
                            }
                        },
                        datalabels: {
                            display: (ctx) => {
                                // 仅顶层显示 & 数值大于0
                                if (ctx.datasetIndex !== ctx.chart.data.datasets.length - 1) return false;
                                const total = ctx.chart.data.datasets.reduce((sum, ds) =>
                                    sum + (ds.data[ctx.dataIndex]?.total || 0), 0);
                                return total > 0;
                            },
                            align: "top",
                            anchor: "end",
                            font: {
                                size: isMobile ? 10 : 14,
                            },
                            formatter: (value, ctx) => {
                                const total = ctx.chart.data.datasets.reduce((sum, ds) =>
                                    sum + (ds.data[ctx.dataIndex]?.total || 0), 0);
                                return total.toFixed(1) + unit;
                            }
                        }
                    }
                }
            });
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

        const initCharts = () => {
            if (userData && Object.keys(userData).length > 2) {
                const nodeColorMap = generateNodeColorMap(userData.nodes);
                createBarChart("hourlyBar", userData.hours.map(String), generateDatasets(userData.hourlyFlows, nodeColorMap), @json(trans_choice('common.hour', 2)));
                createBarChart("dailyBar", userData.days, generateDatasets(userData.dailyFlows, nodeColorMap), @json(trans_choice('common.days.attribute', 2)));
            }
        };

        document.addEventListener("DOMContentLoaded", () => {
            const hourDateSelect = document.getElementById("hour_date");
            if (hourDateSelect) {
                hourDateSelect.addEventListener("change", (event) => handleFormSubmit(event, event.target.form));
                $(hourDateSelect).selectpicker("val", new URLSearchParams(window.location.search).get("hour_date") || @json(now()->toDateString()));
            }

            $(".input-daterange").datepicker({
                startDate: userData.start_date,
                endDate: new Date(),
                language: document.documentElement.lang || 'en',
                autoclose: true,
                todayHighlight: true
            });

            populateFormFromQueryParams();
            initCharts();
        });
    </script>
@endsection
