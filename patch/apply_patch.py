#!/usr/bin/env python3
"""Apply small surgical changes to an existing checkout of ProxyPanel master.
This script aborts if an expected source anchor is missing or ambiguous; rerun safely.
The new adapter files are copied separately from this distribution.
"""
from pathlib import Path
import shutil
import sys

PKG = Path(__file__).resolve().parents[1]
ROOT = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else Path.cwd()

if not (ROOT / 'app/Utils/Payments/PaymentManager.php').is_file():
    raise SystemExit('Not a ProxyPanel source checkout. Usage: python patch/apply_patch.py /path/to/ProxyPanel')

# All string edits are planned and validated before writing files.
replacements = {}
def update(rel, before, after):
    path = ROOT / rel
    content = replacements.get(rel, path.read_text(encoding='utf-8'))
    if after in content:
        return
    if content.count(before) != 1:
        raise SystemExit(f'Patch anchor missing/ambiguous: {rel} : {before[:75]!r}')
    replacements[rel] = content.replace(before, after, 1)

update('routes/user.php', 'use App\\Utils\\Payments\\Manual;\n',
       'use App\\Utils\\Payments\\JangoPay;\nuse App\\Utils\\Payments\\Manual;\n')
update('routes/user.php', "Route::prefix('pay')->group(function () {\n",
       "Route::prefix('pay')->group(function () {\n"
       "    Route::get('/jangopay/{trade_no}', [JangoPay::class, 'checkout'])->name('jangopay.checkout');\n"
       )

update('routes/admin.php', 'use App\\Http\\Controllers\\Admin\\InviteController;\n',
       'use App\\Http\\Controllers\\Admin\\InviteController;\nuse App\\Http\\Controllers\\Admin\\JangoPayController;\n')
update('routes/admin.php', "Route::prefix('admin')->name('admin.')->group(function () {\n",
       "Route::prefix('admin')->name('admin.')->group(function () {\n"
       "    Route::get('payments/jangopay', [JangoPayController::class, 'index'])->name('jangopay.index');\n"
       "    Route::post('payments/jangopay/{id}/check', [JangoPayController::class, 'check'])->whereNumber('id')->name('jangopay.check');\n"
       "    Route::post('payments/jangopay/{id}/resolve', [JangoPayController::class, 'resolve'])->whereNumber('id')->name('jangopay.resolve');\n")
update('app/Console/Kernel.php', '        $this->persistOutput($schedule);\n',
       "        $schedule->command('jangopay:reconcile --limit=10')->everyFiveMinutes()->withoutOverlapping(120);\n\n"
       '        $this->persistOutput($schedule);\n')
update('app/Console/Commands/TaskAuto.php', 'use App\\Models\\Order;\n',
       'use App\\Models\\JangoPayAttempt;\nuse App\\Models\\Order;\nuse App\\Models\\Payment;\n')
update('app/Console/Commands/TaskAuto.php', 'use Log;\n',
       'use Illuminate\\Support\\Facades\\DB;\nuse Log;\n')
update('app/Console/Commands/TaskAuto.php', '            $orders->each->close();\n',
       "            $orders->each(function (Order $order) {\n                DB::transaction(function () use ($order) {\n                    $locked = Order::whereKey($order->id)->lockForUpdate()->first();\n                    if (! $locked) {\n                        return;\n                    }\n                    if ((int) $locked->status === 0 && $locked->pay_way === 'jangopay'\n                        && JangoPayAttempt::whereIn('payment_id', Payment::where('order_id', $locked->id)->select('id'))\n                            ->whereNotIn('state', ['delivered', 'resolved'])\n                            ->where('expires_at', '>', now()->subMinutes(JangoPayAttempt::CONFIRMATION_GRACE_MINUTES))->exists()) {\n                        return;\n                    }\n                    $locked->close(true);\n                });\n            });\n")
update('app/Models/Order.php', '    public function close(): bool\n',
       '    public function close(bool $automatic = false): bool\n')
