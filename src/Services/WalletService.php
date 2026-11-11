<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class WalletService extends Service
{
    public function balance(array $payload = [], array $options = []): Response
    {
        return $this->post('balance', $payload, $options);
    }

    public function request(string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->post(trim($endpoint, '/'), $payload, $options);
    }
}
