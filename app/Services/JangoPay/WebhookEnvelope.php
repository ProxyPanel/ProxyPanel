<?php

namespace App\Services\JangoPay;

use Illuminate\Http\Request;
use RuntimeException;

/**
 * RSA-OAEP-SHA256 unwrap + authenticated AES-256-GCM decrypt.
 * Appended 16-byte tags were verified with authentic merchant callbacks.
 * Enable that framing explicitly for the merchant account before collection.
 */
class WebhookEnvelope
{
    public function decrypt(Request $request): array
    {
        $type = $request->header('X-JangoPay-Event');
        if (! in_array($type, ['payment.completed', 'payout.completed'], true)
            || $request->header('X-JangoPay-Encryption-Alg') !== 'AES-256-GCM'
            || $request->header('X-JangoPay-Key-Encryption') !== 'RSA-OAEP-SHA256') {
            throw new RuntimeException('Unrecognized JangoPay webhook headers.');
        }
        $contentType = strtolower((string) $request->header('Content-Type'));
        if (! preg_match('~^application/json(?:\s*;|$)~', $contentType)
            || strlen($request->getContent()) > 32768) {
            throw new RuntimeException('Invalid JangoPay content type or body size.');
        }
        $body = $request->json()->all();
        if (! is_array($body) || ($body['event_type'] ?? null) !== $type
            || ($body['algorithm'] ?? null) !== 'AES-256-GCM'
            || ($body['key_algorithm'] ?? null) !== 'RSA-OAEP-SHA256') {
            throw new RuntimeException('Webhook envelope mismatch.');
        }
        if (config('jangopay.webhook_tag_format') !== 'appended16') {
            throw new RuntimeException('Webhook tag framing has not been validated.');
        }
        $path = (string) config('jangopay.private_key_path', '');
        if ($path === '' || ! is_readable($path) || ! is_file($path)) {
            throw new RuntimeException('JangoPay private key is unavailable.');
        }
        $keyCipher = $this->base64($body['encrypted_key'] ?? null, 1024);
        $nonce = $this->base64($body['nonce'] ?? null, 32);
        $cipher = $this->base64($body['ciphertext'] ?? null, 32768);
        if (strlen($nonce) !== 12 || strlen($cipher) < 17) {
            throw new RuntimeException('Invalid JangoPay encryption field lengths.');
        }
        $key = $this->unwrap($keyCipher, $path);
        if (strlen($key) !== 32) {
            throw new RuntimeException('Invalid AES session key length.');
        }
        $plain = openssl_decrypt(substr($cipher, 0, -16), 'aes-256-gcm', $key,
            OPENSSL_RAW_DATA, $nonce, substr($cipher, -16));
        if (! is_string($plain)) {
            throw new RuntimeException('GCM authentication failed.');
        }
        $payload = json_decode($plain, true);
        if (! is_array($payload) || (($payload['event_type'] ?? $type) !== $type)) {
            throw new RuntimeException('Invalid JangoPay event.');
        }

        return $payload + ['event_type' => $type];
    }

    private function base64(mixed $input, int $maxBytes): string
    {
        if (! is_string($input) || strlen($input) < 4 || strlen($input) > 2 * $maxBytes) {
            throw new RuntimeException('Invalid base64 field.');
        }
        $decoded = base64_decode($input, true);
        if (! is_string($decoded) || strlen($decoded) > $maxBytes) {
            throw new RuntimeException('Invalid base64 value.');
        }

        return $decoded;
    }

    public function assertAvailable(string $keyPath): void
    {
        $probe = random_bytes(32);
        if (! hash_equals($probe, $this->unwrap($this->unwrap($probe, $keyPath, false), $keyPath))) {
            throw new RuntimeException('RSA-OAEP SHA256 self-test failed.');
        }
    }

    private function unwrap(string $data, string $keyPath, bool $decrypt = true): string
    {
        if (! function_exists('proc_open')) {
            throw new RuntimeException('OpenSSL executable support is unavailable.');
        }
        // PHP's OPENSSL_PKCS1_OAEP_PADDING defaults to SHA-1, not SHA-256.
        $command = ['openssl', 'pkeyutl', $decrypt ? '-decrypt' : '-encrypt', '-inkey', $keyPath,
            '-pkeyopt', 'rsa_padding_mode:oaep', '-pkeyopt', 'rsa_oaep_md:sha256',
            '-pkeyopt', 'rsa_mgf1_md:sha256'];
        $proc = @proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (! is_resource($proc)) {
            throw new RuntimeException('OpenSSL subprocess could not start.');
        }
        fwrite($pipes[0], $data);
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        // Deliberately omit potentially sensitive OpenSSL stderr.
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if (proc_close($proc) !== 0 || ! is_string($out)) {
            throw new RuntimeException('RSA-OAEP SHA256 key unwrap failed.');
        }

        return $out;
    }
}