update('app/Models/Order.php', 'DB::transaction(function (): bool {\n            $locked = static::whereKey($this->id)->lockForUpdate()->first();\n',
       'DB::transaction(function () use ($automatic): bool {\n            $locked = static::whereKey($this->id)->lockForUpdate()->first();\n            if ($automatic && $locked && ! in_array((int) $locked->status, [0, 1], true)) {\n                return false;\n            }\n')

update('database/seeders/RBACSeeder.php', "        'admin.index' => ",
       "        'admin.jangopay.index' => 'JangoPay reconciliation list',\n"
       "        'admin.jangopay.check' => 'JangoPay authenticated recheck',\n"
       "        'admin.jangopay.resolve' => 'JangoPay external resolution record',\n"
       "        'admin.index' => ")
update('resources/views/admin/logs/order.blade.php', '    <div class="page-content container-fluid">\n',
       '    <div class="page-content container-fluid">\n'
       "        @can('admin.jangopay.index')\n"
       "            <p><a class=\"btn btn-default\" href=\"{{ route('admin.jangopay.index') }}\">JangoPay · {{ trans('user.payment.jangopay.reconciliation') }}</a></p>\n"
       '        @endcan\n')

# JangoPay callbacks are encrypted: do not write ciphertext into the shared callback ledger
# and do not generate spurious unmatched-order alerts before decrypt/reconciliation.
update('app/Utils/Library/PaymentHelper.php',
       "        $method = (string) ($request->query('method') ?: $request->input('method'));\n        $tradeNo =",
       "        $method = (string) ($request->query('method') ?: $request->input('method'));\n"
       "        if ($method === 'jangopay') {\n"
       "            $payload = [\n"
       "                'event_type' => $payload['event_type'] ?? null,\n"
       "                'algorithm' => $payload['algorithm'] ?? null,\n"
       "            ];\n"
       "        }\n"
       "        $tradeNo =")
update('app/Utils/Library/PaymentHelper.php',
       '        if ($tradeNo === null) {\n            self::alertUnmatched($log);\n        }',
       "        if ($tradeNo === null && $method !== 'jangopay') {\n            self::alertUnmatched($log);\n        }")
update('app/Utils/Library/PaymentHelper.php', 'public static function paymentReceived(string $tradeNo): bool',
       'public static function paymentReceived(string $tradeNo, bool $afterCommit = false): bool')
update('app/Utils/Library/PaymentHelper.php',
       "                // 通知与广播失败不能让已收款的回调收到 500\n                try {\n                    $payment->user->notify(new PaymentReceived($payment->order->sn, $payment->amount_tag));\n                    broadcast(new PaymentStatusUpdated($tradeNo, 'success', trans('common.success_item', ['attribute' => trans('user.pay')]))); // 触发支付状态更新事件\n                } catch (Throwable $e) {\n                    Log::error('【支付履约】通知/广播失败：'.$e->getMessage());\n                }\n",
       "                $send = function () use ($payment, $tradeNo): void {\n                    // 通知与广播失败不能让已收款的回调收到 500\n                    try {\n                        $payment->user->notify(new PaymentReceived($payment->order->sn, $payment->amount_tag));\n                        broadcast(new PaymentStatusUpdated($tradeNo, 'success', trans('common.success_item', ['attribute' => trans('user.pay')]))); // 触发支付状态更新事件\n                    } catch (Throwable $e) {\n                        Log::error('【支付履约】通知/广播失败：'.$e->getMessage());\n                    }\n                };\n                if ($afterCommit) {\n                    DB::afterCommit($send);\n                } else {\n                    $send();\n                }\n")

update('database/seeders/ConfigSeeder.php', "        'maintenance_content',\n",
       "        'jangopay_assets',\n        'maintenance_content',\n")

