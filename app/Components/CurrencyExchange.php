<?php

namespace App\Components;

use Cache;
use Http;
use Log;

class CurrencyExchange
{
    /**
     * @param  string  $target  target Currency
     * @param  int|float|false  $amount  exchange amount
     * @param  string  $base  Base Currency
     * @return false|float amount in target currency
     */
    public static function convert(string $target, $amount = false, string $base = 'default')
    {
        if ($base === 'default') {
            $base = sysConfig('standard_currency', 'CNY');
        }
        $cacheKey = "Currency_{$base}_{$target}_ExRate";
        $isStored = Cache::has($cacheKey);
        $rate = $isStored ? Cache::get($cacheKey) : false;
        $case = 0;

        while ($case < 7 && $rate === false) {
            switch ($case) {
                case 0:
                    $rate = self::exchangerateApi($base, $target);
                    break;
                case 1:
                    $rate = self::k780($base, $target);
                    break;
                case 2:
                    $rate = self::it120($base, $target);
                    break;
                case 3:
                    $rate = self::exchangerate($base, $target);
                    break;
                case 4:
                    $rate = self::fixer($base, $target);
                    break;
                case 5:
                    $rate = self::currencyData($base, $target);
                    break;
                case 6:
                    $rate = self::exchangeRatesData($base, $target);
                    break;
                default:
                    break;
            }
            $case++;
        }

        if ($rate !== false) {
            if (! $isStored) {
                Cache::put($cacheKey, $rate, Day);
            }
            if ($amount === false) {
                return $rate;
            }

            return round($amount * $rate, 2);
        }

        return false;
    }

    private static function exchangerateApi($base, $target)
    { // Reference: https://www.exchangerate-api.com/docs/php-currency-api
        $response = Http::retry(3)->get('https://open.er-api.com/v6/latest/'.$base); // https://v6.exchangerate-api.com/v6/{{your token}}/latest/USD
        if ($response->ok()) {
            $response = $response->json();

            if ($response['result'] === 'success') {
                return $response['rates'][$target];
            }
            Log::emergency('[CurrencyExchange]exchangerateApi exchange failed with following message: '.$response['error-type']);
        } else {
            Log::emergency('[CurrencyExchange]exchangerateApi request failed '.var_export($response, true));
        }

        return false;
    }

    private static function k780($base, $target)
    { // Reference: https://www.nowapi.com/api/finance.rate
        $response = Http::retry(3)->get("https://sapi.k780.com/?app=finance.rate&scur={$base}&tcur={$target}&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json");
        if ($response->ok()) {
            $response = $response->json();

            if ($response['success'] === '1') {
                return $response['result']['rate'];
            }
            Log::emergency('[CurrencyExchange]Nowapi exchange failed with following message: '.$response['msg']);
        } else {
            Log::emergency('[CurrencyExchange]Nowapi request failed'.var_export($response, true));
        }

        return false;
    }

    private static function it120($base, $target)
    { // Reference: https://www.it120.cc/help/fnun8g.html
        $response = Http::retry(3)->get("https://api.it120.cc/gooking/forex/rate?fromCode={$target}&toCode={$base}");
        if ($response->ok()) {
            $response = $response->json();

            if ($response['code'] === 0) {
                return $response['data']['rate'];
            }
            Log::emergency('[CurrencyExchange]it120 exchange failed with following message: '.$response['msg']);
        } else {
            Log::emergency('[CurrencyExchange]it120 request failed'.var_export($response, true));
        }

        return false;
    }

    private static function exchangerate($base, $target)
    { // Reference: https://exchangerate.host/#/
        $response = file_get_contents("https://api.exchangerate.host/latest?symbols={$target}&base={$base}");
        if (false !== $response) {
            $response = json_decode($response, true);

            if ($response['success'] === true) {
                if ($response['base'] === $base) {
                    return $response['rates'][$target];
                }

                Log::emergency('[CurrencyExchange]exchangerate exchange failed with following message: Get un-supported base currency '.$response['base']);
            } else {
                Log::emergency('[CurrencyExchange]exchangerate exchange failed with following message: '.$response['error-type']);
            }
        } else {
            Log::emergency('[CurrencyExchange]exchangerate request failed');
        }

        return false;
    }

    private static function fixer($base, $target)
    { // Reference: https://apilayer.com/marketplace/fixer-api
        // RATE LIMIT: 100 Requests / Monthly
        if (! config('services.apiLayer')) {
            return false;
        }

        $response = Http::retry(3)->withHeaders(['apikey' => config('services.apiLayer')])->get("https://api.apilayer.com/fixer/latest?symbols={$target}&base={$base}");
        if ($response->ok()) {
            $response = $response->json();

            if ($response['success'] === true) {
                return $response['rates'][$target];
            }

            Log::emergency('[CurrencyExchange]Fixer exchange failed with following message: '.$response['error']['type'] ?? '');
        } else {
            Log::emergency('[CurrencyExchange]Fixer request failed'.var_export($response, true));
        }

        return false;
    }

    private static function currencyData($base, $target)
    { // Reference: https://apilayer.com/marketplace/currency_data-api
        // RATE LIMIT: 100 Requests / Monthly
        if (! config('services.apiLayer')) {
            return false;
        }

        $response = Http::retry(3)->withHeaders(['apikey' => config('services.apiLayer')])
            ->get("https://api.apilayer.com/currency_data/live?source={$base}&currencies={$target}");
        if ($response->ok()) {
            $response = $response->json();

            if ($response['success'] === true) {
                return $response['quotes'][$base.$target];
            }
            Log::emergency('[CurrencyExchange]Currency Data exchange failed with following message: '.$response['error']['info'] ?? '');
        } else {
            Log::emergency('[CurrencyExchange]Currency Data request failed'.var_export($response, true));
        }

        return false;
    }

    private static function exchangeRatesData($base, $target)
    { // Reference: https://apilayer.com/marketplace/exchangerates_data-api
        // RATE LIMIT: 250 Requests / Monthly
        if (! config('services.apiLayer')) {
            return false;
        }

        $response = Http::retry(3)->withHeaders(['apikey' => config('services.apiLayer')])
            ->get("https://api.apilayer.com/exchangerates_data/latest?symbols={$target}&base={$base}");
        if ($response->ok()) {
            $response = $response->json();

            if ($response['success'] === true) {
                return $response['rates'][$target];
            }
            Log::emergency('[CurrencyExchange]Exchange Rates Data exchange failed with following message: '.$response['error']['message'] ?? '');
        } else {
            Log::emergency('[CurrencyExchange]Exchange Rates Data request failed'.var_export($response, true));
        }

        return false;
    }
}
