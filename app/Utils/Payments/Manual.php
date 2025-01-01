<?php

namespace App\Utils\Payments;

use App\Models\Payment;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Hashids\Hashids;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Manual implements Gateway
{
    public static array $methodDetails = [
        'key' => 'manual',
    ];

    public function purchase(Request $request): JsonResponse
    {
        $payment = PaymentHelper::createPayment(auth()->id(), $request->input('id'), $request->input('amount'));

        $url = route('manual.checkout', ['payment' => $payment->trade_no]);
        $payment->update(['url' => $url]);

        return response()->json(['status' => 'success', 'url' => $url, 'message' => trans('user.payment.order_creation.success')]);
    }

    public function redirectPage(string $trade_no): View
    {
        $payment = Payment::uid()->with(['order', 'order.goods'])->whereTradeNo($trade_no)->firstOrFail();
        $goods = $payment->order->goods;

        return view('user.components.payment.manual', [
            'payment' => $payment,
            'name' => $goods->name ?? trans('user.recharge_credit'),
            'days' => $goods->days ?? 0,
            'pay_type' => $payment->order->pay_type_label ?: 0,
            'pay_type_icon' => $payment->order->pay_type_icon,
        ]);
    }

    public function inform(string $trade_no): JsonResponse
    {
        $payment = Payment::uid()->with(['order'])->whereTradeNo($trade_no)->firstOrFail();
        $payment->order->update(['status' => 1]);

        return response()->json(['status' => 'success', 'message' => trans('user.payment.order_creation.info')]);
    }

    public function notify(Request $request): View
    {
        $code = $request->input('sign');
        $status = $request->input('status');
        if (isset($status, $code)) {
            $payment_info = (new Hashids(config('app.key'), 8))->decode($code);
            if ($payment_info) {
                $payment = Payment::findOrFail($payment_info[0]);
                if ($payment && $payment->order && $payment->order->status === 1) {
                    if ($status) {
                        PaymentHelper::paymentReceived($payment->trade_no);
                    } else {
                        $payment->order->close();
                    }
                }

                return view('components.payment.detail', ['order' => $payment->order->refresh(), 'user' => $payment->user->refresh()]);
            }
        }

        return view('auth.error', ['message' => 'No enough information']);
    }
}
