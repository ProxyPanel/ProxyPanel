# JangoPay hosted checkout

Payment integration for all merchant-enabled assets and networks for ProxyPanel. Customers open the official JangoPay hosted checkout; ProxyPanel creates the invoice, verifies merchant records and fulfills its existing native order. There is no local coin selection, payment address display or confirm-coin request.

## Installation and configuration

The integration targets ProxyPanel master at `6e5a0cfeb454f47676f3c0f4fe348fcd3bffd855`, with its existing PHP 8.1+ / Laravel 10 dependencies. No Composer dependency is added. Review and deploy the PR on a clean branch, back up the database, then run:

```bash
php artisan migrate
php artisan optimize:clear
```

Keep all four JangoPay migrations. Rollback intentionally retains financial evidence. Configure server secrets outside Git and keep the RSA private PEM outside the public web root:

```dotenv
JANGOPAY_API_KEY=your_merchant_key
JANGOPAY_RSA_PRIVATE_KEY_PATH=/absolute/private/path/private.pem
JANGOPAY_ENABLE_PAYMENTS=false
JANGOPAY_WEBHOOK_TAG_FORMAT=appended16
JANGOPAY_FINAL_INVOICE_STATUSES=paid
```

`appended16`, final invoice `paid`, transaction `settled` and merchant-paid fees were verified for the tested merchant and USDT/BSC payment on 2026-10-23. Verify these settings for the deploying account before enabling collection. Empty framing/status configuration or unavailable crypto support blocks new collection.

In native administrator payment settings, **JangoPay Assets / Networks** accepts:

- `*` or blank: all asset/network pairs currently enabled for invoice payments by the merchant. The plugin fetches the merchant payment-assets API for each new invoice; it does not maintain a fixed coin/network catalog.
- An explicit list such as `USDT:bsc,USDC:ethereum,BTC:bitcoin`: restrict new invoices to the intersection of that list and the merchant-enabled pairs. Examples are identifiers, not a promise that the deploying account enables them.

Both `enabled` and `invoicePaymentEnabled` must be JSON boolean `true`. Missing, false or loosely typed flags cannot enable an asset, including in all-assets mode. Each invoice sends and persists the resolved explicit list; `*` is never sent to the invoice API or saved as settlement authorization. Existing orders keep that snapshot when account/plugin settings later change. An existing explicit USDT/USDC configuration remains restricted until the operator changes it to `*` or blank.

There is no USDT/USDC restriction or twenty-pair count limit. Pair identifiers are bounded by the existing 80-character database column and the saved list by its text capacity. Unsupported/malformed merchant metadata or an empty enabled set cannot create a payable invoice. New supported pairs become available to new invoices through merchant metadata without a plugin release. Previously created invoices are not expanded retroactively.

JangoPay hosted checkout handles token pricing, selection, exact payment quantity, network, receiving address and QR code. Local denomination remains USD and native delivery still requires complete authenticated identity, fiat amount, fee and transaction proof. Token quantity comparisons use exact decimal strings within the saved proof field capacity, including more than eighteen fractional digits; USD accounting precision is unchanged.

Register the matching RSA public key in JangoPay Developer settings and set the HTTPS callback:

```text
https://your-proxypanel-site.example/callback/notify?method=jangopay
```

The PHP worker needs ext-openssl, AES-256-GCM, `proc_open`, an OpenSSL CLI on PATH and read access to the private key. Readiness executes an OAEP-SHA256 encryption/decryption round trip. Verify the actual PHP-FPM environment and reload workers/config caches after changing credentials. A CLI success does not establish PHP-FPM readiness.

Run a controlled payment for the deploying account, verify callback decryption, authenticated merchant proof and exactly one native delivery, then set `JANGOPAY_ENABLE_PAYMENTS=true`. The option is false by default. All merchant-enabled pairs are configurable. Per user scope, this asset expansion does not repeat real payments; the completed live payment test covers USDT/BSC only.

## Customer payment flow

1. Native checkout reserves one order/payment/attempt and creates one USD invoice.
2. The persisted local payment link requires the customer's authenticated ownership. It redirects only to `https://app.jangopay.org/pay/{saved_invoice_id}` after checking native status, gateway, owner, mapping, amounts and the payment window. Invoice IDs are validated; no response-supplied arbitrary redirect URL is trusted.
3. JangoPay handles asset selection, address display and payment instructions. ProxyPanel sends no coin-confirmation request.
4. A genuine `payment.completed` notification is decrypted and checked against authenticated merchant invoice and transaction reads. Native delivery and its durable completion marker commit in one database transaction.
5. The native invoice page remains the local entry for checking the order. Paid, closed, expired or inconsistent records do not receive a new hosted redirect.

