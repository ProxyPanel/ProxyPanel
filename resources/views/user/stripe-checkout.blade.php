@extends('user.layouts')

@section('css')
@endsection

@section('content')
    <div>
        <p style="text-align: center">Redirecting to Stripe Checkout ...</p>
    </div>
@endsection

@section('script')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe = Stripe('{{ sysConfig('stripe_public_key') }}');
        let redirectData = stripe.redirectToCheckout({ sessionId: '{{ $session_id  }}' });
        console.log(redirectData);
    </script>
@endsection
