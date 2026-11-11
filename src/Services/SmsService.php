<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class SmsService extends Service
{
    public function send(string $to, string $from, string $message, array $options = []): Response
    {
        return $this->post('sms', array_merge($options, [
            'to' => $to,
            'from' => $from,
            'message' => $message
        ]));
    }

    public function requestOtp(string $to, array $options = []): Response
    {
        return $this->post('get_otp', array_merge($options, ['to' => $to]));
    }

    public function resendOtp(string $to, array $options = []): Response
    {
        return $this->post('resend_otp', array_merge($options, ['to' => $to]));
    }

    public function verifyOtp(string $to, string $code, array $options = []): Response
    {
        return $this->post('verify', array_merge($options, ['to' => $to, 'code' => $code]));
    }
}
