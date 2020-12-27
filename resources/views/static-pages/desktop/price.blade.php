@extends('layout.static-desktop-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link href="{{ asset('assets/static/desktop/css/style.css') }}" rel="stylesheet">
     <style>
    .loading {
        position: fixed;
        z-index: 998;
        background: rgba(255, 255, 255, 0.8);
        left: 0;
        top: 0;
        display: none;
        width: 100%;
        height: 100%;
    }
    .loading__content {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px;
    }
    .loading p {
        text-align: center;
        font-weight: 700;
        color: #0040ff;
        font-size: 32px;
    }
   </style>
@endsection

@section('content')
        <main>
            <div class="page-content page-content--subpage">

                <div class="page-content__inner">

                    <div class="page-content__head">
                        <h1>{{ __('static.dsktp_prices_hero_title') }}</h1>
                        <p>{{ __('static.dsktp_prices_hero_text') }}</p>
                    </div>

                    <div class="payment-step">
                        <div class="container">
                            <div class="payment-step__head">
                                <span class="badge">{{ __('static.dsktp_prices_first_step_title') }}</span>
                                <h2>{{ __('static.dsktp_prices_first_step_text') }}</h2>
                            </div>
                        </div>

                        <section class="section--prices">
                            <div class="container">
                                <div class="prices-items">
                                     @foreach($packageList as $package)

                                      @if($package->is_hot)
                                        <a href="#" class="price-item price-item--hot selected">
                                            <div class="fire-ball">Hot</div>
                                            <h2 class="price-item__time" data-package-time= {{$package->days}} >{{$package->name}}</h2>
                                        <p class="price-item__value" data-package-price= {{$package->price}}><span>$</span>{{$package->price}}</p>
                                        <p class="price-item__id" data-package-sku= {{$package->id}}   style="display: none" ><span>$</span>{{$package->id}}</p>
                                        <div class="price-item__duration">{{$package->name}}</div>
                                        <div class="price-item__subtext">$ 0.99 billed every 1 Day</div>
                                      </a>
                                      @else
                                        <a href="#" class="price-item">
                                        <h2 class="price-item__time" data-package-time={{$package->name}} >{{$package->name}}</h2>
                                        <p class="price-item__value" data-package-price= {{$package->price}} >${{$package->price}}</p>
                                        <p class="price-item__id" data-package-id= {{$package->days}}   >{{$package->days}}Days</p>
                                        <div class="price-item__duration">{{$package->name}}</div>
                                        <div class="price-item__subtext">每30天重置流量</div>
                                      </a>
                                      @endif


                                    @endforeach
                                </div>
                                <p class="helper-text">{{ __('static.dsktp_prices_hero_helper_text') }}</p>
                            </div>
                        </section>
                    </div>

                    <div class="payment-step">

                        <div class="container">
                            <div class="payment-step__head">
                                <span class="badge">{{ __('static.dsktp_prices_second_step_title') }}</span>
                                <h2>{{ __('static.dsktp_prices_second_step_text') }}</h2>
                            </div>
                        </div>

                        <section class="section--payment">
                            <div class="container">
                                <div class="payment-items">
                                    <div class="payment-item">


                                        <button class="payment-item__btn" type="button" data-toggle="collapse" data-target="#paypalCollapse" aria-expanded="false" aria-controls="paypalCollapse">
                                            <i class="fas fa-chevron-right"></i>
                                            <span>{{__('static.dsktp_prices_alipay_title')}}</span>
                                            <img src="{{ asset('assets/static/desktop/images/logos/alipay-logo.png') }}" alt="Alipay">
                                        </button>

                                        <div class="collapse payment-item__content" id="paypalCollapse">
                                            <div class="card card-body">

                                            <div class="amounts">
                                                    <div class="amount-row value">
                                                        <span></span>
                                                        <span></span>
                                                    </div>
                                                    <div class="amount-row discount">
                                                        <span></span>
                                                        <span></span>
                                                    </div>
                                                    <div class="amount-row amounts-total">
                                                        <span>Total</span>
                                                        <span class="amount-total-value"></span>
                                                    </div>
                                            </div>

                                                <div>
                                                    <p id="alipay-error" style="color: red;"></p>
                                                     @if(Auth::check())
                                                    <button data-token={{ csrf_token() }} class="cs-btn cs-btn--primary js-checkout-alipay" aria-label="Checkout with alipay">Continue <img src="{{ asset('assets/static/desktop/images/logos/alipay-logo.png') }}" alt="Alipay"></button>
                                                    @else
                                                    <button  class="cs-btn cs-btn--primary js-checkout-alipay" data-toggle="modal" data-target="#signinModal" aria-label="Checkout with alipay">Continue <img src="{{ asset('assets/static/desktop/images/logos/alipay-logo.png') }}" alt="Alipay"></button>
                                                    @endif
                                                </div>

                                                <div class="content-foot">
                                                    <p><i class="icon-reset"></i> {{ __('static.dsktp_prices_alipay_title_1') }} </p>
                                                    <p><i class="icon-lock"></i>  {{ __('static.dsktp_prices_alipay_text_1') }} </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-item">
                                        <button class="payment-item__btn" type="button" data-toggle="collapse" data-target="#creditCollapse" aria-expanded="false" aria-controls="creditCollapse">
                                            <i class="fas fa-chevron-right"></i>
                                            <span>{{__('static.dsktp_prices_card_title')}}</span>
                                            <img src="{{ asset('assets/static/desktop/images/logos/visa.svg') }}" alt="visa">
                                            <img src="{{ asset('assets/static/desktop/images/logos/mastercard.svg') }}" alt="mastercard">
                                            <img src="{{ asset('assets/static/desktop/images/logos/amex.svg') }}" alt="amex">
                                            <img src="{{ asset('assets/static/desktop/images/logos/discover.svg') }}" alt="discover">
                                        </button>
                                        <div class="collapse payment-item__content" id="creditCollapse">
                                            <div class="card card-body">

                                                <div class="amounts">
                                                    <div class="amount-row value">
                                                        <span></span>
                                                        <span></span>
                                                    </div>
                                                    <div class="amount-row discount">
                                                        <span></span>
                                                        <span></span>
                                                    </div>
                                                    <div class="amount-row amounts-total">
                                                        <span>Total</span>
                                                        <span class="amount-total-value"></span>
                                                    </div>
                                                </div>

                                                <form action="." class="form--payment" id="payment-form" method="POST">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label for="firstName">{{__('static.dsktp_prices_card_text_firstname')}}</label>
                                                        <input type="text" class="form-control" name="first_name" id="firstName" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="lastName">{{__('static.dsktp_prices_card_text_lastname')}}</label>
                                                        <input type="text" class="form-control" name="last_name" id="lastName" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="card">{{__('static.dsktp_prices_card_text_cardnum')}}</label>
                                                        <div class="card-input" id="card-element">
                                                            <!-- <input type="text" class="form-control" name="card" id="card" placeholder="1234 1234 1234 1234" required> -->
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="expired">{{__('static.dsktp_prices_card_text_expired')}}</label>
                                                        <div class="card-input" id="card-expire">

                                                        </div>
                                                        <!-- <input type="text" class="form-control" id="expired" placeholder="HH/ÉÉ" required > -->
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <label for="cvc">{{__('static.dsktp_prices_card_text_cvv')}}</label>
                                                            <div class="card-input" id="card-cvc"></div>
                                                            <!-- <input type="text" class="form-control" id="cvc" name="cvc" placeholder="CVC"  required > -->
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label for="postalCode">{{__('static.dsktp_prices_card_text_postcode')}}</label>
                                                            <input type="text" class="form-control" name="postal_code" id="postalCode" required>
                                                        </div>
                                                    </div>

                                                    <div id="card-errors" role="alert"></div>

                                                    <div class="form-group form-group--last">
                                                        @if(Auth::check())
                                                        <button class="cs-btn cs-btn--primary" type="submit" name="card-submit">Pay</button>
                                                        @else
                                                        <button class="cs-btn cs-btn--primary" type="submit" name="card-submit" data-toggle="modal" data-target="#signinModal">Pay</button>
                                                        @endif
                                                    </div>
                                                </form>

                                                <div class="content-foot">
                                                    <p><i class="icon-reset"></i> {{ __('static.dsktp_prices_card_title_1') }} </p>
                                                    <p><i class="icon-lock"></i> {{ __('static.dsktp_prices_card_text_1') }} </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- <div>
                                    <p>You need to login for the purchase!</p>
                                    <button class="cs-btn cs-btn--primary" data-toggle="modal" data-target="#signinModal">Sign in</button>
                                </div> -->

                                <div class="payment-info">
                                    <h3 class="payment-info__title">Enjoy exclusive benefits of premium:</h3>
                                    <ul>
                                        <li><i class="fas fa-check"></i> No ADs</li>
                                        <li><i class="fas fa-check"></i> 6x faster</li>
                                        <li><i class="fas fa-check"></i >Unlimited usage</li>
                                        <li><i class="fas fa-check"></i> Support 4 devices</li>
                                        <li><i class="fas fa-check"></i> 24/7 tech support</li>
                                        <li><i class="fas fa-check"></i> Zero Traffic Logs</li>
                                    </ul>
                                </div>

                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
        
   @if (Auth::check())
    <div class="loading">
    <div class="loading__content">
        <p>Redirecting...</p>
    </div>
    </div>
    @endif

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

    <script src="https://js.stripe.com/v3/"></script>

    <script src="{{ asset('assets/static/desktop/js/app.js') }}"></script>

    <script src="{{ asset('assets/static/desktop/js/stripe.js') }}"></script>


@endsection
