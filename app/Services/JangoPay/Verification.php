<?php

namespace App\Services\JangoPay;

use InvalidArgumentException;

/** Pure, fail-closed merchant-record validation. This class never triggers fulfillment. */
final class Verification
{
    public static function field(array $record, string $snake, string $camel): mixed
    {
        return $record[$snake] ?? $record[$camel] ?? null;
    }

    public static function equalDecimal(string $a, string $b): bool
    {
        $format = '/^(0|[1-9][0-9]*)(?:\.([0-9]+))?$/D';
        if (strlen($a) > 80 || strlen($b) > 80
            || ! preg_match($format, $a, $ma) || ! preg_match($format, $b, $mb)) {
            return false;
        }
        $fa = rtrim($ma[2] ?? '', '0');
        $fb = rtrim($mb[2] ?? '', '0');

        return $ma[1] === $mb[1] && $fa === $fb;
    }

    public static function matches(
        array $invoice,
        array $txn,
        string $invoiceId,
        string $orderId,
        string $txnId,
        string $usd,
        string $pair,
        array $finalInvoiceStatuses,
        ?string $expectedCoinAmount = null,
        ?string $expectedAddress = null
    ): bool {
        [$coin, $chain] = explode(':', $pair, 2) + ['', ''];
        $iId = self::field($invoice, 'invoice_id', 'invoiceId');
        $iOrder = self::field($invoice, 'order_id', 'orderId');
        $iCurrency = self::field($invoice, 'currency_code', 'currencyCode');
        $iAmount = self::field($invoice, 'amount', 'amount');
        $iStatus = self::field($invoice, 'status', 'status');
        $iPaid = self::field($invoice, 'payed_amount', 'payedAmount');
        $tId = self::field($txn, 'txn_id', 'txnId');
        $tInvoice = self::field($txn, 'invoice_id', 'invoiceId');
        $tStatus = self::field($txn, 'status', 'status');
        $tCoin = self::field($txn, 'pay_currency', 'payCurrency');
        $tChain = self::field($txn, 'chain', 'chain');
        if (! is_string($iId) || ! hash_equals($invoiceId, $iId)
            || ! is_string($iOrder) || ! hash_equals($orderId, $iOrder)
            || $iCurrency !== 'USD'
            || ! is_string($iAmount) || ! Money::equalUsd($usd, $iAmount)
            || ! is_string($iPaid) || ! self::settlement($invoice, $txn, $usd)
            || ! is_string($iStatus) || ! in_array(strtolower($iStatus), $finalInvoiceStatuses, true)
            || ! is_string($tId) || ! hash_equals($txnId, $tId)
            || ! is_string($tInvoice) || ! hash_equals($invoiceId, $tInvoice)
            || strtolower((string) $tStatus) !== 'settled'
            || strtoupper((string) $tCoin) !== $coin
            || strtolower((string) $tChain) !== $chain) {
            return false;
        }

        if ($expectedCoinAmount !== null) {
            $remoteAmount = self::field($txn, 'pay_amount', 'payAmount');
            if (! is_string($remoteAmount) || ! self::equalDecimal($expectedCoinAmount, $remoteAmount)) {
                return false;
            }
        }
        if ($expectedAddress !== null) {
            $remoteAddress = self::field($txn, 'receive_address', 'receiveAddress');
            // Merchant transaction GET can omit the receiving address. payAddress is the sender.
            // Ownership remains tied to the saved confirm-coin txn_id and merchant invoice ID.
            if ($remoteAddress !== null && (! is_string($remoteAddress) || ! hash_equals($expectedAddress, $remoteAddress))) {
                return false;
            }
        }

        // Do not silently accept unverified fee deductions, partial/mixed payments or ambiguous invoices.
        return true;
    }

    private static function settlement(array $invoice, array $txn, string $usd): bool
    {
        try {
            $paid = self::field($invoice, 'payed_amount', 'payedAmount');
            if (! is_string($paid)) {
                return false;
            }
            $expected = Money::ledger($usd);
            if (Money::ledger($paid) === $expected
                && self::field($txn, 'fee_amount', 'feeAmount') === null
                && self::field($txn, 'net_amount', 'netAmount') === null) {
                $gross = $txn['amount'] ?? $usd;

                return is_string($gross) && Money::ledger($gross) === $expected
                    && in_array(self::field($txn, 'currency_code', 'currencyCode'), [null, 'USD'], true);
            }
            if (self::field($invoice, 'fee_pay_by_user', 'feePayByUser') !== false
                || self::field($txn, 'fee_pay_by_user', 'feePayByUser') !== false
                || self::field($txn, 'currency_code', 'currencyCode') !== 'USD') {
                return false;
            }
            $gross = $txn['amount'] ?? null;
            $fee = self::field($txn, 'fee_amount', 'feeAmount');
            $net = self::field($txn, 'net_amount', 'netAmount');
            if (! is_string($gross) || ! is_string($fee) || ! is_string($net)) {
                return false;
            }
            $g = Money::ledger($gross);
            $f = Money::ledger($fee);
            $n = Money::ledger($net);

            return $g === $expected && $n > 0 && $n === Money::ledger($paid) && $g >= $f && $g - $f === $n;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
