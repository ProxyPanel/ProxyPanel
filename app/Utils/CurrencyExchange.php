<?php

namespace App\Utils;

use Cache;
use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Log;

class CurrencyExchange
{
    private static array $apis = ['fixer', 'exchangerateApi', 'wise', 'currencyData', 'exchangeRatesData', 'duckduckgo', 'wsj', 'xRates', 'valutafx', 'baidu', 'unionpay', 'jsdelivrFile', 'it120', 'k780'];

    private static ?PendingRequest $basicRequest;

    /**
     * @param  string  $target  target Currency
     * @param  float|int  $amount  exchange amount
     * @param  string|null  $base  Base Currency
     * @param  string|null  $source  API source
     * @return float|null amount in target currency
     */
    public static function convert(string $target, float|int $amount, ?string $base = null, ?string $source = null): ?float
    {
        $rate = self::getCurrencyRate($target, $base, $source);

        return $rate === null ? null : round($amount * $rate, 2);
    }

    public static function getCurrencyRate(string $target, ?string $base = null, ?string $source = null): ?float
    {
        $base = $base ?? (string) sysConfig('standard_currency');
        $cacheKey = "Currency_{$base}_{$target}_ExRate";

        if (! $source && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        self::$basicRequest = Http::timeout(5)->retry(2)->withOptions(['http_errors' => false])->withoutVerifying()->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');

        foreach ($source ? [$source] : self::$apis as $api) {
            if (! method_exists(self::class, $api)) {
                continue;
            }

            try {
                $rate = self::$api($base, $target);
                if ($rate !== null) {
                    Cache::put($cacheKey, $rate, Day);

                    return $rate;
                }
            } catch (Exception $e) {
                Log::error("[$api] 币种汇率信息获取报错: ".$e->getMessage());
            }
        }

        return null;
    }

    private static function exchangerateApi(string $base, string $target): ?float
    { // Reference: https://www.exchangerate-api.com/docs/php-currency-api
        $key = config('services.currency.exchangerate-api_key');
        $url = $key ? "https://v6.exchangerate-api.com/v6/$key/pair/$base/$target" : "https://open.er-api.com/v6/latest/$base";

        return self::callApi($url, static function ($data) use ($key, $target) {
            if ($data['result'] === 'success') {
                if ($key && isset($data['conversion_rate'])) {
                    return $data['conversion_rate'];
                }

                if (isset($data['rates'][$target])) {
                    return $data['rates'][$target];
                }
            }
            Log::error('[CurrencyExchange]exchangerateApi exchange failed with following message: '.$data['error-type'] ?? '');

            return null;
        });
    }

    private static function callApi(string $url, callable $extractor, array $headers = []): ?float
    {
        try {
            $request = self::$basicRequest;
            if (! empty($headers)) {
                $request = $request->withHeaders($headers);
            }

            $response = $request->get($url);
            if ($response->ok()) {
                $data = $response->json();

                return $extractor($data);
            }

            Log::warning('[CurrencyExchange] API request failed: '.$url.' Response: '.var_export($response, true));
        } catch (Exception $e) {
            Log::warning("[CurrencyExchange] API $url request exception: ".$e->getMessage());
        }

        return null;
    }

    private static function k780(string $base, string $target): ?float
    { // Reference: https://www.nowapi.com/api/finance.rate
        return self::callApi("https://sapi.k780.com/?app=finance.rate&scur=$base&tcur=$target&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json", static function ($data) {
            if ($data['success'] === '1') {
                return $data['result']['rate'] ?? null;
            }
            Log::emergency('[CurrencyExchange]Nowapi exchange failed with following message: '.$data['msg']);

            return null;
        });
    }

    private static function it120(string $base, string $target): ?float
    { // Reference: https://www.it120.cc/help/fnun8g.html
        $key = config('services.currency.it120_key');
        if (! $key) {
            return null;
        }

        return self::callApi("https://api.it120.cc/$key/forex/rate?fromCode=$target&toCode=$base", static function ($data) {
            if ($data['code'] === 0) {
                return $data['data']['rate'] ?? null;
            }
            Log::emergency('[CurrencyExchange]it120 exchange failed with following message: '.$data['msg']);

            return null;
        });
    }

    private static function fixer(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/fixer-api RATE LIMIT: 100 Requests / Monthly!!!!
        $key = config('services.currency.apiLayer_key');
        if (! $key) {
            return null;
        }

        return self::callApi("https://api.apilayer.com/fixer/latest?symbols=$target&base=$base", static function ($data) use ($target) {
            if ($data['success']) {
                return $data['rates'][$target] ?? null;
            }
            Log::emergency('[CurrencyExchange]Fixer exchange failed with following message: '.$data['error']['type'] ?? '');

            return null;
        }, ['apikey' => $key]);
    }

    private static function currencyData(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/currency_data-api RATE LIMIT: 100 Requests / Monthly
        $key = config('services.currency.apiLayer_key');
        if (! $key) {
            return null;
        }

        return self::callApi("https://api.apilayer.com/currency_data/live?source=$base&currencies=$target", static function ($data) use ($base, $target) {
            if ($data['success']) {
                return $data['quotes'][$base.$target] ?? null;
            }
            Log::emergency('[CurrencyExchange]Currency Data exchange failed with following message: '.$data['error']['info'] ?? '');

            return null;
        }, ['apikey' => $key]);
    }

    private static function exchangeRatesData(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/exchangerates_data-api RATE LIMIT: 250 Requests / Monthly
        $key = config('services.currency.apiLayer_key');
        if (! $key) {
            return null;
        }

        return self::callApi("https://api.apilayer.com/exchangerates_data/latest?symbols=$target&base=$base", static function ($data) use ($target) {
            if ($data['success']) {
                return $data['rates'][$target];
            }
            Log::emergency('[CurrencyExchange]Exchange Rates Data exchange failed with following message: '.$data['error']['message'] ?? '');

            return null;
        }, ['apikey' => $key]);
    }

    private static function jsdelivrFile(string $base, string $target): ?float
    { // Reference: https://github.com/fawazahmed0/currency-api
        return self::callApi('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/'.strtolower($base).'.min.json', static function ($data) use ($base, $target) {
            return $data[strtolower($base)][strtolower($target)] ?? null;
        });
    }

    private static function duckduckgo(string $base, string $target): ?float
    { // Reference: https://duckduckgo.com  http://www.xe.com/
        return self::callApi("https://duckduckgo.com/js/spice/currency_convert/1/$base/$target", static function ($data) {
            return $data['to'][0]['mid'] ?? null;
        });
    }

    private static function wise(string $base, string $target): ?float
    { // Reference: https://wise.com/zh-cn/currency-converter/
        return self::callApi("https://api.wise.com/v1/rates?source=$base&target=$target", static function ($data) {
            return $data[0]['rate'] ?? null;
        }, ['Authorization' => 'Basic OGNhN2FlMjUtOTNjNS00MmFlLThhYjQtMzlkZTFlOTQzZDEwOjliN2UzNmZkLWRjYjgtNDEwZS1hYzc3LTQ5NGRmYmEyZGJjZA==']);
    }

    private static function xRates(string $base, string $target): ?float
    { // Reference: https://www.x-rates.com/
        try {
            $response = self::$basicRequest->get("https://www.x-rates.com/calculator/?from=$base&to=$target&amount=1");

            if ($response->ok()) {
                preg_match('/<span class="ccOutputRslt">([\d.]+)</', $response->body(), $matches);

                return $matches[1] ?? null;
            }
        } catch (Exception $e) {
            Log::warning('[CurrencyExchange] xRates request failed: '.$e->getMessage());
        }

        return null;
    }

    private static function valutafx(string $base, string $target): ?float
    { // Reference: https://www.valutafx.com/convert/
        return self::callApi("https://www.valutafx.com/api/v2/rates/lookup?isoTo=$target&isoFrom=$base&amount=1", static function ($data) {
            if ($data['ErrorMessage'] === null) {
                return $data['Rate'] ?? null;
            }

            return null;
        });
    }

    private static function unionpay(string $base, string $target): ?float
    { // Reference: https://www.unionpayintl.com/cn/rate/
        try {
            $response = self::$basicRequest->get('https://www.unionpayintl.com/upload/jfimg/'.date('Ymd').'.json');
            if (! $response->ok()) {
                $response = self::$basicRequest->get('https://www.unionpayintl.com/upload/jfimg/'.date('Ymd', strtotime('-1 day')).'.json');
            }

            if ($response->ok()) {
                $data = $response->json();

                return collect($data['exchangeRateJson'])->where('baseCur', $target)->where('transCur', $base)->pluck('rateData')->first();
            }
        } catch (Exception $e) {
            Log::warning('[CurrencyExchange] Unionpay request failed: '.$e->getMessage());
        }

        return null;
    }

    private static function baidu(string $base, string $target): ?float
    {
        return self::callApi("https://finance.pae.baidu.com/vapi/async/v1?from_money=$base&to_money=$target&srcid=5293", static function ($data) {
            if ($data['ResultCode'] === 0) {
                return $data['Result'][0]['DisplayData']['resultData']['tplData']['money2_num'] ?? null;
            }

            return null;
        });
    }
}
