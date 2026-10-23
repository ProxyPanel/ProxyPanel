<?php

namespace App\Services\JangoPay;

use InvalidArgumentException;

final class Assets
{
    /** Resolve * from merchant metadata; persisted attempts always contain explicit pairs. */
    public static function parse(string $text): array
    {
        $text = trim($text);
        if ($text === '*') {
            return ['*'];
        }
        if (strlen($text) > 65535) {
            throw new InvalidArgumentException('JangoPay asset list exceeds storage capacity.');
        }
        $result = [];
        foreach (preg_split('/\s*,\s*/', $text, -1, PREG_SPLIT_NO_EMPTY) as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) !== 2 || strlen($pair) > 80
                || ! preg_match('/^[A-Z0-9][A-Z0-9._-]*$/D', strtoupper($parts[0]))
                || ! preg_match('/^[a-z0-9][a-z0-9_-]*$/D', $parts[1])) {
                throw new InvalidArgumentException('Invalid JangoPay currency/network pair.');
            }
            $result[] = strtoupper($parts[0]).':'.$parts[1];
        }
        $result = array_values(array_unique($result));
        if ($result === []) {
            throw new InvalidArgumentException('Configure * or explicit currency/network pairs.');
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
            $coin = $row['code'] ?? $row['currencyCode'] ?? $row['currency_code'] ?? null;
            $network = $row['chain'] ?? null;
            $ok = $row['invoicePaymentEnabled'] ?? $row['invoice_payment_enabled'] ?? false;
            if (($row['enabled'] ?? false) !== true || $ok !== true
                || ! is_string($coin) || ! is_string($network)) {
                continue;
            }
            $pair = strtoupper($coin).':'.strtolower($network);
            self::parse($pair);
            if ($configured === ['*'] || in_array($pair, $configured, true)) {
                $enabled[] = $pair;
            }
        }
        $enabled = array_values(array_unique($enabled));
        if ($enabled !== []) {
            self::parse(implode(',', $enabled));
        }

        return $enabled;
    }
}
