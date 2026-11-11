<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class BroadcastService extends Service
{
    private const DEFAULT_BASE_URL = 'https://api.ozibus.com.au/broadcast';

    public function iframeToken(array $payload): Response
    {
        return $this->broadcastPost('iframe-token', $payload, ['auth' => 'client_key']);
    }

    public function subscribe(array $payload): Response
    {
        return $this->broadcastPost('subscriptions', $payload, ['auth' => 'client_key']);
    }

    public function unsubscribe(array $payload): Response
    {
        return $this->broadcastPost('subscriptions/unsubscribe', $payload, ['auth' => 'client_key']);
    }

    public function uploadAsset(array $payload): Response
    {
        return $this->broadcastPost('upload-broadcast-asset', $payload);
    }

    public function aiAssist(array $payload): Response
    {
        return $this->broadcastPost('ai-assist', $payload);
    }

    public function dashboard(string $merchantId): Response
    {
        return $this->broadcastGet($merchantId . '/dashboard');
    }

    public function audienceSummary(string $merchantId): Response
    {
        return $this->broadcastGet($merchantId . '/audience-summary');
    }

    public function broadcasts(string $merchantId): Response
    {
        return $this->broadcastGet($merchantId . '/broadcasts');
    }

    public function action(string $merchantId, int $broadcastId, array $payload): Response
    {
        return $this->broadcastPost($merchantId . '/broadcasts/' . $broadcastId . '/action', $payload);
    }

    public function request(string $method, string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->http->request($method, $endpoint, $payload, $this->broadcastOptions($options));
    }

    private function broadcastGet(string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->http->request('GET', $endpoint, $payload, $this->broadcastOptions($options));
    }

    private function broadcastPost(string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->http->request('POST', $endpoint, $payload, $this->broadcastOptions($options));
    }

    private function broadcastOptions(array $options = []): array
    {
        return $options + ['base_url' => self::DEFAULT_BASE_URL];
    }
}
