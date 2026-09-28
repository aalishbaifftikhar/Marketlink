<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function products(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,hidden,unavailable'],
        ]);

        $products = Product::with(['farmer.user', 'category'])
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when(($filters['status'] ?? 'all') === 'active', fn($q) => $q->where('is_active', true)->where('is_available', true))
            ->when(($filters['status'] ?? 'all') === 'hidden', fn($q) => $q->where('is_active', false))
            ->when(($filters['status'] ?? 'all') === 'unavailable', fn($q) => $q->where('is_available', false)->where('is_active', true))
            ->latest()
            ->paginate(15)
            ->appends($filters);

        return view('admin.moderation.products', [
            'products' => $products,
            'filters' => ['q' => $filters['q'] ?? null, 'status' => $filters['status'] ?? 'all'],
        ]);
    }

    public function toggleProduct(Product $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);

        return back()->with('success', $product->is_active
            ? 'Product restored to the catalogue.'
            : 'Product hidden from the catalogue.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return back()->with('success', 'Product permanently removed from the platform.');
    }

    public function reviews(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'flagged' => ['nullable', 'boolean'],
        ]);

        $reviews = Review::with(['user', 'farmer.user', 'product'])
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('comment', 'like', "%{$v}%"))
            ->when(($filters['flagged'] ?? false), fn($q) => $q->where('is_hidden', true))
            ->latest()
            ->paginate(15)
            ->appends($filters);

        return view('admin.moderation.reviews', [
            'reviews' => $reviews,
            'filters' => ['q' => $filters['q'] ?? null, 'flagged' => (bool) ($filters['flagged'] ?? false)],
        ]);
    }

    public function toggleReview(Review $review): RedirectResponse
    {
        $review->update(['is_hidden' => !$review->is_hidden]);

        return back()->with('success', $review->is_hidden
            ? 'Review hidden from public view.'
            : 'Review restored.');
    }

    public function destroyReview(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review permanently removed.');
    }
}
