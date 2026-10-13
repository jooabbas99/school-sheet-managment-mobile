<?php

namespace App\Http\Controllers;

use App\Services\Files\SharedFiles;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves a downloaded file to the browser. Used when the app runs outside NativePHP (development);
 * inside the app, files go to the Android share sheet instead.
 */
class FileController extends Controller
{
    public function __invoke(string $name, SharedFiles $files): BinaryFileResponse
    {
        abort_unless($files->exists($name), 404);

        return response()->download($files->path($name), $name);
    }
}
