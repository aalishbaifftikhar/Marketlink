<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Store reviews for a completed order: one overall farmer review plus
     * an optional rating per product line.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'farmer_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'farmer_comment' => ['nullable', 'string', 'max:1000'],
            'products' => ['array'],
            'products.*.rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'products.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::with('items.product')->findOrFail($validated['order_id']);

        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->status === 'completed', 403, 'You can only review completed orders.');

        DB::transaction(function () use ($request, $order, $validated) {
            // Overall farmer review (product_id = null)
            $exists = Review::where('user_id', $request->user()->id)
                ->where('order_id', $order->id)
                ->whereNull('product_id')
                ->exists();

            if (! $exists) {
                Review::create([
                    'user_id' => $request->user()->id,
                    'farmer_id' => $order->farmer_id,
                    'order_id' => $order->id,
                    'rating' => $validated['farmer_rating'],
                    'comment' => $validated['farmer_comment'] ?? null,
                ]);
            }

            foreach ($order->items as $item) {
                $input = $validated['products'][$item->product_id] ?? null;
                if (! $input || empty($input['rating'])) {
                    continue;
                }

                $exists = Review::where('user_id', $request->user()->id)
                    ->where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                Review::create([
                    'user_id' => $request->user()->id,
                    'farmer_id' => $order->farmer_id,
                    'product_id' => $item->product_id,
                    'order_id' => $order->id,
                    'rating' => $input['rating'],
                    'comment' => $input['comment'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Thank you! Your review was published.');
    }
}
