<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public const KEYS = [
        'site_name' => ['label' => 'Site Name', 'type' => 'text', 'default' => 'MarketLink'],
        'site_tagline' => ['label' => 'Site Tagline', 'type' => 'text', 'default' => 'Fresh from local farms, straight to your basket'],
        'contact_email' => ['label' => 'Contact Email', 'type' => 'email', 'default' => 'hello@marketlink.local'],
        'contact_phone' => ['label' => 'Contact Phone', 'type' => 'text', 'default' => '+1 (555) 010-2030'],
        'contact_address' => ['label' => 'Contact Address', 'type' => 'text', 'default' => '1 Market Square, Greenfield'],
        'contact_latitude' => ['label' => 'Office Latitude', 'type' => 'number', 'default' => '40.7128'],
        'contact_longitude' => ['label' => 'Office Longitude', 'type' => 'number', 'default' => '-74.0060'],
        'footer_text' => ['label' => 'Footer Text', 'type' => 'text', 'default' => ''],
        'announcement_banner' => ['label' => 'Homepage Announcement Banner', 'type' => 'text', 'default' => ''],
    ];

    public function index(): View
    {
        $values = [];
        foreach (array_keys(self::KEYS) as $key) {
            $values[$key] = settings($key, self::KEYS[$key]['default']);
        }

        return view('admin.settings.edit', [
            'keys' => self::KEYS,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::KEYS as $key => $meta) {
            $rules[$key] = match ($meta['type']) {
                'email' => ['nullable', 'email', 'max:150'],
                'number' => ['nullable', 'numeric'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        $data = $request->validate($rules);
        foreach ($data as $key => $value) {
            settings_set($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');

        return back()->with('success', 'Application cache cleared.');
    }
}
