<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopStoreRequest;
use App\Http\Requests\Admin\ShopUpdateRequest;
use App\Models\Goods;
use App\Models\Level;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Log;
use Redirect;
use Response;
use Str;

/**
 * 商店控制器.
 *
 * Class ShopController
 */
class ShopController extends Controller
{
    // 商品列表
    public function index(Request $request)
    {
        $type = $request->input('type');
        $status = $request->input('status');

        $query = Goods::query();

        if (isset($type)) {
            $query->whereType($type);
        }

        if (isset($status)) {
            $query->whereStatus($status);
        }

        $view['goodsList'] = $query->whereIs_del(0)->orderByDesc('status')->paginate(10)->appends($request->except('page'));

        return view('admin.shop.index', $view);
    }

    // 添加商品页面
    public function create()
    {
        $view['levelList'] = Level::orderBy('level')->get();

        return view('admin.shop.info', $view);
    }

    // 添加商品
    public function store(ShopStoreRequest $request): RedirectResponse
    {
        try {
            $data = $request->except('_token', 'logo', 'traffic', 'traffic_unit');
            $data['traffic'] = $request->input('traffic') * $request->input('traffic_unit') ?? 1;
            $data['is_hot'] = $request->input('is_hot') ? 1 : 0;
            $data['status'] = $request->input('status') ? 1 : 0;

            // 商品LOGO
            if ($request->hasFile('logo')) {
                $path = $this->fileUpload($request->file('logo'));
                if (is_string($path)) {
                    $data['logo'] = $path;
                } else {
                    return $path;
                }
            }
            $good = Goods::create($data);

            if ($good) {
                return Redirect::route('admin.goods.edit', $good->id)->with('successMsg', '添加成功');
            }
        } catch (Exception $e) {
            Log::error('添加商品信息异常：'.$e->getMessage());

            return Redirect::back()->withInput()->withErrors('添加商品信息失败：'.$e->getMessage());
        }

        return Redirect::back()->withInput()->withErrors('添加商品信息失败');
    }

    // 图片上传
    public function fileUpload(UploadedFile $file)
    {
        $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('public', $fileName);

        if (! $path) {
            return Redirect::back()->withInput()->withErrors('Logo存储失败');
        }

        return 'upload/'.$fileName;
    }

    // 编辑商品页面
    public function edit($id)
    {
        $view['goods'] = Goods::find($id);
        $view['levelList'] = Level::orderBy('level')->get();

        return view('admin.shop.info', $view);
    }

    // 编辑商品
    public function update(ShopUpdateRequest $request, $id)
    {
        $goods = Goods::findOrFail($id);
        $data = $request->except('_token', '_method', 'logo');
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
            $data['is_hot'] = $request->input('is_hot') ? 1 : 0;
            $data['status'] = $request->input('status') ? 1 : 0;

            if ($goods->update($data)) {
                return Redirect::back()->with('successMsg', '编辑成功');
            }
        } catch (Exception $e) {
            Log::error('编辑商品信息失败：'.$e->getMessage());

            return Redirect::back()->withErrors('编辑商品信息失败：'.$e->getMessage());
        }

        return Redirect::back()->withInput()->withErrors('编辑失败');
    }

    // 删除商品
    public function destroy($id): JsonResponse
    {
        try {
            if (Goods::find($id)->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑商品失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑商品失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
