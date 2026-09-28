@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
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
    $farmerBadge = fn ($status) => match ($status) {
        'pending' => 'badge-amber',
        'active', 'approved' => 'badge-green',
        'suspended' => 'badge-red',
        default => 'badge-stone',
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-stone-500 dark:text-stone-400">Platform overview for the last <span class="font-semibold text-stone-700 dark:text-stone-200">{{ $range }} days</span>.</p>
    <div class="flex items-center gap-1 rounded-xl border border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60 p-1">
        @foreach ([7, 30, 90] as $option)
            <a href="{{ route('admin.dashboard', ['range' => $option]) }}"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ (int) $range === $option ? 'bg-leaf-600 text-white shadow-soft' : 'text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-800/60' }}">
                {{ $option }}D
            </a>
        @endforeach
    </div>
</div>

{{-- ======================= STAT CARDS ======================= --}}
<div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <a href="{{ route('admin.farmers.index', ['status' => 'pending']) }}" class="card card-hover p-5 block">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Farmers Pending Approval</p>
            <span class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 grid place-items-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>
        <p class="mt-2 font-display text-3xl font-semibold {{ $stats['farmers_pending'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-leaf-950 dark:text-cream-50' }}">{{ $stats['farmers_pending'] }}</p>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $stats['farmers'] }} total farmers · review now</p>
    </a>

    <a href="{{ route('admin.orders.index') }}" class="card card-hover p-5 block">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Active Orders</p>
            <span class="w-9 h-9 rounded-xl bg-leaf-100 dark:bg-leaf-500/15 text-leaf-700 dark:text-leaf-300 grid place-items-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 3h6a2 2 0 012 2v0a2 2 0 01-2 2H9a2 2 0 01-2-2v0a2 2 0 012-2zM5 7h14l-1.2 12a2 2 0 01-2 1.8H8.2a2 2 0 01-2-1.8L5 7z"/></svg>
            </span>
        </div>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['orders_active'] }}</p>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $stats['orders'] }} orders all-time</p>
    </a>

    <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="card card-hover p-5 block">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Completed Revenue</p>
            <span class="w-9 h-9 rounded-xl bg-cream-100 dark:bg-cream-500/10 text-cream-700 dark:text-cream-300 grid place-items-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-8c1.7 0 3.1.6 3.7 1.5M12 6V4m0 12v2m0-6v2"/></svg>
            </span>
        </div>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">${{ number_format((float) $stats['revenue'], 2) }}</p>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">from completed orders</p>
    </a>

    <div class="card p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Catalogue Size</p>
            <span class="w-9 h-9 rounded-xl bg-leaf-100 dark:bg-leaf-500/15 text-leaf-700 dark:text-leaf-300 grid place-items-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
        </div>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['products'] }}</p>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $stats['markets'] }} markets · {{ $stats['customers'] }} customers</p>
    </div>

    <a href="{{ route('admin.farmers.index') }}" class="card card-hover p-5 block">
        <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Total Farmers</p>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['farmers'] }}</p>
        <p class="mt-1 text-xs text-leaf-700 dark:text-leaf-300 font-medium">Manage farmers →</p>
    </a>

    <a href="{{ route('admin.customers.index') }}" class="card card-hover p-5 block">
        <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Registered Customers</p>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['customers'] }}</p>
        <p class="mt-1 text-xs text-leaf-700 dark:text-leaf-300 font-medium">Manage customers →</p>
    </a>

    <a href="{{ route('admin.markets.index') }}" class="card card-hover p-5 block">
        <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Markets</p>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['markets'] }}</p>
        <p class="mt-1 text-xs text-leaf-700 dark:text-leaf-300 font-medium">Manage markets →</p>
    </a>

    <a href="{{ route('admin.products.index') }}" class="card card-hover p-5 block">
        <p class="text-sm font-semibold text-stone-500 dark:text-stone-400">Listed Products</p>
        <p class="mt-2 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['products'] }}</p>
        <p class="mt-1 text-xs text-leaf-700 dark:text-leaf-300 font-medium">Moderate catalogue →</p>
    </a>
</div>

{{-- ======================= CHARTS ======================= --}}
<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Daily Orders <span class="text-sm font-sans font-medium text-stone-500 dark:text-stone-400">· last {{ $range }} days</span></h2>
        <div class="mt-4 h-72">
            <canvas id="daily-orders-chart"></canvas>
        </div>
    </div>
    <div class="card p-5">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Orders by Status</h2>
        <div class="mt-4 h-72">
            <canvas id="orders-status-chart"></canvas>
        </div>
    </div>
</div>

