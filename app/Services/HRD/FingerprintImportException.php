<?php

namespace App\Services\HRD;

use RuntimeException;

/** A machine log that cannot be read as a whole (wrong columns, unknown file type). */
class FingerprintImportException extends RuntimeException {}
