<?php

namespace App\Utils\Payments;

use App\Models\JangoPayAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Services\JangoPay\Api;
use App\Services\JangoPay\Assets;
use App\Services\JangoPay\Fx;
use App\Services\JangoPay\Money;
use App\Services\JangoPay\Ready;
use App\Services\JangoPay\Reconciliation;
use App\Services\JangoPay\Verification;
use App\Services\JangoPay\WebhookEnvelope;
use App\Utils\Library\PaymentHelper;
use App\Utils\Library\Templates\Gateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Optional USDT/USDC gateway. All customer entitlements use ProxyPanel's existing PaymentHelper. */
class JangoPay implements Gateway
{
    public static function metadata(): array
    {
        return [
            'key' => 'jangopay',
            'method' => ['other'],
            'settings' => ['jangopay_assets' => ['holder' => 'USDT:tron,USDC:ethereum']],
        ];
    }

    private function allowed(): array
    {
        return Assets::parse((string) sysConfig('jangopay_assets', ''));
    }

    private function api(): Api
    {
        return app(Api::class);
    }

    private function preflightFailed(Request $request): JsonResponse
    {
        // No invoice POST has happened: release the native order and its coupon through its lifecycle.
        DB::transaction(function () use ($request) {
            $order = Order::whereKey((int) $request->input('id'))->where('user_id', auth()->id())
                ->where('pay_way', 'jangopay')->lockForUpdate()->first();
            if ($order && (int) $order->status === 0
                && ! Payment::where('order_id', $order->id)->exists()) {
                $order->close();
            }
        });

        return response()->json(['status' => 'fail', 'message' => trans('user.payment.jangopay.unavailable')]);
    }

