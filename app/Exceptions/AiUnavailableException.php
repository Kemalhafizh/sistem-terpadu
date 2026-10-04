<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The AI provider could not produce a result. The message is Indonesian and
 * meant to be shown to the user as is.
 */
class AiUnavailableException extends RuntimeException {}
