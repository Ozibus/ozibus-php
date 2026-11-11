# Ozibus PHP SDK

PHP SDK for the Ozibus suite: SMS, transactional and marketing email, WhatsApp, Broadcast, Scheduling, Chat, and Account Balance/PAYG APIs.

## Requirements

- PHP 7.4 or newer
- `ext-json`
- `ext-curl` recommended, with PHP streams used as the fallback transport

## Install

During local development:

```php
require __DIR__ . '/ozibus-php-sdk/src/autoload.php';
```

After the package is published on Packagist:

```bash
composer require ozibus/ozibus-php
```

```php
require __DIR__ . '/vendor/autoload.php';
```

## Create A Client

Server-side APIs use a merchant ID and secret key. Version 1.3 exchanges these through `GET /generate_token` for the separate bearer access token required by protected routes. Your server must satisfy the merchant IP allowlist.

```php
use Ozibus\Sdk\Client;

$ozibus = Client::make([
    'merchant_id' => 'MCH-xxxxxxxxxx',
    'secret_key' => 'sk_live_xxx',
    'base_url' => 'https://api.ozibus.com.au/v1',
    'timeout' => 30,
    'throw' => true,
]);
```

### Business credentials and token lifecycle

For a business-specific key, also set `'business_id' => 'YOUR_BUSINESS_UUID'`. The SDK includes that context in both token exchange and API requests. Existing parent-merchant credentials still work without this option; the shared merchant ID is unchanged. Business selection does not create a separate Account Balance.

Tokens are cached only in the client instance's memory, separately per API base URL, and renewed ahead of JWT expiry. Reuse the client within a server process. Recreate it after credential rotation. No tokens are written to files, logs or browser storage.

If your application already manages access tokens, configure `'access_token' => $token`. You must renew supplied tokens yourself. An explicit `Authorization` request header is also respected. Set `'auto_token' => false` only when a particular endpoint requires the API key without a bearer token, or your own code supplies that header. A secret API key is **not** an access token.

The SDK does not automatically replay failed sends. A 401 invalidates the in-memory token for the next call; review the failed operation before attempting another send. A timeout can occur after a provider accepted a message, so automatic retries can duplicate delivery and charges.

Keep secret keys and access tokens in protected server configuration, never in public widget snippets. Use a new client for another merchant/business rather than overriding identity headers on individual requests.

### Public widgets

Public widget APIs use a public key.

```php
$publicOzibus = Client::make([
    'public_key' => 'pk_live_xxx',
    'base_url' => 'https://api.ozibus.com.au/v1',
]);
```

## Responses

Every SDK call returns `Ozibus\Sdk\Response`.

```php
$response = $ozibus->sms()->send('+61400000000', 'OZIBUS', 'Hello');

if ($response->successful()) {
    $data = $response->data();
}

if ($response->failed()) {
    error_log($response->message());
}
```

Useful helpers:

- `statusCode()`
- `headers()`
- `body()`
- `data()`
- `message()`
- `code()`
- `successful()`
- `failed()`
- `toArray()`

## Error Handling

Set `'throw' => true` to throw `Ozibus\Sdk\OzibusException` when an API response is unsuccessful. Transport errors and missing required credentials always throw.

```php
try {
    $ozibus->sms()->send('+61400000000', 'OZIBUS', 'Hello');
} catch (\Ozibus\Sdk\OzibusException $e) {
    error_log($e->getMessage());
    error_log(json_encode($e->context()));
}
```

## SMS

```php
$response = $ozibus->sms()->send(
    '+61400000000',
    'OZIBUS',
    'Your appointment is confirmed.'
);
```

OTP helpers:

```php
$ozibus->sms()->requestOtp('+61400000000');
$ozibus->sms()->resendOtp('+61400000000');
$ozibus->sms()->verifyOtp('+61400000000', '123456');
```

## Email

```php
$response = $ozibus->email()->send([
    'personalizations' => [
        ['to' => [['email' => 'customer@example.com', 'name' => 'Customer']]]
    ],
    'from' => ['email' => 'noreply@ozibus.com.au', 'name' => 'Ozibus'],
    'subject' => 'Welcome',
    'content' => [
        ['type' => 'text/html', 'value' => '<p>Hello from Ozibus</p>']
    ]
]);
```

Email verification helper:

```php
$ozibus->email()->verifyDeliverable('customer@example.com');
```

## Marketing Email

Marketing Email is merchant- and verified-domain-scoped. Use a secret-key client from trusted server-side code.

Subscribe or restore a consented contact:

```php
$response = $ozibus->marketingEmail()->subscribe([
    'firstname' => 'Ada',
    'lastname' => 'Lovelace',
    'subscription_email' => 'ada@example.com',
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
    'tags' => ['newsletter', 'product-updates'],
]);
```

Retrieve the audience for a verified domain:

```php
$response = $ozibus->marketingEmail()->subscribers([
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
]);
```

Use the contact reference returned by `subscribers()` to unsubscribe a contact:

```php
$response = $ozibus->marketingEmail()->unsubscribe([
    'id' => 'SUBSCRIBER_ID',
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
]);
```

The older email-service helpers remain available for backward compatibility:

