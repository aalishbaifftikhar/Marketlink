<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PickupSlot;
use App\Models\Product;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $groups = CartController::grouped($request);

        if (empty($groups)) {
            return redirect()->route('customer.cart.index')->with('error', 'Your basket is empty.');
        }

        foreach ($groups as &$group) {
            $group['schedule'] = $this->scheduleFor($group['farmer']);
        }

        return view('customer.checkout', [
            'groups' => $groups,
            'grandTotal' => collect($groups)->sum('subtotal'),
        ]);
    }

    /**
     * Build the pickup schedule options for a farmer:
     * the next 14 days that have an active pickup slot, with their slots.
     */
    private function scheduleFor($farmer): array
    {
        $slots = $farmer->pickupSlots()->where('is_active', true)->get()
            ->groupBy('day_of_week');
        $cutoffHours = $farmer->order_cutoff_hours ?? 24;

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $date = now()->addDays($i)->startOfDay();
            $dayOfWeek = (int) $date->format('w');

            if (! $slots->has($dayOfWeek)) {
                continue;
            }

            // Only offer slots whose order cutoff has not passed yet.
            $bookable = $slots[$dayOfWeek]
                ->filter(fn (PickupSlot $s) => now()->lt(
                    $date->copy()->setTimeFromTimeString($s->start_time)->subHours($cutoffHours)
                ))
                ->map(fn (PickupSlot $s) => [
                    'id' => $s->id,
                    'label' => $s->label(),
                    'start' => $s->start_time,
                ])->values();

            if ($bookable->isEmpty()) {
                continue;
            }

            $days[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('D, j M'),
                'slots' => $bookable,
            ];
        }

        return $days;
    }

    public function store(Request $request): RedirectResponse
    {
        $groups = CartController::grouped($request);

        if (empty($groups)) {
            return redirect()->route('customer.cart.index')->with('error', 'Your basket is empty.');
        }

        // Validate each farmer group.
        $rules = [];
        foreach ($groups as $farmerId => $group) {
            $rules["orders.$farmerId.market_id"] = ['nullable', 'exists:markets,id'];
            $rules["orders.$farmerId.pickup_date"] = ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(14)->toDateString()];
            $rules["orders.$farmerId.slot_id"] = ['required', 'exists:pickup_slots,id'];
            $rules["orders.$farmerId.notes"] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);
        $placed = [];
        $pendingNotifications = [];

        try {
            DB::transaction(function () use ($groups, $validated, $request, &$placed, &$pendingNotifications) {
            foreach ($groups as $farmerId => $group) {
                $input = $validated['orders'][$farmerId];
                $farmer = $group['farmer'];

                $slot = PickupSlot::where('farmer_id', $farmer->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($input['slot_id']);

                $pickupDate = Carbon::parse($input['pickup_date'])->startOfDay();

                abort_if((int) $pickupDate->format('w') !== (int) $slot->day_of_week, 422, 'The chosen slot does not match the pickup date.');

                $cutoffHours = $farmer->order_cutoff_hours ?? 24;
                $cutoffAt = $pickupDate->copy()->setTimeFromTimeString($slot->start_time)->subHours($cutoffHours);

                if (now()->gte($cutoffAt)) {
                    throw new \RuntimeException('Orders for ' . $farmer->stall_name . ' have closed for ' . $pickupDate->format('j M') . ' (cutoff: ' . $cutoffHours . 'h before pickup). Please choose a later pickup date.');
                }

                $items = [];
                $subtotal = 0;

                foreach ($group['items'] as $line) {
                    /** @var Product $product */
                    $product = Product::where('farmer_id', $farmer->id)->lockForUpdate()->findOrFail($line['product']->id);

                    if (! $product->is_available || $product->stock_quantity < $line['qty']) {
                        throw new \RuntimeException('Insufficient stock for ' . $product->name . '. Please review your basket.');
                    }

                    $lineTotal = round($product->price * $line['qty'], 2);
                    $subtotal += $lineTotal;

                    $items[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit' => $product->unit,
                        'unit_price' => $product->price,
                        'quantity' => $line['qty'],
                        'line_total' => $lineTotal,
                    ];
                }

                $order = Order::create([
                    'customer_id' => auth()->id(),
                    'farmer_id' => $farmer->id,
                    'market_id' => $input['market_id'] ?? $farmer->markets()->first()?->id,
                    'status' => 'placed',
                    'pickup_date' => $pickupDate->toDateString(),
                    'pickup_slot' => $slot->label(),
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal,
                    'customer_notes' => $input['notes'] ?? null,
                    'cutoff_at' => $cutoffAt,
                    'placed_at' => now(),
                ]);

                $order->items()->createMany($items);

                foreach ($group['items'] as $line) {
                    $line['product']->decrement('stock_quantity', $line['qty']);
                }

                $pendingNotifications[] = [$farmer->user, $order->load('items', 'customer')];
                $placed[] = $order->order_number;
            }

            $request->session()->forget('cart');
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('customer.cart.index')->with('error', $e->getMessage());
        }

        // Notify farmers only after the orders are committed, so a rolled-back
        // group never announces an order and a mail hiccup never fails checkout.
        foreach ($pendingNotifications as [$user, $order]) {
            try {
                Notification::send($user, new NewOrderNotification($order));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('customer.orders.index')
            ->with('success', count($placed) > 1
                ? 'Your pre-orders ' . implode(', ', $placed) . ' were placed successfully.'
                : 'Your pre-order ' . $placed[0] . ' was placed successfully.');
    }
}
