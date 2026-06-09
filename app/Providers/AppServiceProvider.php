<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log hanya query lambat (>500ms), hanya saat debug mode aktif
        if (config('app.debug')) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 500) {
                    \Illuminate\Support\Facades\Log::warning("SLOW_QUERY (>500ms): {$query->sql} | Bindings: " . json_encode($query->bindings) . " | Time: {$query->time}ms");
                }
            });
        }
    }
}
