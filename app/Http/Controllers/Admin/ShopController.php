<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Label;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;

/**
 * 商店控制器
 *
 * Class ShopController
 *
 * @package App\Http\Controllers\Controller
 */
class ShopController extends Controller {
	// 商品列表
	public function goodsList(Request $request) {
		$type = $request->input('type');
		$status = $request->input('status');

		$query = Goods::query();

		if(isset($type)){
			$query->whereType($type);
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['goodsList'] = $query->orderBy('status', 'desc')->paginate(10)->appends($request->except('page'));

		return Response::view('admin.shop.goodsList', $view);
	}

	// 添加商品
	public function addGoods(Request $request) {
		if($request->isMethod('POST')){
			$this->validate($request, [
				'name'    => 'required',
				'traffic' => 'required_unless:type,3|integer|min:1024|max:10240000|nullable',
				'price'   => 'required|numeric|min:0',
				'type'    => 'required',
				'days'    => 'required|integer',
			], [
				                'name.required'           => '请填入名称',
				                'traffic.required_unless' => '请填入流量',
				                'traffic.integer'         => '内含流量必须是整数值',
				                'traffic.min'             => '内含流量不能低于1MB',
				                'traffic.max'             => '内含流量不能超过10TB',
				                'price.required'          => '请填入价格',
				                'price.numeric'           => '价格不合法',
				                'price.min'               => '价格最低0',
				                'type.required'           => '请选择类型',
				                'days.required'           => '请填入有效期',
				                'days.integer'            => '有效期不合法',
			                ]);

			$type = $request->input('type');
			$price = $request->input('price');
			$renew = $request->input('renew');
			$days = $request->input('days');

			// 套餐必须有价格
			if($type == 2 && $price <= 0){
				return Redirect::back()->withInput()->withErrors('套餐价格必须大于0');
			}

			if($renew < 0){
				return Redirect::back()->withInput()->withErrors('流量重置价格必须大于0');
			}
			// 套餐有效天数必须大于30天
			if($type == 2 && $days < 1){
				return Redirect::back()->withInput()->withErrors('套餐有效天数必须不能少于1天');
			}

			// 商品LOGO
			if($request->hasFile('logo')){
				$file = $request->file('logo');
				$fileType = $file->getClientOriginalExtension();

				// 验证文件合法性
				if(!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])){
					return Redirect::back()->withInput()->withErrors('LOGO不合法');
				}

				$logoName = date('YmdHis').mt_rand(1000, 2000).'.'.$fileType;
				$move = $file->move(base_path().'/public/upload/image/', $logoName);
				$logo = $move? '/upload/image/'.$logoName : '';
			}else{
				$logo = '';
			}

			DB::beginTransaction();
			try{
				$goods = new Goods();
				$goods->name = $request->input('name');
				$goods->info = $request->input('info');
				$goods->desc = $request->input('desc');
				$goods->logo = $logo;
				$goods->traffic = $request->input('traffic');
				$goods->price = round($price, 2);
				$goods->renew = round($renew, 2);
				$goods->type = $type;
				$goods->days = $days;
				$goods->color = $request->input('color');
				$goods->sort = $request->input('sort');
				$goods->is_hot = $request->input('is_hot');
				$goods->limit_num = $request->input('limit_num');
				$goods->status = $request->input('status');
				$goods->save();

				// 生成SKU
				$goods->sku = 'S0000'.$goods->id;
				$goods->save();

				// 生成商品标签
				$labels = $request->input('labels');
				if(!empty($labels)){
					foreach($labels as $label){
						$goodsLabel = new GoodsLabel();
						$goodsLabel->goods_id = $goods->id;
						$goodsLabel->label_id = $label;
						$goodsLabel->save();
					}
				}

				DB::commit();

				return Redirect::back()->with('successMsg', '添加成功');
			}catch(Exception $e){
				DB::rollBack();
				Log::info($e);

				return Redirect::back()->withInput()->withErrors('添加失败');
			}
		}else{
			$view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

			return Response::view('admin.shop.addGoods', $view);
		}
	}

	// 编辑商品
	public function editGoods(Request $request, $id) {
		if($request->isMethod('POST')){
			$name = $request->input('name');
			$info = $request->input('info');
			$desc = $request->input('desc');
			$price = round($request->input('price'), 2);
			$renew = round($request->input('renew'), 2);
			$labels = $request->input('labels');
			$color = $request->input('color');
			$sort = $request->input('sort');
			$is_hot = $request->input('is_hot');
			$limit_num = $request->input('limit_num');
			$status = $request->input('status');

			$goods = Goods::query()->whereId($id)->first();
			if(!$goods){
				Session::flash('errorMsg', '商品不存在');

				return Redirect::back();
			}

			if(empty($name)){
				Session::flash('errorMsg', '请填写完整');

				return Redirect::back()->withInput();
			}

			// 套餐必须有价格
			if($goods->type == 2 && $price <= 0){
				Session::flash('errorMsg', '套餐价格必须大于0');

				return Redirect::back()->withInput();
			}

			if($renew < 0){
				Session::flash('errorMsg', '流量重置价格必须大于0');

				return Redirect::back()->withInput();
			}

			// 商品LOGO
			$logo = '';
			if($request->hasFile('logo')){
				$file = $request->file('logo');
				$fileType = $file->getClientOriginalExtension();

				// 验证文件合法性
				if(!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])){
					Session::flash('errorMsg', 'LOGO不合法');

					return Redirect::back()->withInput();
				}

				$logoName = date('YmdHis').mt_rand(1000, 2000).'.'.$fileType;
				$move = $file->move(base_path().'/public/upload/image/', $logoName);
				$logo = $move? '/upload/image/'.$logoName : '';
			}

			DB::beginTransaction();
			try{
				$data = [
					'name'      => $name,
					'info'      => $info,
					'desc'      => $desc,
					'price'     => $price * 100,
					'renew'     => $renew * 100,
					'sort'      => $sort,
					'color'     => $color,
					'is_hot'    => $is_hot,
					'limit_num' => $limit_num,
					'status'    => $status
				];

				if($logo){
					$data['logo'] = $logo;
				}

				Goods::query()->whereId($id)->update($data);

				// 先删除该商品所有的标签
				GoodsLabel::query()->whereGoodsId($id)->delete();

				// 生成商品标签
				if(!empty($labels)){
					foreach($labels as $label){
						$goodsLabel = new GoodsLabel();
						$goodsLabel->goods_id = $id;
						$goodsLabel->label_id = $label;
						$goodsLabel->save();
					}
				}

				Session::flash('successMsg', '编辑成功');

				DB::commit();
			}catch(Exception $e){
				Session::flash('errorMsg', '编辑失败');

				DB::rollBack();
			}

			return Redirect::to('shop/editGoods/'.$id);
		}else{
			$goods = Goods::query()->with(['label'])->whereId($id)->first();
			if($goods){
				$label = [];
				foreach($goods->label as $vo){
					$label[] = $vo->label_id;
				}
				$goods->labels = $label;
			}

			$view['goods'] = $goods;
			$view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

			return Response::view('admin.shop.editGoods', $view);
		}
	}

	// 删除商品
	public function delGoods(Request $request) {
		Goods::query()->whereId($request->input('id'))->delete();

		return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
	}
}
