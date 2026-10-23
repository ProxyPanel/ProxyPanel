<?php

namespace App\Services\JangoPay;

use InvalidArgumentException;

final class Assets
{
    /** Whitelist exact network identifiers from merchant account, e.g. USDT:tron,USDC:ethereum. */
    public static function parse(string $text): array
    {
        $result = [];
        foreach (preg_split('/\s*,\s*/', trim($text), -1, PREG_SPLIT_NO_EMPTY) as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) !== 2 || ! in_array(strtoupper($parts[0]), ['USDT', 'USDC'], true)
                || ! preg_match('/^[a-z0-9_-]{2,32}$/D', $parts[1])) {
                throw new InvalidArgumentException('Invalid JangoPay currency/network pair.');
            }
            $result[] = strtoupper($parts[0]).':'.$parts[1];
        }
        $result = array_values(array_unique($result));
        if ($result === [] || count($result) > 20) {
            throw new InvalidArgumentException('Configure between 1 and 20 currency/network pairs.');
        }

        return $result;
    }

    public static function merchantEnabled(array $configured, array $remote): array
    {
        $enabled = [];
        foreach ($remote as $row) {
            if (! is_array($row)) {
                continue;
            }
            $coin = strtoupper((string) ($row['code'] ?? $row['currencyCode'] ?? $row['currency_code'] ?? ''));
            $network = strtolower((string) ($row['chain'] ?? ''));
            $ok = $row['invoicePaymentEnabled'] ?? $row['invoice_payment_enabled'] ?? false;
            if (($row['enabled'] ?? false) === true && $ok === true && in_array($coin.':'.$network, $configured, true)) {
                $enabled[] = $coin.':'.$network;
            }
        }

        return array_values(array_unique($enabled));
    }
}
