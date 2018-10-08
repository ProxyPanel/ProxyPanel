<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Label;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Session;
use DB;

/**
 * 商店控制器
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class ShopController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 商品列表
    public function goodsList(Request $request)
    {
        $view['goodsList'] = Goods::query()->where('is_del', 0)->orderBy('id', 'desc')->paginate(10);

        return Response::view('shop.goodsList', $view);
    }

    // 添加商品
    public function addGoods(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $desc = $request->get('desc', '');
            $traffic = $request->get('traffic');
            $price = $request->get('price', 0);
            $score = intval($request->get('score', 0));
            $type = intval($request->get('type', 1));
            $days = intval($request->get('days', 90));
            $color = trim($request->get('color', 0));
            $sort = intval($request->get('sort', 0));
            $is_hot = intval($request->get('is_hot', 0));
            $labels = $request->get('labels');
            $status = $request->get('status');

            if (empty($name) || empty($traffic)) {
                Session::flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            // 套餐必须有价格
            if ($type == 2 && $price <= 0) {
                Session::flash('errorMsg', '套餐价格必须大于0');

                return Redirect::back()->withInput();
            }

            // 套餐有效天数必须大于90天
            if ($type == 2 && $days < 90) {
                Session::flash('errorMsg', '套餐有效天数必须不能少于90天');

                return Redirect::back()->withInput();
            }

            // 流量不能超过10TB
            if (in_array($type, [1, 2]) && $traffic > 10240000) {
                Session::flash('errorMsg', '内含流量不能超过10TB');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back()->withInput();
                }

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
                $goods->color = $color;
                $goods->sort = $sort;
                $goods->is_hot = $is_hot;
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

                Session::flash('successMsg', '添加成功');

                DB::commit();
            } catch (\Exception $e) {
                Session::flash('errorMsg', '添加失败');

                DB::rollBack();
            }

            return Redirect::to('shop/addGoods');
        } else {
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('shop.addGoods', $view);
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
            $color = trim($request->get('color', 0));
            $sort = intval($request->get('sort', 0));
            $is_hot = intval($request->get('is_hot', 0));
            $status = $request->get('status');

            $goods = Goods::query()->where('id', $id)->first();
            if (!$goods) {
                Session::flash('errorMsg', '商品不存在');

                return Redirect::back();
            }

            if (empty($name)) {
                Session::flash('errorMsg', '请填写完整');

                return Redirect::back()->withInput();
            }

            // 套餐必须有价格
            if ($goods->type == 2 && $price <= 0) {
                Session::flash('errorMsg', '套餐价格必须大于0');

                return Redirect::back()->withInput();
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back()->withInput();
                }

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
                    'sort'   => $sort,
                    'color'  => $color,
                    'is_hot' => $is_hot,
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

                Session::flash('successMsg', '编辑成功');

                DB::commit();
            } catch (\Exception $e) {
                Session::flash('errorMsg', '编辑失败');

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

            return Response::view('shop.editGoods', $view);
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
