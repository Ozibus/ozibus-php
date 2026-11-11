<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class EmailService extends Service
{
    public function send(array $message): Response
    {
        return $this->post('mail', $message);
    }

    public function verifyDeliverable(string $email, array $options = []): Response
    {
        return $this->post('verify_email', array_merge($options, ['email' => $email]));
    }

    public function addSubscribers(array $payload): Response
    {
        return $this->post('newsletter', $payload);
    }

    public function subscribers(array $query = []): Response
    {
        return $this->post('get-subscribers', $query);
    }

    public function unsubscribe(array $payload): Response
    {
        return $this->post('unsubscribe', $payload);
    }
}