    public static function canPay(Payment $payment, JangoPayAttempt $attempt): bool
    {
        return (int) $payment->status === 0 && $payment->order
            && (int) $payment->order->status === 0
            && $payment->order->pay_way === 'jangopay'
            && $payment->user_id === $payment->order->user_id
            && $payment->trade_no === $attempt->order_id
            && $attempt->payment_id === $payment->id
            && Money::equalUsd($attempt->base_amount, Money::fromMajor($payment->amount))
            && Money::equalUsd($attempt->base_amount, Money::fromMajor($payment->order->amount))
            && $attempt->state === 'awaiting_payment'
            && is_string($attempt->invoice_id)
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,119}$/D', $attempt->invoice_id) === 1
            && $attempt->expires_at && now()->lt($attempt->expires_at);
    }

    public function purchase(Request $request): JsonResponse
    {
        if (! Ready::enabled()) {
            return $this->preflightFailed($request);
        }
        // Resolve an account-specific payment asset allowlist before reserving an invoice.
        try {
            $api = $this->api();
            $assets = Assets::merchantEnabled($this->allowed(), $api->assets());
            if ($assets === []) {
                throw new RuntimeException('No merchant-enabled payment assets.');
            }
            $baseCurrency = strtoupper((string) sysConfig('standard_currency'));
            $base = Money::fromMajor((float) $request->input('amount'));
            $fx = Fx::quote((float) $request->input('amount'), $baseCurrency);
        } catch (Throwable $e) {
            Log::warning('[JangoPay] Checkout validation unavailable', ['reason' => get_class($e)]);

            return $this->preflightFailed($request);
        }

        $payment = PaymentHelper::createPayment((int) auth()->id(), (int) $request->input('id'), (float) $request->input('amount'));
        $attempt = JangoPayAttempt::create([
            'payment_id' => $payment->id, 'order_id' => $payment->trade_no,
            'base_currency' => $baseCurrency, 'base_amount' => $base,
            'amount_usd' => $fx['usd'], 'fx_rate' => $fx['rate'],
            'fx_reference' => $fx['reference'] ?? null,
            'allowed_assets' => implode(',', $assets), 'state' => 'reserved',
            'expires_at' => now()->addMinutes(15),
        ]);
        if (JangoPayAttempt::whereKey($attempt->id)->where('state', 'reserved')->update(['state' => 'creating']) !== 1) {
            return response()->json(['status' => 'fail', 'message' => trans('user.payment.jangopay.unavailable')]);
        }
        try {
            // Exactly one POST per attempt. A timeout may mean the remote invoice already exists.
            $invoice = $api->createInvoice($payment->trade_no, $fx['usd'], $assets);
            $invoiceId = Verification::field($invoice, 'invoice_id', 'invoiceId');
            $remoteOrder = Verification::field($invoice, 'order_id', 'orderId');
            if (! is_string($invoiceId) || $invoiceId === '' || strlen($invoiceId) > 120
                || ! is_string($remoteOrder) || ! hash_equals($payment->trade_no, $remoteOrder)) {
                throw new RuntimeException('Invoice did not match local order.');
            }
            JangoPayAttempt::whereKey($attempt->id)->where('state', 'creating')
                ->update(['invoice_id' => $invoiceId, 'state' => 'awaiting_payment']);
            if ($attempt->fresh()->invoice_id !== $invoiceId) {
                throw new RuntimeException('Invoice mapping changed during creation.');
            }
            $url = route('jangopay.checkout', ['trade_no' => $payment->trade_no]);
            $payment->update(['url' => $url]);

            return response()->json(['status' => 'success', 'url' => $url,
                'message' => trans('user.payment.order_creation.success')]);
        } catch (Throwable $e) {
            // Never close/replace this attempt or blindly issue a second POST on network ambiguity.
            JangoPayAttempt::whereKey($attempt->id)->where('state', 'creating')
                ->update(['state' => 'unknown_creation', 'failure_code' => 'create_result_unknown']);
            Log::warning('[JangoPay] Invoice creation requires reconciliation', ['attempt' => $attempt->id, 'reason' => get_class($e)]);

            return response()->json(['status' => 'fail', 'message' => trans('user.payment.jangopay.unknown_creation')]);
        }
    }

    private function mine(string $tradeNo): array
    {
        $payment = Payment::uid()->with('order')->whereTradeNo($tradeNo)->firstOrFail();
        abort_unless($payment->order && $payment->order->pay_way === 'jangopay', 404);

        return [$payment, JangoPayAttempt::wherePaymentId($payment->id)->firstOrFail()];
    }

    public function checkout(string $trade_no): View|RedirectResponse
    {
        [$payment, $attempt] = $this->mine($trade_no);
        if (self::canPay($payment, $attempt) && Ready::enabled()) {
            return redirect()->away('https://app.jangopay.org/pay/'.rawurlencode($attempt->invoice_id));
        }

        return view('user.components.payment.jangopay', compact('payment', 'attempt'));
    }

    public function notify(Request $request): JsonResponse
    {
        try {
            $event = app(WebhookEnvelope::class)->decrypt($request);
            if (($event['event_type'] ?? null) === 'payout.completed') {
                // Merchant payouts share the webhook endpoint but do not fulfill customer orders.
                $this->trace($request, 'ignored_event', $event);

                return response()->json(['status' => 'ignored_event']);
            }
            $invoiceId = Verification::field($event, 'invoice_id', 'invoiceId');
            $orderId = Verification::field($event, 'order_id', 'orderId');
            $txnId = Verification::field($event, 'txn_id', 'txnId');
            $txHash = Verification::field($event, 'tx_hash', 'txHash');
            if (! is_string($invoiceId) || $invoiceId === ''
                || ! is_string($orderId) || $orderId === ''
                || ! is_string($txnId) || $txnId === ''
                || ($txHash !== null && (! is_string($txHash) || $txHash === '' || strlen($txHash) > 160))) {
                $this->trace($request, 'invalid_event', $event);

                return response()->json(['status' => 'invalid_event'], 422);
            }
            $attempt = JangoPayAttempt::whereOrderId($orderId)->first();
            if (! $attempt || ($attempt->invoice_id && $attempt->invoice_id !== $invoiceId)
                || ($attempt->txn_id && $attempt->txn_id !== $txnId)) {
                $this->trace($request, 'reconciliation_needed', $event);

                return response()->json(['status' => 'reconciliation_needed'], 503);
            }
            $result = app(Reconciliation::class)->check($attempt->id, $txnId,
                is_string($txHash) ? $txHash : null, $invoiceId);
            $attempt->refresh();
            $this->trace($request, $result, $event, $attempt);
            if (in_array($result, ['manual_review', 'resolved'], true)
                && $attempt->invoice_id === $invoiceId && $attempt->txn_id === $txnId && $attempt->tx_hash) {
                return response()->json(['status' => 'accepted_for_review']);
            }
            if ($result !== 'delivered') {
                return response()->json(['status' => $result], 503);
            }

            return $this->acknowledge($request, $attempt, Payment::find($attempt->payment_id));
        } catch (Throwable $e) {
            Log::warning('[JangoPay] Webhook verification rejected (sensitive payload redacted)', ['reason' => get_class($e)]);
            try {
                $this->trace($request, 'verification_unavailable', $event ?? []);
            } catch (Throwable $traceError) {
                Log::warning('[JangoPay] Callback trace unavailable', ['reason' => get_class($traceError)]);
            }

            return response()->json(['status' => 'verification_unavailable'], 503);
        }
    }

    private function trace(Request $request, string $result, array $event, ?JangoPayAttempt $attempt = null): void
    {
        $callback = PaymentCallback::find($request->attributes->get('payment_callback_id'));
        if (! $callback) {
            return;
        }
        $eventType = $request->header('X-JangoPay-Event');
        $payload = ['event_type' => is_string($eventType) ? substr($eventType, 0, 80) : null, 'result' => $result,
            'body_sha256' => hash('sha256', $request->getContent())];
        foreach (['invoice_id' => 'invoiceId', 'order_id' => 'orderId', 'txn_id' => 'txnId'] as $snake => $camel) {
            $value = Verification::field($event, $snake, $camel);
            if (is_string($value) && $value !== '' && strlen($value) <= 120) {
                $payload[$snake] = $value;
            }
        }
        $update = ['payload' => json_encode($payload, JSON_UNESCAPED_SLASHES)];
        if ($attempt) {
            $update += ['trade_no' => $attempt->order_id, 'out_trade_no' => substr((string) $attempt->txn_id, 0, 64),
                'amount' => $attempt->base_amount];
        }
        $callback->update($update);
    }

    private function acknowledge(Request $request, JangoPayAttempt $attempt, ?Payment $payment): JsonResponse
    {
        // Every encrypted retry has its own shared ledger row, even when delivery already happened.
        $callback = PaymentCallback::find($request->attributes->get('payment_callback_id'));
        if ($callback) {
            $callback->update(['trade_no' => $attempt->order_id, 'out_trade_no' => substr((string) $attempt->txn_id, 0, 64),
                'amount' => $payment?->amount ?? $attempt->base_amount, 'status' => PaymentCallback::STATUS_FULFILLED]);
        }

        return response()->json(['status' => 'success']);
    }
}
