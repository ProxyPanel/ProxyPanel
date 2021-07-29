@extends('admin.layouts')
@section('css')
    <link rel="stylesheet" href="/assets/global/vendor/chartist/chartist.min.css">
    <style>
        #widgetLinearea .ct-label.ct-vertical, #widgetLinearea .ct-label.ct-horizontal {
            font-size: 14px;
        }

        #widgetLinearea .ct-area {
            fill-opacity: 1;
        }

        #widgetLinearea .ct-series.ct-series-a .ct-area {
            fill: #b8d7ff;
        }

        #widgetLinearea .ct-series.ct-series-b .ct-area {
            fill: #92f0e6;
        }

        #widgetLinearea ul .icon {
            vertical-align: text-bottom;
        }

    </style>
@endsection
@section('content')
    <div class="page-content container" id="widgetLinearea">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">月流水账簿</div>
                    </div>
                </div>
                <div class="days mb-30" style="height:270px;"></div>
                <ul class="list-inline text-center mb-0">
                    <li class="list-inline-item">
                        <i class="icon wb-large-point blue-200 mr-10" aria-hidden="true"></i> 本月
                    </li>
                    <li class="list-inline-item ml-35">
                        <i class="icon wb-large-point teal-200 mr-10" aria-hidden="true"></i> 上月
                    </li>
                </ul>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">年流水账簿</div>
                    </div>
                </div>
                <div class="month mb-30" style="height:270px;"></div>
                <ul class="list-inline text-center mb-0">
                    <li class="list-inline-item">
                        <i class="icon wb-large-point blue-200 mr-10" aria-hidden="true"></i> 今年
                    </li>
                    <li class="list-inline-item ml-35">
                        <i class="icon wb-large-point teal-200 mr-10" aria-hidden="true"></i> 去年
                    </li>
                </ul>
            </div>
        </div>
        <div class="card card-shadow">
            <div class="card-block p-30">
                <div class="row pb-20">
                    <div class="col-md-8 col-sm-6">
                        <div class="blue-grey-700 font-size-26 font-weight-500">历史流水账簿</div>
                    </div>
                </div>
                <div class="year mb-30" style="height:270px;"></div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script src="/assets/global/vendor/chartist/chartist.min.js"></script>
    <script type="text/javascript">
        new Chartist.Line('#widgetLinearea .days', {
            labels: @json($data['days']),
            series: [@json($data['currentMonth']), @json($data['lastMonth'])],
        }, {
            low: 0,
            showArea: true,
            showPoint: false,
            showLine: false,
            fullWidth: true,
            chartPadding: {top: 0, right: 10, bottom: 0, left: 20},
            axisX: {
                showGrid: false,
                labelOffset: {x: -14, y: 0},
            },
            axisY: {
                labelOffset: {x: -10, y: 0},
                labelInterpolationFnc: function labelInterpolationFnc(num) {return num % 1 === 0 ? num : false;},
            },
        });

        new Chartist.Line('#widgetLinearea .month', {
            labels: @json($data['years']),
            series: [@json($data['currentYear']), @json($data['lastYear'])],
        }, {
            low: 0,
            showArea: true,
            showPoint: false,
            showLine: false,
            fullWidth: true,
            chartPadding: {top: 0, right: 10, bottom: 0, left: 20},
            axisX: {
                showGrid: false,
                labelOffset: {x: -14, y: 0},
            },
            axisY: {
                labelOffset: {x: -10, y: 0},
                labelInterpolationFnc: function labelInterpolationFnc(num) {return num % 1 === 0 ? num : false;},
            },
        });

        new Chartist.Line('#widgetLinearea .year', {
            labels: @json(array_keys($data['ordersByYear'])),
            series: [@json(array_values($data['ordersByYear']))],
        }, {
            low: 0,
            showArea: true,
            showPoint: false,
            showLine: false,
            fullWidth: true,
            chartPadding: {top: 0, right: 20, bottom: 0, left: 20},
            axisX: {
                showGrid: false,
                labelOffset: {x: -14, y: 0},
            },
            axisY: {
                labelOffset: {x: -10, y: 0},
                labelInterpolationFnc: function labelInterpolationFnc(num) {return num % 1 === 0 ? num : false;},
            },
        });
    </script>
@endsection
