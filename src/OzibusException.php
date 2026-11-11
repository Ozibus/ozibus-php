<?php

namespace Ozibus\Sdk;

use RuntimeException;

class OzibusException extends RuntimeException
{
    private array $context;

    public function __construct(string $message, int $code = 0, array $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function context(): array
    {
        return $this->context;
    }
}
