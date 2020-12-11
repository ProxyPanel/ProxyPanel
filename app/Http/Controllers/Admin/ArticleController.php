<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Log;
use Redirect;
use Response;

class ArticleController extends Controller
{
    // 文章列表
    public function index(Request $request)
    {
        $view['articles'] = Article::orderByDesc('sort')->paginate(15)->appends($request->except('page'));

        return view('admin.article.index', $view);
    }

    // 添加文章页面
    public function create()
    {
        return view('admin.article.create');
    }

    // 添加文章
    public function store(ArticleRequest $request)
    {
        $data = $request->except('_method', '_token');
        // LOGO
        if ($request->input('type') !== '4' && $request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }

        $article = Article::create($data);
        if ($article->id) {
            return Redirect::route('admin.article.edit', $article->id)->with('successMsg', '添加成功');
        }

        return Redirect::back()->withInput()->withErrors('添加失败');
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

    // 文章页面
    public function show($id)
    {
        $view['article'] = Article::find($id);

        return view('admin.article.show', $view);
    }

    // 编辑文章页面
    public function edit($id)
    {
        $view['article'] = Article::find($id);

        return view('admin.article.edit', $view);
    }

    // 编辑文章
    public function update(ArticleRequest $request, $id): RedirectResponse
    {
        $data = $request->except('_method', '_token');
        $data['logo'] = $data['logo'] ?? null;
        // LOGO
        if ($request->input('type') != 4 && $request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }

        if (Article::find($id)->update($data)) {
            return Redirect::back()->with('successMsg', '编辑成功');
        }

        return Redirect::back()->withErrors('编辑失败');
    }

    // 删除文章
    public function destroy($id): JsonResponse
    {
        try {
            Article::find($id)->delete();
        } catch (Exception $e) {
            Log::error('删除文章失败：'.$e->getMessage());

            return Response::json(
                ['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]
            );
        }

        return Response::json(['status' => 'success', 'message' => '删除成功']);
    }
}
