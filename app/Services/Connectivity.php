<?php

namespace App\Services;

use App\Support\Runtime;
use Native\Mobile\Network;

/**
 * Asks the phone whether it is online. Without a native bridge (browser, tests) the status is unknown,
 * so we assume online and rely on connection errors instead.
 */
class Connectivity
{
    public function __construct(private readonly Network $network) {}

    public function isOffline(): bool
    {
        if (! Runtime::hasBridge()) {
            return false;
        }

        $status = $this->network->status();

        return $status !== null && isset($status->connected) && $status->connected === false;
    }
}
