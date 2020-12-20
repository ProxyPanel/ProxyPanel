@extends('_layout')
@section('title', sysConfig('website_name').' - '.trans('error.title'))
@section('layout_css')
    <link href="/assets/css/errors.min.css" rel="stylesheet">
    @endsection
@section('body_class', 'page-error page-error-400 layout-full')
@section('layout_content')
    <!--[if lt IE 8]>
    <p class="browserupgrade">您正在使用 <strong>过时/老旧</strong> 的浏览器。 为了您的使用体验，请
        <a href="http://browsehappy.com/" target="_blank">升级您的浏览器</a> <br/>You are using an <strong>outdated</strong>
        browser. Please
        <a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <header>
                <h3 class="animation-slide-top">(。・＿・。)ﾉI’m sorry~</h3>
                <p>{{trans('error.title')}}</p>
            </header>
            <p class="error-advise">{!! $message !!}</p>
        </div>
    </div>
@endsection