LANGS = {
  'en': {
    'model': "JangoPay Enabled Assets / Networks (USDT:tron,USDC:ethereum)",
    'unavailable': 'JangoPay checkout is not configured or temporarily unavailable.',
    'unknown_creation': 'Invoice creation is unconfirmed. Please contact support before trying to pay again.',
    'local_amount': 'Local order total', 'usd_amount': 'Payment invoice amount',
    'rate': 'Saved exchange rate', 'choose_network': 'Choose a supported coin and network before sending funds.',
    'asset': 'Currency / network', 'confirm_asset': 'Get payment instructions',
    'instructions': 'Send only the exact amount on the selected network to the address shown.',
    'send_amount': 'Send amount', 'address': 'Receiving address',
    'waiting': 'Waiting for verified confirmation. Do not submit another payment.',
    'completed': 'Payment confirmed.', 'review': 'Payment needs verification or manual support. Do not send additional funds.',
    'orders': 'Back to orders',
  },
  'zh_CN': {
    'model': 'JangoPay 支持的币种及网络（如 USDT:tron,USDC:ethereum）',
    'unavailable': 'JangoPay 尚未完成配置或暂时不可用。',
    'unknown_creation': '尚无法确认收款单是否创建成功。请先联系客服，避免重复支付。',
    'local_amount': '原订单金额', 'usd_amount': '美元收款单金额',
    'rate': '创建订单时锁定的汇率', 'choose_network': '请先选择币种和链网络，确认后再转账。',
    'asset': '币种 / 网络', 'confirm_asset': '获取付款信息',
    'instructions': '务必使用显示的网络和金额转账，网络或金额错误可能造成资产损失。',
    'send_amount': '应付数量', 'address': '收款地址',
    'waiting': '等待支付平台核验。请勿重复付款。',
    'completed': '付款已确认。', 'review': '本单需要人工核查。请勿继续转账，请联系站长。',
    'orders': '返回订单列表',
  },
  'ja': {
    'model': 'JangoPay 利用可能な通貨とネットワーク（USDT:tron,USDC:ethereum）',
    'unavailable': 'JangoPayの設定が完了していないか、現在利用できません。',
    'unknown_creation': '請求書の作成が未確認です。重複送金せずサポートへ連絡してください。',
    'local_amount': '注文金額', 'usd_amount': 'USD請求額',
    'rate': '注文時の保存済みレート', 'choose_network': '送金前に通貨とネットワークを選択してください。',
    'asset': '通貨 / ネットワーク', 'confirm_asset': '送金情報を表示',
    'instructions': '表示されたネットワークと正確な金額で送金してください。',
    'send_amount': '送金数量', 'address': '入金アドレス',
    'waiting': '決済確認待ちです。重複送金しないでください。',
    'completed': '決済が確認されました。', 'review': '手動確認が必要です。追加送金せずサポートへ連絡してください。',
    'orders': '注文一覧に戻る',
  },
}

