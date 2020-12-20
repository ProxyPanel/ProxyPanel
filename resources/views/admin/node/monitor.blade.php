@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">节点流量</h2>
            </div>
            <div class="alert alert-info alert-dismissible">
                <button class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span><span class="sr-only">{{trans('home.close')}}</span>
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
    <script src="/assets/global/vendor/chart-js/Chart.min.js"></script>
    <script>
      const dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
        type: 'line',
        data: {
          labels: {{$dayHours}},
          datasets: [
            {
              fill: true,
              backgroundColor: 'rgba(98, 168, 234, .1)',
              borderColor: Config.colors('primary', 600),
              pointRadius: 4,
              borderDashOffset: 2,
              pointBorderColor: '#fff',
              pointBackgroundColor: Config.colors('primary', 600),
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: Config.colors('primary', 600),
              data: {{$trafficHourly}},
            }],
        },
        options: {
          legend: {
            display: false,
          },
          responsive: true,
          scales: {
            xAxes: [
              {
                display: true,
                scaleLabel: {
                  display: true,
                  labelString: '小时',
                },
              }],
            yAxes: [
              {
                display: true,
                ticks: {
                  beginAtZero: true,
                },
                scaleLabel: {
                  display: true,
                  labelString: '{{trans('home.traffic_log_24hours')}}',
                },
              }],
          },
        },
      });

      const monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
          labels: {{$monthDays}},
          datasets: [
            {
              fill: true,
              backgroundColor: 'rgba(98, 168, 234, .1)',
              borderColor: Config.colors('primary', 600),
              pointRadius: 4,
              borderDashOffset: 2,
              pointBorderColor: '#fff',
              pointBackgroundColor: Config.colors('primary', 600),
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: Config.colors('primary', 600),
              data: {{$trafficDaily}},
            }],
        },
        options: {
          legend: {
            display: false,
          },
          responsive: true,
          scales: {
            xAxes: [
              {
                display: true,
                scaleLabel: {
                  display: true,
                  labelString: '天',
                },
              }],
            yAxes: [
              {
                display: true,
                ticks: {
                  beginAtZero: true,
                },
                scaleLabel: {
                  display: true,
                  labelString: '{{trans('home.traffic_log_30days')}}',
                },
              }],
          },
        },
      });
    </script>
@endsection
