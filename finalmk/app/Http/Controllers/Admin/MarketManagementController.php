<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);

        $markets = Market::withCount('farmers')
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where(fn($qq) => $qq->where('name', 'like', "%{$v}%")->orWhere('city', 'like', "%{$v}%")->orWhere('address', 'like', "%{$v}%")))
            ->when(($filters['status'] ?? 'all') === 'active', fn($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? 'all') === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(12)
            ->appends($filters);

        return view('admin.markets.index', [
            'markets' => $markets,
            'filters' => ['q' => $filters['q'] ?? null, 'status' => $filters['status'] ?? 'all'],
        ]);
    }

    public function create(): View
    {
        return view('admin.markets.create', ['market' => new Market(['operating_days' => []])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->makeUniqueSlug($data['name']);
        [$data['images'], $data['image']] = $this->storeGallery($request, 'markets', []);
        $market = Market::create($data);

        return redirect()->route('admin.markets.index')->with('success', 'Market "' . $market->name . '" created.');
    }

    public function edit(Market $market): View
    {
        return view('admin.markets.edit', compact('market'));
    }

    public function update(Request $request, Market $market): RedirectResponse
    {
        $data = $this->validated($request);
        [$data['images'], $data['image']] = $this->storeGallery($request, 'markets', $market->galleryPaths());
        if ($market->name !== $data['name']) {
            $data['slug'] = $this->makeUniqueSlug($data['name'], $market->id);
        }
        $market->update($data);

        return redirect()->route('admin.markets.index')->with('success', 'Market updated.');
    }

    public function destroy(Market $market): RedirectResponse
    {
        if ($market->farmers()->exists()) {
            return back()->with('error', 'Cannot delete a market that still has farmers assigned.');
        }
        \Illuminate\Support\Facades\Storage::disk('public')->delete($market->galleryPaths());
        $market->delete();

        return back()->with('success', 'Market removed.');
    }

    public function toggle(Market $market): RedirectResponse
    {
        $market->update(['is_active' => !$market->is_active]);

        return back()->with('success', $market->name . ' is now ' . ($market->is_active ? 'active' : 'hidden') . '.');
    }

    private function validated(Request $request): array
    {
        $request->merge([
            'open_time' => $this->toHourMinute($request->input('open_time')),
            'close_time' => $this->toHourMinute($request->input('close_time')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'operating_days' => ['nullable', 'array'],
            'operating_days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i', 'after:open_time'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'operating_days' => array_map('strtolower', $request->input('operating_days', [])),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function toHourMinute(?string $time): ?string
    {
        return is_string($time) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) ? substr($time, 0, 5) : $time;
    }

    /**
     * Validate and persist the 1–6 image gallery. Keeps the legacy single
     * `image` column in sync with the first gallery image. Returns
     * [images array, legacy image column value].
     */
    private function storeGallery(Request $request, string $folder, array $existingPaths): array
    {
        $request->validate([
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
        ]);

        $newFiles = array_slice($request->file('images', []), 0, 6);
        // Only allow removing paths that actually belong to this record.
        $removed = array_intersect($request->input('remove_images', []), $existingPaths);
        $kept = array_values(array_diff($existingPaths, $removed));

        if (count($kept) + count($newFiles) < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'images' => 'Add at least 1 image (JPG, PNG or WebP) before saving.',
            ]);
        }

        if (!empty($removed)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($removed);
        }

        $gallery = $kept;
        foreach ($newFiles as $file) {
            $gallery[] = $file->store($folder, 'public');
        }

        return [$gallery, $gallery[0] ?? null];
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Market::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
