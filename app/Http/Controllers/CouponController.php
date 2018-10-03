<?php

namespace App\Http\Controllers;

use App\Http\Models\Coupon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Response;
use Redirect;
use Session;
use DB;
use Log;

/**
 * 优惠券控制器
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class CouponController extends Controller
{
    // 优惠券列表
    public function couponList(Request $request)
    {
        $view['couponList'] = Coupon::query()->where('is_del', 0)->orderBy('status', 'asc')->orderBy('id', 'desc')->paginate(10);

        return Response::view('coupon.couponList', $view);
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
                Session::flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            if (strtotime($available_start) >= strtotime($available_end)) {
                Session::flash('errorMsg', '有效期范围错误');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back()->withInput();
                }

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
                    $obj->amount = empty($amount) ? 0 : $amount;
                    $obj->discount = empty($discount) ? 0 : $discount;
                    $obj->available_start = strtotime(date('Y-m-d 0:0:0', strtotime($available_start)));
                    $obj->available_end = strtotime(date('Y-m-d 23:59:59', strtotime($available_end)));
                    $obj->status = 0;
                    $obj->save();
                }

                DB::commit();

                Session::flash('successMsg', '生成成功');
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('生成优惠券失败：' . $e->getMessage());

                Session::flash('errorMsg', '生成失败：' . $e->getMessage());
            }

            return Redirect::to('coupon/addCoupon');
        } else {
            return Response::view('coupon.addCoupon');
        }
    }

    // 删除优惠券
    public function delCoupon(Request $request)
    {
        $id = $request->get('id');

        Coupon::query()->where('id', $id)->update(['is_del' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }

    // 导出卡券
    public function exportCoupon(Request $request)
    {
        $cashCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 1)->get();
        $discountCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 2)->get();
        $chargeCouponList = Coupon::query()->where('is_del', 0)->where('status', 0)->where('type', 3)->get();

        $filename = '卡券' . date('Ymd') . '.xlsx';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('SSRPanel')->setLastModifiedBy('SSRPanel')->setTitle('邀请码')->setSubject('邀请码')->setDescription('')->setKeywords('')->setCategory('');

        // 抵用券
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('抵用券');
        $sheet->fromArray(['名称', '类型', '有效期', '券码', '面额（元）'], null);
        foreach ($cashCouponList as $k => $vo) {
            $usage = '仅限一次性使用';
            $dateRange = date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end);
            $sheet->fromArray([$vo->name, $usage, $dateRange, $vo->sn, $vo->amount], null, 'A' . ($k + 2));
        }

        // 折扣券
        $spreadsheet->createSheet(1);
        $spreadsheet->setActiveSheetIndex(1);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('折扣券');
        $sheet->fromArray(['名称', '类型', '有效期', '券码', '折扣（折）'], null);
        foreach ($discountCouponList as $k => $vo) {
            $usage = $vo->usage == 1 ? '仅限一次性使用' : '可重复使用';
            $dateRange = date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end);
            $sheet->fromArray([$vo->name, $usage, $dateRange, $vo->sn, $vo->discount], null, 'A' . ($k + 2));
        }

        // 充值券
        $spreadsheet->createSheet(2);
        $spreadsheet->setActiveSheetIndex(2);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('充值券');
        $sheet->fromArray(['名称', '类型', '有效期', '券码', '面额（元）'], null);
        foreach ($chargeCouponList as $k => $vo) {
            $usage = '仅限一次性使用';
            $dateRange = date('Y-m-d', $vo->available_start) . ' ~ ' . date('Y-m-d', $vo->available_end);
            $sheet->fromArray([$vo->name, $usage, $dateRange, $vo->sn, $vo->amount], null, 'A' . ($k + 2));
        }

        // 指针切换回第一个sheet
        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 输出07Excel文件
        //header('Content-Type:application/vnd.ms-excel'); // 输出Excel03版本文件
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
