@extends('_layout')
@section('title', sysConfig('website_name'))
@section('body_class','page-login-v3 layout-full')
@section('layout_css')
    <style>
        .layout-full {
            margin-right: auto !important;
            margin-left: auto !important;
        }

        table {
            width: 100%
        }

        @media (min-width: 992px) {
            .layout-full {
                max-width: 75vw;
            }
        }

        @media (min-width: 1200px) {
            .layout-full {
                max-width: 50vw;
            }
        }
    </style>

@endsection
@section('layout_content')
    <div class="page vertical-align" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content container vertical-align-middle">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {{ $title }}
                    </h3>
                </div>
                <div class="panel-body">
                    {!! $content !!}
                </div>
            </div>
        </div>
    </div>
@endsection
