<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FarmerManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,pending,approved,suspended'],
            'market' => ['nullable', 'integer', 'exists:markets,id'],
        ]);

        $farmers = Farmer::with(['user', 'markets'])
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where(fn($qq) => $qq->where('stall_name', 'like', "%{$v}%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"))))
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($q) => match ($filters['status']) {
                'pending' => $q->pending(),
                'approved' => $q->approved(),
                'suspended' => $q->suspended(),
                default => $q,
            })
            ->when($filters['market'] ?? null, fn($q, $v) => $q->whereHas('markets', fn($m) => $m->where('markets.id', $v)))
            ->latest()
            ->paginate(12)
            ->appends($filters);

        return view('admin.farmers.index', [
            'farmers' => $farmers,
            'filters' => ['q' => $filters['q'] ?? null, 'status' => $filters['status'] ?? 'all', 'market' => $filters['market'] ?? null],
            'markets' => \App\Models\Market::orderBy('name')->get(['id', 'name']),
            'counts' => [
                'all' => Farmer::count(),
                'pending' => Farmer::pending()->count(),
                'approved' => Farmer::approved()->count(),
                'suspended' => Farmer::suspended()->count(),
            ],
        ]);
    }

    public function show(Farmer $farmer): View
    {
        $farmer->load(['user', 'markets', 'products' => fn($q) => $q->latest()->take(10)]);
        $orders = $farmer->orders()->with('customer')->latest('placed_at')->paginate(10);
        $reviews = $farmer->reviews()->with('user')->latest()->paginate(8);

        return view('admin.farmers.show', [
            'farmer' => $farmer,
            'orders' => $orders,
            'reviews' => $reviews,
            'stats' => [
                'products' => $farmer->products()->count(),
                'orders' => $farmer->orders()->count(),
                'completed' => $farmer->orders()->where('status', 'completed')->count(),
                'revenue' => $farmer->orders()->where('status', 'completed')->sum('total_amount'),
                'rating' => $farmer->averageRating(),
            ],
        ]);
    }

    public function approve(Farmer $farmer): RedirectResponse
    {
        abort_unless($farmer->isPending() || $farmer->isSuspended(), 422);
        DB::transaction(function () use ($farmer) {
            $farmer->user->update(['status' => 'active']);
        });

        return back()->with('success', $farmer->stall_name . ' has been approved and can now list products.');
    }

    public function suspend(Farmer $farmer): RedirectResponse
    {
        abort_if($farmer->isSuspended(), 422);
        DB::transaction(function () use ($farmer) {
            $farmer->products()->update(['is_available' => false]);
            $farmer->user->update(['status' => 'suspended']);
        });

        return back()->with('success', $farmer->stall_name . ' has been suspended. Their listings were hidden.');
    }

    public function restore(Farmer $farmer): RedirectResponse
    {
        abort_unless($farmer->isSuspended(), 422);
        DB::transaction(function () use ($farmer) {
            $farmer->user->update(['status' => 'active']);
        });

        return back()->with('success', $farmer->stall_name . ' has been reinstated.');
    }

    public function destroy(Farmer $farmer): RedirectResponse
    {
        DB::transaction(function () use ($farmer) {
            $user = $farmer->user;
            $farmer->delete();
            $user?->delete();
        });

        return back()->with('success', 'Farmer account and profile removed.');
    }
}
