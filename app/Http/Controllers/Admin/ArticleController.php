<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Redirect;
use Response;
use Session;

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
        if ($request->input('type') !== "4" && $request->hasFile('logo')) {
            $data['logo'] = 'upload/'.$request->file('logo')->store('images');
            if (!$data['logo']) {
                Session::flash('errorMsg', 'LOGO不合法');

                return Redirect::back()->withInput();
            }
        }

        $article = Article::create($data);
        if ($article->id) {
            Session::flash('successMsg', '添加成功');
        } else {
            Session::flash('errorMsg', '添加失败');
        }

        return Redirect::to(route('admin.article.edit', $article->id));
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
            $data['logo'] = 'upload/'.$request->file('logo')->store('images');
            if (!$data['logo']) {
                Session::flash('errorMsg', 'LOGO不合法');

                return Redirect::back()->withInput();
            }
        }

        if (Article::find($id)->update($data)) {
            Session::flash('successMsg', '编辑成功');
        } else {
            Session::flash('errorMsg', '编辑失败');
        }

        return Redirect::to(route('admin.article.edit', $id));
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
