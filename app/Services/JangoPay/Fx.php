<?php

namespace App\Services\JangoPay;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Read-only HTTPS CNY/USD quote. USD invoice amount and rate are snapshotted per attempt. */
final class Fx
{
    public static function quote(float|int $baseAmount, string $baseCurrency): array
    {
        $major = Money::fromMajor($baseAmount);
        if ($baseCurrency === 'USD') {
            return ['usd' => $major, 'rate' => '1.00000000'];
        }
        if ($baseCurrency !== 'CNY') {
            throw new RuntimeException('Only USD and CNY pricing are supported by this adapter.');
        }
        $reference = config('jangopay.fx_reference_usd_per_cny', '');
        $verifiedAt = config('jangopay.fx_reference_verified_at', '');
        $deviation = config('jangopay.fx_max_deviation', 0.05);
        if (! is_numeric($reference) || ! is_finite((float) $reference) || (float) $reference <= 0 || (float) $reference >= 1
            || ! is_numeric($deviation) || ! is_finite((float) $deviation) || (float) $deviation <= 0 || (float) $deviation > 0.20
            || ! is_string($verifiedAt) || ! preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(?:\.\d+)?(?:Z|[+-]\d\d:\d\d)$/D', $verifiedAt)) {
            self::reject('fx_reference_unavailable');
        }
        try {
            $verified = new DateTimeImmutable($verifiedAt);
            $errors = DateTimeImmutable::getLastErrors();
        } catch (Throwable) {
            self::reject('fx_reference_unavailable');
        }
        if (($errors && ($errors['warning_count'] || $errors['error_count']))
            || $verified->getTimestamp() > time() || time() - $verified->getTimestamp() > 86400) {
            self::reject('fx_reference_expired_or_invalid');
        }
        // This datasource must be approved by the operator before production activation.
        $body = Cache::get('jangopay.fx.cny.usd');
        $fresh = ! is_array($body);
        if ($fresh) {
            $res = Http::acceptJson()->connectTimeout(4)->timeout(8)
                ->withOptions(['allow_redirects' => false])->get('https://open.er-api.com/v6/latest/CNY');
            $body = $res->json();
            if (! $res->successful() || ! is_array($body) || ($body['result'] ?? null) !== 'success') {
                self::reject('fx_provider_unavailable');
            }
        }
        $rate = $body['rates']['USD'] ?? null;
        $min = config('jangopay.fx_min_usd_per_cny', 0.05);
        $max = config('jangopay.fx_max_usd_per_cny', 0.30);
        if (! is_numeric($body['time_last_update_unix'] ?? null)
            || abs(time() - (int) $body['time_last_update_unix']) > 172800
            || ! is_numeric($min) || ! is_numeric($max) || ! is_finite((float) $min) || ! is_finite((float) $max)
            || (float) $min <= 0 || (float) $max >= 1 || (float) $min >= (float) $max
            || ! is_numeric($rate) || ! is_finite((float) $rate) || (float) $rate < (float) $min || (float) $rate > (float) $max) {
            Cache::forget('jangopay.fx.cny.usd');
            self::reject('fx_rate_out_of_bounds');
        }
        if (abs((float) $rate - (float) $reference) > (float) $reference * (float) $deviation) {
            Cache::forget('jangopay.fx.cny.usd');
            self::reject('fx_deviation_exceeded');
        }
        if ($fresh) {
            Cache::put('jangopay.fx.cny.usd', ['rates' => ['USD' => $rate], 'time_last_update_unix' => $body['time_last_update_unix']], 300);
        }
        $usd = Money::fromMajor((float) $major * (float) $rate);

        return ['usd' => $usd, 'rate' => number_format((float) $rate, 8, '.', ''),
            'reference' => ['usd_per_cny' => (string) $reference, 'verified_at' => $verifiedAt,
                'expires_at' => $verified->modify('+24 hours')->format(DATE_ATOM), 'max_deviation' => (float) $deviation]];
    }

    private static function reject(string $reason): never
    {
        Log::warning('[JangoPay] FX quote rejected', ['reason' => $reason]);
        throw new RuntimeException($reason);
    }
}
