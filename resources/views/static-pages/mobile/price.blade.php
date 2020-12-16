@extends('layout.static-mobile-master')

@section('header-script')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
<link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
<style>
    .loading {
        position: fixed;
        z-index: 99998;
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
        align-items: flex-start;
        padding: 30px;
    }
    .loading p {
        text-align: center;
        font-weight: 700;
        color: #0040ff;
        font-size: 7vw;
    }
</style>
@endsection

@section('content')
<main>
    <div class="page-content page-content--subpage page-content--price">

        <div class="subpage-content">
            <div class="container">
                <div class="top-content top-content--price">
                    <h2>{{ __('static.mbl_price_top_title') }}</h2>
                    <p>{{ __('static.mbl_price_top_text') }}</p>
                </div>
            </div>

            @if (!Auth::check())
            <div class="status-message" style="margin-bottom: 10px; text-align:center; color: red;">Need to login to purchase.</div>
            @endif

            <form class="form--mobilePayment" action="your url here to process mobile payment" method="POST">
                @csrf
                <div class="prices-items">
                  @foreach($packageList as $package)
                    @if (Auth::check())
                    <div class="prices-items__bar js-checkout" data-plan-id="1">
                        <div class="bar-top"><span>{{$package->name}}</span> <span>{{$package->days}} Days</span> <span>Price $ {{$package->price}}</span></div>
                        <div class="bar-bottom"><span>Device num:{{$package->usage}} and unlimited traffic</span></div>
                    </div>
                    @else
                    <a href="{{ url('/account-n') }}" class="prices-items__bar" data-plan-id="1">
                      
                       <div class="bar-top"><span>1 Day</span> <span>$0.99/Day</span></div>
                        <div class="bar-bottom">$0.99 Total</div>
                    
                    </a>
                    @endif
                 @endforeach
                  
                    <div class="prices-items__type">
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="alipayCheck" name="paymentType" checked value="alipay">
                            <label class="custom-control-label" for="alipayCheck"><img src="{{ asset('assets/static/mobile/images/prices/alipay-logo.png') }}" alt="alipay"></label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="creditCardCheck" name="paymentType" value="card">
                            <label class="custom-control-label" for="creditCardCheck"><img src="{{ asset('assets/static/mobile/images/prices/credit-card.png') }}" alt="creadit card"></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="benefits">
            <h2>Enjoy exclusive benefits of premium</h2>
            <ul>
                <li>No ADs</li>
                <li>6x faster</li>
                <li>Unlimited usage</li>
                <li>Support 4 devices</li>
                <li>24/7 tech support</li>
            </ul>
        </div>

    </div>
</main>

<div class="loading">
    <div class="loading__content">
        <p>Redirecting...</p>
    </div> 
</div>
@endsection

@section('footer-script')
<!-- 3rd party JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

<script src="https://js.stripe.com/v3/"></script>

<script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

<script>
    $(function() {

        var stripe = Stripe('pk_test_JZOOnMThjoHKJdVDc8MwKAH500b2mHbiWF'); // used your stripe key

        $(document).on('click', ".js-checkout", function(e) {
            e.preventDefault();
            // created hidden input for store the choosen value
            let planID = $(this).data('plan-id');
            let payMethod = 'stripe';
            let payMode = 'session';
            let amount = 10 ;
            let input = $('<input />', {
                type: 'hidden',
                name: 'planID',
               // method:'alipay',
                value: planID
            });
            
         

            const formData = new FormData();
            const paymentType = $('input[name="paymentType"]:checked').val();
            const token = $('input[name="_token"]').val();

            formData.append('pay_type', paymentType);
             formData.append('method', payMethod);
            formData.append('pay_mode', payMode);
            formData.append('amount', amount);
            formData.append('goods_id', planID);
            formData.append('_token', token);

            $(".loading").show();

            fetch('payment/purchase', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(session) {
                    // redirecting to session page
                    return stripe.redirectToCheckout({
                        sessionId: session.id
                    });
                })
                .then(function(result) {
                    if (result.error) {
                        alert(result.error.message);
                        $(".loading").hide();
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    $(".loading").hide();
                });

            //$('.form--mobilePayment').append(input);
            //$('.form--mobilePayment')[0].submit();
        });
    });
</script>
@endsection
