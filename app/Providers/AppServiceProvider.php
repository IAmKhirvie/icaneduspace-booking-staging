<?php

namespace App\Providers;

use App\Models\Booking;
use App\Services\CloudflareTurnstileClient;
use App\Observers\BookingObserver;
use App\Policies\BookingPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use RyanChandler\LaravelCloudflareTurnstile\Contracts\ClientInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ClientInterface::class, CloudflareTurnstileClient::class);
    }

    public function boot(): void
    {
        Booking::observe(BookingObserver::class);

        Gate::policy(Booking::class, BookingPolicy::class);

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        // Public booking submissions — strict per-IP + per-email.
        RateLimiter::for('bookings-public', function (Request $request) {
            $email = (string) $request->input('contact');
            $key   = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $request->ip();

            return [
                Limit::perMinute(4)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
                Limit::perHour(8)->by($key),
            ];
        });

        // API + web login flood guard.
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by(strtolower($email).'|'.$request->ip());
        });

        // General API.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
