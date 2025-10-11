<?php

namespace Tests\Unit\Utils;

use App\Utils\NetworkDetection;
use Exception;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class NetworkDetectionTest extends TestCase
{
    public static function providerDetectionServices(): array
    {
        return [
            'toolsdaquan' => [[
                'name' => 'toolsdaquan',
                'responses' => [
                    'https://www.toolsdaquan.com/toolapi/public/ipchecking*' => '{"success":1,"msg":"检查成功","data":{"tcp":"success","icmp":"success","outside_tcp":"success","outside_icmp":"success"}}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'vps234' => [[
                'name' => 'vps234',
                'responses' => [
                    'https://www.vps234.com/ipcheck/getdata/*' => '{"error":false,"data":{"success":true,"msg":"请求成功","data":{"innerICMP":true,"innerTCP":true,"outICMP":true,"outTCP":true}}}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'idcoffer' => [[
                'name' => 'idcoffer',
                'responses' => [
                    'https://api.24kplus.com/ipcheck*' => '{"code":1,"message":"\u68C0\u67E5\u6210\u529F\uFF01","data":{"ping":true,"tcp":true,"ip":"220.181.7.203","countryClode":"CN"}}',
                    'https://api.idcoffer.com/ipcheck*' => '{"code":1,"message":"\u68C0\u67E5\u6210\u529F\uFF01","data":{"ping":true,"tcp":true,"ip":"220.181.7.203","countryClode":"HK"}}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'ip112' => [[
                'name' => 'ip112',
                'responses' => [
                    'https://api.ycwxgzs.com/ipcheck/index.php' => '{"ip":"220.181.7.203","port":"443","tcp":"<span class=\"mdui-text-color-green\">\u7aef\u53e3\u53ef\u7528<\/span>","icmp":"<span class=\"mdui-text-color-green\">IP\u53ef\u7528<\/span>"}',
                    'https://api.52bwg.com/ipcheck/ipcheck.php' => '{"ip":"220.181.7.203","port":"443","tcp":"<span class=\"mdui-text-color-green\">\u7aef\u53e3\u53ef\u7528<\/span>","icmp":"<span class=\"mdui-text-color-green\">IP\u53ef\u7528<\/span>"}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'upx8' => [[
                'name' => 'upx8',
                'responses' => [
                    'https://api.sm171.com/check-cn.php' => '{"ip":"220.181.7.203","port":"443","tcp":"\u6b63\u5e38","icmp":"\u6b63\u5e38"}',
                    'https://ip.upx8.com/api/check-us.php' => '{"ip":"220.181.7.203","port":"443","tcp":"\u6b63\u5e38","icmp":"\u6b63\u5e38"}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'rss' => [[
                'name' => 'rss',
                'responses' => [
                    'https://ip.rss.ink/api/scan*' => '{"code":200,"data":"","msg":"Ok"}',
                    'https://tcp.mk/api/scan*' => '{"code":200,"data":"","msg":"Ok"}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'vps1352' => [[
                'name' => 'vps1352',
                'responses' => [
                    'https://www.vps1352.com/check.php' => '{"ip":"220.181.7.203","port":"443","tcp":"\u5f00\u653e","icmp":"\u5f00\u653e"}',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
            'selfHost' => [[
                'name' => 'selfHost',
                'config' => ['services.probe.domestic' => 'test_domestic.com|test_token', 'services.probe.foreign' => 'test_foreign.com:8080'],
                'responses' => [
                    'test_domestic.com*' => '[{"ip":"220.181.7.203","icmp":29.627562,"tcp":29.17411}]',
                    'test_foreign.com*' => '[{"ip":"220.181.7.203","icmp":29.627562,"tcp":29.17411}]',
                ],
                'expected' => [
                    'icmp' => 1,
                    'tcp' => 1,
                ],
            ]],
        ];
    }

    /**
     * 测试所有检测服务
     *
     * @dataProvider providerDetectionServices
     */
    public function test_network_detection_services(array $case): void
    {
        // 准备响应
        if (! empty($case['config'])) { // 设置可能存在的假token参数，来激活 API 访问
            foreach ($case['config'] as $k => $v) {
                config([$k => $v]);
            }
        }

        $responses = array_map(static function ($response) {
            return Http::response($response);
        }, $case['responses']);

        // 添加通配符响应以防止意外请求
        $responses['*'] = Http::response(['error' => 'Not found'], 404);

        Http::fake($responses);

        $result = NetworkDetection::networkStatus($case['ip'] ?? '8.8.8.8', $case['port'] ?? 443, $case['name']);

        $this->assertIsArray($result, "Service {$case['name']} should return an array");

        foreach ($case['expected'] as $protocol => $status) {
            $this->assertEquals($status, $result[$protocol], "Service {$case['name']} protocol {$protocol} mismatch");
        }
    }

    /**
     * 测试被墙的情况.
     */
    public function test_network_status_detects_blocked_ips()
    {
        Http::fake([
            'https://www.vps234.com/ipcheck/getdata/*' => Http::response([
                'error' => false,
                'data' => [
                    'success' => true,
                    'msg' => '请求成功',
                    'data' => [
                        'innerICMP' => false,
                        'innerTCP' => false,
                        'outICMP' => true,
                        'outTCP' => true,
                    ],
                ],
            ]),

            '*' => Http::response(['error' => 'Not found'], 404),
        ]);
        $result = NetworkDetection::networkStatus('8.8.8.8', 443, 'vps234');

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['icmp']); // 被墙
        $this->assertEquals(3, $result['tcp']); // 被墙
    }

    /**
     * 测试国外访问异常的情况.
     */
    public function test_network_status_detects_foreign_access_issues()
    {
        Http::fake([
            'https://www.vps234.com/ipcheck/getdata/*' => Http::response([
                'error' => false,
                'data' => [
                    'success' => true,
                    'msg' => '请求成功',
                    'data' => [
                        'innerICMP' => true,
                        'innerTCP' => true,
                        'outICMP' => true,
                        'outTCP' => false,
                    ],
                ],
            ]),

            '*' => Http::response(['error' => 'Not found'], 404),
        ]);

        $result = NetworkDetection::networkStatus('8.8.8.8', 443, 'vps234');

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['icmp']); // 正常
        $this->assertEquals(2, $result['tcp']); // 国外访问异常
    }

    /**
     * 测试服务器宕机的情况.
     */
    public function test_network_status_detects_server_down()
    {
        Http::fake([
            'https://www.vps234.com/ipcheck/getdata/*' => Http::response([
                'error' => false,
                'data' => [
                    'success' => true,
                    'data' => [
                        'innerICMP' => false,
                        'innerTCP' => false,
                        'outICMP' => false,
                        'outTCP' => false,
                    ],
                ],
            ]),

            '*' => Http::response(['error' => 'Not found'], 404),
        ]);

        $result = NetworkDetection::networkStatus('8.8.8.8', 443, 'vps234');

        $this->assertIsArray($result);
        $this->assertEquals(4, $result['icmp']); // 服务器宕机
        $this->assertEquals(4, $result['tcp']); // 服务器宕机
    }

    /**
     * 测试当所有检测服务都失败时返回 null.
     */
    public function test_network_status_returns_null_when_all_services_fail()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Service unavailable'], 500),
        ]);

        $result = NetworkDetection::networkStatus('8.8.8.8', 443);

        $this->assertNull($result);
    }

    /**
     * 测试真实可用的 IP.
     */
    public function test_real_ip_connectivity()
    {
        $successfulRequests = 0;
        $failedRequests = 0;
        $results = [];

        $ip = '220.181.7.203';
        $port = 443;

        foreach (['selfHost', 'vps234', 'idcoffer', 'ip112', 'upx8', 'rss', 'vps1352'] as $service) {
            try {
                $result = NetworkDetection::networkStatus($ip, $port, $service);
                if (is_array($result)) {
                    $successfulRequests++;
                    $results["{$ip}:{$port}-{$service}"] = $result;
                } else {
                    $failedRequests++;
                    $results["{$ip}:{$port}-{$service}"] = 'Failed to get result';
                }
            } catch (Exception $e) {
                $failedRequests++;
                $results["{$ip}:{$port}-{$service}"] = 'Exception: '.$e->getMessage();
            }
        }

        // 输出测试结果摘要
        echo "实际网络连通性测试结果:\n";
        echo "成功请求: {$successfulRequests}\n";
        echo "失败请求: {$failedRequests}\n";
        echo '总请求数: '.($successfulRequests + $failedRequests)."\n\n";

        // 输出详细结果
        foreach ($results as $testName => $result) {
            echo "[{$testName}] - ".json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n";
        }

        $this->assertGreaterThan(0, $successfulRequests, '至少应有一个网络检测请求成功');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 清理 HTTP 假造与缓存
        Http::fake([]);

        // 重置 basicRequest
        $ref = new ReflectionClass(NetworkDetection::class);
        if ($ref->hasProperty('basicRequest')) {
            $prop = $ref->getProperty('basicRequest');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }
}
