<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerController extends Controller
{
    /** Public directory of approved farmers. */
    public function index(Request $request): View
    {
        $farmers = Farmer::query()
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->with(['markets', 'user'])
            ->withCount('products')
            ->when($request->q, fn ($q, $v) => $q->where(fn ($qq) => $qq->where('stall_name', 'like', "%$v%")->orWhere('description', 'like', "%$v%")))
            ->when($request->market, fn ($q, $v) => $q->whereHas('markets', fn ($m) => $m->where('markets.id', $v)))
            ->when($request->day, fn ($q, $v) => $q->whereJsonContains('operating_days', $v))
            ->paginate(9)
            ->withQueryString();

        return view('farmers.index', [
            'farmers' => $farmers,
            'markets' => Market::active()->orderBy('name')->get(),
            'mapFarmers' => $farmers->getCollection()->filter(fn ($f) => $f->latitude && $f->longitude)->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->stall_name,
                'lat' => (float) $f->latitude,
                'lng' => (float) $f->longitude,
                'days' => $f->operatingDaysLabel(),
                'url' => route('farmers.show', $f),
            ])->values(),
        ]);
    }

    /** Public farmer profile with weekly stock and reviews. */
    public function show(Farmer $farmer): View
    {
        abort_unless($farmer->user?->status === 'active', 404);

        $products = $farmer->products()
            ->where('is_active', true)
            ->with('category')
            ->get()
            ->groupBy(fn ($p) => $p->category?->name ?? 'Other');

        return view('farmers.show', [
            'farmer' => $farmer,
            'productGroups' => $products,
            'reviews' => $farmer->visibleReviews()->with('user')->take(10)->get(),
            'isFavorited' => auth()->check() ? auth()->user()->hasFavorited('farmer', $farmer->id) : false,
        ]);
    }
}
