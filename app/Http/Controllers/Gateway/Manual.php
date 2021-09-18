<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class Manual extends AbstractPayment
{
    public function purchase(Request $request): JsonResponse
    {
        $payment = $this->creatNewPayment(Auth::id(), $request->input('id'), $request->input('amount'));

        if ($payment) {
            $url = route('manual.checkout', ['payment' => $payment->trade_no]);
            $payment->update(['url' => $url]);

            return Response::json(['status' => 'success', 'url' => $url, 'message' => '创建订单成功!']);
        }

        return Response::json(['status' => 'fail', 'message' => '购买失败，请尝试其他方式']);
    }

    public function redirectPage($trade_no)
    {
        $payment = Payment::uid()->with(['order', 'order.goods'])->whereTradeNo($trade_no)->firstOrFail();
        $goods = $payment->order->goods;

        return view('user.components.payment.manual', [
            'payment'       => $payment,
            'name'          => $goods->name ?? trans('user.recharge_credit'),
            'days'          => $goods->days ?? 0,
            'pay_type'      => $payment->order->pay_type_label ?: 0,
            'pay_type_icon' => $payment->order->pay_type_icon,
        ]);
    }

    public function inform($trade_no)
    {
        $payment = Payment::uid()->with(['order'])->whereTradeNo($trade_no)->firstOrFail();
        $payment->order->update(['status' => 1]);

        return Response::json(['status' => 'success', 'message' => '我们将在【24小时】内对购买/充值的款项进行开通！请耐心等待']);
    }

    public function notify(Request $request): void
    {
        $code = $request->input('sign');
        $status = $request->input('status');
        if (isset($status, $code)) {
            $payment = Payment::findOrFail((int) string_decrypt($code));
            if ($payment && $payment->order && $payment->order->status === 1) {
                if ($status) {
                    $payment->order->complete();
                } else {
                    $payment->order->close();
                }

                exit('success');
            }
            exit('fail');
        }
        exit('No enough information');
    }
}
