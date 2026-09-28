<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->available()
            ->with(['farmer.markets', 'category'])
            ->when($request->q, fn ($q, $v) => $q->where(fn ($qq) => $qq->where('name', 'like', "%$v%")->orWhere('description', 'like', "%$v%")))
            ->when($request->category, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->market, fn ($q, $v) => $q->whereHas('farmer.markets', fn ($m) => $m->where('markets.id', $v)))
            ->when($request->day, fn ($q, $v) => $q->whereHas('farmer', fn ($f) => $f->whereJsonContains('operating_days', $v)))
            ->when($request->min_price, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->max_price, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when($request->sort === 'oldest', fn ($q) => $q->orderBy('created_at'))
            ->when(! $request->sort || $request->sort === 'newest', fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::active()->orderBy('sort_order')->get(),
            'markets' => Market::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active && $product->farmer?->user?->status === 'active', 404);

        return view('products.show', [
            'product' => $product->load(['farmer.markets', 'category']),
            'reviews' => $product->visibleReviews()->with('user')->paginate(5),
            'related' => Product::available()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get(),
            'isFavorited' => auth()->check() ? auth()->user()->hasFavorited('product', $product->id) : false,
        ]);
    }
}
