@extends('_layout')
@section('title', '维护 | Maintenance')
@section('layout_content')
   <!--[if lt IE 8]> <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
    <a href="http://browsehappy.com/" target="_blank">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong>
    browser. Please
    <a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">>
    <div class="page-content vertical-align-middle">
        <i class="icon wb-settings icon-spin font-size-70" aria-hidden="true"></i>
        <h2>维护建设中</h2>
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
        document.getElementById('countdown').innerHTML = '<h2>' + days + ' <span> 天 </span>: ' + hours +
            ' <span>时</span>: ' + minutes + ' <span>分 </span>: ' + seconds + '<span> 秒</span> </h2>';
        if (distance <= 0) {
          clearInterval(x);
          document.getElementById('countdown').remove();
        }
      }, 1000);
    </script>
@endsection