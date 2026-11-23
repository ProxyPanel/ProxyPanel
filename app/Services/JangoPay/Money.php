<?php

namespace App\Services\JangoPay;

use InvalidArgumentException;

final class Money
{
    public static function ledger(string $value): int
    {
        if (! preg_match('/^(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,8}))?$/D', $value, $m)) {
            throw new InvalidArgumentException('Invalid USD ledger amount.');
        }
        if ((int) $m[1] > intdiv(PHP_INT_MAX - 99999999, 100000000)) {
            throw new InvalidArgumentException('USD ledger amount overflow.');
        }

        return (int) $m[1] * 100000000 + (int) str_pad($m[2] ?? '', 8, '0');
    }

    /** Compare USD figures as decimal strings, without floating-point comparisons. */
    public static function cents(string $value): int
    {
        if (! preg_match('/^(0|[1-9][0-9]{0,10})(?:\.([0-9]{1,8}))?$/D', $value, $m)) {
            throw new InvalidArgumentException('Invalid USD amount.');
        }
        $fraction = str_pad($m[2] ?? '', 8, '0');
        if (substr($fraction, 2) !== '000000') {
            throw new InvalidArgumentException('Sub-cent USD value.');
        }
        $units = (int) $m[1];
        if ($units > intdiv(PHP_INT_MAX - 99, 100)) {
            throw new InvalidArgumentException('USD amount overflow.');
        }
        $cents = $units * 100 + (int) substr($fraction, 0, 2);
        if ($cents < 1) {
            throw new InvalidArgumentException('USD amount must be positive.');
        }

        return $cents;
    }

    public static function equalUsd(string $expected, string $actual): bool
    {
        try {
            return self::cents($expected) === self::cents($actual);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public static function fromMajor(float|int $amount): string
    {
        if (! is_finite((float) $amount) || $amount <= 0) {
            throw new InvalidArgumentException('Invalid amount.');
        }
        $usd = number_format((float) $amount, 2, '.', '');
        self::cents($usd);

        return $usd;
    }
}
