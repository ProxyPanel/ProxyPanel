<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Log;
use Str;

class ArticleController extends Controller
{
    public function index(Request $request): View
    { // 文章列表
        $articles = Article::query();

        foreach (['id', 'category', 'language', 'type'] as $field) {
            $request->whenFilled($field, function ($value) use ($articles, $field) {
                $articles->where($field, $value);
            });
        }

        return view('admin.article.index', ['articles' => $articles->latest()->orderByDesc('sort')->paginate()->appends($request->except('page')), 'categories' => Article::whereNotNull('category')->distinct()->pluck('category', 'category')]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    { // 添加文章
        $data = $request->validated();

        try {
            if ($data['type'] !== '4' && $request->hasFile('logo')) {
                $path = $this->fileUpload($request->file('logo'));
                if ($path === false) {
                    return redirect()->back()->withInput()->withErrors(trans('common.failed_action_item', ['action' => trans('common.store'), 'attribute' => trans('model.article.logo')]));
                }
                $data['logo'] = $path;
            }

            if ($article = Article::create($data)) {
                return redirect(route('admin.article.edit', $article))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.article.attribute')]).': '.$e->getMessage());

            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function fileUpload(UploadedFile $file): string|bool
    {
        $fileName = Str::random(8).time().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('public', $fileName) ? 'upload/'.$fileName : false;
    }

    public function create(): View
    { // 添加文章页面
        return view('admin.article.info', ['categories' => Article::whereNotNull('category')->distinct()->pluck('category')]);
    }

    public function show(Article $article): View
    { // 文章页面
        $article->content = (new ArticleService($article))->getContent();

        return view('admin.article.show', compact('article'));
    }

    public function edit(Article $article): View
    { // 编辑文章页面
        $categories = Article::whereNotNull('category')->distinct()->pluck('category');

        return view('admin.article.info', compact('article', 'categories'));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    { // 编辑文章
        $data = $request->validated();

        if ($data['type'] !== '4' && $request->hasFile('logo')) {
            $path = $this->fileUpload($request->file('logo'));
            if ($path === false) {
                return redirect()->back()->withInput()->withErrors(trans('common.failed_action_item', ['action' => trans('common.store'), 'attribute' => trans('model.article.logo')]));
            }
            $data['logo'] = $path;
        } elseif (! $request->has('logo')) {
            $data['logo'] = $article->logo;
        }

        if ($article->update($data)) {
            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(Article $article): JsonResponse
    { // 删除文章
        try {
            $article->delete();
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.article.attribute')]).', '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
    }
}
