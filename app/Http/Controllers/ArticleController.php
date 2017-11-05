<?php

namespace App\Http\Controllers;

use App\Http\Models\Article;
use Illuminate\Http\Request;
use Response;

/**
 * 文章控制器
 * Class SubscribeController
 * @package App\Http\Controllers
 */
class ArticleController extends BaseController
{
    // 文章详情页
    public function index(Request $request)
    {
        $id = $request->get('id');

        $view['info'] = Article::query()->where('is_del', 0)->where('id', $id)->first();
        if (empty($view['info'])) {
            exit('文章已删除');
        }

        return Response::view('article/detail', $view);
    }

}
