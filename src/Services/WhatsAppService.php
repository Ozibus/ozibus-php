<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class WhatsAppService extends Service
{
    public function send(string $to, string $message, array $options = []): Response
    {
        return $this->post('whatsapp', array_merge($options, [
            'to' => $to,
            'message' => $message
        ]));
    }
}