The local payment window is 15 minutes. Automatic order closing permits another 10 minutes of confirmation grace. A previously issued hosted URL is not revoked by closing the local entry; this adapter does not cancel provider invoices. Payments after the original window or against a closed native order require operator handling. A delayed notification can recover a payment only when authenticated `paidAt` proves it occurred within the original window.

## Verification and duplicate handling

The verifier requires the saved order/invoice/transaction identity, USD amount, permitted asset/network, final status, transaction hash and consistent fees. Decimal calculations avoid floating-point settlement comparisons. Merchant-paid fees require explicit flags and exact `gross - fee = net = invoice payedAmount`. Unsupported or inconsistent settlement remains unfulfilled.

RSA-OAEP-SHA256 and AES-256-GCM verify the encrypted envelope, including agreement between HTTP header, outer event and decrypted event type. Appended 16-byte GCM tags were observed in authentic payment and payout callbacks. `payment.completed` can fulfill an order only through merchant proof verification. Successfully decrypted `payout.completed` is acknowledged as `ignored_event`, audited as unfulfilled and never used for customer delivery. Other event types or invalid ciphertext are rejected.

Repeated payment events compare supplied identifiers and any supplied transaction hash with durable proof, including after the delivery lock. Mismatches are audited without repeat credit. Native credit, payment state and completion marker share one transaction. Success notifications are dispatched after the outer transaction commits; rollback discards them. This prevents duplicate dispatch through payment retries, but does not promise durable exactly-once email transport after a worker/provider failure.

Callback audit records contain identifiers, verification result and body SHA-256. They omit raw ciphertext, keys and complete payer data. Verified payments held for review return `accepted_for_review` while the native audit remains unfulfilled. Missing mappings, mismatched evidence or unavailable verification remain non-2xx for retry; concurrent delivery does not relabel a failed check as fulfilled.

## Reconciliation and recovery

Keep the normal Laravel scheduler running, usually `php artisan schedule:run` every minute. It runs `jangopay:reconcile --limit=10` every five minutes without overlap. Manual commands:

```bash
php artisan jangopay:reconcile --attempt=123
php artisan jangopay:reconcile --limit=25
```

The ID is local `jangopay_attempts.id`. Batch checks prioritize least recently checked unresolved attempts, up to 100 per run.

- Invoice creation is attempted once. A timeout can mean the provider accepted it; no blind second creation is sent. Authenticated discovery can recover a uniquely matching invoice and repair the local payment URL while native eligibility still holds. Closed, paid, expired or inconsistent orders do not gain an entry.
- Invoice/transaction discovery validates pagination, identity and uniqueness, with a limit of five pages / 500 merchant records per list. It does not rely on an undocumented invoice filter or assume invoice-detail transaction arrays are complete. A refused/incomplete scan cannot authorize delivery. Larger histories need known invoice/transaction IDs or a reliable platform query contract.
- Legacy `quote_unknown` attempts cannot recover payment instructions from the verified transaction responses; `payAddress` is the payer and is not a receiving address. They retain `quote_instructions_unrecoverable`, permitting later settled payments to be reconciled with full merchant proof. No confirm-coin resend or quotation reconstruction exists. New hosted payments do not use this local selection path.
- Reconciliation never reopens a closed order and never bypasses financial checks. Do not create another top-up to resolve an uncertain earlier payment.

## Operator review

Open **JangoPay reconciliation** in the native administrator order log, or `/admin/payments/jangopay`. Existing roles need:

- `admin.jangopay.index`: search attempts and view recent operator actions.
- `admin.jangopay.check`: authenticated recheck, optionally using known invoice/transaction IDs. These are hints, never authority to deliver.
- `admin.jangopay.resolve`: record handling already completed outside ProxyPanel, with confirmation and a 10–1000 character note. It closes an eligible pending native order and records the actor. It neither refunds money nor grants service.

The upgrade migration inserts permissions on an already-seeded installation; a fresh installation uses the normal RBAC seeder. Assign permissions through native role management. Forms require CSRF and permission checks. In-flight mutation/fulfillment states cannot be resolved by the review button. For late, closed or ambiguous payments, inspect merchant evidence, handle the customer's funds through the normal merchant process, then record the outcome.

