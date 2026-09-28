<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->validate(['range' => ['nullable', 'in:7,30,90']])['range'] ?? 30;
        $since = now()->subDays((int) $range);

        $stats = [
            'farmers' => Farmer::count(),
            'farmers_pending' => Farmer::pending()->count(),
            'customers' => User::where('role', 'customer')->count(),
            'markets' => Market::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'orders_active' => Order::whereIn('status', ['placed', 'accepted', 'ready_for_pickup'])->count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        $dailyOrders = Order::where('placed_at', '>=', $since)
            ->select(DB::raw('DATE(placed_at) as day'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('day')->orderBy('day')->get();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status');

        $topFarmers = Farmer::with('user')->withCount(['orders as completed_orders' => fn($q) => $q->where('status', 'completed')])
            ->withSum(['orders as revenue' => fn($q) => $q->where('status', 'completed')], 'total_amount')
            ->orderByDesc('revenue')->take(5)->get();

        $latestFarmers = Farmer::with('user')->latest()->take(5)->get();
        $latestOrders = Order::with(['customer', 'farmer.user'])->latest('placed_at')->take(8)->get();
        $pendingReviews = Review::where('is_hidden', false)->with(['user', 'farmer.user'])->latest()->take(5)->get();
        $latestAnnouncements = \App\Models\Announcement::latest()->take(4)->get();

        return view('admin.dashboard', compact(
            'stats', 'range', 'dailyOrders', 'ordersByStatus', 'topFarmers', 'latestFarmers', 'latestOrders', 'pendingReviews', 'latestAnnouncements'
        ));
    }
}
