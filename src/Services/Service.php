<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\HttpClient;
use Ozibus\Sdk\Response;

abstract class Service
{
    protected HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    protected function post(string $endpoint, array $payload = [], array $options = []): Response
    {
        return $this->http->request('POST', $endpoint, $payload, $options);
    }

    protected function get(string $endpoint, array $query = [], array $options = []): Response
    {
        return $this->http->request('GET', $endpoint, $query, $options);
    }
}
