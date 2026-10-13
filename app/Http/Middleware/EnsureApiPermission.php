<?php

namespace App\Http\Middleware;

use App\Support\CurrentUser;
use App\Support\Navigation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks app sections the user may not open (the API would refuse them anyway).
 */
class EnsureApiPermission
{
    public function __construct(private readonly CurrentUser $currentUser) {}

    /**
     * @param  string|null  $permission  explicit permission (route middleware parameter), else the section's
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $permission ??= Navigation::permissionFor((string) $request->route()?->getName());

        abort_if($permission !== null && ! $this->currentUser->can($permission), 403, __('errors.forbidden'));

        return $next($request);
    }
}
