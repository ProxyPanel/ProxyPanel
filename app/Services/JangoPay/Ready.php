<?php

namespace App\Services\JangoPay;

use Throwable;

final class Ready
{
    public static function enabled(): bool
    {
        $statuses = self::statuses();
        $path = (string) config('jangopay.private_key_path', '');
        if (! in_array(strtolower((string) config('jangopay.enable_payments', false)), ['1', 'true'], true)
            || config('jangopay.webhook_tag_format') !== 'appended16'
            || $statuses === [] || $path === '' || ! is_file($path) || ! is_readable($path)) {
            return false;
        }
        try {
            $key = @openssl_pkey_get_private('file://'.$path);
            $info = $key === false ? false : openssl_pkey_get_details($key);
            if (! is_array($info) || ($info['type'] ?? null) !== OPENSSL_KEYTYPE_RSA
                || ! in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) {
                return false;
            }
            // Exercise the same SHA-256 OAEP subprocess used by real callbacks before accepting money.
            (new WebhookEnvelope)->assertAvailable($path);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public static function statuses(): array
    {
        $statuses = array_values(array_filter(array_map('trim',
            explode(',', strtolower((string) config('jangopay.final_invoice_statuses', ''))))));

        return array_values(array_unique($statuses));
    }
}
