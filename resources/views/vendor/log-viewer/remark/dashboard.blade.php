@extends('vendor.log-viewer.remark.layouts')

@section('content')
    <div class="page-header">
        <h1>@lang('Dashboard')</h1>
    </div>

    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 col-xl-2">
            <div style="max-height:25vh">
                <canvas id="stats-doughnut-chart"></canvas>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-9 col-xl-10">
            <div class="row">
                @foreach($percents as $level => $item)
                    <div class="col-sm-6 col-md-12 col-lg-4 mb-10">
                        <div class="box level-{{ $level }} {{ $item['count'] === 0 ? 'empty' : '' }}">
                            <div class="box-icon">
                                {!! log_styler()->icon($level) !!}
                            </div>

                            <div class="box-content">
                                <span class="box-text">{{ $item['name'] }}</span>
                                <span class="box-number">
                                    {{ $item['count'] }} @lang('entries') - {!! $item['percent'] !!} %
                                </span>
                                <div class="progress" style="height: 3px;">
                                    <div class="progress-bar" style="width: {{ $item['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
      new Chart(document.getElementById('stats-doughnut-chart'),
          {
            type: 'doughnut',
            data: {!! $chartData !!},
            options: {
              plugins: {
                legend: {
                  position: 'bottom',
                },
              },
            },
          },
      );
    </script>
@endsection
