<?php

namespace App\Http\Controllers;

use App\Http\Models\SensitiveWords;
use Illuminate\Http\Request;
use Response;

/**
 * 敏感词管理控制器
 * Class SensitiveWordsController
 *
 * @package App\Http\Controllers
 */
class SensitiveWordsController extends Controller
{
    // 敏感词列表
    public function sensitiveWordsList(Request $request)
    {
        $view['list'] = SensitiveWords::query()->paginate(15);

        return Response::view('sensitiveWords/sensitiveWordsList', $view);
    }

    // 添加敏感词
    public function addSensitiveWords(Request $request)
    {
        $sensitiveWords = SensitiveWords::query()->where('words', trim($request->input('words')))->first();
        if ($sensitiveWords) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败：敏感词已存在']);
        }

        $obj = new SensitiveWords();
        $obj->words = trim($request->input('words'));
        $result = $obj->save();
        if ($result) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
        }
    }

}
