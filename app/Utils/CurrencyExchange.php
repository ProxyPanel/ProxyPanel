<?php

namespace App\Utils;

use Cache;
use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Log;

class CurrencyExchange
{
    private static PendingRequest $basicRequest;

    private static array $apis = ['fixer', 'exchangerateApi', 'wise', 'currencyData', 'exchangeRatesData', 'duckduckgo', 'wsj', 'xRates', 'valutafx', 'baidu', 'unionpay', 'jsdelivrFile', 'it120', 'k780'];

    /**
     * @param  string  $target  target Currency
     * @param  float|int  $amount  exchange amount
     * @param  string|null  $base  Base Currency
     * @return float|null amount in target currency
     */
    public static function convert(string $target, float|int $amount, ?string $base = null): ?float
    {
        $base = $base ?? (string) sysConfig('standard_currency');
        $cacheKey = "Currency_{$base}_{$target}_ExRate";

        if (Cache::has($cacheKey)) {
            return round($amount * Cache::get($cacheKey), 2);
        }
        self::setClient();

        foreach (self::$apis as $api) {
            try {
                $rate = self::$api($base, $target);
                if ($rate !== null) {
                    Cache::put($cacheKey, $rate, Day);

                    return round($amount * $rate, 2);
                }
            } catch (Exception $e) {
                Log::error("[$api] 币种汇率信息获取报错: ".$e->getMessage());
            }
        }

        return null;
    }

    private static function setClient(): void
    {
        self::$basicRequest = Http::timeout(15)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
    }

    public static function unionTest(string $target, ?string $base = null): void
    {
        $base = $base ?? (string) sysConfig('standard_currency');
        self::setClient();
        foreach (self::$apis as $api) {
            try {
                echo $api.': '.self::$api($base, $target).PHP_EOL;
            } catch (Exception $e) {
                echo $api.': error'.PHP_EOL;
                Log::error("[$api] 币种汇率信息获取报错: ".$e->getMessage());

                continue;
            }
        }
    }

    private static function exchangerateApi(string $base, string $target): ?float
    { // Reference: https://www.exchangerate-api.com/docs/php-currency-api
        $key = config('services.currency.exchangerate-api_key');
        $url = $key ? "https://v6.exchangerate-api.com/v6/$key/pair/$base/$target" : "https://open.er-api.com/v6/latest/$base";

        $response = self::$basicRequest->get($url);
        if ($response->ok()) {
            $data = $response->json();

            if ($data['result'] === 'success') {
                return $key ? $data['conversion_rate'] : $data['rates'][$target];
            }
            Log::emergency('[CurrencyExchange]exchangerateApi exchange failed with following message: '.$data['error-type'] ?? '');
        } else {
            Log::emergency('[CurrencyExchange]exchangerateApi request failed '.var_export($response, true));
        }

        return null;
    }

    private static function k780(string $base, string $target): ?float
    { // Reference: https://www.nowapi.com/api/finance.rate
        $response = self::$basicRequest->get("https://sapi.k780.com/?app=finance.rate&scur=$base&tcur=$target&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['success'] === '1') {
                return $data['result']['rate'];
            }
            Log::emergency('[CurrencyExchange]Nowapi exchange failed with following message: '.$data['msg']);
        } else {
            Log::emergency('[CurrencyExchange]Nowapi request failed'.var_export($response, true));
        }

