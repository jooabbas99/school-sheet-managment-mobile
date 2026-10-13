<?php

namespace App\Providers;

use App\Services\Auth\AutoTokenStore;
use App\Services\Auth\FileTokenStore;
use App\Services\Auth\SecureTokenStore;
use App\Services\Auth\TokenStore;
use App\Support\CurrentUser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenStore::class, fn ($app) => match (config('api.token_store')) {
            'secure' => $app->make(SecureTokenStore::class),
            'file' => $app->make(FileTokenStore::class),
            default => $app->make(AutoTokenStore::class),
        });

        $this->app->scoped(CurrentUser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
