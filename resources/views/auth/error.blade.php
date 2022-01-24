@extends('_layout')
@section('title', sysConfig('website_name').' - '.trans('errors.title'))
@section('layout_css')
    <link href="/assets/css/errors.min.css" rel="stylesheet">
@endsection
@section('body_class', 'page-error layout-full')
@section('layout_content')
    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <header>
                <h1 class="animation-slide-top">{{trans('errors.whoops')}}</h1>
            </header>
            <h3>{{trans('errors.report')}}</h3>
            <code class="error-advise">{!! $message !!}</code>
        </div>
    </div>
@endsection