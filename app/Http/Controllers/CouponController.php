<?php

namespace App\Http\Controllers;

use App\Http\Models\Coupon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use Redirect;
use DB;
use Log;

/**
 * 优惠券控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class CouponController extends Controller
{
    // 优惠券列表
    public function couponList(Request $request)
    {
        $couponList = Coupon::query()->where('is_del', 0)->orderBy('id', 'desc')->paginate(10);
        foreach ($couponList as $coupon) {
            $coupon->amount = $coupon->amount / 100;
        }

        $view['couponList'] = $couponList;

        return Response::view('coupon/couponList', $view);
    }

    // 添加商品
    public function addCoupon(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $type = $request->get('type', 1);
            $usage = $request->get('usage', 1);
            $num = $request->get('num', 1);
            $amount = $request->get('amount');
            $discount = $request->get('discount');
            $available_start = $request->get('available_start');
            $available_end = $request->get('available_end');

            if (empty($num) || (empty($amount) && empty($discount)) || empty($available_start) || empty($available_end)) {
                $request->session()->flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            if (strtotime($available_start) >= strtotime($available_end)) {
                $request->session()->flash('errorMsg', '有效期范围错误');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();
                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/coupon/', $logoName);
                $logo = $move ? '/upload/image/coupon/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                for ($i = 0; $i < $num; $i++) {
                    $obj = new Coupon();
                    $obj->name = $name;
                    $obj->sn = strtoupper(makeRandStr(7));
                    $obj->logo = $logo;
                    $obj->type = $type;
                    $obj->usage = $usage;
                    $obj->amount = empty($amount) ? 0 : $amount * 100;
                    $obj->discount = empty($discount) ? 0 : $discount / 10;
                    $obj->available_start = strtotime(date('Y-m-d 0:0:0', strtotime($available_start)));
                    $obj->available_end = strtotime(date('Y-m-d 23:59:59', strtotime($available_end)));
                    $obj->status = 0;
                    $obj->save();
                }

                DB::commit();

                $request->session()->flash('successMsg', '生成成功');
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('生成优惠券失败：' . $e->getMessage());

                $request->session()->flash('errorMsg', '生成失败：' . $e->getMessage());
            }

            return Redirect::to('coupon/addCoupon');
        } else {
            return Response::view('coupon/addCoupon');
        }
    }

    // 删除优惠券
    public function delCoupon(Request $request)
    {
        $id = $request->get('id');

        Coupon::query()->where('id', $id)->update(['is_del' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }

    // 导出优惠券
    public function exportCoupon(Request $request)
    {
        $cashCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 1)->get();
        $discountCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 2)->get();
        $chargeCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 3)->get();

        $filename = '卡券' . date('Ymd');
        Excel::create($filename, function($excel) use($cashCouponList, $discountCouponList, $chargeCouponList) {
            $excel->sheet('抵用券', function($sheet) use($cashCouponList) {
                $sheet->row(1, array(
                    '名称', '类型', '有效期', '券码', '面额'
                ));

                if (!$cashCouponList->isEmpty()) {
                    foreach ($cashCouponList as $k => $vo) {
                        $sheet->row($k + 2, array(
                            $vo->name, $vo->type == 1 ? '一次性' : '可重复', date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end), $vo->sn, $vo->amount / 100
                        ));
                    }
                }
            });

            $excel->sheet('折扣券', function($sheet) use($discountCouponList) {
                $sheet->row(1, array(
                    '名称', '类型', '有效期', '券码', '折扣'
                ));

                if (!$discountCouponList->isEmpty()) {
                    foreach ($discountCouponList as $k => $vo) {
                        $sheet->row($k + 2, array(
                            $vo->name, $vo->type == 1 ? '一次性' : '可重复', date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end), $vo->sn, $vo->discount
                        ));
                    }
                }
            });

            $excel->sheet('充值券', function($sheet) use($chargeCouponList) {
                $sheet->row(1, array(
                    '名称', '类型', '有效期', '券码', '面额'
                ));

                if (!$chargeCouponList->isEmpty()) {
                    foreach ($chargeCouponList as $k => $vo) {
                        $sheet->row($k + 2, array(
                            $vo->name, '一次性', date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end), $vo->sn, $vo->amount / 100
                        ));
                    }
                }
            });
        })->export('xls');
    }
}
