<?php

namespace App\Http\Controllers;

use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Label;
use Illuminate\Http\Request;
use Response;
use Redirect;
use DB;

/**
 * 商店控制器
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class ShopController extends Controller
{
    // 商品列表
    public function goodsList(Request $request)
    {
        $goodsList = Goods::query()->where('is_del', 0)->orderBy('id', 'desc')->paginate(10);
        foreach ($goodsList as $goods) {
            $goods->traffic = flowAutoShow($goods->traffic * 1048576);
        }

        $view['goodsList'] = $goodsList;

        return Response::view('shop/goodsList', $view);
    }

    // 添加商品
    public function addGoods(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $desc = $request->get('desc', '');
            $traffic = $request->get('traffic');
            $price = $request->get('price', 0);
            $score = $request->get('score', 0);
            $type = $request->get('type', 1);
            $days = $request->get('days', 90);
            $labels = $request->get('labels');
            $status = $request->get('status');

            if (empty($name) || empty($traffic)) {
                $request->session()->flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            // 套餐必须有价格
            if ($type == 2 && $price <= 0) {
                $request->session()->flash('errorMsg', '套餐价格必须大于0');

                return Redirect::back()->withInput();
            }

            // 套餐有效天数必须大于90天
            if ($type == 2 && $days < 90) {
                $request->session()->flash('errorMsg', '套餐有效天数必须不能少于90天');

                return Redirect::back()->withInput();
            }

            // 流量不能超过1PB
            if ($traffic > 1073741824) {
                $request->session()->flash('errorMsg', '内含流量不能超过1PB');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();
                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/goods/', $logoName);
                $logo = $move ? '/upload/image/goods/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                $goods = new Goods();
                $goods->name = $name;
                $goods->desc = $desc;
                $goods->logo = $logo;
                $goods->traffic = $traffic;
                $goods->price = $price;
                $goods->score = $score;
                $goods->type = $type;
                $goods->days = $days;
                $goods->is_del = 0;
                $goods->status = $status;
                $goods->save();

                // 生成SKU
                $goods->sku = 'S0000' . $goods->id;
                $goods->save();

                // 生成商品标签
                if (!empty($labels)) {
                    foreach ($labels as $label) {
                        $goodsLabel = new GoodsLabel();
                        $goodsLabel->goods_id = $goods->id;
                        $goodsLabel->label_id = $label;
                        $goodsLabel->save();
                    }
                }

                $request->session()->flash('successMsg', '添加成功');

                DB::commit();
            } catch (\Exception $e) {
                $request->session()->flash('errorMsg', '添加失败');

                DB::rollBack();
            }

            return Redirect::to('shop/addGoods');
        } else {
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('shop/addGoods', $view);
        }
    }

    // 编辑商品
    public function editGoods(Request $request)
    {
        $id = $request->get('id');

        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $desc = $request->get('desc');
            $price = $request->get('price', 0);
            $labels = $request->get('labels');
            $status = $request->get('status');

            $goods = Goods::query()->where('id', $id)->first();
            if (!$goods) {
                $request->session()->flash('errorMsg', '商品不存在');

                return Redirect::back();
            }

            if (empty($name)) {
                $request->session()->flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            // 套餐必须有价格
            if ($goods->type == 2 && $price <= 0) {
                $request->session()->flash('errorMsg', '套餐价格必须大于0');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();
                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/goods/', $logoName);
                $logo = $move ? '/upload/image/goods/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                $data = [
                    'name'   => $name,
                    'desc'   => $desc,
                    'logo'   => $logo,
                    'price'  => $price * 100,
                    'status' => $status
                ];

                Goods::query()->where('id', $id)->update($data);

                // 先删除该商品所有的标签
                GoodsLabel::query()->where('goods_id', $id)->delete();

                // 生成商品标签
                if (!empty($labels)) {
                    foreach ($labels as $label) {
                        $goodsLabel = new GoodsLabel();
                        $goodsLabel->goods_id = $id;
                        $goodsLabel->label_id = $label;
                        $goodsLabel->save();
                    }
                }

                $request->session()->flash('successMsg', '编辑成功');

                DB::commit();
            } catch (\Exception $e) {
                $request->session()->flash('errorMsg', '编辑失败');

                DB::rollBack();
            }

            return Redirect::to('shop/editGoods?id=' . $id);
        } else {
            $goods = Goods::query()->with(['label'])->where('id', $id)->first();
            if ($goods) {
                $label = [];
                foreach ($goods->label as $vo) {
                    $label[] = $vo->label_id;
                }
                $goods->labels = $label;
            }

            $view['goods'] = $goods;
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('shop/editGoods', $view);
        }
    }

    // 删除商品
    public function delGoods(Request $request)
    {
        $id = $request->get('id');

        Goods::query()->where('id', $id)->update(['is_del' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }
}