EXTRA = {
  'en': {
    'reconciliation': 'Payment reconciliation', 'order_reference': 'Order / invoice / transaction',
    'state': 'Local state', 'remote_state': 'Merchant state / last checked', 'action': 'Actions',
    'all_states': 'All states', 'search': 'Search', 'recheck': 'Verify with merchant',
    'recovery_help': 'Recheck only reads merchant records. It never creates an invoice or repeats coin confirmation. Closed or late payments stay on hold; arrange a refund or an agreed alternative in the merchant console before recording resolution here.',
    'resolution_note': 'External handling result and reference',
    'resolve_confirm': 'I confirm external customer handling is complete. This closes a pending local order; it neither refunds funds nor grants service.',
    'resolve': 'Record external resolution', 'operator_log': 'Recent operator actions',
    'invoice_hint': 'Merchant invoice ID (optional fallback)', 'transaction_hint': 'Merchant transaction ID (optional fallback)',
  },
  'zh_CN': {
    'reconciliation': '支付对账', 'order_reference': '订单号 / 发票号 / 交易号',
    'state': '本地状态', 'remote_state': '商户状态 / 最近核查', 'action': '操作',
    'all_states': '所有状态', 'search': '查询', 'recheck': '从商户核验',
    'recovery_help': '核验只读取商户记录，不重新开票或重复确认币种。关闭订单与过期付款保持待处理；请先在商户后台完成退款或与客户约定其他处理，再在这里登记处理结果。',
    'resolution_note': '实际处理结果及凭证编号',
    'resolve_confirm': '我确认已在本页面之外完成客户处理。此操作会关闭待付款本地订单，不会退款，也不会发放权益。',
    'resolve': '登记已在外部处理', 'operator_log': '最近操作记录',
    'invoice_hint': '商户发票号（自动查找失败时选填）', 'transaction_hint': '商户交易号（自动查找失败时选填）',
  },
  'ja': {
    'reconciliation': '決済照合', 'order_reference': '注文 / 請求書 / 取引番号',
    'state': 'ローカル状態', 'remote_state': '加盟店状態 / 最終確認', 'action': '操作',
    'all_states': 'すべて', 'search': '検索', 'recheck': '加盟店記録を確認',
    'recovery_help': '照合は記録の読み取りのみです。請求書作成や通貨確定を再送しません。終了・期限後の入金は保留されます。加盟店画面で返金等を実施してから対応結果を記録してください。',
    'resolution_note': '外部対応の結果と参照番号',
    'resolve_confirm': '外部対応を完了しました。未払いの注文は終了します。返金もサービス付与も行いません。',
    'resolve': '外部対応済みを記録', 'operator_log': '最近の操作',
    'invoice_hint': '加盟店請求書番号（任意）', 'transaction_hint': '加盟店取引番号（任意）',
  },
}
for lang, labels in EXTRA.items():
    LANGS[lang].update(labels)

def phpquote(value):
    return "'"+value.replace('\\','\\\\').replace("'", "\\'")+"'"

for lang, labels in LANGS.items():
    admin = f'resources/lang/{lang}/admin.php'
    update(admin, "                'cryptomus' => 'Cryptomus',\n",
           "                'cryptomus' => 'Cryptomus',\n                'jangopay' => 'JangoPay',\n")
    model = f'resources/lang/{lang}/model.php'
    update(model, "        'standard_currency' => ",
           "        'jangopay_assets' => "+phpquote(labels['model'])+",\n        'standard_currency' => ")
    user = f'resources/lang/{lang}/user.php'
    body = ''.join(f'            {phpquote(k)} => {phpquote(v)},\n' for k,v in labels.items() if k != 'model')
    update(user, "\n    'payment' => [\n", "\n    'payment' => [\n        'jangopay' => [\n"+body+"        ],\n")

# Preflight the files and stop before any writes if a destination conflicts.
for base in ('app', 'database', 'resources', 'config', 'tests', 'docs', 'patch'):
    for src in (PKG / base).rglob('*'):
        if src.is_file():
            rel = src.relative_to(PKG)
            dst = ROOT / rel
            planned = replacements.get(rel.as_posix())
            if dst.exists() and dst.read_bytes() != src.read_bytes() and (
                planned is None or planned.encode('utf-8') != src.read_bytes()
            ):
                raise SystemExit(f'Refusing to overwrite existing file: {dst}')

for rel, content in replacements.items():
    (ROOT / rel).write_bytes(content.encode('utf-8'))

copied = []
for base in ('app', 'database', 'resources', 'config', 'tests', 'docs', 'patch'):
    for src in (PKG / base).rglob('*'):
        if not src.is_file():
            continue
        rel = src.relative_to(PKG)
        dst = ROOT / rel
        if dst.exists() and dst.read_bytes() != src.read_bytes():
            raise SystemExit(f'Refusing to overwrite existing file: {dst}')
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        copied.append(str(rel))
print(f'Applied {len(replacements)} safe source edits; installed {len(copied)} adapter files.')
print('Review with git diff, then run php artisan migrate and php artisan optimize:clear.')
