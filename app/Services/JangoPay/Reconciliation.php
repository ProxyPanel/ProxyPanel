<?php

namespace App\Services\JangoPay;

use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Utils\Library\PaymentHelper;
use App\Utils\Payments\JangoPay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Merchant GETs recover evidence. Native fulfillment and its marker commit under the same order lock. */
class Reconciliation
{
    public function check(int $id, ?string $eventTxn = null, ?string $eventHash = null, ?string $eventInvoice = null): string
    {
        $attempt = JangoPayAttempt::findOrFail($id);
        if ($attempt->state === 'delivered') {
            return $this->deliveredResult($attempt, $eventTxn, $eventHash, $eventInvoice);
        }
        if ($attempt->state === 'resolved') {
            return 'resolved';
        }
        try {
            $api = app(Api::class);
            $invoiceId = $attempt->invoice_id ?: $eventInvoice;
            if (! $invoiceId) {
                $found = $api->findInvoice($attempt->order_id);
                $invoiceId = $found ? Verification::field($found, 'invoice_id', 'invoiceId') : null;
                if (! is_string($invoiceId) || $invoiceId === '') {
                    return $this->record($id, 'invoice_not_found');
                }
            }
            $invoice = $api->invoice($invoiceId);
            if (($eventInvoice !== null && $eventInvoice !== $invoiceId)
                || Verification::field($invoice, 'invoice_id', 'invoiceId') !== $invoiceId
                || Verification::field($invoice, 'order_id', 'orderId') !== $attempt->order_id
                || Verification::field($invoice, 'currency_code', 'currencyCode') !== 'USD'
                || ! is_string($invoice['amount'] ?? null) || ! Money::equalUsd($attempt->amount_usd, $invoice['amount'])) {
                return $this->record($id, 'invoice_identity_mismatch');
            }
            $this->bindInvoice($id, $invoiceId);
            $txnId = $attempt->txn_id ?: $eventTxn;
            if (! $txnId) {
                // A complete authenticated scan is required, never the first hit from an unverified filter.
                $transactions = $api->transactions($invoiceId);
                $settled = array_values(array_filter($transactions, fn ($row) => ($row['status'] ?? null) === 'settled'));
                if (count($settled) > 1) {
                    return $this->record($id, 'multiple_settlements');
                }
                $txnId = isset($settled[0]) ? Verification::field($settled[0], 'txn_id', 'txnId') : null;
            }
            if (! is_string($txnId) || $txnId === '') {
                return $this->record($id, $attempt->state === 'quote_unknown' ? 'quote_instructions_unrecoverable' : 'awaiting_settlement', $invoice);
            }
            if ($eventTxn !== null && ! hash_equals($txnId, $eventTxn)) {
                return $this->record($id, 'event_transaction_mismatch');
            }
            $txn = $api->transaction($txnId);
            if ($attempt->state === 'quote_unknown' && ($txn['status'] ?? null) === 'pending') {
                return $this->record($id, 'quote_instructions_unrecoverable', $invoice, $txn);
            }
            $pair = strtoupper((string) Verification::field($txn, 'pay_currency', 'payCurrency')).':'.strtolower((string) ($txn['chain'] ?? ''));
            $expectedPair = $attempt->selected_asset ?: $pair;
            $coinAmount = $attempt->quote_amount ?: Verification::field($txn, 'pay_amount', 'payAmount');
            $hash = Verification::field($txn, 'tx_hash', 'txHash');
            if (! in_array($pair, Assets::parse($attempt->allowed_assets), true)
                || ! is_string($coinAmount) || ! preg_match('/[1-9]/', $coinAmount)
                || ! is_string($hash) || $hash === '' || strlen($hash) > 160
                || ($eventHash !== null && $eventHash !== '' && ! hash_equals($hash, $eventHash))
                || ! Verification::matches($invoice, $txn, $invoiceId, $attempt->order_id, $txnId,
                    $attempt->amount_usd, $expectedPair, Ready::statuses(), $coinAmount, $attempt->receive_address ?: null)) {
                return $this->record($id, 'verification_pending', $invoice, $txn);
            }

            // Remote calls finish before locks. All order mutation and durable completion share one transaction.
            return DB::transaction(function () use ($id, $attempt, $invoiceId, $txnId, $invoice, $txn, $pair, $coinAmount, $hash, $eventTxn, $eventHash, $eventInvoice) {
                $paymentSnapshot = Payment::find($attempt->payment_id);
                $order = $paymentSnapshot ? Order::whereKey($paymentSnapshot->order_id)->lockForUpdate()->first() : null;
                $payment = $paymentSnapshot ? Payment::whereKey($paymentSnapshot->id)->lockForUpdate()->first() : null;
                $locked = JangoPayAttempt::whereKey($id)->lockForUpdate()->firstOrFail();
                if ($locked->state === 'delivered') {
                    return $this->deliveredResult($locked, $eventTxn, $eventHash, $eventInvoice);
                }
                if ($locked->state === 'resolved') {
                    return 'resolved';
                }
                if (! $order || ! $payment || $order->pay_way !== 'jangopay'
                    || $payment->trade_no !== $locked->order_id
                    || ! Money::equalUsd($locked->base_amount, Money::fromMajor($order->amount))
                    || ! Money::equalUsd($locked->base_amount, Money::fromMajor($payment->amount))
                    || ($locked->invoice_id && $locked->invoice_id !== $invoiceId)
                    || ($locked->txn_id && $locked->txn_id !== $txnId)
                    || ! in_array($pair, Assets::parse($locked->allowed_assets), true)
                    || ! Verification::matches($invoice, $txn, $invoiceId, $locked->order_id, $txnId,
                        $locked->amount_usd, $locked->selected_asset ?: $pair, Ready::statuses(),
                        $locked->quote_amount ?: $coinAmount, $locked->receive_address ?: null)) {
                    return $this->record($id, 'local_order_changed', $invoice, $txn);
                }
                $proof = ['invoice_id' => $invoiceId, 'txn_id' => $txnId, 'selected_asset' => $pair,
                    'quote_amount' => $coinAmount, 'tx_hash' => $hash, 'last_checked_at' => now(),
                    'remote_invoice_status' => substr((string) ($invoice['status'] ?? ''), 0, 40),
                    'remote_txn_status' => substr((string) ($txn['status'] ?? ''), 0, 40)];
                $alreadyPaid = in_array((int) $order->status, [2, 3], true) && (int) $payment->status === 1;
                if (! $alreadyPaid && ((int) $order->status !== 0 || (int) $payment->status !== 0)) {
                    $locked->update($proof + ['state' => 'manual_review', 'failure_code' => 'closed_or_ineligible_order']);

                    return 'manual_review';
                }
                if (! $alreadyPaid && ($locked->state === 'manual_review' || ! $this->timely($locked, $invoice))) {
                    $locked->update($proof + ['state' => 'manual_review', 'failure_code' => $locked->failure_code ?: 'late_payment']);

                    return 'manual_review';
                }
                if (! $alreadyPaid) {
                    $locked->update($proof + ['state' => 'fulfilling']);
                    if (! PaymentHelper::paymentReceived($locked->order_id, true)) {
                        throw new RuntimeException('Native fulfillment failed.');
                    }
                    $order->refresh();
                    $payment->refresh();
                    if (! in_array((int) $order->status, [2, 3], true) || (int) $payment->status !== 1) {
                        throw new RuntimeException('Native fulfillment did not commit a paid order.');
                    }
                }
                $locked->update($proof + ['state' => 'delivered', 'delivered_at' => now(), 'failure_code' => null]);
                $eventKey = hash('sha256', 'payment.completed|'.$invoiceId.'|'.$txnId.'|'.$hash);
                DB::table('jangopay_events')->updateOrInsert(['event_hash' => $eventKey], [
                    'invoice_id' => $invoiceId, 'txn_id' => $txnId, 'tx_hash' => $hash,
                    'status' => 'fulfilled', 'created_at' => now(), 'updated_at' => now(),
                ]);

                return 'delivered';
            });
        } catch (Throwable $e) {
            Log::warning('[JangoPay] Reconciliation deferred', ['attempt' => $id, 'reason' => get_class($e)]);

            return $this->record($id, 'reconciliation_unavailable');
        }
    }

