<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Session;

class ToolsController extends Controller {
	// SS(R)链接反解析
	public function decompile(Request $request) {
		if($request->isMethod('POST')){
			$content = $request->input('content');

			if(empty($content)){
				return Response::json(['status' => 'fail', 'message' => '请在左侧填入要反解析的SS(R)链接']);
			}

			// 反解析处理
			$content = str_replace("\n", ",", $content);
			$content = explode(',', $content);
			$txt = '';
			foreach($content as $item){
				// 判断是SS还是SSR链接
				$str = '';
				if(str_contains($item, 'ssr://')){
					$str = mb_substr($item, 6);
				}elseif(str_contains($item, 'ss://')){
					$str = mb_substr($item, 5);
				}

				$txt .= "\r\n".base64url_decode($str);
			}

			// 生成转换好的JSON文件
			file_put_contents(public_path('downloads/decompile.json'), $txt);

			return Response::json(['status' => 'success', 'data' => $txt, 'message' => '反解析成功']);
		}
		return Response::view('admin.tools.decompile');
	}

	// 格式转换(SS转SSR)
	public function convert(Request $request) {
		if($request->isMethod('POST')){
			$method = $request->input('method');
			$transfer_enable = $request->input('transfer_enable');
			$protocol = $request->input('protocol');
			$protocol_param = $request->input('protocol_param');
			$obfs = $request->input('obfs');
			$obfs_param = $request->input('obfs_param');
			$content = $request->input('content');

			if(empty($content)){
				return Response::json(['status' => 'fail', 'message' => '请在左侧填入要转换的内容']);
			}

			// 校验格式
			$content = json_decode($content, true);
			if(empty($content->port_password)){
				return Response::json(['status' => 'fail', 'message' => '转换失败：配置信息里缺少【port_password】字段，或者该字段为空']);
			}

			// 转换成SSR格式JSON
			$data = [];
			foreach($content->port_password as $port => $passwd){
				$data[] = [
					'u'               => 0,
					'd'               => 0,
					'enable'          => 1,
					'method'          => $method,
					'obfs'            => $obfs,
					'obfs_param'      => empty($obfs_param)? "" : $obfs_param,
					'passwd'          => $passwd,
					'port'            => $port,
					'protocol'        => $protocol,
					'protocol_param'  => empty($protocol_param)? "" : $protocol_param,
					'transfer_enable' => toGB($transfer_enable),
					'user'            => date('Ymd').'_IMPORT_'.$port,
				];
			}

			$json = json_encode($data);

			// 生成转换好的JSON文件
			file_put_contents(public_path('downloads/convert.json'), $json);

			return Response::json(['status' => 'success', 'data' => $json, 'message' => '转换成功']);
		}

		// 加密方式、协议、混淆
		$view['methodList'] = Helpers::methodList();
		$view['protocolList'] = Helpers::protocolList();
		$view['obfsList'] = Helpers::obfsList();

		return Response::view('admin.tools.convert', $view);
	}

	// 下载转换好的JSON文件
	public function download(Request $request) {
		$type = $request->input('type');
		if(empty($type)){
			exit('参数异常');
		}

		if($type == '1'){
			$filePath = public_path('downloads/convert.json');
		}else{
			$filePath = public_path('downloads/decompile.json');
		}

		if(!file_exists($filePath)){
			exit('文件不存在，请检查目录权限');
		}

		return Response::download($filePath);
	}

	// 数据导入
	public function import(Request $request) {
		if($request->isMethod('POST')){
			if(!$request->hasFile('uploadFile')){
				Session::flash('errorMsg', '请选择要上传的文件');

				return Redirect::back();
			}

			$file = $request->file('uploadFile');

			// 只能上传JSON文件
			if($file->getClientMimeType() !== 'application/json' || $file->getClientOriginalExtension() !== 'json'){
				Session::flash('errorMsg', '只允许上传JSON文件');

				return Redirect::back();
			}

			if(!$file->isValid()){
				Session::flash('errorMsg', '产生未知错误，请重新上传');

				return Redirect::back();
			}

			$save_path = realpath(storage_path('uploads'));
			$new_name = md5($file->getClientOriginalExtension()).'.json';
			$file->move($save_path, $new_name);

			// 读取文件内容
			$data = file_get_contents($save_path.'/'.$new_name);
			$data = json_decode($data, true);
			if(!$data){
				Session::flash('errorMsg', '内容格式解析异常，请上传符合SSR(R)配置规范的JSON文件');

				return Redirect::back();
			}

			try{
				DB::beginTransaction();
				foreach($data as $user){
					$obj = new User();
					$obj->username = $user->user;
					$obj->email = $user->user;
					$obj->password = Hash::make('123456');
					$obj->port = $user->port;
					$obj->passwd = $user->passwd;
					$obj->vmess_id = $user->uuid;
					$obj->transfer_enable = $user->transfer_enable;
					$obj->method = $user->method;
					$obj->protocol = $user->protocol;
					$obj->obfs = $user->obfs;
					$obj->enable_time = date('Y-m-d');
					$obj->expire_time = '2099-01-01';
					$obj->reg_ip = getClientIp();
					$obj->created_at = date('Y-m-d H:i:s');
					$obj->updated_at = date('Y-m-d H:i:s');
					$obj->save();
				}

				DB::commit();
			}catch(Exception $e){
				DB::rollBack();

				Session::flash('errorMsg', '出错了，可能是导入的配置中有端口已经存在了');

				return Redirect::back();
			}

			Session::flash('successMsg', '导入成功');

			return Redirect::back();
		}

		return Response::view('admin.tools.import');
	}

	// 日志分析
	public function analysis(): \Illuminate\Http\Response {
		$file = storage_path('app/ssserver.log');
		if(!file_exists($file)){
			Session::flash('analysisErrorMsg', $file.' 不存在，请先创建文件');

			return Response::view('admin.tools.analysis');
		}

		$logs = $this->tail($file, 10000);
		if(false === $logs){
			$view['urlList'] = [];
		}else{
			$url = [];
			foreach($logs as $log){
				if(str_contains($log, 'TCP connecting')){
					continue;
				}

				preg_match('/TCP request (\w+\.){2}\w+/', $log, $tcp_matches);
				if(!empty($tcp_matches)){
					$url[] = str_replace('TCP request ', '[TCP] ', $tcp_matches[0]);
				}else{
					preg_match('/UDP data to (25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/',
						$log, $udp_matches);
					if(!empty($udp_matches)){
						$url[] = str_replace('UDP data to ', '[UDP] ', $udp_matches[0]);
					}
				}
			}

			$view['urlList'] = array_unique($url);
		}

		return Response::view('admin.tools.analysis', $view);
	}
}
