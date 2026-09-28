<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $upcoming = $user->orders()->active()->with('farmer')->orderBy('pickup_date')->take(4)->get();
        $recent = $user->orders()->history()->with('farmer')->latest('placed_at')->take(4)->get();

        $favoriteFarmerIds = $user->favorites()->where('favoritable_type', 'farmer')->pluck('favoritable_id');
        $favoriteProductIds = $user->favorites()->where('favoritable_type', 'product')->pluck('favoritable_id');

        return view('customer.dashboard', [
            'upcomingOrders' => $upcoming,
            'recentOrders' => $recent,
            'stats' => [
                'active' => $user->orders()->active()->count(),
                'completed' => $user->orders()->where('status', 'completed')->count(),
                'favorite_farmers' => $favoriteFarmerIds->count(),
                'favorite_products' => $favoriteProductIds->count(),
            ],
            'suggestions' => Product::available()
                ->where(fn ($q) => $q->whereIn('farmer_id', $favoriteFarmerIds)->orWhereIn('id', $favoriteProductIds))
                ->with('farmer')
                ->inRandomOrder()
                ->take(4)
                ->get(),
            'announcements' => Announcement::published()->forAudience('customer')->latest('published_at')->take(3)->get(),
        ]);
    }

    public function assistant(): View
    {
        return view('customer.assistant');
    }

    public function orders(Request $request): View
    {
        $tab = $request->get('tab', 'active');

        $query = $request->user()->orders()->with('farmer');

        if ($tab === 'history') {
            $query->history();
        } else {
            $tab = 'active';
            $query->active();
        }

        return view('customer.orders', [
            'orders' => $query->orderByDesc('placed_at')->paginate(8)->withQueryString(),
            'tab' => $tab,
        ]);
    }

    public function orderShow(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $order->load(['items.product', 'farmer.markets', 'market']);

        $reviews = Review::where('order_id', $order->id)->where('user_id', $request->user()->id)->get()->keyBy('product_id');

        return view('customer.orders-show', [
            'order' => $order,
            'reviews' => $reviews,
            'canReview' => $order->canBeReviewed(),
        ]);
    }

    public function orderEdit(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->canBeModified(), 403, 'This order can no longer be modified.');

        $order->load(['items.product', 'farmer.markets']);

        return view('customer.orders-edit', ['order' => $order]);
    }

    public function orderUpdate(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->canBeModified(), 403, 'This order can no longer be modified.');

        $rules = ['notes' => ['nullable', 'string', 'max:500']];
        foreach ($order->items as $item) {
            $rules["quantities.{$item->id}"] = ['required', 'integer', 'min:0', 'max:99'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($order, $validated) {
            $subtotal = 0;

            foreach ($order->items()->lockForUpdate()->get() as $item) {
                $qty = (int) ($validated['quantities'][$item->id] ?? $item->quantity);

                if ($qty === 0) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                    $item->delete();
                    continue;
                }

                $product = $item->product()->lockForUpdate()->first();
                if ($product) {
                    $delta = $qty - $item->quantity;
                    if ($delta > 0 && $product->stock_quantity < $delta) {
                        throw new \RuntimeException('Not enough stock to increase ' . $product->name . '.');
                    }
                    $product->decrement('stock_quantity', $delta);
                }

                $lineTotal = round($item->unit_price * $qty, 2);
                $subtotal += $lineTotal;
                $item->update(['quantity' => $qty, 'line_total' => $lineTotal]);
            }

            if ($order->items()->count() === 0) {
                $order->update(['status' => 'cancelled', 'subtotal' => 0, 'total_amount' => 0]);
            } else {
                $order->update(['subtotal' => $subtotal, 'total_amount' => $subtotal, 'customer_notes' => $validated['notes'] ?? $order->customer_notes]);
            }
        });

        return redirect()->route('customer.orders.show', $order)->with('success', 'Order updated.');
    }

    public function orderCancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->canBeCancelled(), 403, 'This order can no longer be cancelled.');

        DB::transaction(function () use ($order) {
            foreach ($order->items()->with('product')->get() as $item) {
                $item->product?->increment('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);
        });

        return redirect()->route('customer.orders.index')->with('success', 'Order ' . $order->order_number . ' was cancelled.');
    }

    public function orderReorder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $cart = $request->session()->get('cart', []);
        $added = 0;

        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;
            if ($product && $product->is_available && $product->stock_quantity > 0) {
                $current = $cart[$product->id] ?? 0;
                $qty = min($item->quantity, $product->stock_quantity - $current);
                if ($qty > 0) {
                    $cart[$product->id] = $current + $qty;
                    $added++;
                }
            }
        }

        $request->session()->put('cart', $cart);

        return $added > 0
            ? redirect()->route('customer.cart.index')->with('success', "$added item(s) from your previous order were added to the basket.")
            : back()->with('error', 'None of the items from that order are currently available.');
    }

    public function favorites(Request $request): View
    {
        $favorites = $request->user()->favorites()->with('favoritable')->latest()->get();

        return view('customer.favorites', [
            'farmers' => $favorites->where('favoritable_type', 'farmer')->map->favoritable,
            'products' => $favorites->where('favoritable_type', 'product')->map->favoritable,
            'markets' => $favorites->where('favoritable_type', 'market')->map->favoritable,
        ]);
    }
}
