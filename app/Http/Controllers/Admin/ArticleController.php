<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Exception;
use Illuminate\Http\UploadedFile;
use Log;

class ArticleController extends Controller
{
    // 文章列表
    public function index()
    {
        return view('admin.article.index', ['articles' => Article::orderByDesc('sort')->paginate(15)->appends(request('page'))]);
    }

    // 添加文章页面
    public function create()
    {
        return view('admin.article.create');
    }

    // 添加文章
    public function store(ArticleRequest $request)
    {
        $data = $request->validated();
        // LOGO
        if ($data['type'] !== '4' && $request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }

        if ($article = Article::create($data)) {
            return redirect(route('admin.article.edit', $article))->with('successMsg', '添加成功');
        }

        return redirect()->back()->withInput()->withErrors('添加失败');
    }

    // 图片上传
    public function fileUpload(UploadedFile $file)
    {
        $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();

        if (! $file->storeAs('public', $fileName)) {
            return redirect()->back()->withInput()->withErrors('Logo存储失败');
        }

        return 'upload/'.$fileName;
    }

    // 文章页面
    public function show(Article $article)
    {
        return view('admin.article.show', compact('article'));
    }

    // 编辑文章页面
    public function edit(Article $article)
    {
        return view('admin.article.edit', compact('article'));
    }

    // 编辑文章
    public function update(ArticleRequest $request, Article $article)
    {
        $data = $request->validated();
        $data['logo'] = $data['logo'] ?? null;
        // LOGO
        if ($data['type'] !== '4' && $request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if (is_string($path)) {
                $data['logo'] = $path;
            } else {
                return $path;
            }
        }

        if ($article->update($data)) {
            return redirect()->back()->with('successMsg', '编辑成功');
        }

        return redirect()->back()->withInput()->withErrors('编辑失败');
    }

    // 删除文章
    public function destroy(Article $article)
    {
        try {
            $article->delete();
        } catch (Exception $e) {
            Log::error('删除文章失败：'.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => '删除成功']);
    }
}
