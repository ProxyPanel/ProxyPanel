<?php

namespace App\Http\Controllers;

use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Label;
use Illuminate\Http\Request;
use Log;
use Response;
use Redirect;
use Session;
use DB;

/**
 * 商店控制器
 *
 * Class ShopController
 *
 * @package App\Http\Controllers
 */
class ShopController extends Controller
{
    // 商品列表
    public function goodsList(Request $request)
    {
        $view['goodsList'] = Goods::query()->orderBy('id', 'desc')->paginate(10);

        return Response::view('shop.goodsList', $view);
    }

    // 添加商品
    public function addGoods(Request $request)
    {
        if ($request->isMethod('POST')) {
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

            // 套餐必须有价格
            if ($request->type == 2 && $request->price <= 0) {
                return Redirect::back()->withInput()->withErrors('套餐价格必须大于0');
            }

            // 套餐有效天数必须大于90天
            if ($request->type == 2 && $request->days < 90) {
                return Redirect::back()->withInput()->withErrors('套餐有效天数必须不能少于90天');
            }

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    return Redirect::back()->withInput()->withErrors('LOGO不合法');
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $logo = $move ? '/upload/image/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                $goods = new Goods();
                $goods->name = $request->name;
                $goods->desc = $request->desc;
                $goods->logo = $logo;
                $goods->traffic = $request->traffic;
                $goods->price = round($request->price, 2);
                $goods->type = $request->type;
                $goods->days = $request->days;
                $goods->color = $request->color;
                $goods->sort = intval($request->sort);
                $goods->is_hot = intval($request->is_hot);
                $goods->is_limit = intval($request->is_limit);
                $goods->status = $request->status;
                $goods->save();

                // 生成SKU
                $goods->sku = 'S0000' . $goods->id;
                $goods->save();

                // 生成商品标签
                $labels = $request->get('labels');
                if (!empty($labels)) {
                    foreach ($labels as $label) {
                        $goodsLabel = new GoodsLabel();
                        $goodsLabel->goods_id = $goods->id;
                        $goodsLabel->label_id = $label;
                        $goodsLabel->save();
                    }
                }

                DB::commit();

                return Redirect::back()->with('successMsg', '添加成功');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info($e);

                return Redirect::back()->withInput()->withErrors('添加失败');
            }
        } else {
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('shop.addGoods', $view);
        }
    }

    // 编辑商品
    public function editGoods(Request $request)
    {
        $id = $request->get('id');

        if ($request->isMethod('POST')) {
            $name = $request->get('name');
            $desc = $request->get('desc');
            $price = round($request->get('price'), 2);
            $labels = $request->get('labels');
            $color = trim($request->get('color', 0));
            $sort = intval($request->get('sort', 0));
            $is_hot = intval($request->get('is_hot', 0));
            $is_limit = intval($request->get('is_limit', 0));
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
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $logo = $move ? '/upload/image/' . $logoName : '';
            }

            DB::beginTransaction();
            try {
                $data = [
                    'name'     => $name,
                    'desc'     => $desc,
                    'price'    => $price * 100,
                    'sort'     => $sort,
                    'color'    => $color,
                    'is_hot'   => $is_hot,
                    'is_limit' => $is_limit,
                    'status'   => $status
                ];

                if ($logo) {
                    $data['logo'] = $logo;
                }

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

        Goods::query()->where('id', $id)->delete();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
    }
}