{{-- ======================= TOP FARMERS + LATEST FARMERS ======================= --}}
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Top Farmers</h2>
            <a href="{{ route('admin.farmers.index') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">View all</a>
        </div>
        <div class="mt-3 overflow-x-auto -mx-5 px-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 border-b border-stone-100 dark:border-leaf-800">
                        <th class="py-2 pr-4">Stall</th>
                        <th class="py-2 pr-4 text-right">Completed Orders</th>
                        <th class="py-2 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                    @forelse ($topFarmers as $farmer)
                        <tr>
                            <td class="py-2.5 pr-4">
                                <a href="{{ route('admin.farmers.show', $farmer) }}" class="font-semibold text-stone-800 dark:text-stone-100 hover:text-leaf-700 dark:hover:text-leaf-300">{{ $farmer->stall_name }}</a>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $farmer->user?->name }}</p>
                            </td>
                            <td class="py-2.5 pr-4 text-right text-stone-600 dark:text-stone-300">{{ $farmer->completed_orders }}</td>
                            <td class="py-2.5 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format((float) ($farmer->revenue ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-stone-500 dark:text-stone-400">No farmer sales recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Newest Farmers</h2>
            <a href="{{ route('admin.farmers.index') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">View all</a>
        </div>
        <ul class="mt-3 space-y-3">
            @forelse ($latestFarmers as $farmer)
                @php $fStatus = $farmer->user?->status ?? 'pending'; @endphp
                <li class="flex items-center gap-3">
                    <img src="{{ $farmer->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=' . urlencode($farmer->stall_name) . '&background=307233&color=fff' }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-leaf-100 dark:ring-leaf-800 shrink-0">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.farmers.show', $farmer) }}" class="font-semibold text-sm text-stone-800 dark:text-stone-100 hover:text-leaf-700 dark:hover:text-leaf-300">{{ $farmer->stall_name }}</a>
                        <p class="text-xs text-stone-500 dark:text-stone-400">Joined {{ $farmer->created_at?->diffForHumans() }}</p>
                    </div>
                    <span class="{{ $farmerBadge($fStatus) }}">{{ $fStatus === 'active' ? 'Approved' : ucfirst($fStatus) }}</span>
                    @if ($fStatus !== 'active')
                        <form method="POST" action="{{ route('admin.farmers.approve', $farmer) }}">
                            @csrf
                            <button type="submit" class="btn-primary btn-sm">Approve</button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="py-8 text-center text-stone-500 dark:text-stone-400">No farmers registered yet.</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- ======================= LATEST ORDERS + REVIEWS/ANNOUNCEMENTS ======================= --}}
<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Latest Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">View all</a>
        </div>
        <div class="mt-3 overflow-x-auto -mx-5 px-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 border-b border-stone-100 dark:border-leaf-800">
                        <th class="py-2 pr-4">Order</th>
                        <th class="py-2 pr-4">Customer</th>
                        <th class="py-2 pr-4">Farmer</th>
                        <th class="py-2 pr-4 text-right">Total</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2">Placed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                    @forelse ($latestOrders as $order)
                        <tr>
                            <td class="py-2.5 pr-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $order->order_number }}</a>
                            </td>
                            <td class="py-2.5 pr-4 text-stone-600 dark:text-stone-300">{{ $order->customer?->name ?? '—' }}</td>
                            <td class="py-2.5 pr-4 text-stone-600 dark:text-stone-300">{{ $order->farmer?->stall_name ?? '—' }}</td>
                            <td class="py-2.5 pr-4 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="py-2.5 pr-4"><span class="{{ $orderBadge($order->status) }}">{{ $statusLabel($order->status) }}</span></td>
                            <td class="py-2.5 text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $order->placed_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-stone-500 dark:text-stone-400">No orders placed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Reviews Awaiting Moderation</h2>
                <a href="{{ route('admin.reviews.index') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">Moderate</a>
            </div>
            <ul class="mt-3 space-y-3">
                @forelse ($pendingReviews as $review)
                    <li class="flex items-start gap-3">
                        <img src="{{ $review->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=U&background=307233&color=fff' }}" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $review->user?->name ?? 'Customer' }}
                                <span class="font-normal text-stone-500 dark:text-stone-400">on {{ $review->farmer?->stall_name ?? '—' }}</span>
                            </p>
                            <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2">{{ $review->comment ?: '(no comment)' }}</p>
                        </div>
                        <span class="badge-amber shrink-0">{{ $review->rating }}★</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-stone-500 dark:text-stone-400">No visible reviews to check.</li>
                @endforelse
            </ul>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Latest Announcements</h2>
                <a href="{{ route('admin.announcements.index') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">Manage</a>
            </div>
            <ul class="mt-3 space-y-3">
                @forelse ($latestAnnouncements as $announcement)
                    <li class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-stone-800 dark:text-stone-100 truncate">{{ $announcement->title }}</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="{{ $announcement->is_published ? 'badge-green' : 'badge-stone' }} shrink-0">{{ ucfirst($announcement->audience) }}</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-stone-500 dark:text-stone-400">No announcements yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    const palette = ['#65a30d', '#84cc16', '#a3e635', '#f59e0b', '#78716c', '#307233'];

    // --- Daily orders (line) ---
    const daily = @json($dailyOrders);
    const dailyLabels = daily.map(r => {
        const d = new Date(r.day + 'T00:00:00');
        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    });
    const dailyCanvas = document.getElementById('daily-orders-chart');
    if (dailyCanvas) {
        new Chart(dailyCanvas, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Orders',
                    data: daily.map(r => r.count),
                    borderColor: '#65a30d',
                    backgroundColor: 'rgba(132, 204, 22, 0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#307233',
                    pointBorderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(120, 113, 108, 0.15)' } },
                },
            },
        });
    }

    // --- Orders by status (doughnut) ---
    const byStatus = @json($ordersByStatus);
    const statusLabels = {
        placed: 'Placed', accepted: 'Accepted', ready_for_pickup: 'Ready for Pickup',
        completed: 'Completed', cancelled: 'Cancelled', declined: 'Declined',
    };
    const statusKeys = Object.keys(byStatus);
    const statusCanvas = document.getElementById('orders-status-chart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusKeys.map(s => statusLabels[s] || s),
                datasets: [{
                    data: statusKeys.map(s => byStatus[s]),
                    backgroundColor: statusKeys.map((_, i) => palette[i % palette.length]),
                    borderWidth: 2,
                    borderColor: '#fdfcf9',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } },
            },
        });
    }
});
</script>
@endpush
