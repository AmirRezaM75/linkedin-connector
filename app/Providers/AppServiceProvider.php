<?php

namespace App\Providers;

use App\Services\LinkedinHttpService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LinkedinHttpService::class, fn() => new LinkedinHttpService(
            config('services.linkedin.username'),
            config('services.linkedin.password')
        ));
    }

    public function boot(): void
    {
        //
    }
}
