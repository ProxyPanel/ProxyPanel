<?php

namespace App\Http\Controllers;

use App\Http\Models\SensitiveWords;
use Illuminate\Http\Request;
use Response;
use Validator;

/**
 * 敏感词管理控制器
 *
 * Class SensitiveWordsController
 *
 * @package App\Http\Controllers
 */
class SensitiveWordsController extends Controller
{
    // 敏感词列表
    public function sensitiveWordsList(Request $request)
    {
        $view['list'] = SensitiveWords::query()->orderBy('id', 'desc')->paginate(15);

        return Response::view('sensitiveWords.sensitiveWordsList', $view);
    }

    // 添加敏感词
    public function addSensitiveWords(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'words' => 'required|unique:sensitive_words'
        ], [
            'words.required' => '添加失败：请填写敏感词',
            'words.unique'   => '添加失败：敏感词已存在'
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => $validator->getMessageBag()->first()]);
        }

        $obj = new SensitiveWords();
        $obj->words = strtolower($request->words);
        $obj->save();
        if ($obj->id) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
        }
    }

    // 删除敏感词
    public function delSensitiveWords(Request $request)
    {
        $result = SensitiveWords::query()->where('id', $request->id)->delete();
        if ($result) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

}
