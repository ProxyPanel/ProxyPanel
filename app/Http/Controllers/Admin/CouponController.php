<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Redirect;
use Response;
use Str;
use Validator;

/**
 * 优惠券控制器
 *
 * Class CouponController
 *
 * @package App\Http\Controllers\Controller
 */
class CouponController extends Controller {
	// 优惠券列表
	public function couponList(Request $request) {
		$sn = $request->input('sn');
		$type = $request->input('type');
		$status = $request->input('status');

		$query = Coupon::query();

		if(isset($sn)){
			$query->where('sn', 'like', '%'.$sn.'%');
		}

		if(isset($type)){
			$query->whereType($type);
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['couponList'] = $query->latest()->paginate(15)->appends($request->except('page'));

		return view('admin.coupon.couponList', $view);
	}

	// 添加优惠券
	public function addCoupon(Request $request) {
		if($request->isMethod('POST')){
			Validator::make($request->all(), [
				'name'         => 'required',
				'sn'           => 'unique:coupon',
				'type'         => 'required|integer|between:1,3',
				'usable_times' => 'integer|nullable',
				'num'          => 'required|integer|min:1',
				'value'        => 'required|numeric|min:0',
				'start_time'   => 'required|date|before_or_equal:end_time',
				'end_time'     => 'required|date|after_or_equal:start_time',
			], [
				'name.required'              => '请填入卡券名称',
				'type.required'              => '请选择卡券类型',
				'type.integer'               => '卡券类型不合法，请重选',
				'type.between'               => '卡券类型不合法，请重选',
				'num.required'               => '请填写卡券数量',
				'num.integer'                => '卡券数量不合法',
				'num.min'                    => '卡券数量不合法，最小1',
				'value.required_unless'      => '请填入优惠值',
				'value.numeric'              => '优惠值金额不合法',
				'value.min'                  => '优惠值不合法，最小0',
				'start_time.required'        => '请填入有效期',
				'start_time.date'            => '有效期不合法',
				'start_time.before_or_equal' => '有效期不合法',
				'end_time.required'          => '请填入有效期',
				'end_time.date'              => '有效期不合法',
				'end_time.after_or_equal'    => '有效期不合法'
			]);

			$type = $request->input('type');

			// 优惠卷LOGO
			$logo = '';
			if($request->hasFile('logo')){
				$logo = $this->uploadFile($request->file('logo'));

				if(!$logo){
					return Redirect::back()->withInput()->withErrors('LOGO不合法');
				}
			}

			try{
				DB::beginTransaction();
				$num = $request->input('num');
				for($i = 0; $i < $num; $i++){
					$obj = new Coupon();
					$obj->name = $request->input('name');
					$obj->logo = $logo;
					$obj->sn = $num == 1 && $request->input('sn')? $request->input('sn') : Str::random(8);
					$obj->type = $type;
					$obj->usable_times = $request->input('usable_times');
					$obj->value = $request->input('value');
					$obj->rule = $request->input('rule');
					$obj->start_time = strtotime($request->input('start_time'));
					$obj->end_time = strtotime($request->input('end_time'));
					$obj->status = 0;
					$obj->save();
				}

				DB::commit();

				return Redirect::back()->with('successMsg', '生成成功');
			}catch(Exception $e){
				DB::rollBack();

				Log::error('生成优惠券失败：'.$e->getMessage());

				return Redirect::back()->withInput()->withErrors('生成失败：'.$e->getMessage());
			}
		}else{
			return view('admin.coupon.addCoupon');
		}
	}

	// 删除优惠券
	public function delCoupon(Request $request): JsonResponse {
		Coupon::find($request->input('id'))->delete();

		return Response::json(['status' => 'success', 'message' => '删除成功']);
	}

	// 导出卡券
	public function exportCoupon(): void {
		$voucherList = Coupon::type(1)->whereStatus(0)->get();
		$discountCouponList = Coupon::type(2)->whereStatus(0)->get();
		$refillList = Coupon::type(3)->whereStatus(0)->get();

		$filename = '卡券'.date('Ymd').'.xlsx';
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()
		            ->setCreator('ProxyPanel')
		            ->setLastModifiedBy('ProxyPanel')
		            ->setTitle('邀请码')
		            ->setSubject('邀请码')
		            ->setDescription('')
		            ->setKeywords('')
		            ->setCategory('');

		// 抵用券
		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('抵用券');
		$sheet->fromArray(['名称', '使用次数', '有效期', '券码', '金额（元）', '使用限制（元）'], null);
		foreach($voucherList as $k => $vo){
			$dateRange = date('Y-m-d', $vo->start_time).' ~ '.date('Y-m-d', $vo->end_time);
			$sheet->fromArray([$vo->name, $vo->usable_times, $dateRange, $vo->sn, $vo->value, $vo->rule], null,
				'A'.($k + 2));
		}

		// 折扣券
		$spreadsheet->createSheet(1);
		$spreadsheet->setActiveSheetIndex(1);
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('折扣券');
		$sheet->fromArray(['名称', '使用次数', '有效期', '券码', '折扣（折）', '使用限制（元）'], null);
		foreach($discountCouponList as $k => $vo){
			$dateRange = date('Y-m-d', $vo->start_time).' ~ '.date('Y-m-d', $vo->end_time);
			$sheet->fromArray([$vo->name, $vo->usable_times, $dateRange, $vo->sn, $vo->value, $vo->rule], null,
				'A'.($k + 2));
		}

		// 充值券
		$spreadsheet->createSheet(2);
		$spreadsheet->setActiveSheetIndex(2);
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('充值券');
		$sheet->fromArray(['名称', '有效期', '券码', '金额（元）'], null);
		foreach($refillList as $k => $vo){
			$dateRange = date('Y-m-d', $vo->start_time).' ~ '.date('Y-m-d', $vo->end_time);
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
	}
}
