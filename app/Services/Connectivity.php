<?php

namespace App\Services;

use Native\Mobile\Network;

/**
 * Asks the device whether it is online. Outside the native app (browser, tests) the status is unknown,
 * so we assume online and rely on connection errors instead.
 */
class Connectivity
{
    public function __construct(private readonly Network $network) {}

    public function isOffline(): bool
    {
        $status = $this->network->status();

        return $status !== null && isset($status->connected) && $status->connected === false;
    }
}
