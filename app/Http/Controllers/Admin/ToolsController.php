<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ProxyConfig;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\IP;
use DB;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ToolsController extends Controller
{
    use ProxyConfig;

    public function decompile(Request $request): JsonResponse|View
    { // SS(R)链接反解析
        if ($request->isMethod('POST')) {
            $content = $request->input('content');

            if (empty($content)) {
                return response()->json(['status' => 'fail', 'message' => trans('admin.tools.decompile.content_placeholder')]);
            }

            // 反解析处理
            $content = str_replace("\n", ',', $content);
            $content = explode(',', $content);
            $txt = '';
            foreach ($content as $item) {
                // 判断是SS还是SSR链接
                $str = '';
                if (str_contains($item, 'ssr://')) {
                    $str = mb_substr($item, 6);
                } elseif (str_contains($item, 'ss://')) {
                    $str = mb_substr($item, 5);
                }

                $txt .= "\r\n".base64url_decode($str);
            }

            return response()->json(['status' => 'success', 'data' => $txt, 'message' => trans('common.success_item', ['attribute' => trans('admin.tools.decompile.attribute')])]);
        }

        return view('admin.tools.decompile');
    }

    public function convert(Request $request): JsonResponse|View
    { // 格式转换(SS转SSR)
        if ($request->isMethod('POST')) {
            $method = $request->input('method');
            $transfer_enable = $request->input('transfer_enable');
            $protocol = $request->input('protocol');
            $protocol_param = $request->input('protocol_param');
            $obfs = $request->input('obfs');
            $obfs_param = $request->input('obfs_param');
            $content = $request->input('content');

            if (empty($content)) {
                return response()->json(['status' => 'fail', 'message' => trans('admin.tools.convert.content_placeholder')]);
            }

            // 校验格式
            $content = json_decode($content, true);
            if (! isset($content['port_password']) || ! is_array($content['port_password'])) {
                return response()->json(['status' => 'fail', 'message' => trans('admin.tools.convert.missing_error')]);
            }

            // 转换成SSR格式JSON
            $data = [];
            foreach ($content['port_password'] as $port => $passwd) {
                $data[] = [
                    'u' => 0,
                    'd' => 0,
                    'enable' => 1,
                    'method' => $method,
                    'obfs' => $obfs,
                    'obfs_param' => empty($obfs_param) ? '' : $obfs_param,
                    'passwd' => $passwd,
                    'port' => $port,
                    'protocol' => $protocol,
                    'protocol_param' => empty($protocol_param) ? '' : $protocol_param,
                    'transfer_enable' => $transfer_enable,
                    'user' => date('Ymd').'_IMPORT_'.$port,
                ];
            }

            $json = json_encode($data);

            // 生成转换好的JSON文件
            file_put_contents(public_path('downloads/convert.json'), $json);

            return response()->json(['status' => 'success', 'data' => $json, 'message' => trans('common.success_item', ['attribute' => trans('common.convert')])]);
        }

        return view('admin.tools.convert', $this->proxyConfigOptions());
    }

    public function download(Request $request): BinaryFileResponse
    { // 下载转换好的JSON文件
        $type = (int) $request->input('type');
        if (empty($type)) {
            abort(400, trans('admin.tools.convert.params_unknown'));
        }

        if ($type === 1) {
            $filePath = public_path('downloads/convert.json');
        } else {
            $filePath = public_path('downloads/decompile.json');
        }

        if (! file_exists($filePath)) {
            abort(404, trans('admin.tools.convert.file_missing'));
        }

        return response()->download($filePath);
    }

    public function import(Request $request): RedirectResponse|View
    { // 数据导入
        if ($request->isMethod('POST')) {
            if (! $request->hasFile('uploadFile')) {
                return redirect()->back()->withErrors(trans('admin.tools.import.file_required'));
            }

            $file = $request->file('uploadFile');

            // 只能上传JSON文件
            if ($file->getClientMimeType() !== 'application/json' || $file->getClientOriginalExtension() !== 'json') {
                return redirect()->back()->withErrors(trans('admin.tools.import.file_type_error', ['type' => 'JSON']));
            }

            if (! $file->isValid()) {
                return redirect()->back()->withErrors(trans('admin.tools.import.file_error'));
            }

            $save_path = realpath(storage_path('uploads'));
            $new_name = md5($file->getClientOriginalExtension()).'.json';

            try {
                $file->move($save_path, $new_name);
            } catch (Exception $e) {
                Log::error(trans('common.error_action_item', ['action' => trans('common.import'), 'attribute' => trans('admin.menu.tools.import')]).': '.$e->getMessage());

                return redirect()->back()->withErrors(trans('admin.tools.import.file_error'));
            }

            // 读取文件内容
            $file_path = $save_path.'/'.$new_name;
            $data = file_get_contents($file_path);

            // 删除临时文件
            @unlink($file_path);

            $data = json_decode($data, true);
            if (! $data || ! is_array($data)) {
                return redirect()->back()->withErrors(trans('admin.tools.import.format_error', ['type' => 'JSON']));
            }

            try {
                DB::beginTransaction();
                foreach ($data as $user) {
                    User::create([
                        'nickname' => $user['user'] ?? ('User_'.time()),
                        'username' => $user['user'] ?? ('user_'.time().'_'.rand(1000, 9999)),
                        'password' => bcrypt('123456'),
                        'port' => $user['port'] ?? 0,
                        'passwd' => $user['passwd'] ?? '',
                        'vmess_id' => $user['uuid'] ?? '',
                        'transfer_enable' => $user['transfer_enable'] ?? 0,
                        'method' => $user['method'] ?? '',
                        'protocol' => $user['protocol'] ?? '',
                        'obfs' => $user['obfs'] ?? '',
                        'expired_at' => '2099-01-01',
                        'reg_ip' => IP::getClientIp(),
                    ]);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error(trans('common.error_action_item', ['action' => trans('common.import'), 'attribute' => trans('admin.menu.tools.import')]).': '.$e->getMessage());

                return redirect()->back()->withErrors(trans('common.failed_item', ['attribute' => trans('common.import')]).', '.$e->getMessage());
            }

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.import')]));
        }

        return view('admin.tools.import');
    }

    public function analysis(): View
    { // 日志分析
        $file = storage_path('app/ssserver.log');
        if (! file_exists($file)) {
            session()->flash('analysisErrorMsg', trans('admin.tools.analysis.file_missing', ['file_name' => $file]));

            return view('admin.tools.analysis');
        }

        $logs = $this->tail($file, 10000);
        $url = [];
        if ($logs) {
            foreach ($logs as $log) {
                if (str_contains($log, 'TCP connecting')) {
                    continue;
                }

                preg_match('/TCP request (\w+\.){2}\w+/', $log, $tcp_matches);
                if (! empty($tcp_matches)) {
                    $url[] = str_replace('TCP request ', '[TCP] ', $tcp_matches[0]);
                } else {
                    preg_match(
                        '/UDP data to (25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/',
                        $log,
                        $udp_matches
                    );
                    if (! empty($udp_matches)) {
                        $url[] = str_replace('UDP data to ', '[UDP] ', $udp_matches[0]);
                    }
                }
            }
        }

        return view('admin.tools.analysis', ['urlList' => array_unique($url)]);
    }

    private function tail(string $file, int $n, int $base = 5): array|false
    { // 类似Linux中的tail命令
        $fileLines = $this->countLine($file);
        if ($fileLines < 15000) {
            return false;
        }

        $fp = fopen($file, 'rb+');
        assert($n > 0);
        $pos = $n + 1;
        $lines = [];
        $counts = 0;
        while ($counts <= $n) {
            try {
                fseek($fp, -$pos, SEEK_END);
            } catch (Exception) {
                break;
            }

            $pos *= $base;
            while (! feof($fp)) {
                array_unshift($lines, fgets($fp));
                $counts++;
            }
        }

        fclose($fp);

        return array_slice($lines, 0, $n);
    }

    private function countLine(string $file): int
    { // 计算文件行数
        $fp = fopen($file, 'rb');
        $i = 0;
        while (! feof($fp)) {
            // 每次读取2M
            if ($data = fread($fp, 1024 * 1024 * 2)) {
                // 计算读取到的行数
                $num = substr_count($data, "\n");
                $i += $num;
            }
        }

        fclose($fp);

        return $i;
    }
}
