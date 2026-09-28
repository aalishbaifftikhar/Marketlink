<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FarmerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,' . implode(',', Order::STATUSES)],
            'q' => ['nullable', 'string', 'max:50'],
            'date' => ['nullable', 'date'],
        ]);

        $orders = $farmers->orders()->with(['customer', 'market', 'items.product'])
            ->when(($filters['status'] ?? 'all') !== 'all', fn($q, $v) => $q->where('status', $filters['status']), fn($q) => $q->whereIn('status', ['placed', 'accepted', 'ready_for_pickup']))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($filters['date'] ?? null, fn($q, $v) => $q->whereDate('pickup_date', $v))
            ->orderByRaw("FIELD(status, 'placed', 'accepted', 'ready_for_pickup', 'completed', 'declined', 'cancelled')")
            ->latest('placed_at')
            ->paginate(12)
            ->appends($filters);

        $counts = [];
        foreach (Order::STATUSES as $s) {
            $counts[$s] = $farmers->orders()->where('status', $s)->count();
        }

        return view('farmer.orders.index', [
            'farmer' => $farmers,
            'orders' => $orders,
            'counts' => $counts,
            'statuses' => Order::STATUSES,
            'filters' => ['status' => $filters['status'] ?? 'all', 'q' => $filters['q'] ?? null, 'date' => $filters['date'] ?? null],
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);
        $order->load(['customer', 'market', 'items.product']);

        return view('farmer.orders.show', ['farmer' => $order->farmer, 'order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);
        $data = $request->validate([
            'status' => ['required', 'in:accepted,declined,ready_for_pickup,completed'],
            'farmer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = $data['status'];
        $current = $order->status;

        $allowed = match ($newStatus) {
            'accepted' => in_array($current, ['placed']),
            'declined' => in_array($current, ['placed']),
            'ready_for_pickup' => in_array($current, ['accepted']),
            'completed' => in_array($current, ['ready_for_pickup', 'accepted']),
            default => false,
        };

        if (! $allowed) {
            return back()->with('error', 'This order is already ' . str_replace('_', ' ', $current) . '.');
        }

        $order->update([
            'status' => $newStatus,
            'farmer_notes' => $data['farmer_notes'] ?? $order->farmer_notes,
            'completed_at' => $newStatus === 'completed' ? now() : $order->completed_at,
        ]);

        // Restock items when a placed order is declined.
        if ($newStatus === 'declined' && $current === 'placed') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product()->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $order->customer->notify(new OrderStatusNotification($order, $newStatus));

        $message = match ($newStatus) {
            'accepted' => 'Order accepted. The customer has been notified.',
            'declined' => 'Order declined and stock returned. The customer has been notified.',
            'ready_for_pickup' => 'Order marked ready for pickup. The customer has been notified.',
            'completed' => 'Order completed. Thanks for the sale!',
            default => 'Order updated.',
        };

        return back()->with('success', $message);
    }
}