@extends('layout.static-desktop-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/desktop/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')
        <div class="hero hero--help">
            <div class="container">
                <h1 class="hero__title">{!! __('static.dsktp_tutorial_hero_title') !!}</h1>
                <p class="hero__text">{{ __('static.dsktp_tutorial_hero_text') }}</p>
            </div>
        </div>

        <main>
            <div class="page-content">

                <div class="questions">
                    <div class="container questions__inner">
                        <h2 class="questions__title">{{ __('static.dsktp_tutorial_list_maintitle') }}</h2>
                        

                        <div class="questions__foot">
                            <p>{{ __('static.dsktp_help_list_contact_1') }} <a href="#">{{ __('static.dsktp_help_list_contact_2') }}</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

@endsection

@section('footer-script')
    <!-- 3rd party JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>

    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>


    <script src="{{ asset('assets/static/desktop/js/app.js') }}"></script>
@endsection
