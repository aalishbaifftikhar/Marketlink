<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Schema::defaultStringLength(191);

        Password::defaults(fn () => Password::min(8)->max(16)->mixedCase()->letters()->numbers()->symbols());

        Relation::morphMap([
            'farmer' => \App\Models\Farmer::class,
            'product' => \App\Models\Product::class,
            'market' => \App\Models\Market::class,
        ]);

        RateLimiter::for('otp-verify', fn (Request $request) => Limit::perMinute(12)
            ->by(($request->session()->get('otp.email') ?? 'guest') . '|' . $request->ip()));

        RateLimiter::for('otp-resend', fn (Request $request) => Limit::perMinute(6)
            ->by(($request->session()->get('otp.email') ?? 'guest') . '|' . $request->ip()));
    }
}
