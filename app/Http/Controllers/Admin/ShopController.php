<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Models\Level;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
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
	public function goodsList(Request $request): \Illuminate\Http\Response {
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

		return Response::view('admin.shop.goodsList', $view);
	}

	// 添加商品
	public function addGoods(Request $request) {
		if($request->isMethod('POST')){
			Validator::make($request->all(), [
				'name'    => 'required',
				'traffic' => 'required|integer|min:1024|max:10240000|nullable',
				'price'   => 'required|numeric|min:0',
				'type'    => 'required',
				'renew'   => 'require_unless:type,2|min:0',
				'days'    => 'required|integer',
			], [
				'traffic.min' => '内含流量不能低于1MB',
				'traffic.max' => '内含流量不能超过10TB',
			]);

			$logo = null;
			// 商品LOGO
			if($request->hasFile('logo')){
				$file = $request->file('logo');
				$fileType = $file->getClientOriginalExtension();

				// 验证文件合法性
				if(!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])){
					return Redirect::back()->withInput()->withErrors('LOGO不合法');
				}

				$logoName = date('YmdHis').random_int(1000, 2000).'.'.$fileType;
				$move = $file->move(base_path().'/public/upload/image/', $logoName);
				$logo = $move? '/upload/image/'.$logoName : '';
			}

			try{
				DB::beginTransaction();

				$goods = new Goods();
				$goods->name = $request->input('name');
				$goods->logo = $logo?: null;
				$goods->traffic = $request->input('traffic');
				$goods->type = $request->input('type');
				$goods->price = round($request->input('price'), 2);
				$goods->level = $request->input('level');
				$goods->renew = round($request->input('renew'), 2);
				$goods->period = $request->input('period');
				$goods->info = $request->input('info');
				$goods->description = $request->input('description');
				$goods->days = $request->input('days');
				$goods->invite_num = $request->input('invite_num');
				$goods->limit_num = $request->input('limit_num');
				$goods->color = $request->input('color');
				$goods->sort = $request->input('sort');
				$goods->is_hot = $request->input('is_hot', 0);
				$goods->status = $request->input('status', 0);
				$goods->save();

				DB::commit();

				return Redirect::back()->with('successMsg', '添加成功');
			}catch(Exception $e){
				DB::rollBack();
				Log::info($e);

				return Redirect::back()->withInput()->withErrors('添加失败');
			}
		}else{
			$view['level_list'] = Level::query()->orderBy('level')->get();

			return Response::view('admin.shop.goodsInfo', $view);
		}
	}

	// 编辑商品
	public function editGoods(Request $request) {
		$id = $request->input('id');
		if($request->isMethod('POST')){
			Validator::make($request->all(), [
				'name'    => 'required',
				'traffic' => 'required|integer|min:1024|max:10240000|nullable',
				'price'   => 'required|numeric|min:0',
				'type'    => 'required',
				'renew'   => 'require_unless:type,2|min:0',
				'days'    => 'required|integer',
			], [
				'traffic.min' => '内含流量不能低于1MB',
				'traffic.max' => '内含流量不能超过10TB',
			]);

			$goods = Goods::query()->whereId($id)->first();
			if(!$goods){
				Session::flash('errorMsg', '商品不存在');

				return Redirect::back();
			}

			// 商品LOGO
			if($request->hasFile('logo')){
				$file = $request->file('logo');
				$fileType = $file->getClientOriginalExtension();

				// 验证文件合法性
				if(!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])){
					Session::flash('errorMsg', 'LOGO不合法');

					return Redirect::back()->withInput();
				}

				$logoName = date('YmdHis').random_int(1000, 2000).'.'.$fileType;
				$move = $file->move(base_path().'/public/upload/image/', $logoName);
				$logo = $move? '/upload/image/'.$logoName : '';
				Goods::query()->whereId($id)->update(['logo' => $logo]);
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

				Goods::query()->whereId($id)->update($data);

				Session::flash('successMsg', '编辑成功');

				DB::commit();
			}catch(Exception $e){
				Session::flash('errorMsg', '编辑失败');

				DB::rollBack();
			}

			return Redirect::to('shop/edit?id='.$id);
		}

		$goods = Goods::query()->whereId($id)->first();
		$view['level_list'] = Level::query()->orderBy('level')->get();

		return view('admin.shop.goodsInfo', $view)->with(compact('goods'));
	}

	// 删除商品
	public function delGoods(Request $request): JsonResponse {
		try{
			Goods::query()->whereId($request->input('id'))->delete();
		}catch(Exception $e){
			Session::flash('errorMsg', '编辑失败'.$e);
		}

		return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
	}
}
