<?php

require __DIR__.'/bootstrap.php';
use App\Services\JangoPay\Money;
use App\Services\JangoPay\Verification;

$fixture = json_decode(file_get_contents(__DIR__.'/../fixtures/merchant-paid.json'), true, 512, JSON_THROW_ON_ERROR);
$inv = $fixture['invoice'];
$txn = $fixture['transaction'];
$valid = fn ($i, $t) => Verification::matches($i, $t, 'I1', 'ORDER1', 'T1', '3.00', 'USDT:bsc', ['paid'], '3', 'SYNTHETIC_RECEIVER');
check($valid($inv, $txn), 'real net-of-fee contract without receiveAddress');
check(! $valid($inv, array_replace($txn, ['receiveAddress' => 'WRONG'])), 'explicit receiving address mismatch rejected');
check(! $valid($inv, array_replace($txn, ['netAmount' => '2.98'])), 'incorrect fee equation rejected');
check(! $valid(array_replace($inv, ['payedAmount' => '2.96']), $txn), 'invoice net disagrees with transaction');
check(! $valid($inv, array_replace($txn, ['feeAmount' => '-0.03'])), 'negative fee rejected');
check(! $valid($inv, array_replace($txn, ['feeAmount' => '0.030000001'])), 'unsupported precision rejected');
check(! $valid($inv, array_replace($txn, ['feePayByUser' => true])), 'unknown payer-fee contract rejected');
check(! $valid($inv, array_replace($txn, ['currencyCode' => 'CNY'])), 'non-USD settlement rejected');
check(! $valid($inv, array_replace($txn, ['invoiceId' => 'FOREIGN'])), 'foreign invoice rejected');
check(! $valid($inv, array_replace($txn, ['payAmount' => '2.99'])), 'coin underpayment rejected');
check(! $valid($inv, array_replace($txn, ['netAmount' => 2.97])), 'binary floats rejected');
check(Money::ledger('0.00000001') === 1 && Money::ledger('2.97000000') === 297000000, 'eight-decimal integer arithmetic');
check(! $valid(array_replace($inv, ['payedAmount' => '3.00']), array_replace($txn, ['amount' => '300.00', 'netAmount' => '3.00'])), 'full-paid flag cannot mask wrong gross');
check(! $valid(array_replace($inv, ['payedAmount' => '3.00']), array_replace($txn, ['netAmount' => '3.00'])), 'full-paid flag cannot mask incorrect fee math');
echo "PASS {$checks} merchant contract checks (financial field shape verified by authenticated GET)\n";
