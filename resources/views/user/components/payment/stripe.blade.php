@extends('_layout')
@section('title', sysConfig('website_name'))
@section('body_class','page-login-v3 layout-full')
@section('layout_content')
    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <h2>{{trans('user.payment.redirect_stripe')}}</h2>
            <i class="mt-30 loader loader-ellipsis" aria-hidden="true"></i>
        </div>
    </div>
@endsection

@section('layout_javascript')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
      let stripe = Stripe('{{ sysConfig('stripe_public_key') }}');
      let redirectData = stripe.redirectToCheckout({sessionId: '{{ $session_id  }}'});
    </script>
@endsection
