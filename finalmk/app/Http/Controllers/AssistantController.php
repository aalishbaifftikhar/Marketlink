<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Basic rule-based assistant (per the SRS "optional basic AI" requirement).
 * Answers common questions about markets, farmers, pickup windows and products,
 * and can look up the customer's own orders. No external API key required.
 */
class AssistantController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $message = trim((string) $request->input('message', ''));
        $request->validate(['message' => ['required', 'string', 'max:500']]);

        $reply = $this->answer($message, $request);

        return response()->json($reply);
    }

    private function answer(string $message, Request $request): array
    {
        $m = Str::lower($message);

        return match (true) {
            $this->isGreeting($m) => $this->greeting(),
            str_contains($m, 'order') && (str_contains($m, 'status') || str_contains($m, 'track') || str_contains($m, 'where')) => $this->orderStatus($request),
            str_contains($m, 'market') && (str_contains($m, 'time') || str_contains($m, 'hour') || str_contains($m, 'open') || str_contains($m, 'when')) => $this->marketTimings(),
            str_contains($m, 'pickup') || str_contains($m, 'pick up') || str_contains($m, 'collect') => $this->pickupWindows($m),
            str_contains($m, 'open today') || str_contains($m, 'today') => $this->whatsOpenToday(),
            str_contains($m, 'who') || str_contains($m, 'farmer') || str_contains($m, 'stall') || str_contains($m, 'vendor') => $this->farmerInfo($m),
            str_contains($m, 'how') && (str_contains($m, 'work') || str_contains($m, 'order') || str_contains($m, 'use')) => $this->howItWorks(),
            default => $this->searchProducts($message),
        };
    }

    private function isGreeting(string $m): bool
    {
        return in_array($m, ['hi', 'hello', 'hey', 'hi there', 'good morning', 'good afternoon', 'good evening'], true)
            || str_starts_with($m, 'hi ') || str_starts_with($m, 'hello ') || str_starts_with($m, 'hey ');
    }

    private function greeting(): array
    {
        return [
            'reply' => "Hello! I'm the MarketLink assistant. I can help you find fresh produce, check market and pickup times, or track your pre-orders. Try asking things like “Do you have organic tomatoes?” or “What time does Greenfield market open?”.",
            'suggestions' => ['What markets are open today?', 'Do you have fresh eggs?', 'Where is my order?', 'How do pre-orders work?'],
        ];
    }

    private function orderStatus(Request $request): array
    {
        if (! $request->user() || ! $request->user()->isCustomer()) {
            return [
                'reply' => 'You can track your pre-orders from your account. Log in as a customer, then open “My Orders” to see live statuses — placed, accepted, ready for pickup and completed.',
                'link' => ['label' => 'Go to My Orders', 'url' => route('login')],
            ];
        }

        $orders = $request->user()->orders()->active()->with('farmer')->orderBy('pickup_date')->take(3)->get();

        if ($orders->isEmpty()) {
            return [
                'reply' => "You don't have any active pre-orders right now. Once you place one, I'll keep an eye on it for you.",
                'link' => ['label' => 'Browse fresh products', 'url' => route('products.index')],
            ];
        }

        $lines = $orders->map(fn ($o) => "• {$o->order_number} with {$o->farmer->stall_name} — {$o->statusLabel()} · pickup {$o->pickup_date->format('D, j M')} {$o->pickup_slot}")->implode("\n");

        return [
            'reply' => "Here are your active pre-orders:\n{$lines}",
            'link' => ['label' => 'Open my orders', 'url' => route('customer.orders.index')],
        ];
    }

    private function marketTimings(): array
    {
        $markets = Market::active()->orderBy('name')->take(6)->get();

        if ($markets->isEmpty()) {
            return ['reply' => 'Markets will be listed here soon — our team is adding them.'];
        }

        $lines = $markets->map(fn ($m) => "• {$m->name}: {$m->operatingDaysLabel()} · " . ($m->open_time ? substr($m->open_time, 0, 5) . '–' . substr($m->close_time, 0, 5) : 'times vary'))->implode("\n");

        return [
            'reply' => "Here are our current markets and their hours:\n{$lines}",
            'link' => ['label' => 'See all markets on the map', 'url' => route('markets.index')],
        ];
    }

    private function pickupWindows(string $m): array
    {
        $farmers = Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->with('pickupSlots')
            ->take(5)
            ->get();

        if ($farmers->isEmpty()) {
            return ['reply' => 'Pickup windows are published by each farmer on their stall page.'];
        }

        $lines = $farmers->map(function ($f) {
            $slots = $f->pickupSlots->where('is_active', true)->groupBy('day_of_week')
                ->map(fn ($g, $d) => \App\Models\PickupSlot::make(['day_of_week' => $d])->dayName() . ' ' . $g->map->label()->implode(', '))
                ->implode(' · ');

            return "• {$f->stall_name}: " . ($slots ?: 'flexible pickup — message them after ordering');
        })->implode("\n");

        return [
            'reply' => "Current pickup windows:\n{$lines}\nWhen you pre-order you can pick any slot the farmer offers for your pickup day.",
        ];
    }

    private function whatsOpenToday(): array
    {
        $open = Market::active()->get()->filter->isOpenToday();

        if ($open->isEmpty()) {
            return [
                'reply' => 'No markets are listed as open today — but you can still pre-order for the next market day and reserve your items in advance.',
                'link' => ['label' => 'Browse products', 'url' => route('products.index')],
            ];
        }

        $lines = $open->map(fn ($m) => "• {$m->name} (" . substr($m->open_time, 0, 5) . '–' . substr($m->close_time, 0, 5) . ') — ' . $m->address)->implode("\n");

        return [
            'reply' => "Markets open today:\n{$lines}",
            'link' => ['label' => 'View on map', 'url' => route('markets.index')],
        ];
    }

    private function farmerInfo(string $m): array
    {
        $farmers = Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(5)
            ->get();

        if ($farmers->isEmpty()) {
            return ['reply' => 'Farmers will appear here once they are approved by our team.'];
        }

        $lines = $farmers->map(fn ($f) => "• {$f->stall_name} — {$f->products_count} products this week, rated {$f->averageRating()}/5")->implode("\n");

        return [
            'reply' => "Here are some of our popular farmers right now:\n{$lines}",
            'link' => ['label' => 'Browse all farmers', 'url' => route('farmers.index')],
        ];
    }

    private function howItWorks(): array
    {
        return [
            'reply' => "Shopping on MarketLink works in 4 steps:\n1. Browse farmers and their weekly stock.\n2. Add items to your basket and choose a pickup day + time slot.\n3. The farmer accepts your pre-order and prepares it.\n4. Pay in person when you collect it at the market — no online payment needed.\nYou can modify or cancel a pre-order up to the farmer's cutoff time.",
            'link' => ['label' => 'Start shopping', 'url' => route('products.index')],
        ];
    }

    private function searchProducts(string $message): array
    {
        // Strip filler words to use the message as a product search query.
        $stop = ['do', 'you', 'have', 'any', 'is', 'are', 'there', 'can', 'i', 'get', 'buy', 'find', 'show', 'me', 'please', 'the', 'a', 'an', 'some', 'fresh', 'organic', 'local', 'today', 'available', 'what', 'which', 'about', 'tell', 'looking', 'for', 'near'];
        $words = collect(preg_split('/\s+/', Str::lower($message)))
            ->reject(fn ($w) => in_array($w, $stop, true) || strlen($w) < 2)
            ->values();

        $query = Product::available()->with('farmer');

        foreach ($words as $w) {
            $query->where('name', 'like', "%{$w}%");
        }

        $products = $query->take(4)->get();

        if ($products->isEmpty() && $words->isNotEmpty()) {
            // Fallback: match any word in name or description.
            $query = Product::available()->with('farmer');
            foreach ($words as $w) {
                $query->orWhere('name', 'like', "%{$w}%")->orWhere('description', 'like', "%{$w}%");
            }
            $products = $query->take(4)->get();
        }

        if ($products->isEmpty()) {
            return [
                'reply' => "I couldn't find anything matching that, but our farmers update stock every week. Try browsing the full catalogue, or ask me about market hours and pickup times instead.",
                'link' => ['label' => 'Browse all products', 'url' => route('products.index')],
                'suggestions' => ['What markets are open today?', 'How do pre-orders work?', 'Who are the farmers?'],
            ];
        }

        $cards = $products->map(fn ($p) => [
            'name' => $p->name,
            'meta' => $p->farmer->stall_name . ' · $' . number_format($p->price, 2) . ' / ' . $p->unit,
            'url' => route('products.show', $p),
        ])->values();

        $lines = $products->map(fn ($p) => "• {$p->name} — {$p->farmer->stall_name} at $" . number_format($p->price, 2) . '/' . $p->unit)->implode("\n");

        return [
            'reply' => "Here's what I found:\n{$lines}",
            'cards' => $cards,
            'link' => ['label' => 'See all results', 'url' => route('products.index', ['q' => $words->implode(' ')])],
        ];
    }
}
