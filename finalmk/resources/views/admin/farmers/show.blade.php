@extends('layouts.admin')

@section('title', $farmer->stall_name)

@section('content')
@php
    $fStatus = $farmer->user?->status ?? 'pending';
    $badge = fn ($status) => match ($status) {
        'pending' => 'badge-amber',
        'active', 'approved' => 'badge-green',
        'suspended' => 'badge-red',
        default => 'badge-stone',
    };
    $orderBadge = fn ($status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };
    $statusLabel = fn ($status) => match ($status) {
        'ready_for_pickup' => 'Ready for Pickup',
        default => ucfirst(str_replace('_', ' ', (string) $status)),
    };
@endphp

<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.farmers.index') }}" class="text-stone-500 dark:text-stone-400 hover:text-leaf-700 dark:hover:text-leaf-300 font-medium">← Farmers</a>
</div>

{{-- ======================= PROFILE HEADER ======================= --}}
<div class="mt-3 card p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-4 min-w-0">
            <img src="{{ $farmer->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=' . urlencode($farmer->stall_name) . '&background=307233&color=fff' }}" alt="" class="w-16 h-16 rounded-2xl object-cover ring-2 ring-leaf-100 dark:ring-leaf-800 shrink-0">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $farmer->stall_name }}</h2>
                    <span class="{{ $badge($fStatus) }}">{{ $fStatus === 'active' ? 'Approved' : ucfirst($fStatus) }}</span>
                </div>
                <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">
                    {{ $farmer->contact_person ?? $farmer->user?->name ?? '—' }}
                    @if ($farmer->user?->email) · <a href="mailto:{{ $farmer->user->email }}" class="text-leaf-700 dark:text-leaf-300 hover:underline">{{ $farmer->user->email }}</a> @endif
                    @if ($farmer->phone) · <a href="tel:{{ $farmer->phone }}" class="text-leaf-700 dark:text-leaf-300 hover:underline">{{ $farmer->phone }}</a> @endif
                </p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($farmer->markets as $market)
                        <span class="badge bg-leaf-100 text-leaf-800 dark:bg-leaf-500/15 dark:text-leaf-300">{{ $market->name }}</span>
                    @endforeach
                    <span class="badge bg-cream-100 text-cream-800 dark:bg-cream-500/10 dark:text-cream-300">Open: {{ $farmer->operatingDaysLabel() }}</span>
                </div>
                @if ($farmer->description)
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300 leading-relaxed max-w-2xl">{{ $farmer->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2 shrink-0">
            @if ($fStatus === 'pending' || $fStatus === 'suspended')
                <form method="POST" action="{{ route('admin.farmers.approve', $farmer) }}">
                    @csrf
                    <button type="submit" class="btn-primary">Approve</button>
                </form>
            @endif
            @if ($fStatus === 'active')
                <form method="POST" action="{{ route('admin.farmers.suspend', $farmer) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Suspend</button>
                </form>
            @endif
            @if ($fStatus === 'suspended')
                <form method="POST" action="{{ route('admin.farmers.restore', $farmer) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Restore</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.farmers.destroy', $farmer) }}" onsubmit="return confirm('Permanently delete {{ $farmer->stall_name }} and their account? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

