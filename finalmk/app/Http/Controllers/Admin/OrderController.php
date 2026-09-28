<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,' . implode(',', Order::STATUSES)],
            'q' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'market' => ['nullable', 'integer', 'exists:markets,id'],
        ]);

        $orders = Order::with(['customer', 'farmer.user', 'market', 'items'])
            ->when(($filters['status'] ?? 'all') !== 'all', fn($q) => $q->where('status', $filters['status']))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('placed_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('placed_at', '<=', $v))
            ->when($filters['market'] ?? null, fn($q, $v) => $q->where('market_id', $v))
            ->latest('placed_at')
            ->paginate(15)
            ->appends($filters);

        $counts = [];
        foreach (Order::STATUSES as $s) {
            $counts[$s] = Order::where('status', $s)->count();
        }

        return view('admin.orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'statuses' => Order::STATUSES,
            'markets' => \App\Models\Market::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'status' => $filters['status'] ?? 'all',
                'q' => $filters['q'] ?? null,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
                'market' => $filters['market'] ?? null,
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'farmer.user', 'market', 'items.product', 'reviews.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->canBeCancelled(), 422, 'This order can no longer be cancelled.');
        $order->update(['status' => 'cancelled']);

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product()->increment('stock_quantity', $item->quantity);
            }
        }

        $order->customer->notify(new \App\Notifications\OrderStatusNotification($order));
        $order->farmer?->user?->notify(new \App\Notifications\OrderStatusNotification($order));

        return back()->with('success', 'Order ' . $order->order_number . ' cancelled and stock returned.');
    }
}
