<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request) {
            $email = $request->input('email');
            $email = is_string($email)
                ? mb_strtolower(trim($email))
                : '';

            return [
                Limit::perMinute(30)
                    ->by('login-ip:'.$request->ip()),

                Limit::perMinute(5)
                    ->by('login-account:'.hash(
                        'sha256',
                        $email.'|'.$request->ip()
                    )),
            ];
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(3)
                ->by('register-ip:'.$request->ip());
        });
    }
}
