<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Log hanya query lambat (>500ms), hanya saat debug mode aktif
        if (config('app.debug')) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 500) {
                    \Illuminate\Support\Facades\Log::warning(
                        "SLOW_QUERY (>500ms): {$query->sql} | Bindings: "
                        . json_encode($query->bindings)
                        . " | Time: {$query->time}ms"
                    );
                }
            });
        }
    }
}