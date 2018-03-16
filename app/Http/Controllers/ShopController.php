<?php

namespace App\Http\Controllers;

use App\Http\Models\Goods;
use Illuminate\Http\Request;
use Response;
use Redirect;

/**
 * 商店控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class ShopController extends Controller
{
    // 商品列表
    public function goodsList(Request $request)
    {
        $goodsList = Goods::query()->where('is_del', 0)->orderBy('id', 'desc')->paginate(10);
        foreach ($goodsList as $goods) {
            $goods->price = $goods->price / 100;
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
            $price = $request->get('price');
            $score = $request->get('score', 0);
            $type = $request->get('type', 1);
            $days = $request->get('days', 30);
            $status = $request->get('status');

            if (empty($name) || empty($traffic) || $price == '') {
                $request->session()->flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            // 套餐有效天数必须大于30天
            if ($type == 2 && $days < 30) {
                $request->session()->flash('errorMsg', '套餐有效天数必须不能少于30天');

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

            $obj = new Goods();
            $obj->name = $name;
            $obj->desc = $desc;
            $obj->logo = $logo;
            $obj->traffic = $traffic;
            $obj->price = $price * 100; // 单位分
            $obj->score = $score;
            $obj->type = $type;
            $obj->days = $days;
            $obj->status = $status;
            $obj->save();

            if ($obj->id) {
                // 生成SKU
                $obj->sku = 'S0000' . $obj->id;
                $obj->save();

                $request->session()->flash('successMsg', '添加成功');
            } else {
                $request->session()->flash('errorMsg', '添加失败');
            }

            return Redirect::to('shop/addGoods');
        } else {
            return Response::view('shop/addGoods');
        }
    }

    // 编辑商品
    public function editGoods(Request $request)
    {
        $id = $request->get('id');

        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $desc = $request->get('desc');
            $traffic = $request->get('traffic');
            $price = $request->get('price');
            $score = $request->get('score', 0);
            $type = $request->get('type', 1);
            $days = $request->get('days', 30);
            $status = $request->get('status');

            if (empty($name) || empty($traffic) || $price == '') {
                $request->session()->flash('errorMsg', '请填写完整');

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

            $data = [
                'name'    => $name,
                'desc'    => $desc,
                'logo'    => $logo,
                'traffic' => $traffic,
                'price'   => $price * 100, // 单位分
                'score'   => $score,
                'type'    => $type,
                'days'    => $days,
                'status'  => $status
            ];
            $ret = Goods::query()->where('id', $id)->update($data);
            if ($ret) {
                $request->session()->flash('successMsg', '编辑成功');
            } else {
                $request->session()->flash('errorMsg', '编辑失败');
            }

            return Redirect::to('shop/editGoods?id=' . $id);
        } else {
            $goods = Goods::query()->where('id', $id)->first();
            if (!empty($goods)) {
                $goods->price = $goods->price / 100;
            }

            $view['goods'] = $goods;

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
