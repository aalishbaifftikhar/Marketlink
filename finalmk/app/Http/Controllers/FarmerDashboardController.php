<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\FarmerEntry;
use App\Models\Market;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FarmerDashboardController extends Controller
{
    private function farmer(Request $request)
    {
        return $request->user()->farmer()->withCount('products')->firstOrFail();
    }

    public function pending(): View
    {
        return view('farmer.pending');
    }

    public function dashboard(Request $request): View
    {
        $farmer = $this->farmer($request);

        $orders = $farmer->orders();
        $bestSellers = $farmer->products()
            ->withSum(['orderItems as sold_units' => fn ($q) => $q->whereHas('order', fn ($o) => $o->where('status', '!=', 'cancelled'))], 'quantity')
            ->orderByDesc('sold_units')
            ->take(5)
            ->get();

        return view('farmer.dashboard', [
            'farmer' => $farmer,
            'stats' => [
                'total_orders' => $orders->count(),
                'pending_orders' => $farmer->orders()->where('status', 'placed')->count(),
                'active_orders' => $farmer->orders()->active()->count(),
                'revenue' => $farmer->orders()->whereIn('status', ['completed', 'accepted', 'ready_for_pickup'])->sum('total_amount'),
            ],
            'recentOrders' => $farmer->orders()->with('customer')->orderByDesc('placed_at')->take(5)->get(),
            'bestSellers' => $bestSellers,
            'lowStock' => $farmer->products()->where('is_active', true)->where('stock_quantity', '<=', 5)->orderBy('stock_quantity')->take(5)->get(),
            'announcements' => Announcement::published()->forAudience('farmer')->latest('published_at')->take(3)->get(),
        ]);
    }

    public function profileEdit(Request $request): View
    {
        $farmer = $this->farmer($request)->load('markets');

        return view('farmer.profile', [
            'farmer' => $farmer,
            'markets' => Market::active()->orderBy('name')->get(),
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'categories' => Category::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function profileUpdate(Request $request): RedirectResponse
    {
        $farmer = $this->farmer($request);

        $validated = $request->validate([
            'stall_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'operating_days' => ['array'],
            'operating_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'markets' => ['array'],
            'markets.*' => ['exists:markets,id'],
            'order_cutoff_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ], [
            'phone.regex' => 'The phone number must be 10-15 digits, numbers only (no spaces or symbols).',
        ]);

        if ($request->hasFile('cover_image')) {
            $oldCover = $farmer->cover_image;
            $validated['cover_image'] = $request->file('cover_image')->store('farmers', 'public');
            if ($oldCover) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldCover);
            }
        } else {
            unset($validated['cover_image']);
        }

        $markets = $validated['markets'] ?? [];
        unset($validated['markets']);

        $farmer->update($validated);
        $farmer->markets()->sync($markets);

        return back()->with('success', 'Stall profile updated.');
    }

    public function insights(Request $request): View
    {
        $farmer = $this->farmer($request);

        $orders = $farmer->orders()->where('status', '!=', 'declined');

        // Sales for the last 30 days (completed + active).
        $daily = $farmer->orders()
            ->where('placed_at', '>=', now()->subDays(30))
            ->whereIn('status', ['completed', 'accepted', 'ready_for_pickup'])
            ->selectRaw('DATE(placed_at) as day, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $salesSeries = collect(range(29, 0))->map(function ($i) use ($daily) {
            $day = now()->subDays($i)->toDateString();

            return [
                'day' => now()->subDays($i)->format('j M'),
                'revenue' => (float) ($daily[$day]->revenue ?? 0),
                'orders' => (int) ($daily[$day]->orders ?? 0),
            ];
        });

        $byStatus = $farmer->orders()->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');

        $topProducts = $farmer->products()
            ->withSum(['orderItems as sold_units' => fn ($q) => $q->whereHas('order', fn ($o) => $o->where('status', '!=', 'cancelled'))], 'quantity')
            ->withSum(['orderItems as revenue' => fn ($q) => $q->whereHas('order', fn ($o) => $o->where('status', '!=', 'cancelled'))], 'line_total')
            ->orderByDesc('sold_units')
            ->take(8)
            ->get();

        $revenueByMarket = $farmer->orders()
            ->whereIn('status', ['completed', 'accepted', 'ready_for_pickup'])
            ->with('market')
            ->get()
            ->groupBy(fn ($o) => $o->market?->name ?? 'Other')
            ->map->sum('total_amount');

        return view('farmer.insights', [
            'farmer' => $farmer,
            'stats' => [
                'total_orders' => $orders->count(),
                'completed_orders' => $farmer->orders()->where('status', 'completed')->count(),
                'total_revenue' => $orders->whereIn('status', ['completed', 'accepted', 'ready_for_pickup'])->sum('total_amount'),
                'avg_order' => round($orders->whereIn('status', ['completed', 'accepted', 'ready_for_pickup'])->avg('total_amount') ?? 0, 2),
            ],
            'salesSeries' => $salesSeries,
            'byStatus' => $byStatus,
            'topProducts' => $topProducts,
            'revenueByMarket' => $revenueByMarket,
        ]);
    }

    public function entries(Request $request): View
    {
        $farmer = $this->farmer($request);

        $filters = $request->validate([
            'mode' => ['nullable', 'in:month,range'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'date' => ['nullable', 'date'],
        ]);

        $mode = $filters['mode'] ?? 'month';
        $selectedDate = $filters['date'] ?? null;
        $month = (int) ($filters['month'] ?? now()->format('n'));
        $year = (int) ($filters['year'] ?? now()->format('Y'));

        if ($mode === 'range') {
            $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
            $to = $filters['to'] ?? now()->toDateString();
            $rangeStart = Carbon::parse($from)->startOfDay();
            $rangeEnd = Carbon::parse($to)->endOfDay();
        } else {
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = (clone $monthStart)->endOfMonth();
        }

        $periodStart = $mode === 'range' ? $rangeStart : $monthStart;
        $periodEnd = $mode === 'range' ? $rangeEnd : $monthEnd;

        $records = $farmer->entries()
            ->whereBetween('entry_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->when($selectedDate, fn ($q, $d) => $q->whereDate('entry_date', $d))
            ->get();

        $totalCost = $records->sum(fn (FarmerEntry $e) => $e->totalCost());
        $totalSales = $records->sum(fn (FarmerEntry $e) => $e->totalSales());

        // Daily profit map for the calendar grid (month view only).
        $daily = $farmer->entries()
            ->reorder() // drop the relationship's "order by id" so GROUP BY works
            ->selectRaw('entry_date, SUM(cost_price * quantity) AS total_cost, SUM(selling_price * quantity) AS total_sales, COUNT(*) AS entries')
            ->whereBetween('entry_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->groupBy('entry_date')
            ->get()
            ->keyBy(fn ($row) => $row->entry_date instanceof Carbon ? $row->entry_date->toDateString() : (string) $row->entry_date);

        $weeks = [];
        if ($mode === 'month') {
            $cursor = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
            while ($cursor->lte($monthEnd)) {
                $week = [];
                for ($i = 0; $i < 7; $i++) {
                    $day = $cursor->copy()->addDays($i);
                    $row = $daily[$day->toDateString()] ?? null;
                    $week[] = [
                        'date' => $day,
                        'in_month' => $day->month === $monthStart->month,
                        'profit' => $row ? round((float) $row->total_sales - (float) $row->total_cost, 2) : null,
                        'entries' => $row ? (int) $row->entries : 0,
                    ];
                }
                $weeks[] = $week;
                $cursor->addDays(7);
            }
        }

        return view('farmer.entries', [
            'farmer' => $farmer,
            'records' => $records,
            'totals' => [
                'cost' => round($totalCost, 2),
                'sales' => round($totalSales, 2),
                'profit' => round($totalSales - $totalCost, 2),
                'count' => $records->count(),
            ],
            'mode' => $mode,
            'month' => $month,
            'year' => $year,
            'from' => $mode === 'range' ? $rangeStart->toDateString() : null,
            'to' => $mode === 'range' ? $rangeEnd->toDateString() : null,
            'selectedDate' => $selectedDate,
            'periodLabel' => $mode === 'range'
                ? $rangeStart->format('j M Y') . ' – ' . $rangeEnd->format('j M Y')
                : $monthStart->format('F Y'),
            'weeks' => $weeks,
        ]);
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $farmer = $this->farmer($request);

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'entry_date' => ['required', 'date'],
        ]);

        $farmer->entries()->create($validated);

        return back()->with('success', 'Record added — profit/loss recalculated for the selected period.');
    }

    public function destroyEntry(Request $request, FarmerEntry $entry): RedirectResponse
    {
        abort_unless($entry->farmer_id === $request->user()->farmer->id, 403);
        $entry->delete();

        return back()->with('success', 'Record deleted.');
    }

    public function reviews(Request $request): View
    {
        $farmer = $this->farmer($request);

        return view('farmer.reviews', [
            'farmer' => $farmer,
            'reviews' => $farmer->reviews()->with(['user', 'product'])->latest()->paginate(10),
            'average' => $farmer->averageRating(),
            'total' => $farmer->totalReviews(),
        ]);
    }

    public function reviewRespond(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->farmer_id === $request->user()->farmer->id, 403);

        $validated = $request->validate([
            'response' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'farmer_response' => $validated['response'],
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Your response was published.');
    }
}
