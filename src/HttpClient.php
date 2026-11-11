<?php

namespace Ozibus\Sdk;

class HttpClient
{
    public const VERSION = '1.3.0';

    private string $baseUrl;
    private ?string $secretKey;
    private ?string $publicKey;
    private ?string $merchantId;
    private int $timeout;
    private bool $throw;
    private ?string $accessToken;
    private ?string $businessId;
    private bool $autoToken;
    private array $tokenCache = [];

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim((string)($config['base_url'] ?? 'https://api.ozibus.com.au/v1'), '/');
        $this->secretKey = $this->nullableString($config['secret_key'] ?? null);
        $this->publicKey = $this->nullableString($config['public_key'] ?? null);
        $this->merchantId = $this->nullableString($config['merchant_id'] ?? null);
        $this->timeout = max(1, (int)($config['timeout'] ?? 30));
        $this->throw = (bool)($config['throw'] ?? false);
        $this->accessToken = $this->nullableString($config['access_token'] ?? null);
        $this->businessId = $this->nullableString($config['business_id'] ?? null);
        $this->autoToken = (bool)($config['auto_token'] ?? true);

        if ($this->baseUrl === '') {
            throw new OzibusException('Ozibus SDK requires a non-empty base_url.');
        }
    }

    public function request(string $method, string $endpoint, array $payload = [], array $options = []): Response
    {
        $method = strtoupper($method);
        $endpoint = trim($endpoint, '/');
        $baseUrl = rtrim((string)($options['base_url'] ?? $this->baseUrl), '/');
        if ($baseUrl === '') {
            throw new OzibusException('Ozibus SDK requires a non-empty base_url.');
        }

        $url = $baseUrl . '/' . $endpoint;
        $auth = (string)($options['auth'] ?? 'secret');

        $this->validateAuth($auth);
        if ($auth === 'secret' && $endpoint !== 'generate_token' && !$this->hasAuthorizationHeader($options)) {
            $options['access_token'] = $this->accessToken;
            if (!$options['access_token'] && $this->autoToken) {
                $options['access_token'] = $this->obtainAccessToken($baseUrl);
            }
        }
        $headers = $this->headers($options + ['auth' => $auth]);

        if ($auth === 'public' && $this->publicKey && !isset($payload['public_key'])) {
            $payload['public_key'] = $this->publicKey;
        }

        if ($auth === 'client_key' && $this->publicKey && !isset($payload['client_key'])) {
            $payload['client_key'] = $this->publicKey;
        }

        if (($options['include_merchant_id'] ?? true) && $this->merchantId && !isset($payload['merchant_id'])) {
            $payload['merchant_id'] = $this->merchantId;
        }
        if ($auth === 'secret' && $this->businessId && !isset($payload['business_id'])) {
            $payload['business_id'] = $this->businessId;
        }

        if ($method === 'GET' && $payload) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($payload);
            $payload = [];
        }

        $raw = $this->send($method, $url, $headers, $payload);
        $decoded = json_decode($raw['body'], true);
        $body = json_last_error() === JSON_ERROR_NONE ? $decoded : $raw['body'];
        $response = new Response($raw['status'], $raw['headers'], $body);
        // Invalidate for the NEXT call, never replay a potentially billable send.
        if ($raw['status'] === 401) unset($this->tokenCache[$baseUrl]);

        if ($this->throw && !$response->successful()) {
            throw new OzibusException($response->message() ?: 'Ozibus API request failed.', $response->code(), [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->statusCode(),
                'body' => $response->toArray()
            ]);
        }

        return $response;
    }

    private function headers(array $options): array
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Ozibus-PHP-SDK/' . self::VERSION
        ];

        $auth = $options['auth'] ?? 'secret';
        if ($auth === 'secret' && $this->secretKey) {
            if (!empty($options['access_token']) && !$this->hasAuthorizationHeader($options)) {
                $headers[] = 'Authorization: Bearer ' . $options['access_token'];
            }
            $headers[] = 'Api-Key: ' . $this->secretKey;
            if ($this->businessId) {
                $headers[] = 'X-Ozibus-Business-Id: ' . $this->businessId;
                $headers[] = 'Business-Id: ' . $this->businessId;
            }
        }
        if ($auth === 'client_key' && $this->publicKey) {
            $headers[] = 'X-Client-Key: ' . $this->publicKey;
        }
        if (($options['include_merchant_id'] ?? true) && $this->merchantId) {
            $headers[] = 'Merchant-Id: ' . $this->merchantId;
        }

        foreach (($options['headers'] ?? []) as $name => $value) {
            $headers[] = is_int($name) ? $value : $name . ': ' . $value;
        }

        return $headers;
    }

    protected function send(string $method, string $url, array $headers, array $payload): array
    {
        if (function_exists('curl_init')) {
            return $this->sendWithCurl($method, $url, $headers, $payload);
        }

        return $this->sendWithStreams($method, $url, $headers, $payload);
    }

    private function sendWithCurl(string $method, string $url, array $headers, array $payload): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeout
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodePayload($payload));
        }

        $result = curl_exec($ch);
        if ($result === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new OzibusException('Ozibus API transport error: ' . $message);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'headers' => $this->parseHeaders(substr($result, 0, $headerSize)),
            'body' => substr($result, $headerSize)
        ];
    }

    private function sendWithStreams(string $method, string $url, array $headers, array $payload): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $method === 'GET' ? '' : $this->encodePayload($payload),
                'ignore_errors' => true,
                'follow_location' => 0,
                'timeout' => $this->timeout
            ]
        ]);

        $body = file_get_contents($url, false, $context);
        if ($body === false) {
            throw new OzibusException('Ozibus API transport error.');
        }

        $headersOut = $http_response_header ?? [];
        $status = 0;
        if (isset($headersOut[0]) && preg_match('/\s(\d{3})\s/', $headersOut[0], $matches)) {
            $status = (int)$matches[1];
        }

        return [
            'status' => $status,
            'headers' => $headersOut,
            'body' => $body
        ];
    }

    private function parseHeaders(string $headerBlock): array
    {
        return array_values(array_filter(array_map('trim', explode("\r\n", $headerBlock))));
    }

    private function hasAuthorizationHeader(array $options): bool
    {
        foreach (($options['headers'] ?? []) as $name => $value) {
            if (is_int($name) ? stripos(ltrim((string)$value), 'Authorization:') === 0 : strcasecmp((string)$name, 'Authorization') === 0) return true;
        }
        return false;
    }

    private function obtainAccessToken(string $baseUrl): string
    {
        $cached = $this->tokenCache[$baseUrl] ?? null;
        if ($cached && $cached['expires'] > time() + 30) return $cached['token'];
        $raw = $this->send('GET', $baseUrl . '/generate_token', $this->headers(['auth' => 'secret']), []);
        $body = json_decode($raw['body'], true);
        $auth = is_array($body) ? ($body['data']['auth'] ?? []) : [];
        $token = is_array($auth) ? ($auth['access_token'] ?? null) : null;
        if ($raw['status'] < 200 || $raw['status'] >= 300 || !is_array($body)
            || (string)($body['response_code'] ?? '') !== '0' || !is_string($token) || trim($token) === '') {
            // Never include a token response (which can contain secrets) in exception context.
            throw new OzibusException('Ozibus access token could not be obtained. Check merchant credentials, approval and IP allowlist.', 0, ['status' => $raw['status']]);
        }
        // JWT expiry is used only for cache scheduling; the API validates authenticity.
        $parts = explode('.', $token);
        $claims = isset($parts[1]) ? json_decode((string)base64_decode(strtr($parts[1], '-_', '+/'), true), true) : null;
        $expires = is_array($claims) && isset($claims['exp']) ? (int)$claims['exp'] : time();
        if ($expires > time() + 30) $this->tokenCache[$baseUrl] = ['token' => $token, 'expires' => $expires];
        return $token;
    }

    private function encodePayload(array $payload): string
    {
        $json = json_encode($payload);
        if ($json === false) {
            throw new OzibusException('Ozibus SDK could not encode request payload: ' . json_last_error_msg());
        }

        return $json;
    }

    private function validateAuth(string $auth): void
    {
        if (!in_array($auth, ['secret', 'public', 'client_key', 'none'], true)) {
            throw new OzibusException('Ozibus SDK received unsupported auth mode: ' . $auth);
        }

        if ($auth === 'secret' && !$this->secretKey) {
            throw new OzibusException('Ozibus SDK secret-key request requires secret_key.');
        }

        if (($auth === 'public' || $auth === 'client_key') && !$this->publicKey) {
            throw new OzibusException('Ozibus SDK public request requires public_key.');
        }
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }
}
