<?php

require_once __DIR__ . '/../src/autoload.php';

$classes = [
    \Ozibus\Sdk\Client::class,
    \Ozibus\Sdk\HttpClient::class,
    \Ozibus\Sdk\Response::class,
    \Ozibus\Sdk\OzibusException::class,
    \Ozibus\Sdk\Services\SmsService::class,
    \Ozibus\Sdk\Services\EmailService::class,
    \Ozibus\Sdk\Services\MarketingEmailService::class,
    \Ozibus\Sdk\Services\WhatsAppService::class,
    \Ozibus\Sdk\Services\BroadcastService::class,
    \Ozibus\Sdk\Services\SchedulingService::class,
    \Ozibus\Sdk\Services\ChatService::class,
    \Ozibus\Sdk\Services\WalletService::class,
];

foreach ($classes as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, 'Class not found: ' . $class . PHP_EOL);
        exit(1);
    }
}

$client = \Ozibus\Sdk\Client::make(['public_key' => 'pk_example']);
foreach (['sms', 'email', 'marketingEmail', 'whatsapp', 'broadcast', 'scheduling', 'chat', 'wallet'] as $method) {
    if (!is_object($client->{$method}())) {
        fwrite(STDERR, 'Service factory failed: ' . $method . PHP_EOL);
        exit(1);
    }
}

echo 'Ozibus PHP SDK smoke test passed.' . PHP_EOL;
