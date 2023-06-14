<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Level;
use App\Models\UserGroup;
use App\Utils\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
            $query->where('sn', 'like', "%$sn%");
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
            'coupon' => $coupon,
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels' => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    // 添加优惠券
    public function store(CouponRequest $request): ?RedirectResponse
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
            'minimum' => $request->input('minimum'),
            'used' => $request->input('used'),
            'users' => [
                'white' => $request->has('users_whitelist') ? array_map('intval', explode(', ', $request->input('users_whitelist'))) : null,
                'black' => $request->has('users_blacklist') ? array_map('intval', explode(', ', $request->input('users_blacklist'))) : null,
                'newbie' => [
                    'coupon' => $request->input('coupon'),
                    'order' => $request->input('order'),
                    'days' => $request->has('days') ? (int) $request->input(['days']) : null,
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
            'levels' => Level::all()->pluck('name', 'level')->toArray(),
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
        $couponList = Coupon::whereStatus(0)->get();

        try {
            $filename = '卡券_Coupon_'.date('Ymd').'.xlsx';
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('ProxyPanel')
                ->setLastModifiedBy('ProxyPanel')
                ->setTitle('卡券')
                ->setSubject('卡券');

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('卡券');
            $sheet->fromArray([
                trans('model.common.type'), trans('model.coupon.name'), trans('model.coupon.usable_times'), trans('common.available_date'), trans('common.expired_at'), trans('model.coupon.sn'), trans('admin.coupon.discount'),
                trans('model.coupon.priority'), trans('model.rule.attribute'),
            ]);

            foreach ($couponList as $index => $coupon) {
                $sheet->fromArray([
                    [trans('common.status.unknown'), trans('admin.coupon.type.voucher'), trans('admin.coupon.type.discount'), trans('admin.coupon.type.charge')][$coupon->type], $coupon->name,
                    $coupon->type === 3 ? trans('admin.coupon.single_use') : ($coupon->usable_times ?? trans('common.unlimited')), $coupon->start_time, $coupon->end_time, $coupon->sn,
                    $coupon->type === 2 ? $coupon->value : Helpers::getPriceTag($coupon->value), $coupon->priority, json_encode($coupon->limit),
                ], null, 'A'.($index + 2));
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('导出优惠券时报错：'.$e->getMessage());
        }
    }
}
