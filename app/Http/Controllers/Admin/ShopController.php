<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopStoreRequest;
use App\Http\Requests\Admin\ShopUpdateRequest;
use App\Models\Goods;
use App\Models\GoodsCategory;
use App\Models\Level;
use App\Models\Order;
use Arr;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Log;
use Str;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Goods::query();

        foreach (['type', 'status'] as $field) {
            $request->whenFilled($field, function ($value) use ($query, $field) {
                $query->where($field, $value);
            });
        }

        $goodsList = $query->orderByDesc('status')->paginate(10)->appends($request->except('page'));

        // 优化订单统计查询，使用更高效的方式
        $goodsIds = $goodsList->pluck('id')->toArray();

        // 批量获取订单统计数据
        $orderStats = Order::whereIn('goods_id', $goodsIds)
            ->whereIn('status', [2, 3])
            ->selectRaw('goods_id, is_expire, count(*) as count')
            ->groupBy('goods_id', 'is_expire')
            ->get()
            ->groupBy('goods_id');

        // 为每个商品设置使用统计
        foreach ($goodsList as $goods) {
            $stats = $orderStats->get($goods->id, collect());
            $usedCount = $stats->where('is_expire', 0)->sum('count');
            $totalCount = $stats->sum('count');

            $goods->use_count = $usedCount;
            $goods->total_count = $totalCount;
        }

        return view('admin.shop.index', compact('goodsList'));
    }

    public function store(ShopStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (array_key_exists('traffic_unit', $data)) {
            $data['traffic'] *= $data['traffic_unit'];
            Arr::forget($data, 'traffic_unit');
        }
        $data['is_hot'] = array_key_exists('is_hot', $data) ? 1 : 0;
        $data['status'] = array_key_exists('status', $data) ? 1 : 0;

        // 商品LOGO
        if ($request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }
        try {
            if ($good = Goods::create($data)) {
                return redirect()->route('admin.goods.edit', $good)->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.goods.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage());
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function fileUpload(UploadedFile $file): RedirectResponse|string
    { // 图片上传
        $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();
        if (! $file->storeAs('public', $fileName)) {
            return redirect()->back()->withInput()->withErrors(trans('common.failed_action_item', ['action' => trans('common.store'), 'attribute' => trans('model.goods.logo')]));
        }

        return 'upload/'.$fileName;
    }

    public function create(): View
    {
        return view('admin.shop.info', ['levels' => Level::orderBy('level')->pluck('name', 'id'), 'categories' => GoodsCategory::pluck('name', 'id')]);
    }

    public function edit(Goods $good): View
    {
        return view('admin.shop.info', [
            'good' => $good,
            'levels' => Level::orderBy('level')->pluck('name', 'id'),
            'categories' => GoodsCategory::pluck('name', 'id'),
        ]);
    }

    public function update(ShopUpdateRequest $request, Goods $good): RedirectResponse
    {
        $data = $request->validated();

        // 商品LOGO
        if ($request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }

        try {
            $data['is_hot'] = array_key_exists('is_hot', $data) ? 1 : 0;
            $data['status'] = array_key_exists('status', $data) ? 1 : 0;
            if ($good->update($data)) {
                return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.goods.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage());
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(Goods $good): JsonResponse
    {
        try {
            if ($good->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.goods.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
