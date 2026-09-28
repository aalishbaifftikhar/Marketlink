<?php

namespace App\Http\Controllers;

use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $markets = Market::active()
            ->withCount(['farmers' => fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', 'active'))])
            ->when($request->q, fn ($q, $v) => $q->where(fn ($qq) => $qq->where('name', 'like', "%$v%")->orWhere('city', 'like', "%$v%")->orWhere('address', 'like', "%$v%")))
            ->when($request->day, fn ($q, $v) => $q->whereJsonContains('operating_days', $v))
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('markets.index', [
            'markets' => $markets,
            'mapMarkets' => $markets->getCollection()
                ->filter(fn ($m) => $m->latitude && $m->longitude)
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'lat' => (float) $m->latitude,
                    'lng' => (float) $m->longitude,
                    'days' => $m->operatingDaysLabel(),
                    'url' => route('markets.show', $m),
                ])->values(),
        ]);
    }

    public function nearby(Request $request): View
    {
        $filters = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'in:5,10,25,50'],
        ]);

        $lat = $filters['lat'] ?? null;
        $lng = $filters['lng'] ?? null;
        $radius = $filters['radius'] ?? 10;
        $hasLocation = $lat !== null && $lng !== null;

        $markets = collect();
        $noCoordsCount = 0;

        if ($hasLocation) {
            $all = Market::active()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->withCount(['farmers' => fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', 'active'))])
                ->get();

            $noCoordsCount = Market::active()->where(fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude'))->count();

            $markets = $all
                ->map(function (Market $market) use ($lat, $lng) {
                    $market->distance_km = haversine_km($lat, $lng, (float) $market->latitude, (float) $market->longitude);

                    return $market;
                })
                ->filter(fn (Market $market) => $market->distance_km <= $radius)
                ->sortBy('distance_km')
                ->values();
        }

        return view('markets.nearby', [
            'markets' => $markets,
            'hasLocation' => $hasLocation,
            'radius' => $radius,
            'lat' => $lat,
            'lng' => $lng,
            'noCoordsCount' => $noCoordsCount,
        ]);
    }

    public function show(Market $market): View
    {
        $farmers = $market->farmers()
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->withCount('products')
            ->get()
            ->sortByDesc(fn ($f) => $f->averageRating())
            ->values();

        return view('markets.show', [
            'market' => $market,
            'farmers' => $farmers,
            'isOpenToday' => $market->isOpenToday(),
        ]);
    }
}
