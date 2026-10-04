<?php

namespace App\Services\Academic;

use RuntimeException;

/**
 * A business-rule rejection from ClassSessionService. Carries the HTTP status
 * the API should answer with, a stable `errorCode` the app acts on (the
 * message is in the user's language and must never be parsed) and,
 * optionally, a payload for the client (e.g. the original check-in time when
 * a student scans twice).
 */
class ClassSessionException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly mixed $data = null,
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($message);
    }
}
