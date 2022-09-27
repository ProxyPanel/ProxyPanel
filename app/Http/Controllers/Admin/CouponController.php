<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Level;
use App\Models\UserGroup;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Redirect;
use Response;
use Str;

class CouponController extends Controller
{
    // 优惠券列表
    public function index(Request $request)
    {
        $query = Coupon::query();

        $request->whenFilled('sn', function ($sn) use ($query) {
            $query->where('sn', 'like', "%{$sn}%");
        });

        foreach (['type', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        return view('admin.coupon.index', ['couponList' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    // 优惠券列表
    public function show(Coupon $coupon)
    {
        return view('admin.coupon.show', [
            'coupon'     => $coupon,
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels'     => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    // 添加优惠券
    public function store(CouponRequest $request)
    {
        // 优惠卷LOGO
        $logo = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            if (! $file->storeAs('public', $fileName)) {
                return Redirect::back()->withInput()->withErrors('LOGO不合法');
            }
            $logo = 'upload/'.$fileName;
        }
        $num = (int) $request->input('num');
        $data = $request->only(['name', 'type', 'priority', 'usable_times', 'value', 'start_time', 'end_time']);
        $data['limit'] = [
            'minimum'  => $request->input('minimum'),
            'used'     => $request->input('used'),
            'users'    => [
                'white'  => $request->has('users_whitelist') ? array_map('intval', explode(', ', $request->input('users_whitelist'))) : null,
                'black'  => $request->has('users_blacklist') ? array_map('intval', explode(', ', $request->input('users_blacklist'))) : null,
                'newbie' => [
                    'coupon' => $request->input('coupon'),
                    'order'  => $request->input('order'),
                    'days'   => $request->has('days') ? (int) $request->input(['days']) : null,
                ],
                'levels' => $request->has('levels') ? array_map('intval', $request->input('levels')) : null,
                'groups' => $request->has('groups') ? array_map('intval', $request->input('groups')) : null,
            ],
            'services' => [
                'white' => $request->has('services_whitelist') ? array_map('intval', explode(', ', $request->input('services_whitelist'))) : null,
                'black' => $request->has('services_blacklist') ? array_map('intval', explode(', ', $request->input('services_blacklist'))) : null,
            ],
        ];
        array_clean($data);

        $data['logo'] = $logo;
        $data['status'] = 0;
        try {
            for ($i = 0; $i < $num; $i++) {
                $data['sn'] = $num === 1 && $request->input('sn') ? $request->input('sn') : Str::random(8);
                Coupon::create($data);
            }

            return Redirect::route('admin.coupon.index')->with('successMsg', trans('common.generate_item', ['attribute' => trans('common.success')]));
        } catch (Exception $e) {
            Log::error('生成优惠券失败：'.$e->getMessage());

            return Redirect::back()->withInput()->withInput()->withErrors('生成优惠券失败：'.$e->getMessage());
        }
    }

    // 添加优惠券页面
    public function create()
    {
        return view('admin.coupon.create', [
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels'     => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    // 删除优惠券
    public function destroy(Coupon $coupon): JsonResponse
    {
        try {
            if ($coupon->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除优惠券失败：'.$e->getMessage());

            return Response::json(['status' => 'success', 'message' => '删除优惠券失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }

    // 导出卡券
    public function exportCoupon(): void
    {
        $voucherList = Coupon::type(1)->whereStatus(0)->get();
        $discountCouponList = Coupon::type(2)->whereStatus(0)->get();
        $refillList = Coupon::type(3)->whereStatus(0)->get();

        try {
            $filename = '卡券'.date('Ymd').'.xlsx';
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('ProxyPanel')
                ->setLastModifiedBy('ProxyPanel')
                ->setTitle('邀请码')
                ->setSubject('邀请码');

            // 抵用券
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('抵用券');
            $sheet->fromArray(['名称', '使用次数', '有效期', '券码', '金额（元）', '权重', '使用限制']);
            foreach ($voucherList as $k => $vo) {
                $dateRange = $vo->start_time.' ~ '.$vo->end_time;
                $sheet->fromArray([$vo->name, $vo->usable_times ?? '无限制', $dateRange, $vo->sn, $vo->value, $vo->priority, json_encode($vo->limit)], null, 'A'.($k + 2));
            }

            // 折扣券
            $spreadsheet->createSheet(1);
            $spreadsheet->setActiveSheetIndex(1);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('折扣券');
            $sheet->fromArray(['名称', '使用次数', '有效期', '券码', '折扣（折）', '权重', '使用限制']);
            foreach ($discountCouponList as $k => $vo) {
                $dateRange = $vo->start_time.' ~ '.$vo->end_time;
                $sheet->fromArray([$vo->name, $vo->usable_times ?? '无限制', $dateRange, $vo->sn, $vo->value, $vo->priority, json_encode($vo->limit)], null, 'A'.($k + 2));
            }

            // 充值券
            $spreadsheet->createSheet(2);
            $spreadsheet->setActiveSheetIndex(2);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('充值券');
            $sheet->fromArray(['名称', '有效期', '券码', '金额（元）']);
            foreach ($refillList as $k => $vo) {
                $dateRange = $vo->start_time.' ~ '.$vo->end_time;
                $sheet->fromArray([$vo->name, $dateRange, $vo->sn, $vo->value], null, 'A'.($k + 2));
            }

            // 指针切换回第一个sheet
            $spreadsheet->setActiveSheetIndex(0);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 输出07Excel文件
            //header('Content-Type:application/vnd.ms-excel'); // 输出Excel03版本文件
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('导出优惠券时报错：'.$e->getMessage());
        }
    }
}
