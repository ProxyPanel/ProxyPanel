@extends('user.layouts')

@section('css')
@endsection

@section('content')
    <div>
        <p style="text-align: center">{{trans('user.payment.redirect_stripe')}}</p>
    </div>
@endsection

@section('javascript')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe = Stripe('{{ sysConfig('stripe_public_key') }}');
        let redirectData = stripe.redirectToCheckout({sessionId: '{{ $session_id  }}'});
        console.log(redirectData);
    </script>
@endsection
