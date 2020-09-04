<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

/**
 * 邮箱后缀管理控制器
 *
 * Class EmailFilterController
 *
 * @package App\Http\Controllers\Controller
 */
class EmailFilterController extends Controller {
	// 邮箱过滤列表
	public function filterList() {
		$view['list'] = EmailFilter::orderByDesc('id')->paginate(15);

		return view('admin.config.emailFilter', $view);
	}

	// 添加邮箱后缀
	public function addSuffix(Request $request): ?JsonResponse {
		$validator = Validator::make($request->all(), [
			'words' => 'required|unique:sensitive_words'
		], [
			'words.required' => '添加失败：请填写邮箱后缀',
			'words.unique'   => '添加失败：邮箱后缀已存在'
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
		}

		$obj = new EmailFilter();
		$obj->type = $request->input('type');
		$obj->words = strtolower($request->input('words'));
		$obj->save();
		if($obj->id){
			return Response::json(['status' => 'success', 'message' => '添加成功']);
		}

		return Response::json(['status' => 'fail', 'message' => '添加失败']);
	}

	// 删除邮箱后缀
	public function delSuffix(Request $request): ?JsonResponse {
		$result = EmailFilter::whereId($request->input('id'))->delete();
		if($result){
			return Response::json(['status' => 'success', 'message' => '删除成功']);
		}

		return Response::json(['status' => 'fail', 'message' => '删除失败']);
	}
}
