<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\Level;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;
use Validator;

/**
 * 商店控制器
 *
 * Class ShopController
 *
 * @package App\Http\Controllers\Controller
 */
class ShopController extends Controller {
	// 商品列表
	public function index(Request $request): \Illuminate\Http\Response {
		$type = $request->input('type');
		$status = $request->input('status');

		$query = Goods::query();

		if(isset($type)){
			$query->whereType($type);
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['goodsList'] = $query->orderByDesc('status')->paginate(10)->appends($request->except('page'));

		return Response::view('admin.shop.index', $view);
	}

	// 添加商品页面
	public function create(): \Illuminate\Http\Response {
		$view['goods'] = null;
		$view['levelList'] = Level::orderBy('level')->get();

		return Response::view('admin.shop.info', $view);
	}

	// 添加商品
	public function store(Request $request): RedirectResponse {
		$validator = Validator::make($request->all(), [
			'name'    => 'required',
			'traffic' => 'required|integer|min:1|max:10240000|nullable',
			'price'   => 'required|numeric|min:0',
			'type'    => 'required',
			'renew'   => 'required_unless:type,2|min:0',
			'days'    => 'required|integer',
		], [
			'traffic.min' => '内含流量不能低于1MB',
			'traffic.max' => '内含流量不能超过10TB',
		]);

		if($validator->fails()){
			return Redirect::back()->withInput()->withErrors($validator->errors());
		}

		// 商品LOGO
		$logo = null;
		if($request->hasFile('logo')){
			$logo = $this->uploadFile($request->file('logo'));

			if(!$logo){
				return Redirect::back()->withInput()->withErrors('LOGO不合法');
			}
		}

		try{
			DB::beginTransaction();

			$obj = new Goods();
			$obj->name = $request->input('name');
			$obj->logo = $logo?: null;
			$obj->traffic = $request->input('traffic');
			$obj->type = $request->input('type');
			$obj->price = round($request->input('price'), 2);
			$obj->level = $request->input('level');
			$obj->renew = round($request->input('renew'), 2);
			$obj->period = $request->input('period');
			$obj->info = $request->input('info');
			$obj->description = $request->input('description');
			$obj->days = $request->input('days');
			$obj->invite_num = $request->input('invite_num');
			$obj->limit_num = $request->input('limit_num');
			$obj->color = $request->input('color');
			$obj->sort = $request->input('sort');
			$obj->is_hot = $request->input('is_hot', 0);
			$obj->status = $request->input('status', 0);
			$obj->save();

			DB::commit();

			return Redirect::back()->with('successMsg', '添加成功');
		}catch(Exception $e){
			DB::rollBack();
			Log::info('添加商品信息异常：'.$e->getMessage());

			return Redirect::back()->withInput()->withErrors('添加失败');
		}
	}

	// 编辑商品页面
	public function edit($id): \Illuminate\Http\Response {
		$view['goods'] = Goods::find($id);
		$view['levelList'] = Level::orderBy('level')->get();

		return Response::view('admin.shop.info', $view);
	}

	// 编辑商品
	public function update(Request $request, $id) {
		$goods = Goods::find($id);
		if(!$goods){
			Session::flash('errorMsg', '商品不存在');

			return Redirect::back();
		}

		// 商品LOGO
		if($request->hasFile('logo')){
			$logo = $this->uploadFile($request->file('logo'));

			if(!$logo){
				Session::flash('errorMsg', 'LOGO不合法');

				return Redirect::back()->withInput();
			}
			Goods::whereId($id)->update(['logo' => $logo]);
		}

		try{
			DB::beginTransaction();

			$data = [
				'name'        => $request->input('name'),
				'price'       => round($request->input('price'), 2) * 100,
				'level'       => $request->input('level'),
				'renew'       => round($request->input('renew'), 2) * 100,
				'period'      => $request->input('period'),
				'info'        => $request->input('info'),
				'description' => $request->input('description'),
				'invite_num'  => $request->input('invite_num'),
				'limit_num'   => $request->input('limit_num'),
				'color'       => $request->input('color'),
				'sort'        => $request->input('sort'),
				'is_hot'      => $request->input('is_hot', 0),
				'status'      => $request->input('status', 0)
			];

			Goods::whereId($id)->update($data);

			Session::flash('successMsg', '编辑成功');

			DB::commit();
		}catch(Exception $e){
			Session::flash('errorMsg', '编辑失败');

			DB::rollBack();
		}

		return Redirect::back();
	}

	// 删除商品
	public function destroy($id): JsonResponse {
		try{
			$goods = Goods::findOrFail($id)->delete();

		}catch(Exception $e){
			Session::flash('errorMsg', '编辑失败'.$e);
		}

		return Response::json(['status' => 'success', 'message' => '删除成功']);
	}
}
