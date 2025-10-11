<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use App\Utils\IP;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class IPTest extends TestCase
{
    public static function providerApiCases(): array
    {
        return [
            'ipApi' => [[
                'name' => 'ipApi',
                'endpoint' => '*ip-api.com/json/*',
                'response' => '{"city":"沈阳市","country":"中国","district":"","isp":"China Unicom CHINA169 Network","lat":41.8357,"lon":123.429,"query":"8.8.8.8","regionName":"辽宁","status":"success"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'city' => '沈阳市',
                    'isp' => 'China Unicom CHINA169 Network',
                    'latitude' => 41.8357,
                    'longitude' => 123.429,
                ],
            ]],
            'baidu' => [[
                'name' => 'Baidu',
                'config' => ['services.ip.baidu_ak' => 'fake_baidu_ak'],
                'endpoint' => 'https://api.map.baidu.com/location/ip*',
                'response' => '{"status":0,"address":"CN|辽宁省|沈阳市|None|None|100|65|0","content":{"address":"辽宁省沈阳市","address_detail":{"adcode":"210100","city":"沈阳市","city_code":58,"district":"","province":"辽宁省","street":"","street_number":""},"point":{"x":"123.46466579069178","y":"41.67756788393409"}}}',
                'expected' => [
                    'country' => 'CN',
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                    'latitude' => '41.67756788393409',
                    'longitude' => '123.46466579069178',
                ],
            ]],
            'baiduBce' => [[
                'name' => 'baiduBce',
                'endpoint' => 'https://qifu-api.baidubce.com/ip/geo/v1/district*',
                'response' => '{"code":"Success","data":{"continent":"亚洲","country":"中国","zipcode":"110000","owner":"中国联通","isp":"中国联通","adcode":"210100","prov":"辽宁省","city":"沈阳市","district":""},"ip":"8.8.8.8"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                    'isp' => '中国联通',
                ],
            ]],
            'ipGeoLocation' => [[
                'name' => 'ipGeoLocation',
                'endpoint' => 'https://api.ipgeolocation.io/ipgeo?ip=*',
                'response' => '{"ip":"8.8.8.8","country_name":"中国","country_name_official":"","state_prov":"辽宁","district":"沈阳","city":"沈阳","isp":"China Unicom CHINA169 Network","latitude":"41.79680","longitude":"123.42910"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'city' => '沈阳',
                    'isp' => 'China Unicom CHINA169 Network',
                    'area' => '沈阳',
                    'latitude' => '41.79680',
                    'longitude' => '123.42910',
                ],
            ]],
            'taobao' => [[
                'name' => 'TaoBao',
                'endpoint' => 'https://ip.taobao.com/outGetIpInfo?ip=*',
                'response' => '{"data":{"area":"","country":"中国","isp_id":"100026","queryIp":"103.250.104.0","city":"沈阳","ip":"8.8.8.8","isp":"联通","county":"","region_id":"210000","area_id":"","county_id":null,"region":"辽宁","country_id":"CN","city_id":"210100"},"msg":"query success","code":0}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'city' => '沈阳',
                    'isp' => '联通',
                ],
            ]],
            'speedtest' => [[
                'name' => 'speedtest',
                'endpoint' => 'https://api-v3.speedtest.cn/ip*',
                'response' => '{"code":0,"data":"1z41bLnGrmhViAP9vBtxaTvcnepxEF7nyynwu4VDL1s6YCnSK48PoPFgNf6lDQQ3GV9cmRtDJTrLLrU16eItDnmB+8+3stMtsFBhaLaRH1ece5b+D4lR73Cy1FvaxFXmIfGuPxIOjV/g4Mh4F7GuvEy1gm5/tj9gm4egANOyl3vkzMFvp9tB1ET9PUhaP29DTMQOwxCV4CendJn2LdwF6tP6elucLUoy3xweFC4h2w20oha/GcOiQAxKLB+h6aslydvXqDAzvMmeXRV6e0CQ6A==","\'msg\'":"ok"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'city' => '沈阳',
                    'isp' => '中国联通',
                    'area' => '沈河区',
                    'latitude' => '41.796767',
                    'longitude' => '123.429096',
                ],
            ]],
            'juHe' => [[
                'name' => 'juHe',
                'endpoint' => 'https://apis.juhe.cn/ip/Example/query.php*',
                'response' => '{"resultcode":"200","reason":"success","result":{"Country":"中国","Province":"辽宁","City":"沈阳","District":"","Isp":"联通"},"error_code":0}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'city' => '沈阳',
                    'isp' => '联通',
                ],
            ]],
            'ip2Region' => [[
                'name' => 'ip2Region',
                'ip' => '103.250.104.0',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                    'isp' => '联通',
                ],
            ]],
            'IPDB' => [[
                'name' => 'IPDB',
                'ip' => '103.250.104.0',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁',
                    'isp' => '联通',
                ],
            ]],
            'IPSB' => [[
                'name' => 'IPSB',
                'endpoint' => 'https://api.ip.sb/geoip/*',
                'response' => '{"country":"China","organization":"China Unicom","country_code":"CN","ip":"8.8.8.8","isp":"China Unicom","asn_organization":"CHINA UNICOM China169 Backbone","asn":4837,"offset":28800,"latitude":34.7732,"timezone":"Asia\/Shanghai","continent_code":"AS","longitude":113.722}',
                'expected' => [
                    'country' => 'China',
                    'isp' => 'China Unicom',
                    'latitude' => 34.7732,
                    'longitude' => 113.722,
                ],
            ]],
            'ipinfo' => [[
                'name' => 'ipinfo',
                'endpoint' => 'https://ipinfo.io*',
                'response' => '{"input":"103.250.104.0","data":{"ip":"8.8.8.8","city":"Shenyang","region":"Liaoning","country":"CN","loc":"41.7922,123.4328","org":"AS4837 CHINA UNICOM China169 Backbone","postal":"110000","timezone":"Asia/Shanghai"}}',
                'expected' => [
                    'country' => 'CN',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'AS4837 CHINA UNICOM China169 Backbone',
                    'latitude' => '41.7922',
                    'longitude' => '123.4328',
                ],
            ]],
            'ip234' => [[
                'name' => 'ip234',
                'endpoint' => 'https://ip234.in/search_ip*',
                'response' => '{"code":0,"data":{"asn":4837,"city":"Shenyang","continent":"Asia","continent_code":"AS","country":"china","country_code":"CN","ip":"8.8.8.8","latitude":41.8357,"longitude":123.429,"metro_code":null,"network":"103.250.104.0/22","organization":"CHINA UNICOM China169 Backbone","postal":"210000","region":"Liaoning","timezone":"Asia/Shanghai"},"msg":""}',
                'expected' => [
                    'country' => 'china',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'CHINA UNICOM China169 Backbone',
                    'latitude' => '41.8357',
                    'longitude' => '123.429',
                ],
            ]],
            'dbIP' => [[
                'name' => 'dbIP',
                'endpoint' => 'https://api.db-ip.com/v2/free/*',
                'response' => '{"ipAddress":"8.8.8.8","continentCode":"AS","continentName":"Asia","countryCode":"CN","countryName":"China","stateProv":"Liaoning","city":"Shenyang"}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                ],
            ]],
            'IP2Online' => [[
                'name' => 'IP2Online',
                'config' => ['services.ip.IP2Location_key' => 'fake_ip2location_key'],
                'endpoint' => 'https://api.ip2location.io/*',
                'response' => '{"ip":"8.8.8.8","country_code":"CN","country_name":"China","region_name":"Liaoning","city_name":"Shenyang","latitude":41.79222,"longitude":123.43288,"zip_code":"210000","time_zone":"+08:00","asn":"4837","as":"China Unicom China169 Backbone","is_proxy":false}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'China Unicom China169 Backbone',
                    'latitude' => '41.79222',
                    'longitude' => '123.43288',
                ],
            ]],
            'ipdata' => [[
                'name' => 'ipdata',
                'config' => ['services.ip.ipdata_key' => 'fake_ipdata_key'],
                'endpoint' => 'https://api.ipdata.co/*',
                'response' => '{"ip":"8.8.8.8","city":null,"region":null,"country_name":"China","latitude":34.77320098876953,"longitude":113.72200012207031,"asn":null}',
                'expected' => [
                    'country' => 'China',
                    'latitude' => 34.77320098876953,
                    'longitude' => 113.72200012207031,
                ],
            ]],
            'ipApiCo' => [[
                'name' => 'ipApiCo',
                'endpoint' => 'https://ipapi.co/*/json/*',
                'response' => '{"ip":"8.8.8.8","network":"103.250.104.0/22","version":"IPv4","city":"Shenyang","region":"Liaoning","region_code":"LN","country":"CN","country_name":"China","country_code":"CN","country_code_iso3":"CHN","country_capital":"Beijing","country_tld":".cn","continent_code":"AS","in_eu":false,"postal":null,"latitude":41.79222,"longitude":123.43278,"timezone":"Asia/Shanghai","utc_offset":"+0800","country_calling_code":"+86","currency":"CNY","currency_name":"Yuan Renminbi","languages":"zh-CN,yue,wuu,dta,ug,za","country_area":9596960,"country_population":1411778724,"asn":"AS4837","org":"CHINA UNICOM China169 Backbone"}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'CHINA UNICOM China169 Backbone',
                    'latitude' => 41.79222,
                    'longitude' => 123.43278,
                ],
            ]],
            'ip2Location' => [[
                'name' => 'ip2Location',
                'ip' => '103.250.104.0',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'latitude' => 41.792221,
                    'longitude' => 123.432877,
                ],
            ]],
            'GeoIP2' => [[
                'name' => 'GeoIP2',
                'ip' => '103.250.104.0',
                'expected' => [
                    'country' => 'China',
                    'latitude' => 34.7732,
                    'longitude' => 113.722,
                ],
            ]],
            'ipApiCom' => [[
                'name' => 'ipApiCom',
                'config' => ['services.ip.ipApiCom_acess_key' => 'fake_acess_key'],
                'endpoint' => 'https://api.ipapi.com/api/*',
                'response' => '{"ip": "8.8.8.8", "type": "ipv4", "continent_code": "AS", "continent_name": "Asia", "country_code": "CN", "country_name": "China", "region_code": "LN", "region_name": "Liaoning", "city": "Shenyang", "zip": "110000", "latitude": 41.801021575927734, "longitude": 123.40206909179688, "msa": null, "dma": null, "radius": "0", "ip_routing_type": "fixed", "connection_type": "tx", "location": {"geoname_id": 2034937, "capital": "Beijing", "languages": [{"code": "zh", "name": "Chinese", "native": "\u4e2d\u6587"}], "country_flag": "https://assets.ipstack.com/flags/cn.svg", "country_flag_emoji": "\ud83c\udde8\ud83c\uddf3", "country_flag_emoji_unicode": "U+1F1E8 U+1F1F3", "calling_code": "86", "is_eu": false}}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'latitude' => 41.801021575927734,
                    'longitude' => 123.40206909179688,
                    'address' => 'China Liaoning Shenyang',
                ],
            ]],
            'vore' => [[
                'name' => 'vore',
                'endpoint' => 'https://api.vore.top/api/IPdata*',
                'response' => '{"code":200,"msg":"SUCCESS","ipinfo":{"type":"ipv4","text":"103.250.104.0","cnip":true},"ipdata":{"info1":"辽宁省","info2":"沈阳市","info3":"","isp":"联通"},"adcode":{"o":"辽宁省沈阳市 - 联通","p":"辽宁","c":"沈阳","n":"辽宁-沈阳","r":"辽宁-沈阳","a":"210100","i":true},"tips":"接口由VORE-API(https://api.vore.top/)免费提供","time":1757149038}',
                'expected' => [
                    'country' => '辽宁省',
                    'region' => '沈阳市',
                    'isp' => '联通',
                ],
            ]],
            'ipw_v4' => [[
                'name' => 'ipw',
                'endpoint' => 'https://rest.ipw.cn/api/aw/v1/ipv4*',
                'response' => '{"code":"Success","data":{"continent":"亚洲","country":"中国","zipcode":"110000","timezone":"UTC+8","accuracy":"城市","owner":"中国联通","isp":"中国联通","source":"数据挖掘","areacode":"CN","adcode":"210100","asnumber":"4837","lat":"41.800551","lng":"123.420011","radius":"109.2745","prov":"辽宁省","city":"沈阳市","district":""},"charge":false,"msg":"查询成功","ip":"8.8.8.8","coordsys":"WGS84"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                    'isp' => '中国联通',
                    'latitude' => '41.800551',
                    'longitude' => '123.420011',
                ],
            ]],
            'ipw_v6' => [[
                'name' => 'ipw',
                'ip' => '2408:8207:1850:2a60::4c8',
                'endpoint' => 'https://rest.ipw.cn/api/aw/v1/ipv6*',
                'response' => '{"code":"Success","data":{"continent":"亚洲","country":"日本","zipcode":"167-0033","timezone":"UTC+9","accuracy":"城市","owner":"亚马逊","isp":"亚马逊","source":"数据挖掘","areacode":"JP","adcode":"","asnumber":"16509","lat":"35.713914","lng":"139.616508","radius":"","prov":"东京都","city":"Suginami","district":"","currency_code":"JPY","currency_name":"日元"},"charge":false,"msg":"查询成功","ip":"2408:8207:1850:2a60::4c8","coordsys":"WGS84"}',
                'expected' => [
                    'country' => '日本',
                    'region' => '东京都',
                    'city' => 'Suginami',
                    'isp' => '亚马逊',
                    'latitude' => '35.713914',
                    'longitude' => '139.616508',
                    'address' => '日本 东京都 Suginami',
                ],
            ]],
            'bjjii' => [[
                'name' => 'bjjii',
                'config' => ['services.ip.bjjii_key' => 'fake_acess_key'],
                'endpoint' => 'https://api.bjjii.com/api/ip/query*',
                'response' => '{"code":200,"msg":"请求成功","data":{"ip":"8.8.8.8","info":{"StartIPNum":1744463872,"StartIPText":"103.250.104.0","EndIPNum":1744464895,"EndIPText":"103.250.107.255","Country":"辽宁省沈阳市","Local":"联通","lat":41.835709999999999,"lng":123.42925,"nation":"中国","province":"辽宁省","city":"沈阳市","district":"","adcode":210000,"nation_code":156,"update":"2025-09-06 17:48:39"}},"exec_time":0.023085000000000001,"ip":"117.147.44.132"}',
                'expected' => [
                    'country' => '中国',
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                    'latitude' => 41.83571,
                    'longitude' => 123.42925,
                ],
            ]],
            'pconline' => [[
                'name' => 'pconline',
                'endpoint' => 'https://whois.pconline.com.cn/*',
                'response' => '{"ip":"8.8.8.8","pro":"辽宁省","proCode":"210000","city":"沈阳市","cityCode":"210100","region":"","regionCode":"0","addr":"辽宁省沈阳市 联通","regionNames":"","err":""}',
                'expected' => [
                    'region' => '辽宁省',
                    'city' => '沈阳市',
                ],
            ]],
            'ipApiIO' => [[
                'name' => 'ipApiIO',
                'endpoint' => 'https://ip-api.io/api/v1/ip/*',
                'response' => '{"ip":"8.8.8.8","suspicious_factors":{"is_proxy":false,"is_tor_node":false,"is_spam":false,"is_crawler":false,"is_datacenter":false,"is_vpn":false,"is_threat":false},"location":{"country":"China","country_code":"CN","city":null,"latitude":34.7732,"longitude":113.722,"zip":null,"timezone":"Asia/Shanghai","local_time":"2025-09-07T14:37:47+08:00","local_time_unix":1757227067,"is_daylight_savings":false}}',
                'expected' => [
                    'country' => 'China',
                    'latitude' => 34.7732,
                    'longitude' => 113.722,
                ],
            ]],
            'ipApiIS' => [[
                'name' => 'ipApiIS',
                'endpoint' => 'https://api.ipapi.is/*',
                'response' => '{"ip":"8.8.8.8","asn":{"asn":4837,"abuser_score":"0.001 (Low)","route":"103.250.104.0/22","descr":"CHINA169-BACKBONE CHINA UNICOM China169 Backbone, CN","country":"cn","active":true,"org":"CHINA UNICOM China169 Backbone","domain":"chinaunicom.cn","abuse":"zhaoyz3@chinaunicom.cn","type":"isp","updated":"2024-02-06","rir":"APNIC","whois":"https://api.ipapi.is/?whois=AS4837"},"location":{"is_eu_member":false,"calling_code":"86","currency_code":"CNY","continent":"AS","country":"China","country_code":"CN","state":"Liaoning","city":"Shenyang","latitude":41.79222,"longitude":123.43278,"zip":"110000","timezone":"Asia/Shanghai","local_time":"2025-09-07T14:40:03+08:00","local_time_unix":1757227203,"is_dst":false},"elapsed_ms":1.05}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'CHINA UNICOM China169 Backbone',
                    'latitude' => 41.79222,
                    'longitude' => 123.43278,
                ],
            ]],
            'freeipapi' => [[
                'name' => 'freeipapi',
                'endpoint' => 'https://free.freeipapi.com/*',
                'response' => '{"ipVersion":4,"ipAddress":"8.8.8.8","latitude":41.8357,"longitude":123.429,"countryName":"China","countryCode":"CN","capital":"Beijing","phoneCodes":[86],"timeZones":["Asia\/Shanghai","Asia\/Urumqi"],"zipCode":"210000","cityName":"Shenyang","regionName":"Liaoning","continent":"Asia","continentCode":"AS","currencies":["CNY"],"languages":["zh"],"asn":"4837","asnOrganization":"CHINA UNICOM China169 Backbone","isProxy":false}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'CHINA UNICOM China169 Backbone',
                    'latitude' => 41.8357,
                    'longitude' => 123.429,
                ],
            ]],
            'ipwhois' => [[
                'name' => 'ipwhois',
                'endpoint' => 'https://ipwhois.app/json/*',
                'response' => '{"ip":"8.8.8.8","success":true,"type":"IPv4","continent":"Asia","continent_code":"AS","country":"China","country_code":"CN","country_flag":"https://cdn.ipwhois.io/flags/cn.svg","country_capital":"Beijing","country_phone":"+86","country_neighbours":"AF,BT,HK,IN,KG,KP,KZ,LA,MM,MN,MO,NP,PK,RU,TJ,VN","region":"Liaoning","city":"Shenyang","latitude":41.805699,"longitude":123.431472,"asn":"AS4837","org":"China Unicom Liaoning Province Network","isp":"China Unicom China1 Backbone","timezone":"Asia/Shanghai","timezone_name":"CST","timezone_dstOffset":0,"timezone_gmtOffset":28800,"timezone_gmt":"+08:00","currency":"Chinese Yuan","currency_code":"CNY","currency_symbol":"¥","currency_rates":7.133,"currency_plural":"Chinese yuan"}',
                'expected' => [
                    'country' => 'China',
                    'region' => 'Liaoning',
                    'city' => 'Shenyang',
                    'isp' => 'China Unicom China1 Backbone',
                    'latitude' => 41.805699,
                    'longitude' => 123.431472,
                ],
            ]],
        ];
    }

    public function test_localhost_returns_false_for_loopback_ips(): void
    {
        $this->assertFalse(IP::getIPInfo('127.0.0.1'));
        $this->assertFalse(IP::getIPInfo('::1'));
    }

    public function test_get_client_ip_is_string_or_null(): void
    {
        $ip = IP::getClientIP();
        $this->assertTrue(is_null($ip) || is_string($ip));
    }

    /**
     * @dataProvider providerApiCases
     */
    public function test_get_ip_info_from_each_provider(array $case): void
    {
        App::setLocale($case['locale'] ?? 'zh_CN');

        if (! empty($case['config'])) { // 设置可能存在的假token参数，来激活 API 访问
            foreach ($case['config'] as $k => $v) {
                config([$k => $v]);
            }
        }
        // 模拟HTTP响应
        if (isset($case['response'])) {
            $fakeResponses[$case['endpoint']] = Http::response($case['response']);
        }
        $fakeResponses['*'] = Http::response([], 500);

        Http::fake($fakeResponses);

        $result = IP::getIPInfo($case['ip'] ?? '8.8.8.8', $case['name']);

        $this->assertIsArray($result, "Provider {$case['name']} should return an array");

        foreach ($case['expected'] as $k => $v) {
            $this->assertEquals($v, $result[$k] ?? null, "Provider {$case['name']} field {$k} mismatch");
        }
    }

    public function test_get_ip_info_caches_result_and_prevents_http_calls(): void
    {
        $ip = '9.9.9.9';
        $cached = [
            'country' => 'CachedLand',
            'region' => 'CachedRegion',
            'city' => 'CachedCity',
            'latitude' => 1.23,
            'longitude' => 4.56,
        ];

        // 将结果写入缓存
        Cache::tags('IP_INFO')->put($ip, $cached, now()->addMinutes(10));

        // 伪造 HTTP，如果有请求发生将返回 500（测试应从缓存直接返回）
        Http::fake(['*' => Http::response([], 500)]);

        $result = IP::getIPInfo($ip);

        $this->assertIsArray($result);
        $this->assertEquals('CachedLand', $result['country']);
        // 确保没有执行任何外部 HTTP 请求
        Http::assertNothingSent();
    }

    public function test_get_ip_geo_returns_lat_lon_consistent_with_get_ip_info(): void
    {
        $ip = '5.6.7.8';

        Http::fake([
            'http://ip-api.com/*' => Http::response([
                'status' => 'success',
                'country' => 'GeoTest',
                'lat' => 11.11,
                'lon' => 22.22,
            ]),
            '*' => Http::response([], 500),
        ]);

        // getIPInfo 会缓存并返回完整数据，getIPGeo 只返回 lat/lon
        $info = IP::getIPInfo($ip);
        $geo = IP::getIPGeo($ip);

        $this->assertIsArray($info);
        $this->assertIsArray($geo);
        $this->assertArrayHasKey('latitude', $geo);
        $this->assertArrayHasKey('longitude', $geo);
        $this->assertEquals($info['latitude'], $geo['latitude']);
        $this->assertEquals($info['longitude'], $geo['longitude']);
    }

    public function test_local_database_providers_are_used_when_http_fails(): void
    {
        // 设为中文以便优先走本地库（例如 IPDB / ip2region / ipip 等）
        App::setLocale('zh_CN');
        $ip = '123.123.123.123';

        // 强制 HTTP 全部失败，确保使用本地数据库驱动（已在测试环境通过 eval 注入本地驱动 mock）
        Http::fake(['*' => Http::response([], 500)]);
        Cache::tags('IP_INFO')->forget($ip);

        $result = IP::getIPInfo($ip);

        $this->assertIsArray($result);
        // 这些值依赖于测试时注入的本地 DB mock（原测试中为 中国 / 北京 / 北京市）
        $this->assertEquals('中国', $result['country'] ?? null);
        $this->assertEquals('北京', $result['region'] ?? null);
        $this->assertEquals('北京市', $result['city'] ?? null);
    }

    public function test_get_ip_info_returns_null_when_http_timeout(): void
    {
        App::setLocale('en_US');
        $ip = '2.2.2.2';

        // 模拟超时情况
        Http::fake([
            '*' => function () {
                // 模拟超时，抛出异常或返回超时错误
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $result = IP::getIPInfo($ip, 'ipApi');
        $this->assertNull($result);
    }

    public function test_get_ip_info_returns_null_when_invalid_json_response(): void
    {
        App::setLocale('en_US');
        $ip = '3.3.3.3';

        // 模拟返回无效JSON
        Http::fake([
            'http://ip-api.com/*' => Http::response('Invalid JSON response', 200),
            '*' => Http::response([], 500),
        ]);

        $result = IP::getIPInfo($ip, 'ipApi');
        $this->assertNull($result);
    }

    public function test_get_ip_info_with_specific_checker_returns_null_when_provider_fails(): void
    {
        App::setLocale('en_US');
        $ip = '5.5.5.5';

        // 模拟特定检查器失败，并阻止其他检查器被调用
        Http::fake([
            '*' => Http::response([], 500), // 确保其他所有请求也失败
        ]);

        // 使用指定的checker
        $result = IP::getIPInfo($ip, 'ipApi');
        $this->assertNull($result);
    }

    public function test_real_api_requests(): void
    {
        $testIp = '8.8.8.8'; // 使用一个公共的IP地址进行测试

        $checkers = ['ipApi', 'Baidu', 'baiduBce', 'ipw', 'ipGeoLocation', 'TaoBao', 'speedtest', 'bjjii', 'vore', 'juHe', 'ip2Region', 'IPDB', 'IPSB', 'ipinfo', 'ip234', 'dbIP', 'IP2Online', 'ipdata', 'ipApiCo', 'ip2Location', 'GeoIP2', 'ipApiCom', 'pconline', 'ipApiIO', 'ipApiIS', 'freeipapi', 'ipwhois'];

        $successfulRequests = 0;
        $failedRequests = 0;
        $results = [];

        // 为每个检查器执行实际请求
        foreach ($checkers as $checker) {
            try {
                // 清除之前的缓存
                Cache::tags('IP_INFO')->forget($testIp);

                // 执行实际的API请求
                $result = IP::getIPInfo($testIp, $checker);

                if (is_array($result) && ! empty(array_filter($result))) {
                    $successfulRequests++;
                } else {
                    $failedRequests++;
                }
                $results[$checker] = $result;
            } catch (Exception $e) {
                $failedRequests++;
                echo "Checker {$checker} failed with exception: ".$e->getMessage()."\n";
            }
        }

        // 输出测试结果摘要
        echo "实际API请求测试结果:\n";
        echo "成功请求: {$successfulRequests}\n";
        echo "失败请求: {$failedRequests}\n";
        echo '总请求数: '.($successfulRequests + $failedRequests)."\n";

        $this->assertGreaterThan(0, $successfulRequests, '至少应有一个API请求成功');

        foreach ($results as $checker => $result) {
            echo "[$checker] - ".json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n";
        }
    }

    /**
     * 测试单个API的实际请求
     *
     * @param  string  $checker  检查器名称
     * @param  string  $ip  测试IP地址
     */
    public function test_single_real_api_request(string $checker = 'speedtest', string $ip = '8.8.8.8'): void
    {
        try {
            // 执行实际的API请求
            $result = IP::getIPInfo($ip, $checker);

            // 输出结果
            echo "检查器: {$checker}\n";
            echo "测试IP: {$ip}\n";
            echo '结果: '.json_encode($result, JSON_UNESCAPED_UNICODE)."\n";

            // 验证结果
            if (is_array($result) && ! empty(array_filter($result))) {
                $this->assertIsArray($result);
                echo "测试成功: {$checker} 返回了有效数据\n";
            } else {
                echo "警告: {$checker} 没有返回有效数据\n";
            }
        } catch (Exception $e) {
            echo "检查器 {$checker} 失败，异常信息: ".$e->getMessage()."\n";
            $this->markTestIncomplete("检查器 {$checker} 请求失败: ".$e->getMessage());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 清理 HTTP 假造与缓存
        Http::fake([]);
        Cache::tags('IP_INFO')->flush();

        // 重置 basicRequest
        $ref = new ReflectionClass(IP::class);
        if ($ref->hasProperty('basicRequest')) {
            $prop = $ref->getProperty('basicRequest');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }
}
