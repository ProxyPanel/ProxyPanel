@extends('_layout')
@section('title', trans('auth.maintenance'))
@section('body_class','page-login-v3 layout-full')
@section('layout_content')
    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">>
        <div class="page-content vertical-align-middle">
            <i class="icon wb-settings icon-spin font-size-70" aria-hidden="true"></i>
            <h2>{{trans('auth.maintenance_tip')}}</h2>
            {!! $message !!}
            <footer class="page-copyright">
                <p id="countdown"></p>
            </footer>
        </div>
    </div>
@endsection
@section('layout_javascript')
    <script>
        // 每秒更新计时器
        const countDownDate = new Date("{{$time}}").getTime();
        const x = setInterval(function() {
            const distance = countDownDate - new Date().getTime();
            const days = Math.floor(distance / 86400000);
            const hours = Math.floor(distance % 86400000 / 3600000);
            const minutes = Math.floor(distance % 3600000 / 60000);
            const seconds = Math.floor(distance % 60000 / 1000);
            document.getElementById('countdown').innerHTML = '<h2>' + days + ' <span> {{trans('validation.attributes.day')}} </span>: ' +
                hours + ' <span>{{trans('validation.attributes.hour')}}</span>: ' + minutes + ' <span>{{trans('validation.attributes.minute')}} </span>: ' +
                seconds + '<span> {{trans('validation.attributes.second')}}</span> </h2>';
            if (distance <= 0) {
                clearInterval(x);
                document.getElementById('countdown').remove();
            }
        }, 1000);
    </script>
@endsection