@extends('layout.static-mobile-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')
        <main>
            <div class="page-content page-content--subpage page-content--price">

                <div class="subpage-content">
                    <div class="container">
                        <div class="top-content top-content--price">
                            <h1>Payment was <strpng>successful!</strong></h1>
                            <p>Thank you for your payment!</p>
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

    <script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

@endsection
