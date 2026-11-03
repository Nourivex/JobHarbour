<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Paksa skema HTTPS saat di production atau di balik proxy SSL Render/Cloudflare
        if (
            $this->app->environment('production') ||
            str_contains((string) config('app.url'), 'https://') ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        ) {
            URL::forceScheme('https');
        }

        // Batas percobaan login: 5 kali per menit untuk kombinasi email + IP, dan 20 kali per menit per IP.
        // Melindungi semua akun (termasuk admin) dari tebak sandi tanpa mengganggu pengguna yang salah ketik.
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });
    }
}
