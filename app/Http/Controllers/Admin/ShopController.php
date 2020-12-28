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

        return view('admin.shop.index', ['goodsList' => $query->orderByDesc('status')->paginate(10)->appends($request->except('page'))]);
    }

    // 添加商品页面
    public function create()
    {
        return view('admin.shop.info', ['levels' => Level::orderBy('level')->get()]);
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

            if ($good = Goods::create($data)) {
                return Redirect::route('admin.goods.edit', $good)->with('successMsg', '添加成功');
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
    public function edit(Goods $good)
    {
        return view('admin.shop.info', [
            'good' => $good,
            'levels' => Level::orderBy('level')->get(),
        ]);
    }

    // 编辑商品
    public function update(ShopUpdateRequest $request, Goods $good)
    {
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

            if ($good->update($data)) {
                return Redirect::back()->with('successMsg', '编辑成功');
            }
        } catch (Exception $e) {
            Log::error('编辑商品信息失败：'.$e->getMessage());

            return Redirect::back()->withErrors('编辑商品信息失败：'.$e->getMessage());
        }

        return Redirect::back()->withInput()->withErrors('编辑失败');
    }

    // 删除商品
    public function destroy(Goods $good): JsonResponse
    {
        try {
            if ($good->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑商品失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑商品失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