{{-- ======================= STATS ======================= --}}
<div class="mt-5 grid gap-4 grid-cols-2 lg:grid-cols-5">
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Products</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['products'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Orders</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['orders'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Completed</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['completed'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Revenue</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">${{ number_format((float) $stats['revenue'], 2) }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Rating</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ number_format((float) $stats['rating'], 1) }}<span class="text-base text-amber-500">★</span></p>
    </div>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    {{-- ======================= PRODUCTS ======================= --}}
    <div class="card p-5">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Latest Products</h2>
        <div class="mt-3 overflow-x-auto -mx-5 px-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 border-b border-stone-100 dark:border-leaf-800">
                        <th class="py-2 pr-4">Product</th>
                        <th class="py-2 pr-4 text-right">Price</th>
                        <th class="py-2 pr-4 text-center">Stock</th>
                        <th class="py-2">Availability</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                    @forelse ($farmer->products as $product)
                        <tr>
                            <td class="py-2.5 pr-4">
                                <a href="{{ route('products.show', $product) }}" class="font-semibold text-stone-800 dark:text-stone-100 hover:text-leaf-700 dark:hover:text-leaf-300">{{ $product->name }}</a>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $product->category?->name ?? 'Uncategorised' }}</p>
                            </td>
                            <td class="py-2.5 pr-4 text-right text-stone-600 dark:text-stone-300">${{ number_format((float) $product->price, 2) }}<span class="text-xs text-stone-400">/{{ $product->unit }}</span></td>
                            <td class="py-2.5 pr-4 text-center text-stone-600 dark:text-stone-300">{{ $product->stock_quantity }}</td>
                            <td class="py-2.5">
                                <span class="{{ $product->is_available ? 'badge-green' : 'badge-amber' }}">{{ $product->is_available ? 'Available' : 'Unavailable' }}</span>
                                @unless ($product->is_active) <span class="badge-red">Hidden</span> @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-stone-500 dark:text-stone-400">No products listed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ======================= LOCATION ======================= --}}
    <div class="space-y-4">
        <div class="card p-5">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Location</h2>
            @if ($farmer->latitude && $farmer->longitude)
                <div class="mt-3">
                    <x-map id="farmer-map" height="h-64"
                        :center="[(float) $farmer->latitude, (float) $farmer->longitude]" :zoom="14"
                        :markers="[['id' => $farmer->id, 'name' => $farmer->stall_name, 'lat' => (float) $farmer->latitude, 'lng' => (float) $farmer->longitude, 'popup' => $farmer->stall_name]]" />
                </div>
                <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">
                    {{ $farmer->address ?? 'No address on file' }}
                    <span class="text-stone-400 dark:text-stone-500">({{ $farmer->latitude }}, {{ $farmer->longitude }})</span>
                </p>
            @else
                <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">
                    {{ $farmer->address ?? 'This farmer has not set a location on the map yet.' }}
                </p>
            @endif
        </div>

        <div class="card p-5">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Account</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-stone-500 dark:text-stone-400">Owner</dt><dd class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $farmer->user?->name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500 dark:text-stone-400">Email</dt><dd class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $farmer->user?->email ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500 dark:text-stone-400">Phone</dt><dd class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $farmer->phone ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500 dark:text-stone-400">Order cutoff</dt><dd class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $farmer->order_cutoff_hours ?? 24 }}h before pickup</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500 dark:text-stone-400">Joined</dt><dd class="font-medium text-stone-800 dark:text-stone-100 text-right">{{ $farmer->created_at?->format('M j, Y') }}</dd></div>
            </dl>
        </div>
    </div>
</div>

{{-- ======================= RECENT ORDERS ======================= --}}
<div class="mt-6 card p-5">
    <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Recent Orders</h2>
    <div class="mt-3 overflow-x-auto -mx-5 px-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 border-b border-stone-100 dark:border-leaf-800">
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4 text-right">Total</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2">Placed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                @forelse ($orders as $order)
                    <tr>
                        <td class="py-2.5 pr-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="py-2.5 pr-4 text-stone-600 dark:text-stone-300">{{ $order->customer?->name ?? '—' }}</td>
                        <td class="py-2.5 pr-4 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format((float) $order->total_amount, 2) }}</td>
                        <td class="py-2.5 pr-4"><span class="{{ $orderBadge($order->status) }}">{{ $statusLabel($order->status) }}</span></td>
                        <td class="py-2.5 text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $order->placed_at?->format('M j, Y g:ia') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-stone-500 dark:text-stone-400">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>

{{-- ======================= REVIEWS ======================= --}}
<div class="mt-6 card p-5">
    <div class="flex items-center justify-between">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Customer Reviews</h2>
        <span class="text-sm text-stone-500 dark:text-stone-400">Average {{ number_format((float) $stats['rating'], 1) }} / 5</span>
    </div>
    <ul class="mt-4 space-y-4">
        @forelse ($reviews as $review)
            <li class="flex items-start gap-3 border-b border-stone-100 dark:border-leaf-800/70 last:border-0 pb-4 last:pb-0">
                <img src="{{ $review->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=U&background=307233&color=fff' }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $review->user?->name ?? 'Customer' }}</p>
                        <span class="text-amber-500 text-xs font-semibold">{{ $review->rating }}★</span>
                        <span class="text-xs text-stone-400">{{ $review->created_at?->diffForHumans() }}</span>
                        @if ($review->is_hidden) <span class="badge-red">Hidden</span> @endif
                    </div>
                    <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">{{ $review->comment ?: '(no comment)' }}</p>
                    @if ($review->farmer_response)
                        <p class="mt-2 text-xs bg-leaf-50 dark:bg-leaf-900/50 rounded-lg px-3 py-2 text-stone-600 dark:text-stone-300"><span class="font-semibold">Farmer reply:</span> {{ $review->farmer_response }}</p>
                    @endif
                </div>
            </li>
        @empty
            <li class="py-8 text-center text-stone-500 dark:text-stone-400">No reviews received yet.</li>
        @endforelse
    </ul>
    @if ($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
