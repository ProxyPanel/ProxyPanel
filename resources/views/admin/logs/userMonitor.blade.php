@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="panel">
            <div class="panel-heading">
                <h2 class="panel-title">{{ trans('admin.monitor.user') }}</h2>
            </div>
            <div class="alert alert-info alert-dismissible">
                <button class="close" data-dismiss="alert" aria-label="{{ trans('common.close') }}">
                    <span aria-hidden="true">&times;</span><span class="sr-only">{{trans('common.close')}}</span>
                </button>
                <h4 class="block">{{$username}}</h4>
                {!! trans('admin.monitor.hint') !!}
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="dailyChart" aria-label="{{ trans('admin.monitor.daily_chart') }}" role="img"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="monthlyChart" aria-label="{{ trans('admin.monitor.monthly_chart') }}" role="img"></canvas>
                    </div>
                </div>
            </div>
        </div>
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
          datasets: [
            {
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
