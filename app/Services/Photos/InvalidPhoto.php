<?php

namespace App\Services\Photos;

use RuntimeException;

/**
 * The uploaded file cannot be used as a statue photo; the message is shown to the admin as is.
 */
final class InvalidPhoto extends RuntimeException {}
