<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class PingController extends Controller
{
    public function ping(Request $request)
    {
        $host = $request->input('host');
        $port = $request->input('port', 22);
        $transport = $request->input('transport', 'tcp');
        $timeout = $request->input('timeout', 0.5);

        if (empty($host)) {
            echo "<pre>";
            echo "使用方法：";
            echo "<br>";
            echo "GET /api/ping?host=www.baidu.com&port=80&transport=tcp&timeout=0.5";
            echo "<br>";
            echo "host：检测地址，必传，可以是域名、IPv4、IPv6";
            echo "<br>";
            echo "port：检测端口，可不传，默认22";
            echo "<br>";
            echo "transport：检测协议，可不传，默认tcp，可以是tcp、udp";
            echo "<br>";
            echo "timeout：检测超时，单位秒，可不传，默认0.5秒，建议不超过3秒";
            echo "<br>";
            echo "成功返回：1，失败返回：0";
            echo "</pre>";
            exit();
        }

        // 如果不是IPv4
        if (false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // 如果是IPv6
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $host = '[' . $host . ']';
            } else {
                $host = gethostbyname($host);
            }
        }

        $transport = $transport . '://';

        try {
            $fp = stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, $timeout);
            if (!$fp) {
                Log::info("$errstr ($errno)");
                $ret = 0;
            } else {
                $ret = 1;
            }

            fclose($fp);

            return response()->json(['status' => $ret]);
        } catch (\Exception $e) {
            Log::info($e);

            return response()->json(['status' => 0]);
        }
    }
}
