<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The device has no connection, or the server can't be reached.
 */
class OfflineException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('errors.offline'));
    }
}
