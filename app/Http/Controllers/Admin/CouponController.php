<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Level;
use App\Models\UserGroup;
use App\Utils\Helpers;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Str;

class CouponController extends Controller
{
    public function index(Request $request): View
    { // 优惠券列表
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

    public function show(Coupon $coupon): View
    { // 优惠券详情
        return view('admin.coupon.show', ['coupon' => $coupon, 'userGroups' => UserGroup::pluck('name', 'id'), 'levels' => Level::pluck('name', 'level')]);
    }

    public function store(CouponRequest $request): RedirectResponse
    { // 添加优惠券
        $logo = null;
        if ($request->hasFile('logo')) { // 优惠卷LOGO
            $file = $request->file('logo');
            $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            if (! $file->storeAs('public', $fileName)) {
                return redirect()->back()->withInput()->withErrors(trans('common.failed_action_item', ['action' => trans('common.store'), 'attribute' => trans('model.coupon.logo')]));
            }
            $logo = 'upload/'.$fileName;
        }
        $num = (int) $request->input('num');
        $data = $request->only(['name', 'type', 'priority', 'usable_times', 'value', 'start_time', 'end_time']);
        $data['end_time'] .= ' 23:59:59';

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

            return redirect(route('admin.coupon.index'))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.generate')]));
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.generate'), 'attribute' => trans('model.coupon.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.generate')]).', '.$e->getMessage());
        }
    }

    public function create(): View
    { // 添加优惠券页面
        return view('admin.coupon.create', ['userGroups' => UserGroup::pluck('name', 'id'), 'levels' => Level::pluck('name', 'level')]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    { // 删除优惠券
        try {
            if ($coupon->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.coupon.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }

    public function exportCoupon(): void
    { // 导出卡券
        $couponList = Coupon::whereStatus(0)->get();

        $filename = trans('model.coupon.attribute').'_'.date('Ymd').'.xlsx';
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('ProxyPanel')
            ->setLastModifiedBy('ProxyPanel')
            ->setTitle(trans('model.coupon.attribute'))
            ->setSubject(trans('model.coupon.attribute'));

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(trans('model.coupon.attribute'));
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
        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('admin.massive_export'), 'attribute' => trans('model.coupon.attribute')]).': '.$e->getMessage());
        }
    }
}
