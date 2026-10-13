<?php

namespace App\Support;

use Native\Mobile\System;

/**
 * Where the PHP code is running. Note: `function_exists('nativephp_call')` is NOT a valid check —
 * NativePHP defines that function as a fallback on the dev machine too (for Jump).
 */
final class Runtime
{
    /**
     * Inside the installed Android/iOS app: PHP runs on the phone.
     */
    public static function onDevice(): bool
    {
        // nativephp-internal.running reads NATIVEPHP_RUNNING, which the app sets on the phone.
        return (bool) config('nativephp-internal.running');
    }

    /**
     * Under `php artisan native:jump`: PHP runs on the computer, native calls are relayed to the phone.
     * Local file paths and localhost URLs mean nothing to the phone in this mode.
     */
    public static function inJump(): bool
    {
        return System::runningInJump();
    }

    /**
     * Native features (share sheet, network status, secure storage) can be used.
     */
    public static function hasBridge(): bool
    {
        return self::onDevice() || self::inJump();
    }
}
