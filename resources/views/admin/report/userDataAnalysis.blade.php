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
                        <a class="btn btn-danger" href="{{ route('admin.report.userAnalysis') }}">{{ trans('common.reset') }}</a>
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
                    <canvas id="hourlyDoughnut"></canvas>
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
                    <canvas id="dailyDoughnut"></canvas>
                </div>
            </div>
        @endisset
    </div>
@endsection
@section('javascript')
    @isset($data)
        <script src="/assets/global/vendor/chart-js/chart.min.js"></script>
        <script type="text/javascript">
            function createBarChart(elementId, labels, datasets, labelTail) {
                new Chart(document.getElementById(elementId), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets,
                    },
                    options: {
                        parsing: {
                            xAxisKey: 'time',
                            yAxisKey: 'total',
                        },
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                            },
                        },
                        responsive: true,
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
                            tooltip: label_callbacks(labelTail),
                        },
                    },
                });
            }

            function label_callbacks(tail) {
                return {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: function(context) {
                            return context[0].label + ' ' + tail;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' MiB';
                            }
                            return label;
                        },
                    },
                };
            }

            const userData = @json($data);
            const nodeColorMap = generateNodeColorMap(userData.nodes); // 获取所有节点名称并生成颜色映射

            function generateNodeColorMap(nodeNames) {
                const colorMap = {};
                Object.entries(nodeNames).forEach(([id, name]) => {
                    colorMap[id] = getRandomColor(name);
                });
                return colorMap;
            }

            // 生成随机颜色
            function getRandomColor(name) {
                // 将字符串转换为哈希值
                let hash = 0;
                for (let i = 0; i < name.length; i++) {
                    hash = name.charCodeAt(i) + ((hash << 5) - hash);
                }

                // 定义不同色调的范围
                const hueOffset = hash % 360;
                const hueRange = 20; // 色调范围

                // 计算最终色调
                const hue = (hueOffset + Math.random() * hueRange) % 360; // 确保 hue 在 0-359 之间

                // 保持饱和度和亮度固定
                const saturation = 70; // 保持饱和度较高
                const lightness = 50; // 保持亮度适中

                // 添加透明度
                const alpha = 0.55; // 50% 透明度

                return `hsla(${hue}, ${saturation}%, ${lightness}%, ${alpha})`;
            }

            // 生成数据集
            // 生成数据集
            function generateDatasets(flows) {
                const dataByNode = {};

                // 按节点 ID 分组数据
                flows.forEach(flow => {
                    if (!dataByNode[flow.id]) {
                        dataByNode[flow.id] = [];
                    }
                    dataByNode[flow.id].push({
                        time: flow.time,
                        total: flow.total,
                        name: flow.name,
                    });
                });

                // 创建 datasets 数组
                let datasets = [];
                for (const nodeId in dataByNode) {
                    if (dataByNode.hasOwnProperty(nodeId)) {
                        datasets.push({
                            label: dataByNode[nodeId][0].name, // 使用 name 作为标签
                            backgroundColor: nodeColorMap[nodeId],
                            borderColor: nodeColorMap[nodeId],
                            data: dataByNode[nodeId],
                            fill: true,
                        });
                    }
                }
                return datasets;
            }

            // 创建图表
            createBarChart('hourlyBar', userData.hours, generateDatasets(userData.hourlyFlows), @json(trans_choice('common.hour', 2)));
            createBarChart('dailyBar', userData.days, generateDatasets(userData.dailyFlows), @json(trans_choice('common.days.attribute', 2)));
        </script>
    @endisset
@endsection
