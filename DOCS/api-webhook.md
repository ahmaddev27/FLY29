# Webhook API — Developer Quick Start

> **Audience:** Main Site team integrating with the Loyalty system.
> **Full spec:** [`DOCS/09_MAIN_SITE_API_SPEC.md`](../DOCS/09_MAIN_SITE_API_SPEC.md).
> **Postman collection:** [`docs/postman/29fly-loyalty-webhook.postman_collection.json`](./postman/29fly-loyalty-webhook.postman_collection.json).

---

## 1. TL;DR

Send a signed POST to:
```
POST https://loyalty.29fly.com/api/v1/transactions/ingest
```
With these 3 headers and a JSON body. We respond in < 300 ms with the new wallet balances.

```http
Content-Type: application/json
X-API-Key:    <your key>
X-Signature:  sha256=<HMAC-SHA256(rawBody, webhookSecret)>
```

```json
{
  "agent_id":         "AGT-1234",
  "transaction_type": "package",
  "amount_usd":       1500.00,
  "destination":      "Thailand",
  "transaction_date": "2026-05-11T10:30:00Z",
  "reference_id":     "TXN-MAIN-998877"
}
```

---

## 2. Computing the signature (PHP)

```php
$rawBody   = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$signature = 'sha256=' . hash_hmac('sha256', $rawBody, $webhookSecret);

curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Key: ' . $apiKey,
    'X-Signature: ' . $signature,
]);
```

⚠️ **Sign the exact same byte stream you send.** Re-encoding the JSON breaks the signature.

---

## 3. Response cheat sheet

| HTTP | `status` body field | Meaning | Retry? |
|------|---------------------|---------|--------|
| 200  | `accepted`          | Booked + points credited | No |
| 200  | `duplicate_ignored` | Same `reference_id` seen before | No (safe to call again) |
| 401  | `unauthorized`      | Bad API key OR bad signature | No (fix config) |
| 404  | `agent_not_found`   | Agent unknown to Loyalty | No (alert ops) |
| 422  | `validation_failed` | Body schema invalid | No (fix payload) |
| 422  | `agent_suspended`   | Held for later — we'll process on reactivation | No |
| 429  | `rate_limit_exceeded` | Too many requests | Yes (after `retry_after` seconds) |
| 500  | `server_error`      | Transient on our side | Yes (exponential backoff) |

Backoff schedule (recommended): **1 min → 5 min → 30 min → 2 h → 6 h → 24 h.** Dead-letter after attempt 6.

---

## 4. Common pitfalls

1. **Re-encoding the body before sending** → signature mismatch. Sign once, send once, same bytes.
2. **Sending different JSON key order between sign-and-send** → some libs reorder keys; use the raw string.
3. **Using `==` to compare signatures on your side** → timing attack. Use `hash_equals()` in PHP.
4. **Sending UTC without `Z` suffix** → may be parsed as local time. Use `2026-05-11T10:30:00Z`.
5. **Reusing `reference_id`** → we accept it (idempotent), but you may misread our response as a new credit.

---

## 5. Local development against this code

If you're working in this repo:

```bash
# 1. Set test credentials in .env
MAIN_SITE_API_KEY=test_key_dev_only_12345
MAIN_SITE_WEBHOOK_SECRET=test_secret_dev_only_67890

# 2. Start the server
php artisan serve

# 3. Run the test suite (uses sqlite in-memory)
php artisan test --testsuite=Feature --filter=Webhook

# 4. Manual test — send a signed request
BODY='{"agent_id":"AGT-TEST-001","transaction_type":"package","amount_usd":1500,"destination":"Thailand","transaction_date":"2026-05-11T10:30:00Z","reference_id":"TXN-LOCAL-001"}'
SIG=$(php -r "echo 'sha256=' . hash_hmac('sha256', '$BODY', 'test_secret_dev_only_67890');")
curl -X POST http://127.0.0.1:8000/api/v1/transactions/ingest \
  -H "Content-Type: application/json" \
  -H "X-API-Key: test_key_dev_only_12345" \
  -H "X-Signature: $SIG" \
  -d "$BODY"
```

---

## 6. Where things live (for our team)

| Concern | File |
|---------|------|
| Routes | [`routes/api.php`](../routes/api.php) |
| Controller | [`app/Http/Controllers/Api/V1/WebhookController.php`](../app/Http/Controllers/Api/V1/WebhookController.php) |
| Orchestrator | [`app/Actions/IngestTransactionAction.php`](../app/Actions/IngestTransactionAction.php) |
| API Key check | [`app/Http/Middleware/WebhookAuth.php`](../app/Http/Middleware/WebhookAuth.php) |
| HMAC check | [`app/Http/Middleware/VerifyHmacSignature.php`](../app/Http/Middleware/VerifyHmacSignature.php) |
| Request logging | [`app/Http/Middleware/ApiLog.php`](../app/Http/Middleware/ApiLog.php) |
| Points math | [`app/Services/PointsCalculationService.php`](../app/Services/PointsCalculationService.php) |
| Wallet operations | [`app/Services/WalletService.php`](../app/Services/WalletService.php) |
| Tier upgrades | [`app/Services/TierService.php`](../app/Services/TierService.php) |
| Feature tests | [`tests/Feature/Webhook/IngestTransactionTest.php`](../tests/Feature/Webhook/IngestTransactionTest.php) |
