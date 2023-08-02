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

    /**
     * @param  string  $target  target Currency
     * @param  float|int  $amount  exchange amount
     * @param  string|null  $base  Base Currency
     * @return float|null amount in target currency
     */
    public static function convert(string $target, float|int $amount, string $base = null): ?float
    {
        if ($base === null) {
            $base = (string) sysConfig('standard_currency');
        }
        $cacheKey = "Currency_{$base}_{$target}_ExRate";

        if (Cache::has($cacheKey)) {
            return round($amount * Cache::get($cacheKey), 2);
        }

        $apis = ['exchangerateApi', 'k780', 'it120', 'exchangerate', 'fixer', 'currencyData', 'exchangeRatesData', 'jsdelivrFile'];
        self::$basicRequest = Http::timeout(15)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

        foreach ($apis as $api) {
            try {
                $rate = self::callApis($api, $base, $target);
                if ($rate !== null) {
                    Cache::put($cacheKey, $rate, Day);

                    return round($amount * $rate, 2);
                }
            } catch (Exception $e) {
                Log::error("[$api] 币种汇率信息获取报错: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private static function callApis(string $api, string $base, string $target): ?float
    {
        return match ($api) {
            'exchangerateApi' => self::exchangerateApi($base, $target),
            'k780' => self::k780($base, $target),
            'it120' => self::it120($base, $target),
            'exchangerate' => self::exchangerate($base, $target),
            'fixer' => self::fixer($base, $target),
            'currencyData' => self::currencyData($base, $target),
            'exchangeRatesData' => self::exchangeRatesData($base, $target),
            'jsdelivrFile' => self::jsdelivrFile($base, $target),
        };
    }

    private static function exchangerateApi(string $base, string $target): ?float
    { // Reference: https://www.exchangerate-api.com/docs/php-currency-api
        $key = config('services.currency.exchangerate-api_key');
        if ($key) {
            $url = "https://v6.exchangerate-api.com/v6/$key/pair/$base/$target";
        } else {
            $url = "https://open.er-api.com/v6/latest/$base";
        }
        $response = self::$basicRequest->get($url);
        if ($response->ok()) {
            $data = $response->json();

            if ($data['result'] === 'success') {
                return $data[$key ? 'conversion_rate' : 'rates'][$target];
            }
            Log::emergency('[CurrencyExchange]exchangerateApi exchange failed with following message: '.$data['error-type']);
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
        $response = self::$basicRequest->get("https://api.it120.cc/gooking/forex/rate?fromCode=$target&toCode=$base");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['code'] === 0) {
                return $data['data']['rate'];
            }
            Log::emergency('[CurrencyExchange]it120 exchange failed with following message: '.$data['msg']);
        } else {
            Log::emergency('[CurrencyExchange]it120 request failed'.var_export($response, true));
        }

        return null;
    }

    private static function exchangerate(string $base, string $target): ?float
    { // Reference: https://exchangerate.host/#/
        $response = self::$basicRequest->get("https://api.exchangerate.host/latest?base=$base&symbols=$target");
        if ($response->ok()) {
            $data = $response->json();

            if ($data['success'] && $data['base'] === $base) {
                return $data['rates'][$target];
            }
            Log::emergency('[CurrencyExchange]exchangerate exchange failed with following message: '.$data['error-type']);
        }
        Log::emergency('[CurrencyExchange]exchangerate request failed');

        return null;
    }

    private static function fixer(string $base, string $target): ?float
    { // Reference: https://apilayer.com/marketplace/fixer-api RATE LIMIT: 100 Requests / Monthly!!!!
        $key = config('services.currency.apiLayer_key');
        if ($key) {
            $response = self::$basicRequest->withHeaders(['apikey' => $key])->get("https://api.apilayer.com/fixer/latest?symbols=$target&base=$base");
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
            $response = self::$basicRequest->withHeaders(['apikey' => $key])->get("https://api.apilayer.com/currency_data/live?source=$base&currencies=$target");
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
        $response = self::$basicRequest->get('https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies/'.strtolower($base).'/'.strtolower($target).'.min.json');
        if ($response->ok()) {
            $data = $response->json();

            return $data[strtolower($target)];
        }

        return null;
    }
}
