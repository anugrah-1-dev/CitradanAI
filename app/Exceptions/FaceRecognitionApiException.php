<?php

namespace App\Exceptions;

use RuntimeException;

class FaceRecognitionApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 503,
        public readonly ?string $apiCode = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
