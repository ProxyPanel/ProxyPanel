<?php

// Standalone synthetic checks (NO Laravel, payment network, DB or merchant credentials).

namespace Illuminate\Http {
    class Request
    {
        public function __construct(private array $headers, private array $payload) {}

        public function header(string $name): ?string
        {
            return $this->headers[$name] ?? null;
        }

        public function getContent(): string
        {
            return json_encode($this->payload);
        }

        public function json(): object
        {
            return new class($this->payload)
            {
                public function __construct(private array $data) {}

                public function all(): array
                {
                    return $this->data;
                }
            };
        }
    }
}

namespace {
    use App\Services\JangoPay\Assets;
    use App\Services\JangoPay\Money;
    use App\Services\JangoPay\Verification;
    use App\Services\JangoPay\WebhookEnvelope;
    use Illuminate\Http\Request;

    require __DIR__.'/../../app/Services/JangoPay/Money.php';
    require __DIR__.'/../../app/Services/JangoPay/Assets.php';
    require __DIR__.'/../../app/Services/JangoPay/Verification.php';
    require __DIR__.'/../../app/Services/JangoPay/WebhookEnvelope.php';

    $config = ['jangopay.webhook_tag_format' => 'appended16', 'jangopay.private_key_path' => ''];
    function config(string $key, $default = null)
    {
        global $config;

        return $config[$key] ?? $default;
    }
    $passes = 0;
    function ok($expr, string $label)
    {
        global $passes;
        if (! $expr) {
            throw new RuntimeException('FAIL '.$label);
        } $passes++;
    }
    function rejects(callable $f): bool
    {
        try {
            $f();

            return false;
        } catch (Throwable) {
            return true;
        }
    }

