<?php

namespace App\Http\Controllers;

use App\Http\Models\Coupon;
use Illuminate\Http\Request;
use Response;
use Redirect;

/**
 * 优惠券控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class CouponController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 优惠券列表
    public function couponList(Request $request)
    {
        $view['couponList'] = Coupon::where('is_del', 0)->paginate(10);

        return Response::view('coupon/couponList', $view);
    }

    // 添加商品
    public function addCoupon(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $type = $request->get('type');
            $usage = $request->get('usage');
            $amount = $request->get('amount');
            $discount = $request->get('discount');
            $available_start = $request->get('available_start');
            $available_end = $request->get('available_end');

            if ((empty($amount) && empty($discount)) || empty($available_start) || empty($available_end)) {
                $request->session()->flash('errorMsg', '请填写完整');

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

            $obj = new Coupon();
            $obj->name = $name;
            $obj->sn = strtoupper($this->makeRandStr(7));
            $obj->logo = $logo;
            $obj->type = $type;
            $obj->usage = $usage;
            $obj->amount = $amount;
            $obj->discount = $discount / 10;
            $obj->available_start = strtotime(date('Y-m-d 0:0:0', strtotime($available_start)));
            $obj->available_end = strtotime(date('Y-m-d 23:59:59', strtotime($available_end)));
            $obj->status = 0;
            $obj->save();

            if ($obj->id) {
                $request->session()->flash('successMsg', '添加成功');
            } else {
                $request->session()->flash('errorMsg', '添加失败');
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

        Coupon::where('id', $id)->update(['is_del' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }
}