```php

$ozibus->email()->addSubscribers([
    'firstname' => 'Ada',
    'lastname' => 'Lovelace',
    'subscription_email' => 'ada@example.com',
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
    'tags' => ['newsletter', 'product-updates'],
]);

$ozibus->email()->subscribers([
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
]);

$ozibus->email()->unsubscribe([
    'id' => 'SUBSCRIBER_ID',
    'source_domain_id' => 'YOUR_VERIFIED_DOMAIN_ID',
]);
```

## WhatsApp

```php
$response = $ozibus->whatsapp()->send(
    '+61400000000',
    'Your driver is on the way.'
);
```

## Scheduling

Scheduling widget endpoints use the public client.

```php
$config = $publicOzibus->scheduling()->config([
    'profession' => 'clinic',
    'timezone' => 'Australia/Melbourne',
]);

$booking = $publicOzibus->scheduling()->book([
    'firstname' => 'Ada',
    'lastname' => 'Lovelace',
    'email' => 'ada@example.com',
    'phone' => '+61400000000',
    'location' => 'Melbourne',
    'date' => '2026-06-01',
    'appointment_start' => '09:00',
    'appointment_end' => '09:30',
    'service_name' => 'Consultation',
    'timezone' => 'Australia/Melbourne',
]);
```

Other scheduling helpers:

```php
$publicOzibus->scheduling()->officeHours();
$publicOzibus->scheduling()->colors();
$publicOzibus->scheduling()->portal(['email' => 'ada@example.com']);
$publicOzibus->scheduling()->reschedule(['appointment_id' => 'APT-123']);
$publicOzibus->scheduling()->cancel(['appointment_id' => 'APT-123']);
$publicOzibus->scheduling()->calendarSyncFeed(['token' => 'feed-token']);
```

## Chat

Chat widget endpoints use the public client.

```php
$config = $publicOzibus->chat()->widgetConfig([
    'profile_key' => 'default',
]);

$message = $publicOzibus->chat()->submit([
    'session_id' => 'session-123',
    'sender_type' => 'user',
    'message' => 'Hello, I need help.',
    'userName' => 'Ada',
    'userEmail' => 'ada@example.com',
]);
```

Other chat helpers:

```php
$publicOzibus->chat()->history(['session_id' => 'session-123']);
$publicOzibus->chat()->knowledgeBase(['query' => 'refund']);
$publicOzibus->chat()->event(['event' => 'opened']);
$publicOzibus->chat()->close(['session_id' => 'session-123']);
$publicOzibus->chat()->emailTranscript(['session_id' => 'session-123']);
$ozibus->chat()->billingSummary(['merchant_id' => 'MCH-xxxxxxxxxx']);
```

## Broadcast

Broadcast browser flows use the public client key. Merchant dashboard flows use the server-side client.

```php
$token = $publicOzibus->broadcast()->iframeToken([
    'origin' => 'https://example.com',
]);

$publicOzibus->broadcast()->subscribe([
    'subscriber_id' => 'user-123',
    'channel' => 'in_app',
]);

$summary = $ozibus->broadcast()->dashboard('MCH-xxxxxxxxxx');
$broadcasts = $ozibus->broadcast()->broadcasts('MCH-xxxxxxxxxx');
```

For broadcast endpoints that do not yet have first-class helpers:

```php
$response = $publicOzibus->broadcast()->request('POST', 'subscriptions', [
    'subscriber_id' => 'user-123',
], ['auth' => 'client_key']);
```

## Wallet / PAYG

```php
$balance = $ozibus->wallet()->balance();
```

For wallet endpoints that do not yet have first-class helpers:

```php
$response = $ozibus->wallet()->request('transactions', [
    'limit' => 20,
], [
    'headers' => ['Idempotency-Key' => 'wallet-transactions-page-1'],
]);
```

## Generic Requests

Use `request()` for endpoints that do not have first-class helpers yet.

```php
$response = $ozibus->request('POST', 'merchant-domains', [
    'domain' => 'example.com',
]);

$response = $publicOzibus->request('POST', 'book-appointment', [
    'firstname' => 'Ada',
], ['auth' => 'public']);
```

Supported auth modes:

- `secret`: uses `Api-Key`, `Authorization: Bearer {secret_key}`, and `Merchant-Id`
- `public`: injects `public_key` into the payload or query
- `client_key`: injects `client_key` and sends `X-Client-Key`
- `none`: sends no SDK-managed credential

You can add headers per request:

```php
$response = $ozibus->request('POST', 'sms', [
    'to' => '+61400000000',
    'from' => 'OZIBUS',
    'message' => 'Hello',
], [
    'headers' => [
        'Idempotency-Key' => 'order-123-sms-confirmation',
    ],
]);
```

## Production Notes

- Never expose `secret_key` in browser code.
- Use the public client only for widget-safe flows.
- Use idempotency headers for retryable server actions.
- Keep `throw` enabled in backend jobs so failed API responses are visible.
- Log `statusCode()`, `message()`, and request context when handling failures.

## Package releases

This package is prepared for Packagist publication under `ozibus/ozibus-php`.
The Composer version is determined by Git tags (for example, `v1.3.0`).
See [PUBLISHING.md](PUBLISHING.md) for release, validation, and registration steps.

## License

Proprietary. See [LICENSE](LICENSE). The owner should review the included license notice before public distribution.