    ok(Money::cents('12.34000000') === 1234, 'fixed precision');
    ok(Money::equalUsd('12.3', '12.30'), 'equivalent cents');
    ok(! Money::equalUsd('12.30', '12.301'), 'no rounding sub-cents');
    ok(rejects(fn () => Money::cents('0')), 'zero');
    ok(rejects(fn () => Money::cents('-1')), 'negative');
    ok(rejects(fn () => Money::cents('999999999999')), 'overflow');
    ok(Money::fromMajor(2.5) === '2.50', 'float normalized at boundary');
    ok(Assets::parse('USDT:tron,USDC:ethereum,USDT:tron') === ['USDT:tron', 'USDC:ethereum'], 'dedup allowlist');
    ok(rejects(fn () => Assets::parse('BTC:tron')), 'reject nonstablecoin');
    ok(rejects(fn () => Assets::parse('USDT:tron,USDT:TRON')), 'case sensitive chain');
    $enabled = Assets::merchantEnabled(['USDT:tron', 'USDC:ethereum'], [
        ['code' => 'USDT', 'chain' => 'tron', 'enabled' => true, 'invoicePaymentEnabled' => true],
        ['code' => 'USDC', 'chain' => 'ethereum', 'enabled' => true, 'invoicePaymentEnabled' => false],
    ]);
    ok($enabled === ['USDT:tron'], 'merchant-enabled only');
    $asset = ['code' => 'USDT', 'chain' => 'bsc', 'enabled' => true, 'invoicePaymentEnabled' => true];
    foreach (['enabled', 'invoicePaymentEnabled'] as $flag) {
        foreach ([false, null, 1, 'true', 'false'] as $value) {
            ok(Assets::merchantEnabled(['USDT:bsc'], [array_replace($asset, [$flag => $value])]) === [],
                'reject disabled or non-boolean asset flag '.$flag.' '.json_encode($value));
        }
        $missing = $asset;
        unset($missing[$flag]);
        ok(Assets::merchantEnabled(['USDT:bsc'], [$missing]) === [], 'reject missing asset flag '.$flag);
    }
    $inv = ['invoiceId' => 'INV', 'orderId' => 'TRADENO', 'currencyCode' => 'USD', 'amount' => '12.30', 'payedAmount' => '12.30', 'status' => 'paid'];
    $txn = ['txnId' => 'TXN', 'invoiceId' => 'INV', 'status' => 'settled', 'payCurrency' => 'USDT', 'chain' => 'tron'];
    ok(Verification::matches($inv, $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid']), 'full merchant match');
    ok(! Verification::matches(array_replace($inv, ['orderId' => 'OTHER']), $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid']), 'order mismatch');
    ok(! Verification::matches(array_replace($inv, ['payedAmount' => '12.29']), $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid']), 'underpaid mismatch');
    ok(! Verification::matches($inv, array_replace($txn, ['status' => 'pending']), 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid']), 'unsettled rejected');
    ok(! Verification::matches($inv, $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', []), 'unknown invoice final status rejected');
    ok(! Verification::matches($inv, $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDC:ethereum', ['paid']), 'asset mismatch');
    $detailTxn = $txn + ['payAmount' => '10.12340000', 'receiveAddress' => 'TAddress'];
    ok(Verification::matches($inv, $detailTxn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid'], '10.1234', 'TAddress'), 'cryptocurrency quote confirmed');
    ok(! Verification::matches($inv, $detailTxn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid'], '10.1235', 'TAddress'), 'quote mismatch');
    ok(! Verification::matches($inv, $detailTxn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid'], '10.1234', 'OtherAddress'), 'receive address mismatch');
    ok(! Verification::matches($inv, $txn, 'INV', 'TRADENO', 'TXN', '12.30', 'USDT:tron', ['paid'], '10.1234', 'TAddress'), 'missing quote fields rejected');
    ok(! Verification::matches($inv, $txn, 'INV', 'TRADENO', 'WRONG', '12.30', 'USDT:tron', ['paid']), 'transaction ownership');

    if (! function_exists('openssl_pkey_new')) {
        throw new RuntimeException('Need ext-openssl');
    }
    $rsa = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    if (! $rsa || ! openssl_pkey_export($rsa, $private)) {
        throw new RuntimeException('RSA generation failed');
    }
    $dir = sys_get_temp_dir().'/jangopay-test-'.bin2hex(random_bytes(8));
    mkdir($dir, 0700);
    $keyPath = $dir.'/private.pem';
    $pubPath = $dir.'/public.pem';
    file_put_contents($keyPath, $private);
    chmod($keyPath, 0600);
    file_put_contents($pubPath, openssl_pkey_get_details($rsa)['key']);
    $config['jangopay.private_key_path'] = $keyPath;
    $aes = random_bytes(32);
    $nonce = random_bytes(12);
    $message = ['event_type' => 'payment.completed', 'invoice_id' => 'INV', 'order_id' => 'TRADENO', 'txn_id' => 'TXN'];
    $plain = json_encode($message);
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $aes, OPENSSL_RAW_DATA, $nonce, $tag);
    $cmd = ['openssl', 'pkeyutl', '-encrypt', '-pubin', '-inkey', $pubPath,
        '-pkeyopt', 'rsa_padding_mode:oaep', '-pkeyopt', 'rsa_oaep_md:sha256', '-pkeyopt', 'rsa_mgf1_md:sha256'];
    $p = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
    fwrite($pipes[0], $aes);
    fclose($pipes[0]);
    $encrypted = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    if (proc_close($p) !== 0) {
        throw new RuntimeException('RSA encryption failed');
    }
    $headers = ['Content-Type' => 'application/json', 'X-JangoPay-Event' => 'payment.completed',
        'X-JangoPay-Encryption-Alg' => 'AES-256-GCM', 'X-JangoPay-Key-Encryption' => 'RSA-OAEP-SHA256'];
    $payload = ['event_type' => 'payment.completed', 'algorithm' => 'AES-256-GCM',
        'key_algorithm' => 'RSA-OAEP-SHA256', 'encrypted_key' => base64_encode($encrypted),
        'nonce' => base64_encode($nonce), 'ciphertext' => base64_encode($cipher.$tag)];
    $service = new WebhookEnvelope;
    ok($service->decrypt(new Request($headers, $payload)) === $message, 'synthetic RSA-OAEP-SHA256 + AES-256-GCM');
    $payoutMessage = ['event_type' => 'payout.completed', 'payout_id' => 'PAYOUT'];
    $payoutCipher = openssl_encrypt(json_encode($payoutMessage), 'aes-256-gcm', $aes, OPENSSL_RAW_DATA, $nonce, $payoutTag);
    $payoutPayload = array_replace($payload, ['event_type' => 'payout.completed', 'ciphertext' => base64_encode($payoutCipher.$payoutTag)]);
    $payoutHeaders = array_replace($headers, ['X-JangoPay-Event' => 'payout.completed']);
    ok($service->decrypt(new Request($payoutHeaders, $payoutPayload)) === $payoutMessage, 'payout also requires authenticated decryption');
    ok(rejects(fn () => $service->decrypt(new Request($payoutHeaders, $payload))), 'reject mismatched payout header and payment envelope');
    ok(rejects(fn () => $service->decrypt(new Request($headers, array_replace($payload, ['ciphertext' => $payoutPayload['ciphertext']])))), 'reject mismatched decrypted event type');
    $tampered = $payload;
    $ct = base64_decode($tampered['ciphertext'], true);
    $ct[0] = chr(ord($ct[0]) ^ 1);
    $tampered['ciphertext'] = base64_encode($ct);
    ok(rejects(fn () => $service->decrypt(new Request($headers, $tampered))), 'reject tampered ciphertext');
    ok(rejects(fn () => $service->decrypt(new Request(array_replace($headers, ['X-JangoPay-Event' => 'payment.failed']), $payload))), 'reject wrong event');
    $config['jangopay.webhook_tag_format'] = '';
    ok(rejects(fn () => $service->decrypt(new Request($headers, $payload))), 'fail closed without validated tag framing');
    unlink($keyPath);
    unlink($pubPath);
    rmdir($dir);
    echo "PASS {$passes} standalone checks (synthetic only)\n";
}
