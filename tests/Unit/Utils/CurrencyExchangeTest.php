<?php

namespace Tests\Unit\Utils;

use App\Utils\CurrencyExchange;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class CurrencyExchangeTest extends TestCase
{
    public static function providerExchangeServices(): array
    {
        return [
            'exchangerateApi_free' => [[
                'name' => 'exchangerateApi',
                'endpoint' => 'https://open.er-api.com/v6/latest/*',
                'response' => '{"result":"success","provider":"https://www.exchangerate-api.com","documentation":"https://www.exchangerate-api.com/docs/free","terms_of_use":"https://www.exchangerate-api.com/terms","time_last_update_unix":1757462551,"time_last_update_utc":"Wed, 10 Sep 2025 00:02:31 +0000","time_next_update_unix":1757550141,"time_next_update_utc":"Thu, 11 Sep 2025 00:22:21 +0000","time_eol_unix":0,"base_code":"USD","rates":{"USD":1,"BYN":3.233971,"BZD":2,"CAD":1.383268,"CDF":2878.431948,"CHF":0.79644,"CLP":971.373897,"CNY":7.124079}}',
                'expected' => 7.12,
            ]],
            'exchangerateApi_paid' => [[
                'name' => 'exchangerateApi',
                'config' => ['services.currency.exchangerate-api_key' => 'fake_key'],
                'endpoint' => 'https://v6.exchangerate-api.com/v6/*',
                'response' => '{"result":"success","documentation":"https://www.exchangerate-api.com/docs","terms_of_use":"https://www.exchangerate-api.com/terms","time_last_update_unix":1757462401,"time_last_update_utc":"Wed, 10 Sep 2025 00:00:01 +0000","time_next_update_unix":1757548801,"time_next_update_utc":"Thu, 11 Sep 2025 00:00:01 +0000","base_code":"USD","target_code":"CNY","conversion_rate":7.1241}',
                'expected' => 7.12,
            ]],
            'k780' => [[
                'name' => 'k780',
                'endpoint' => 'https://sapi.k780.com/?app=finance.rate&scur=USD&tcur=CNY&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json',
                'response' => '{"success":"1","result":{"status":"ALREADY","scur":"USD","tcur":"CNY","ratenm":"美元/人民币","rate":"7.1206","update":"2025-09-10 22:47:01"}}',
                'expected' => 7.12,
            ]],
            'it120' => [[
                'name' => 'it120',
                'config' => ['services.currency.it120_key' => 'fake_key'],
                'endpoint' => 'https://api.it120.cc/*',
                'response' => '{"code":0,"data":{"rate":6.5749,"toCode":657.49,"fromCode":100},"msg":"success"}',
                'expected' => 6.57,
            ]],
            'fixer' => [[
                'name' => 'fixer',
                'config' => ['services.currency.apiLayer_key' => 'fake_key'],
                'endpoint' => 'https://api.apilayer.com/fixer/latest*',
                'response' => '{"success":true,"timestamp":1757522110,"base":"USD","date":"2025-09-10","rates":{"CNY":7.121499}}',
                'expected' => 7.12,
            ]],
            'currencyData' => [[
                'name' => 'currencyData',
                'config' => ['services.currency.apiLayer_key' => 'fake_key'],
                'endpoint' => 'https://api.apilayer.com/currency_data/live*',
                'response' => '{"success":true,"timestamp":1757522348,"source":"USD","quotes":{"USDCNY":7.121498}}',
                'expected' => 7.12,
            ]],
            'exchangeRatesData' => [[
                'name' => 'exchangeRatesData',
                'config' => ['services.currency.apiLayer_key' => 'fake_key'],
                'endpoint' => 'https://api.apilayer.com/exchangerates_data/latest*',
                'response' => '{"success":true,"timestamp":1757564885,"base":"USD","date":"2025-09-11","rates":{"CNY":7.12125}}',
                'expected' => 7.12,
            ]],
            'jsdelivrFile' => [[
                'name' => 'jsdelivrFile',
                'endpoint' => 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/*',
                'response' => '{"date":"2025-09-10","usd":{"cfx":5.80551999,"chf":0.79810103,"chz":23.57267514,"clp":967.40244215,"cnh":7.1267651,"cny":7.12926824,"comp":0.022938216,"cop":3922.09820874,"crc":505.17600184,"cro":3.94111354,"crv":1.28387416,"cspr":103.9529098,"cuc":1,"cup":24.06001705,"cve":94.26691294,"cvx":0.28371305,"cyp":0.5003353,"czk":20.81016181,"dai":0.9997095,"dash":0.040430194}}',
                'expected' => 7.12,
            ]],
            'duckduckgo' => [[
                'name' => 'duckduckgo',
                'endpoint' => 'https://duckduckgo.com/js/spice/currency_convert/1/*',
                'response' => '{"terms":"https://www.xe.com/legal/","privacy":"http://www.xe.com/privacy.php","from":"USD","amount":1.0,"timestamp":"2025-09-11T04:44:00Z","to":[{"quotecurrency":"CNY","mid":7.1209786645}]}',
                'expected' => 7.12,
            ]],
            'wise' => [[
                'name' => 'wise',
                'endpoint' => 'https://api.wise.com/v1/rates*',
                'response' => '[{"rate":7.12105,"source":"USD","target":"CNY","time":"2025-09-11T04:47:28+0000"}]',
                'expected' => 7.12,
            ]],
            'xRates' => [[
                'name' => 'xRates',
                'endpoint' => 'https://www.x-rates.com/calculator/*',
                'response' => '<div class="ccOutputBx"><span class="ccOutputTxt">1.00 USD =</span><span class="ccOutputRslt">7.121<span class="ccOutputTrail">208</span><span class="ccOutputCode"> CNY</span></span></div><span class="calOutputTS">Sep 11, 2025 05:21 UTC</span>',
                'expected' => 7.12,
            ]],
            'valutafx' => [[
                'name' => 'valutafx',
                'endpoint' => 'https://www.valutafx.com/api/v2/rates/lookup*',
                'response' => '{"Amount":1,"Rate":7.1218,"UpdatedDateTimeUTC":"2025-09-11T06:15:00","FormattedResult":"= <span class=\"converter-result-to\">7.1218 CNY</span>","FormattedRates":"<span class=\"converter-rate-from\">1 USD</span> = <span class=\"converter-rate-to\">7.1218 CNY</span>","FormattedIndirectRates":"<span class=\"converter-rate-to\">1 CNY</span> = <span class=\"converter-rate-from\">0.14041 USD</span>","FormattedDateTime":"Last update <span class=\"converter-last-updated-value\">2025-09-11 6:15 AM UTC</span>","ErrorMessage":null}',
                'expected' => 7.12,
            ]],
            'unionpay' => [[
                'name' => 'unionpay',
                'endpoint' => 'https://www.unionpayintl.com/upload/jfimg/*',
                'response' => '{"exchangeRateJson":[{"transCur":"AED","baseCur":"AUD","rateData":0.41397204},{"transCur":"TJS","baseCur":"CNY","rateData":0.75934168},{"transCur":"TMT","baseCur":"CNY","rateData":2.0464021},{"transCur":"TND","baseCur":"CNY","rateData":2.45719918},{"transCur":"TOP","baseCur":"CNY","rateData":2.99489085},{"transCur":"UGX","baseCur":"CNY","rateData":0.00204028},{"transCur":"USD","baseCur":"CNY","rateData":7.1399},{"transCur":"UYU","baseCur":"CNY","rateData":0.17892278},{"transCur":"UZS","baseCur":"CNY","rateData":0.00058941}],"curDate":"2025-09-11"}',
                'expected' => 7.13,
            ]],
            'baidu' => [[
                'name' => 'baidu',
                'endpoint' => 'https://finance.pae.baidu.com/vapi/async/v1*',
                'response' => '{"DispExt":null,"QueryDispInfo":null,"ResultCode":0,"ResultNum":2,"QueryID":"162386106721317024","Result":[{"ClickNeed":"1","Degree":"0","DisplayData":{"StdCls":"2","StdStg":"5293","StdStl":"2","resultData":{"extData":{"OriginQuery":"","resourceid":"5293","tplt":"exrate"},"tplData":{"StdCls":"2","StdStg":"5293","StdStl":"2","card_order":"1","content1":"1美元=7.12270000人民币","content2":"1人民币=0.14039700美元","money1":"美元","money1_num":"1","money1_rev":"1","money2":"人民币","money2_num":"7.12270000","money2_rev":"0.140397","pk":[],"sigma_use":"1","strong_use":"1","templateName":"exrate","template_type":"1","text":"更新时间：2025-09-11 15:23 数据仅供参考"}},"strategy":{"ctplOrPhp":"1","hilightWord":"","precharge":"0","tempName":"unitstatic"}},"RecoverCacheTime":"0","Sort":"1","SrcID":"5293","SubResNum":"0","SubResult":[],"SuppInfo":"汇率换算","Title":"汇率换算","Weight":"3"}]}',
                'expected' => 7.13,
            ]],
        ];
    }

    /**
     * @dataProvider providerExchangeServices
     */
    public function test_currency_exchange_services(array $case): void
    {
        // 设置配置
        if (isset($case['config'])) {
            foreach ($case['config'] as $key => $value) {
                config([$key => $value]);
            }
        }

        // 模拟HTTP响应
        if (isset($case['response'])) {
            $fakeResponses[$case['endpoint']] = Http::response($case['response']);
        }
        $fakeResponses['*'] = Http::response([], 500);

        Http::fake($fakeResponses);

        $result = CurrencyExchange::getCurrencyRate($case['target'] ?? 'CNY', $case['base'] ?? 'USD', $case['name']);

        $this->assertEqualsWithDelta(
            $case['expected'],
            $result,
            0.01,
            "Currency exchange service {$case['name']} failed"
        );
    }

    public function test_currency_exchange_with_cache(): void
    {
        Cache::put('Currency_USD_CNY_ExRate', 6.5, 3600);

        $result = CurrencyExchange::getCurrencyRate('CNY', 'USD');

        $this->assertEquals(6.5, $result);
        // 验证没有进行HTTP请求
        Http::assertNothingSent();
    }

    public function test_currency_exchange_fallback(): void
    {
        // 模拟所有API都失败
        Http::fake([
            '*' => Http::response('Not Found', 404),
        ]);

        $result = CurrencyExchange::getCurrencyRate('CNY', 'USD');

        $this->assertNull($result);
    }

    public function test_convert_method(): void
    {
        Cache::put('Currency_USD_CNY_ExRate', 6.5, 3600);

        $result = CurrencyExchange::convert('CNY', 100, 'USD');

        $this->assertEquals(650.0, $result);
    }

    public function test_real_api_requests(): void
    {
        $target = 'CNY';
        $base = 'USD';

        $services = ['exchangerateApi', 'k780', 'it120', 'fixer', 'currencyData', 'exchangeRatesData',
            'jsdelivrFile', 'duckduckgo', 'wise', 'xRates', 'valutafx', 'unionpay', 'baidu'];

        $successfulRequests = 0;
        $failedRequests = 0;
        $results = [];

        foreach ($services as $service) {
            try {
                $result = CurrencyExchange::getCurrencyRate($target, $base, $service);

                if (is_numeric($result)) {
                    $successfulRequests++;
                } else {
                    $failedRequests++;
                }

                $results[$service] = $result;
            } catch (Exception $e) {
                $failedRequests++;
                echo "Service {$service} failed with exception: ".$e->getMessage()."\n";
            }
        }

        // 输出测试结果摘要
        echo "实际汇率API请求测试结果:\n";
        echo "成功请求: {$successfulRequests}\n";
        echo "失败请求: {$failedRequests}\n";
        echo '总请求数: '.($successfulRequests + $failedRequests)."\n\n";

        // 输出详细结果
        foreach ($results as $service => $result) {
            echo "[{$service}] - ".json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n";
        }

        $this->assertGreaterThan(0, $successfulRequests, '至少应有一个汇率API请求成功');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 清理 HTTP 假造与缓存
        Http::fake([]);
        Cache::flush();

        // 重置 basicRequest
        $ref = new ReflectionClass(CurrencyExchange::class);
        if ($ref->hasProperty('basicRequest')) {
            $prop = $ref->getProperty('basicRequest');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }
}