        return null;
    }

    private static function it120(string $base, string $target): ?float
    { // Reference: https://www.it120.cc/help/fnun8g.html
        $key = config('services.currency.it120_key');
        if ($key) {
            $response = self::$basicRequest->get("https://api.it120.cc/$key/forex/rate?fromCode=$target&toCode=$base");
            if ($response->ok()) {
                $data = $response->json();

                if ($data['code'] === 0) {
                    return $data['data']['rate'];
                }
                Log::emergency('[CurrencyExchange]it120 exchange failed with following message: '.$data['msg']);
            } else {
                Log::emergency('[CurrencyExchange]it120 request failed'.var_export($response, true));
            }
        }

        return null;
    }

    private static function fixer(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/fixer-api RATE LIMIT: 100 Requests / Monthly!!!!
        $key = config('services.currency.apiLayer_key');
        if ($key) {
            $response = self::$basicRequest->withHeader('apikey', $key)->get("https://api.apilayer.com/fixer/latest?symbols=$target&base=$base");
            if ($response->ok()) {
                $data = $response->json();

                if ($data['success']) {
                    return $data['rates'][$target];
                }

                Log::emergency('[CurrencyExchange]Fixer exchange failed with following message: '.$data['error']['type'] ?? '');
            } else {
                Log::emergency('[CurrencyExchange]Fixer request failed'.var_export($response, true));
            }
        }

        return null;
    }

    private static function currencyData(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/currency_data-api RATE LIMIT: 100 Requests / Monthly
        $key = config('services.currency.apiLayer_key');
        if ($key) {
            $response = self::$basicRequest->withHeader('apikey', $key)->get("https://api.apilayer.com/currency_data/live?source=$base&currencies=$target");
            if ($response->ok()) {
                $data = $response->json();

                if ($data['success']) {
                    return $data['quotes'][$base.$target];
                }

                Log::emergency('[CurrencyExchange]Currency Data exchange failed with following message: '.$data['error']['info'] ?? '');
            } else {
                Log::emergency('[CurrencyExchange]Currency Data request failed'.var_export($response, true));
            }
        }

        return null;
    }

    private static function exchangeRatesData(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/exchangerates_data-api RATE LIMIT: 250 Requests / Monthly
        $key = config('services.currency.apiLayer_key');
        if ($key) {
            $response = self::$basicRequest->withHeaders(['apikey' => $key])->get("https://api.apilayer.com/exchangerates_data/latest?symbols=$target&base=$base");
            if ($response->ok()) {
                $data = $response->json();

                if ($data['success']) {
                    return $data['rates'][$target];
                }
                Log::emergency('[CurrencyExchange]Exchange Rates Data exchange failed with following message: '.$data['error']['message'] ?? '');
            } else {
                Log::emergency('[CurrencyExchange]Exchange Rates Data request failed'.var_export($response, true));
            }
        }

        return null;
    }

    private static function jsdelivrFile(string $base, string $target): ?float
    { // Reference: https://github.com/fawazahmed0/currency-api
        $response = self::$basicRequest->get('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/'.strtolower($base).'.min.json');
        if ($response->ok()) {
            $data = $response->json();

            return $data[strtolower($base)][strtolower($target)];
        }

        return null;
    }

    private static function duckduckgo(string $base, string $target): ?float
    { // Reference: https://duckduckgo.com  http://www.xe.com/
        $response = self::$basicRequest->get("https://duckduckgo.com/js/spice/currency_convert/1/$base/$target");
        if ($response->ok()) {
            $data = $response->json();

            return $data['to'][0]['mid'];
        }

        return null;
    }

    private static function wise(string $base, string $target): ?float
    { // Reference: https://wise.com/zh-cn/currency-converter/
        $response = self::$basicRequest->withHeader('Authorization', 'Basic OGNhN2FlMjUtOTNjNS00MmFlLThhYjQtMzlkZTFlOTQzZDEwOjliN2UzNmZkLWRjYjgtNDEwZS1hYzc3LTQ5NGRmYmEyZGJjZA==')->get("https://api.wise.com/v1/rates?source=$base&target=$target");
        if ($response->ok()) {
            $data = $response->json();

            return $data[0]['rate'];
        }

        return null;
    }

    private static function wsj(string $base, string $target): ?float
    { // Reference: https://www.wsj.com/market-data/quotes/fx/USDCNY
        $response = self::$basicRequest->get("https://www.wsj.com/market-data/quotes/ajax/fx/9/$base$target?source=$base&target=$base$target&value=1");
        if ($response->ok()) {
            $data = $response->body();
            preg_match('/<span[^>]*>([\d\.]+)<\/span>/', $data, $matches);

            return $matches[1];
        }

        return null;
    }

    private static function xRates(string $base, string $target): ?float
    { // Reference: https://www.x-rates.com/
        $response = self::$basicRequest->get("https://www.x-rates.com/calculator/?from=$base&to=$target&amount=1");
        if ($response->ok()) {
            $data = $response->body();
            preg_match('/<span class="ccOutputRslt">([\d.]+)/', $data, $matches);

            return $matches[1];
        }

        return null;
    }

    private static function valutafx(string $base, string $target): ?float
    { // Reference: https://www.valutafx.com/convert/
        $response = self::$basicRequest->get("https://www.valutafx.com/api/v2/rates/lookup?isoTo=$target&isoFrom=$base&amount=1");
        if ($response->ok()) {
            $data = $response->json();

            if (! $data['ErrorMessage']) {
                return $data['Rate'];
            }
        }

        return null;
    }

    private static function unionpay(string $base, string $target): ?float
    { // Reference: https://www.unionpayintl.com/cn/rate/
        $response = self::$basicRequest->get('https://www.unionpayintl.com/upload/jfimg/'.date('Ymd').'.json');
        if (! $response->ok()) {
            $response = self::$basicRequest->get('https://www.unionpayintl.com/upload/jfimg/'.date('Ymd', strtotime('-1 day')).'.json');
        }

        if ($response->ok()) {
            $data = $response->json();

            return collect($data['exchangeRateJson'])->where('baseCur', $target)->where('transCur', $base)->pluck('rateData')->first();
        }

        return null;
    }

    private static function baidu(string $base, string $target): ?float
    {
        $response = self::$basicRequest->get("https://finance.pae.baidu.com/vapi/async/v1?from_money=$base&to_money=$target&srcid=5293");

        if ($response->ok()) {
            $data = $response->json();

            if ($data['ResultCode'] !== -1) {
                return $data['Result'][0]['DisplayData']['resultData']['tplData']['money2_num'];
            }
        }

        return null;
    }
}