    private function timely(JangoPayAttempt $attempt, array $invoice): bool
    {
        if (! $attempt->expires_at) {
            return false;
        }
        if (now()->lt($attempt->expires_at)) {
            return true;
        }
        $paidAt = Verification::field($invoice, 'paid_at', 'paidAt');
        if (! is_string($paidAt) || ! preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(?:\.\d+)?(?:Z|[+-]\d\d:\d\d)$/D', $paidAt)) {
            return false;
        }
        try {
            $paid = Carbon::parse($paidAt);

            return $paid->gte($attempt->created_at) && $paid->lt($attempt->expires_at);
        } catch (Throwable) {
            return false;
        }
    }

    private function bindInvoice(int $id, string $invoiceId): void
    {
        DB::transaction(function () use ($id, $invoiceId) {
            $snapshot = JangoPayAttempt::findOrFail($id);
            $paymentSnapshot = Payment::find($snapshot->payment_id);
            $order = $paymentSnapshot ? Order::whereKey($paymentSnapshot->order_id)->lockForUpdate()->first() : null;
            $payment = $paymentSnapshot ? Payment::whereKey($paymentSnapshot->id)->lockForUpdate()->first() : null;
            $locked = JangoPayAttempt::whereKey($id)->lockForUpdate()->firstOrFail();
            if (! $locked->invoice_id && in_array($locked->state, ['reserved', 'creating', 'unknown_creation'], true)) {
                $locked->update(['invoice_id' => $invoiceId, 'state' => 'awaiting_payment']);
            }
            if ($order && $payment && $order->pay_way === 'jangopay'
                && $payment->trade_no === $locked->order_id && $payment->user_id === $order->user_id
                && $locked->invoice_id === $invoiceId
                && Money::equalUsd($locked->base_amount, Money::fromMajor($order->amount))
                && Money::equalUsd($locked->base_amount, Money::fromMajor($payment->amount))
                && JangoPay::canPay($payment->setRelation('order', $order), $locked)) {
                $payment->update(['url' => route('jangopay.checkout', ['trade_no' => $payment->trade_no])]);
            }
        });
    }

    private function deliveredResult(JangoPayAttempt $attempt, ?string $eventTxn, ?string $eventHash, ?string $eventInvoice): string
    {
        if (($eventInvoice !== null && $eventInvoice !== $attempt->invoice_id)
            || ($eventTxn !== null && $eventTxn !== $attempt->txn_id)) {
            return 'delivered_event_identity_mismatch';
        }
        if ($eventHash !== null
            && (! is_string($attempt->tx_hash) || ! hash_equals($attempt->tx_hash, $eventHash))) {
            return 'delivered_event_hash_mismatch';
        }

        return 'delivered';
    }

    private function record(int $id, string $reason, array $invoice = [], array $txn = []): string
    {
        JangoPayAttempt::whereKey($id)->whereNotIn('state', ['delivered', 'resolved'])->update([
            'last_checked_at' => now(), 'failure_code' => $reason,
            'remote_invoice_status' => substr((string) ($invoice['status'] ?? ''), 0, 40),
            'remote_txn_status' => substr((string) ($txn['status'] ?? ''), 0, 40),
        ]);

        // A concurrent delivery cannot turn a failed evidence check into a successful callback audit.
        return $reason;
    }
}
