<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleService;
use App\Services\NodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function index(NodeService $nodeService): View
    { // 帮助中心
        $subscribe = auth()->user()->subscribe;

        return view('user.knowledge', [
            'subType' => $nodeService->getActiveNodeTypes(),
            'subUrl' => route('sub', $subscribe->code),
            'subStatus' => $subscribe->status,
            'subMsg' => $subscribe->ban_desc,
            'knowledge' => Article::type(1)->lang()->orderByDesc('sort')->latest()->get()->groupBy('category'),
        ]);
    }

    public function show(Article $article): JsonResponse
    { // 公告详情
        $articleService = new ArticleService($article);

        return response()->json(['title' => $article->title, 'content' => $articleService->getContent()]);
    }
}
