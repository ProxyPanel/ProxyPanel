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
 *
 * Class CouponController
 *
 * @package App\Http\Controllers
 */
class CouponController extends Controller
{
    // 优惠券列表
    public function couponList(Request $request)
    {
        $view['couponList'] = Coupon::query()->orderBy('status', 'asc')->orderBy('id', 'desc')->paginate(10);

        return Response::view('coupon.couponList', $view);
    }

    // 添加商品
    public function addCoupon(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->validate($request, [
                'name'            => 'required',
                'type'            => 'required|integer|between:1,3',
                'usage'           => 'required|integer|between:1,2',
                'num'             => 'required|integer|min:1',
                'amount'          => 'required_unless:type,2|numeric|min:0.01|nullable',
                'discount'        => 'required_if:type,2|numeric|between:1,9.9|nullable',
                'available_start' => 'required|date|before_or_equal:available_end',
                'available_end'   => 'required|date|after_or_equal:available_start',
            ], [
                'name.required'                   => '请填入卡券名称',
                'type.required'                   => '请选择卡券类型',
                'type.integer'                    => '卡券类型不合法，请重选',
                'type.between'                    => '卡券类型不合法，请重选',
                'usage.required'                  => '请选择卡券用途',
                'usage.integer'                   => '卡券用途不合法，请重选',
                'usage.between'                   => '卡券用途不合法，请重选',
                'num.required'                    => '请填写卡券数量',
                'num.integer'                     => '卡券数量不合法',
                'num.min'                         => '卡券数量不合法，最小1',
                'amount.required_unless'          => '请填入卡券面值',
                'amount.numeric'                  => '卡券金额不合法',
                'amount.min'                      => '卡券金额不合法，最小0.01',
                'discount.required_if'            => '请填入卡券折扣',
                'discount.numeric'                => '卡券折扣不合法',
                'discount.between'                => '卡券折扣不合法，有效范围：1 ~ 9.9',
                'available_start.required'        => '请填入有效期',
                'available_start.date'            => '有效期不合法',
                'available_start.before_or_equal' => '有效期不合法',
                'available_end.required'          => '请填入有效期',
                'available_end.date'              => '有效期不合法',
                'available_end.after_or_equal'    => '有效期不合法'
            ]);

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    return Redirect::back()->withInput()->withErrors('LOGO不合法');
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $logo = $move ? '/upload/image/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                for ($i = 0; $i < $request->num; $i++) {
                    $obj = new Coupon();
                    $obj->name = $request->name;
                    $obj->sn = strtoupper(makeRandStr(7));
                    $obj->logo = $logo;
                    $obj->type = $request->type;
                    $obj->usage = $request->usage;
                    $obj->amount = empty($request->amount) ? 0 : $request->amount;
                    $obj->discount = empty($request->discount) ? 0 : $request->discount;
                    $obj->available_start = strtotime(date('Y-m-d 00:00:00', strtotime($request->available_start)));
                    $obj->available_end = strtotime(date('Y-m-d 23:59:59', strtotime($request->available_end)));
                    $obj->status = 0;
                    $obj->save();
                }

                DB::commit();

                return Redirect::back()->with('successMsg', '生成成功');
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('生成优惠券失败：' . $e->getMessage());

                return Redirect::back()->withInput()->withErrors('生成失败：' . $e->getMessage());
            }
        } else {
            return Response::view('coupon.addCoupon');
        }
    }

    // 删除优惠券
    public function delCoupon(Request $request)
    {
        Coupon::query()->where('id', $request->id)->delete();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }

    // 导出卡券
    public function exportCoupon(Request $request)
    {
        $cashCouponList = Coupon::type(1)->where('status', 0)->get();
        $discountCouponList = Coupon::type(2)->where('status', 0)->get();
        $chargeCouponList = Coupon::type(3)->where('status', 0)->get();

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
