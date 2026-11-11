<?php

namespace Ozibus\Sdk;

use JsonSerializable;

class Response implements JsonSerializable
{
    private int $statusCode;
    private array $headers;
    private $body;

    public function __construct(int $statusCode, array $headers, $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body()
    {
        return $this->body;
    }

    public function data(): array
    {
        if (!is_array($this->body)) {
            return [];
        }

        $data = $this->body['data'] ?? $this->body['result'] ?? $this->body;

        return is_array($data) ? $data : ['value' => $data];
    }

    public function message(): string
    {
        if (!is_array($this->body)) {
            return '';
        }

        return (string)($this->body['response_message'] ?? $this->body['message'] ?? $this->body['error'] ?? '');
    }

    public function code(): int
    {
        if (!is_array($this->body)) {
            return $this->statusCode;
        }

        if (array_key_exists('response_code', $this->body)) {
            return (int)$this->body['response_code'];
        }

        if (array_key_exists('code', $this->body) && is_numeric($this->body['code'])) {
            return (int)$this->body['code'];
        }

        return $this->statusCode;
    }

    public function successful(): bool
    {
        if ($this->statusCode < 200 || $this->statusCode >= 300) {
            return false;
        }

        if (is_array($this->body) && array_key_exists('response_code', $this->body)) {
            return (int)$this->body['response_code'] === 0;
        }

        return true;
    }

    public function failed(): bool
    {
        return !$this->successful();
    }

    public function toArray(): array
    {
        return is_array($this->body) ? $this->body : ['raw' => $this->body];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        if (is_string($this->body)) {
            return $this->body;
        }

        return json_encode($this->body, JSON_UNESCAPED_SLASHES) ?: '';
    }
}
