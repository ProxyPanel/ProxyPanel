@extends('admin.layouts')
@section('content')
    <div class="page-content container-fluid">
        <div class="card card-shadow">
            <div class="card-block p-30">
                <form class="form-row">
                    <div class="form-group col-xxl-1 col-lg-1 col-md-1 col-sm-4">
                        <input type="number" class="form-control" name="uid" value="{{Request::query('uid')}}" placeholder="{{ trans('model.user.id') }}"/>
                    </div>
                    <div class="form-group col-xxl-2 col-lg-3 col-md-3 col-sm-4">
                        <input type="text" class="form-control" name="username" value="{{Request::query('username')}}" placeholder="{{ trans('model.user.username') }}"/>
                    </div>
                    <div class="form-group col-xxl-1 col-lg-3 col-md-3 col-4 btn-group">
                        <button type="submit" class="btn btn-primary">{{ trans('common.search') }}</button>
                        <a href="{{route('admin.report.userAnalysis')}}" class="btn btn-danger">{{ trans('common.reset') }}</a>
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
                    label += context.parsed.y + ' GB';
                  }
                  return label;
                },
              },
            };
          }

          const common_options = {
            stack: 'node_id',
            parsing: {
              xAxisKey: 'date',
              yAxisKey: 'total',
            },
            responsive: true,
            plugins: {
              legend: {
                labels: {
                  padding: 20,
                  usePointStyle: true,
                  pointStyle: 'circle',
                  font: {size: 14},
                },
              },
              tooltip: label_callbacks(' {{ trans_choice('common.days.attribute', 1) }}'),
            },
          };

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

          new Chart(document.getElementById('hourlyBar'), {
            type: 'bar',
            data: {
              labels: @json($data['hours']),
              datasets: [area_a('{{ trans('admin.report.today') }}',@json($data['hourlyFlow']))],
            },
            options: common_options,
          });

          new Chart(document.getElementById('dailyBar'), {
            type: 'bar',
            data: {
              labels: @json($data['days']),
              datasets: [area_a('{{ trans('admin.report.current_month') }}',@json($data['dailyFlow']))],
            },
            options: common_options,
          });
        </script>
    @endisset
@endsection
