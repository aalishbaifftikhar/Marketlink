<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('settings')) {
    /**
     * Read a platform setting value, or all settings when no key is given.
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('platform_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        if ($key === null) {
            return $all;
        }

        return $all[$key] ?? $default;
    }
}

if (! function_exists('settings_set')) {
    /**
     * Persist a platform setting and refresh the cached copy.
     */
    function settings_set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        Cache::forget('platform_settings');
    }
}

if (! function_exists('haversine_km')) {
    /**
     * Great-circle distance between two coordinates in kilometres.
     */
    function haversine_km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

if (! function_exists('dashboard_layout')) {
    /**
     * The Blade shell for shared pages (products, markets, farmers, profile,
     * notifications): the role's dashboard layout when signed in, the public
     * marketing layout for guests.
     */
    function dashboard_layout(): string
    {
        return match (auth()->user()?->role) {
            'farmer' => 'layouts.farmer',
            'admin' => 'layouts.admin',
            'customer' => 'layouts.customer',
            default => 'layouts.app',
        };
    }
}
