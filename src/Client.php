<?php

namespace Ozibus\Sdk;

use Ozibus\Sdk\Services\BroadcastService;
use Ozibus\Sdk\Services\ChatService;
use Ozibus\Sdk\Services\EmailService;
use Ozibus\Sdk\Services\MarketingEmailService;
use Ozibus\Sdk\Services\SchedulingService;
use Ozibus\Sdk\Services\SmsService;
use Ozibus\Sdk\Services\WalletService;
use Ozibus\Sdk\Services\WhatsAppService;

class Client
{
    private HttpClient $http;

    public function __construct(array $config = [])
    {
        $this->http = new HttpClient($config);
    }

    public static function make(array $config = []): self
    {
        return new self($config);
    }

    public function request(string $method, string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->http->request($method, $endpoint, $payload, $options);
    }

    public function sms(): SmsService
    {
        return new SmsService($this->http);
    }

    public function email(): EmailService
    {
        return new EmailService($this->http);
    }

    public function marketingEmail(): MarketingEmailService
    {
        return new MarketingEmailService($this->http);
    }

    public function whatsapp(): WhatsAppService
    {
        return new WhatsAppService($this->http);
    }

    public function broadcast(): BroadcastService
    {
        return new BroadcastService($this->http);
    }

    public function scheduling(): SchedulingService
    {
        return new SchedulingService($this->http);
    }

    public function chat(): ChatService
    {
        return new ChatService($this->http);
    }

    public function wallet(): WalletService
    {
        return new WalletService($this->http);
    }
}
