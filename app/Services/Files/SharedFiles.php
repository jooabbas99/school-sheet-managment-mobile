<?php

namespace App\Services\Files;

use App\Support\Runtime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Native\Mobile\Share;

/**
 * Keeps downloaded files on the device and hands them to the Android share sheet (WhatsApp, Drive, ...).
 * Under Jump the signed link is shared instead; in a browser the file is offered as a normal download.
 */
class SharedFiles
{
    private const DIR = 'downloads';

    public function __construct(private readonly Share $share) {}

    /**
     * Save bytes under a safe, unique name and return that name.
     */
    public function store(string $content, string $fileName): string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'xlsx';
        $base = trim(preg_replace('/[\\\\\/:*?"<>|]+/u', '-', pathinfo($fileName, PATHINFO_FILENAME)) ?? '') ?: 'file';
        $name = Str::limit($base, 80, '').'-'.Str::lower(Str::random(6)).'.'.$extension;

        Storage::disk('local')->put(self::DIR.'/'.$name, $content);

        return $name;
    }

    public function path(string $name): string
    {
        return Storage::disk('local')->path(self::DIR.'/'.basename($name));
    }

    public function exists(string $name): bool
    {
        return Storage::disk('local')->exists(self::DIR.'/'.basename($name));
    }

    /**
     * Open the share sheet with a file stored on this device. Only possible when PHP runs on the phone.
     */
    public function share(string $name, string $title): bool
    {
        if (! Runtime::onDevice()) {
            return false;
        }

        $this->share->file($title, $title, $this->path($name));

        return true;
    }

    /**
     * Under Jump the file lives on the computer, so share the (signed, reachable) download link instead.
     */
    public function shareLink(string $url, string $title): bool
    {
        if (! Runtime::inJump()) {
            return false;
        }

        $this->share->url($title, $title, $url);

        return true;
    }
}
