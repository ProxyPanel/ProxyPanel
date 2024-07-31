@extends('_layout')
@section('title', trans('auth.maintenance'))
@section('body_class', 'page-login-v3 layout-full')
@section('layout_content')
    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <i class="icon wb-settings icon-spin font-size-70" aria-hidden="true"></i>
            <h2>{{ trans('auth.maintenance_tip') }}</h2>
            {!! $message !!}
            <footer class="page-copyright">
                <p id="countdown"></p>
                <div class="social">
                    @foreach (config('common.language') as $key => $value)
                        <a class="btn btn-icon btn-pure" href="{{ route('lang', ['locale' => $key]) }}">
                            <i class="font-size-30 icon fi fi-{{ $value[1] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            </footer>
        </div>
    </div>
@endsection
@section('layout_javascript')
    <script>
        // 每秒更新计时器
        const countDownDate = new Date("{{ $time }}").getTime();
        const countdownElement = document.getElementById('countdown');
        const daysLabel = '{{ trans_choice('common.days.attribute', 1) }}';
        const hoursLabel = '{{ trans_choice('common.hour', 1) }}';
        const minutesLabel = '{{ ucfirst(trans('validation.attributes.minute')) }}';
        const secondsLabel = '{{ ucfirst(trans('validation.attributes.second')) }}';

        const updateCountdown = () => {
            const distance = countDownDate - new Date().getTime();
            if (distance <= 0) {
                clearInterval(interval);
                countdownElement.remove();
                return;
            }

            const days = Math.floor(distance / 86400000);
            const hours = Math.floor(distance % 86400000 / 3600000);
            const minutes = Math.floor(distance % 3600000 / 60000);
            const seconds = Math.floor(distance % 60000 / 1000);

            countdownElement.innerHTML =
                `<h3>${days} <span> ${daysLabel} </span>: ${hours} <span>${hoursLabel}</span>: ${minutes} <span>${minutesLabel}</span>: ${seconds}<span> ${secondsLabel}</span></h3>`;
        };

        const interval = setInterval(updateCountdown, 1000);
    </script>
@endsection
