<?php

namespace App\Services\Academic;

use RuntimeException;

/**
 * A business-rule rejection from ClassSessionService. Carries the HTTP status
 * the API should answer with and, optionally, a payload for the client (e.g.
 * the original check-in time when a student scans twice).
 */
class ClassSessionException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly mixed $data = null,
    ) {
        parent::__construct($message);
    }
}