## CNY pricing

USD needs no exchange-rate request or reference. CNY requires an independently approved reference:

```dotenv
JANGOPAY_FX_REFERENCE_USD_PER_CNY=
JANGOPAY_FX_REFERENCE_VERIFIED_AT=
JANGOPAY_FX_MAX_DEVIATION=0.05
```

Use USD per CNY and the actual approval time in RFC3339 with `Z` or an offset. The reference must be no more than 24 hours old and not future-dated. Default relative tolerance is 5%, configurable above zero up to 20%. Reference independence is the operator's responsibility; do not copy the primary provider/cache automatically.

Fresh primary pricing uses a five-minute cache, configured static bounds and the approved reference. Missing/expired approval, stale data, provider failure or excessive deviation blocks new CNY invoices. There is no expired/fixed-rate fallback. Each attempt retains its applied rate and approval snapshot; existing invoices are never repriced. Older records remain nullable. Monitor logs for `fx_reference_*`, `fx_rate_out_of_bounds`, `fx_provider_unavailable` and `fx_deviation_exceeded`; no external alert channel is configured.

## External services and data

- `https://api.jangopay.org`: server-side merchant authentication, enabled assets, invoice creation and invoice/transaction queries. Sends merchant credentials to authentication and a local trade number, USD amount and asset allowlist for invoice creation. Credentials and private keys never enter the browser.
- `https://app.jangopay.org/pay/{invoice_id}`: customer browser opens JangoPay hosted checkout with the saved invoice ID; the provider handles asset selection and payer interaction.
- `https://open.er-api.com/v6/latest/CNY`: primary CNY pricing, without merchant credentials or order identifiers. USD orders do not call it.

There are no payout, transfer, automatic refund or cryptocurrency conversion operations. Acknowledging a payout notification does not initiate a payout. No application dependency is added.

## Validation

Run the offline suite with a clean upstream checkout and installed upstream dependencies:

```bash
python3 tests/regression/run.py \
  --upstream /path/to/clean/ProxyPanel \
  --vendor-autoload /path/to/ProxyPanel/vendor/autoload.php
```

Optional arguments: `--php`, `--php-ini`, `--output`. Requires Python 3 without `-O`, PHP SQLite/OpenSSL and OpenSSL CLI. Merchant HTTP, decrypted callback fixtures and the lightweight test user are substituted in this suite; upstream order/payment/coupon/observer/service code and Blade are exercised. Cryptography has independent synthetic tests. Real callbacks are a separate acceptance layer.

The 2026-10-23 validation used PHP 8.5.11 / Laravel 10.50.3 / MariaDB 11.8.9. The current isolated suite passed 287 assertions and four patch checks plus syntax checks, including generic tokens, lists above twenty pairs, strict enable flags, wildcard resolution, frozen invoice allowlists, nonstablecoin settlement and duplicate protection. No live merchant requests were made in this expansion. The hosted baseline also passed ten MariaDB migration checks and eight two-process rounds: six single deliveries, one closed-order hold and two read-only hosted redirects with zero coin POSTs; those baseline concurrency results are not claimed as newly repeated asset-specific acceptance.

Real acceptance used the full isolated upstream application with actual native User/Order/Payment models, an authenticated customer session, official hosted checkout and one new USD 3.00 USDT/BSC payment. An original encrypted callback completed the order and produced one USD 3.00 native credit. The user's transaction hash matched saved and authenticated merchant proof. Two local replays of that captured payment ciphertext remained successful without another credit or notification. A captured payout ciphertext was decrypted, acknowledged as ignored and did not deliver an order. One notification ID was dispatched to database and log-mail channels; no external email delivery was tested. Temporary public ingress was closed after acceptance. Raw financial records, secrets and callback bodies are excluded from the PR.

PHP 8.1, production PHP-FPM, Additional tokens/networks, other fee modes and real CNY payments were not separately paid during this change. This live result covers the tested merchant and payment; deploying accounts still need their own configuration/acceptance. Discovery limits and operational review remain as described above.

## References

- [Integration issue #295](https://github.com/ProxyPanel/ProxyPanel/issues/295)
- [Pinned upstream](https://github.com/ProxyPanel/ProxyPanel/tree/6e5a0cfeb454f47676f3c0f4fe348fcd3bffd855)
- [JangoPay developer documentation](https://github.com/jangopay/jangopay-docs)

GPL-3.0-or-later, consistent with upstream.
