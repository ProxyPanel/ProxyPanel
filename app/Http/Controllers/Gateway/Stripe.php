<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use App\Models\Order;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class Stripe extends AbstractPayment
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function purchase($request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));
        
        $pay_mode= $request->input('mode');
        
     if ($pay_mode == 'session'){
     
        $data = $this->getCheckoutSessionData( $request->type, $payment->trade_no, $payment->amount);

        try {
            $session = Session::create($data);
            //\Log::debug($session);
            $session_id =  $session->id;
           // $payment->update(['url' => $url]);
           
            Payment::query()->where('trade_no', $payment->trade_no)->update(['pay_secret' => $session->payment_intent]);
            
            return Response::json(['status' => 'success', 'id' => $session_id, 'message' => '创建订单成功!']);
            
        } catch (Exception $e) {
            Log::error('【Stripe】错误: '.$e->getMessage());
            exit;
        }
     }else{
         
         $data = $this->getCheckoutIntentData( $request->type, $payment->trade_no, $payment->amount);

        try {
            $payment_intent = \Stripe\PaymentIntent::create($data);
            
          //  \Log::debug($payment_intent->id);
            
            $client_secret = $payment_intent->client_secret;
       
            $intent_id = $payment_intent->id;
            
            Payment::query()->where('trade_no', $payment->trade_no)->update(['pay_secret' => $intent_id]);
            
            //$payment->update(['pay_secret' => $intent_id]);
          
            return Response::json(['status' => 'success', 'client_secret' => $client_secret , 'message' => '创建订单成功!']);
             } catch (Exception $e) {
                Log::error('【Stripe】错误: '.$e->getMessage());
                exit;
               }
        }
      
      
     
    }

    protected function getCheckoutSessionData(string $pay_type,string $tradeNo, int $amount): array
    {
        $unitAmount = $amount * 100;
        $successURL = route('payment-success');
        $cancelURL = route('payment-failed');

        return [
            'payment_method_types' =>[$pay_type],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => '77vpn',
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url'          => $successURL,
            'cancel_url'          => $cancelURL,
            'client_reference_id' => $tradeNo,
            'customer_email' => Auth::getUser()->email,
        ];
    }
    
     protected function getCheckoutIntentData(string $pay_type, string $tradeNo, int $amount): array
    {
        $unitAmount = $amount * 100;
       
        
        //  \Log::debug($pay_type);
        return [
            
            'payment_method_types' => [$pay_type],
            'amount' => $unitAmount,
            'currency' => 'gbp',
        
        ];
    }
    

    // redirect to Stripe Payment url
    public function redirectPage($session_id, request $request)
    {
        return view('user.stripe-checkout', ['session_id' => $session_id]);
    }

    // url = '/callback/notify?method=stripe'
    public function notify($request): void
    {
       
        // \Log::debug($request);
       /*
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $endpointSecret = sysConfig('stripe_signing_secret');
        $event = null;
        $payload = @file_get_contents('php://input');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        Log::info('Passed signature verification!');
        
        */
        switch ($request->type) {
            case 'payment_intent.succeeded':
              
              
                // \Log::debug($request);

                // \Log::debug(3333);
                
                //$intent=[];
                 
                $intent = $request['data']['object'];
                
                
                
               //\Log::debug($intent);
                
              //  \Log::debug( $intent);

                // Check if the order is paid (e.g., from a card payment)
                //
                // A delayed notification payment will have an `unpaid` status, as
                // you're still waiting for funds to be transferred from the customer's
                // account.
                
              //  \Log::debug($intent);
               
               // if ($intent->payment_status == 'paid') {
                    // Fulfill the purchase
               $this->fulfillOrder($intent['id']);
               // }
                break;
                
          //  case 'checkout.session.async_payment_succeeded':
         //       $session = $event->data->object;
                // Fulfill the purchase
         //       $this->fulfillOrder($session);
             
            case 'payment_intent.payment_failed':
                $intent = $request['data']['object'];
                // Send an email to the customer asking them to retry their order
                $this->failedPayment($intent['id']);
                break;
        }

    
        http_response_code(200);
        exit();
    }

    public function fulfillOrder( $intentId)
    {
            
         $payment = Payment::where('status', 0)->where('pay_secret', $intentId)->first();
        if ($payment) {
            $payment->order->update(['status' => 2]);
        }
    }

    // 未支付成功则关闭订单
    public function failedPayment($intentId)
    {
       $payment =  Payment::where('status', 0)->where('pay_secret', $intentId)->first();
        if ($payment) {
            $payment->order->update(['status' => -1]);
        }
        
        
    }
}