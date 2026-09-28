<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FarmerProductController extends Controller
{
    public function index(Request $request): View
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'availability' => ['nullable', 'in:all,in_stock,out_of_stock,hidden'],
        ]);

        $products = $this->availabilityScope($this->applyFilters($farmers->products(), $filters), $filters['availability'] ?? 'all')
            ->with('category')
            ->latest()
            ->paginate(12)
            ->appends($filters);

        $counts = collect(['all', 'in_stock', 'out_of_stock', 'hidden'])
            ->mapWithKeys(fn ($key) => [$key => $this->availabilityScope($this->applyFilters($farmers->products(), $filters), $key)->count()])
            ->all();

        return view('farmer.products.index', [
            'farmer' => $farmers,
            'products' => $products,
            'categories' => \App\Models\Category::active()->orderBy('sort_order')->get(),
            'counts' => $counts,
            'filters' => ['q' => $filters['q'] ?? null, 'category' => $filters['category'] ?? null, 'availability' => $filters['availability'] ?? 'all'],
        ]);
    }

    public function create(Request $request): View
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        return view('farmer.products.create', [
            'farmer' => $farmers,
            'categories' => \App\Models\Category::active()->orderBy('sort_order')->get(),
            'templates' => $farmers->stockTemplates()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $data = $this->validated($request);
        $data['slug'] = $this->makeUniqueSlug($farmers, $data['name']);
        [$data['images'], $data['image']] = $this->storeGallery($request, 'products', []);
        $farmers->products()->create($data);

        return redirect()->route('farmer.products.index')->with('success', 'Product "' . $data['name'] . '" added to your weekly stock.');
    }

    public function edit(Request $request, Product $product): View
    {
        Gate::authorize('update', $product);
        return view('farmer.products.edit', [
            'farmer' => $product->farmer,
            'product' => $product,
            'categories' => \App\Models\Category::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $data = $this->validated($request);
        [$data['images'], $data['image']] = $this->storeGallery($request, 'products', $product->galleryPaths());
        if ($product->name !== $data['name']) {
            $data['slug'] = $this->makeUniqueSlug($product->farmer, $data['name'], $product->id);
        }
        $product->update($data);

        return redirect()->route('farmer.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($product->galleryPaths());
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    public function toggleAvailability(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $product->update(['is_available' => !$product->is_available]);

        return back()->with('success', $product->is_available
            ? $product->name . ' is now available again.'
            : $product->name . ' marked as sold out / unavailable.');
    }

    private function applyFilters($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when(($filters['category'] ?? null), fn($q, $v) => $q->where('category_id', $v));
    }

    private function availabilityScope($query, string $availability)
    {
        return match ($availability) {
            'in_stock' => $query->where('stock_quantity', '>', 0)->where('is_available', true)->where('is_active', true),
            'out_of_stock' => $query->where(fn($qq) => $qq->where('stock_quantity', '<=', 0)->orWhere('is_available', false)),
            'hidden' => $query->where('is_active', false),
            default => $query,
        };
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'unit' => ['required', 'string', 'max:20'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_available' => $request->boolean('is_available', true), 'is_active' => $request->boolean('is_active', true)];
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

    private function makeUniqueSlug(Farmer $farmers, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while ($farmers->products()->where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
