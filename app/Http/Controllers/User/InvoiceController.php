<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class InvoiceController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Response
    { // 订单列表
        return Response::view('user.invoices', [
            'orderList' => auth()->user()->orders()->with(['goods', 'payment'])->orderByDesc('id')->paginate(10)->appends($request->except('page')),
            'prepaidPlan' => Order::userPrepay()->exists(),
        ]);
    }

    public function show(string $sn): \Illuminate\Http\Response
    { // 订单明细
        return Response::view('user.invoiceDetail', ['order' => Order::uid()->whereSn($sn)->with(['goods', 'coupon'])->firstOrFail()]);
    }

    public function activate(): JsonResponse
    { // 激活套餐
        $activePlan = Order::userActivePlan()->first();
        if ($activePlan) {
            if ($activePlan->expired()) { // 关闭先前套餐后，新套餐自动运行
                if (Order::userActivePlan()->exists()) {
                    return Response::json(['status' => 'success', 'message' => trans('common.active_item', ['attribute' => trans('common.success')])]);
                }

                return Response::json(['status' => 'success', 'message' => trans('common.close')]);
            }
        } else {
            $prepaidPlan = Order::userPrepay()->first();
            if ($prepaidPlan) { // 关闭先前套餐后，新套餐自动运行
                if ($prepaidPlan->complete()) {
                    return Response::json(['status' => 'success', 'message' => trans('common.active_item', ['attribute' => trans('common.success')])]);
                }

                return Response::json(['status' => 'success', 'message' => trans('common.close')]);
            }
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.close')])]);
    }
}
