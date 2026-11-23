<?php

namespace App\Services\JangoPay;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Narrow server-only API adapter; no payouts, transfers or cryptocurrency conversion. */
class Api
{
    private const BASE = 'https://api.jangopay.org';

    private ?string $token = null;

    public function __construct(private ?string $key = null)
    {
        $this->key = $key ?? (string) config('jangopay.api_key', '');
    }

    private function request(string $method, string $path, string $authorization, ?array $body = null): array
    {
        // Redirects and automatic POST retries are intentionally disabled.
        $req = Http::acceptJson()->connectTimeout(4)->timeout(12)->withOptions(['allow_redirects' => false])
            ->withHeaders($authorization === '' ? ['Content-Type' => 'application/json'] : ['Authorization' => $authorization, 'Content-Type' => 'application/json']);
        $res = match ($method) {
            'GET' => $req->get(self::BASE.$path),
            'POST' => $req->post(self::BASE.$path, $body ?? new \stdClass),
            default => throw new RuntimeException('Unsupported API method.'),
        };
        $json = $res->json();
        if (! $res->successful() || ! is_array($json) || ($json['success'] ?? true) === false
            || (array_key_exists('code', $json) && $json['code'] !== '0000')) {
            throw new RuntimeException('JangoPay API did not confirm success (HTTP '.$res->status().').');
        }

        return $json;
    }

    private function token(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }
        if ($this->key === null || ! str_starts_with($this->key, 'JangoPay_')) {
            throw new RuntimeException('Missing or invalid JangoPay API key.');
        }
        $reply = $this->request('POST', '/api/v1/auth/apikey/token', 'Basic '.$this->key);
        $value = $reply['data']['accessToken'] ?? $reply['data']['access_token'] ?? null;
        if (! is_string($value) || $value === '') {
            throw new RuntimeException('JangoPay access token missing.');
        }

        return $this->token = $value;
    }

    private function merchant(string $method, string $path, ?array $payload = null): array
    {
        return $this->request($method, $path, 'Bearer '.$this->token(), $payload);
    }

    public function assets(): array
    {
        $reply = $this->merchant('GET', '/api/v1/invoice/payment-assets');
        if (! is_array($reply['items'] ?? null)) {
            throw new RuntimeException('Invalid JangoPay payment-assets response.');
        }

        return $reply['items'];
    }

    public function createInvoice(string $orderId, string $usd, array $assets): array
    {
        // JangoPay PHP SDK sends top-level snake_case fields.
        $reply = $this->merchant('POST', '/api/v1/invoice/create', [
            'currency_code' => 'USD', 'amount' => $usd, 'order_id' => $orderId,
            'allowed_currency' => $assets, 'cover_pct' => 100, 'mix_pay' => false,
            'auto_to_u' => false, 'auto_withdraw' => false,
        ]);
        $invoice = $reply['invoice'] ?? null;
        if (! is_array($invoice)) {
            throw new RuntimeException('Invoice response is incomplete.');
        }

        return $invoice;
    }

    public function findInvoice(string $orderId): ?array
    {
        $matches = array_values(array_filter($this->scan('/api/v1/invoice/list', 'invoices'),
            fn ($row) => Verification::field($row, 'order_id', 'orderId') === $orderId));
        if (count($matches) > 1) {
            throw new RuntimeException('Ambiguous merchant invoices.');
        }

        return $matches[0] ?? null;
    }

    public function transactions(string $invoiceId): array
    {
        return array_values(array_filter($this->scan('/api/v1/invoice/transactions', 'transactions'),
            fn ($row) => Verification::field($row, 'invoice_id', 'invoiceId') === $invoiceId));
    }

    private function scan(string $path, string $key): array
    {
        $rows = [];
        $total = null;
        $pages = null;
        for ($page = 1; $page <= 5; $page++) {
            $body = $this->merchant('GET', $path.'?page='.$page.'&page_size=100');
            $items = $body[$key] ?? null;
            if (! is_array($items) || ! array_is_list($items) || count($items) > 100
                || ! is_int($body['total'] ?? null) || ! is_int($body['totalPages'] ?? null)
                || ($body['page'] ?? null) !== $page || ($body['pageSize'] ?? null) !== 100
                || $body['total'] < 0 || $body['total'] > 500
                || $body['totalPages'] < 0 || $body['totalPages'] > 5
                || ($page > 1 && ($total !== $body['total'] || $pages !== $body['totalPages']))) {
                throw new RuntimeException('Merchant pagination incomplete or changed.');
            }
            $total = $body['total'];
            $pages = $body['totalPages'];
            if ($pages !== (int) ceil($total / 100) && ! ($total === 0 && $pages === 1)) {
                throw new RuntimeException('Merchant pagination inconsistent.');
            }
            foreach ($items as $row) {
                if (! is_array($row)) {
                    throw new RuntimeException('Invalid merchant row.');
                }
                $id = Verification::field($row, $key === 'invoices' ? 'invoice_id' : 'txn_id', $key === 'invoices' ? 'invoiceId' : 'txnId');
                if (! is_string($id) || $id === '' || isset($rows[$id])) {
                    throw new RuntimeException('Duplicate or invalid merchant row.');
                }
                $rows[$id] = $row;
            }
            if ($page >= $pages) {
                if (count($rows) !== $total) {
                    throw new RuntimeException('Merchant scan is incomplete.');
                }

                return array_values($rows);
            }
        }
        throw new RuntimeException('Merchant scan limit reached.');
    }

    public function invoice(string $id): array
    {
        $reply = $this->merchant('GET', '/api/v1/invoice/'.rawurlencode($id));

        return is_array($reply['invoice'] ?? null) ? $reply['invoice'] : $reply;
    }

    public function transaction(string $id): array
    {
        $reply = $this->merchant('GET', '/api/v1/invoice/transaction/'.rawurlencode($id));

        return is_array($reply['transaction'] ?? null) ? $reply['transaction'] : $reply;
    }
}
