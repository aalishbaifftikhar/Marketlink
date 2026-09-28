@extends('layouts.farmer')

@section('title', 'Insights')

@section('content')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('farmer.insights') }}" class="{{ request()->routeIs('farmer.entries.*') ? 'btn-ghost btn-sm' : 'btn-primary btn-sm' }}">Sales overview</a>
    <a href="{{ route('farmer.entries.index') }}" class="{{ request()->routeIs('farmer.entries.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Profit &amp; Loss</a>
</div>

@php
    $statCards = [
        ['label' => 'Total orders', 'value' => number_format($stats['total_orders'])],
        ['label' => 'Completed orders', 'value' => number_format($stats['completed_orders'])],
        ['label' => 'Total revenue', 'value' => '$' . number_format($stats['total_revenue'], 2)],
        ['label' => 'Average order', 'value' => '$' . number_format($stats['avg_order'], 2)],
    ];

    $statusColors = [
        'placed' => '#d97706',
        'accepted' => '#65a30d',
        'ready_for_pickup' => '#84cc16',
        'completed' => '#307233',
        'cancelled' => '#a8a29e',
        'declined' => '#b91c1c',
    ];
    $doughnutLabels = [];
    $doughnutData = [];
    $doughnutPalette = [];
    foreach ($statusColors as $status => $color) {
        $count = (int) ($byStatus[$status] ?? 0);
        if ($count <= 0) continue;
        $doughnutLabels[] = ucwords(str_replace('_', ' ', $status));
        $doughnutData[] = $count;
        $doughnutPalette[] = $color;
    }

    $maxMarketRevenue = $revenueByMarket->max() ?: 1;
@endphp

<p class="text-sm text-stone-500 dark:text-stone-400">How your stall is performing. Figures include accepted and completed sales from the last 30 days.</p>

{{-- Stat cards --}}
<div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach ($statCards as $card)
        <div class="card card-hover p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">{{ $card['label'] }}</p>
            <p class="mt-3 font-display text-2xl font-semibold text-stone-800 dark:text-stone-100 sm:text-3xl">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card p-5 xl:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Daily revenue — last 30 days</h2>
            <span class="badge-green">Live</span>
        </div>
        <div class="mt-4 h-80"><canvas id="salesChart"></canvas></div>
    </div>

    <div class="card p-5">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Orders by status</h2>
        <div class="mt-4 h-80"><canvas id="statusChart"></canvas></div>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    {{-- Top products --}}
    <div class="card xl:col-span-2">
        <div class="border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Top products</h2>
        </div>
        @if ($topProducts->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[30rem] text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide text-stone-400 dark:text-stone-500">
                            <th class="px-5 py-3 font-semibold">Product</th>
                            <th class="px-3 py-3 font-semibold">Units sold</th>
                            <th class="px-5 py-3 text-right font-semibold">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                        @foreach ($topProducts as $product)
                            <tr class="transition-colors hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg border border-stone-200 object-cover dark:border-leaf-700">
                                        <span class="font-semibold text-stone-800 dark:text-stone-100">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-stone-600 dark:text-stone-300">{{ number_format($product->sold_units ?? 0) }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($product->revenue ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-5 py-10 text-center text-sm text-stone-500 dark:text-stone-400">No sales recorded yet.</p>
        @endif
    </div>

    {{-- Revenue by market --}}
    <div class="card p-5">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Revenue by market</h2>
        <div class="mt-4 space-y-4">
            @forelse ($revenueByMarket as $name => $amount)
                <div>
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <span class="truncate font-semibold text-stone-800 dark:text-stone-100">{{ $name }}</span>
                        <span class="font-semibold text-leaf-700 dark:text-leaf-300">${{ number_format($amount, 2) }}</span>
                    </div>
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-stone-100 dark:bg-leaf-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-leaf-500 to-leaf-400" style="width: {{ max(3, round($amount / $maxMarketRevenue * 100)) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-stone-500 dark:text-stone-400">Revenue will be split by market once orders come in.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    const salesSeries = @json($salesSeries->values());
    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas) {
        new Chart(salesCanvas, {
            type: 'line',
            data: {
                labels: salesSeries.map((d) => d.day),
                datasets: [{
                    label: 'Revenue',
                    data: salesSeries.map((d) => d.revenue),
                    borderColor: '#65a30d',
                    backgroundColor: 'rgba(101, 163, 13, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHitRadius: 12,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#65a30d',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' $' + Number(ctx.parsed.y).toFixed(2),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#a8a29e', maxTicksLimit: 10 },
                        border: { color: 'rgba(168, 162, 158, 0.25)' },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(168, 162, 158, 0.15)' },
                        ticks: { color: '#a8a29e', callback: (v) => '$' + v },
                        border: { display: false },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($doughnutLabels),
                datasets: [{
                    data: @json($doughnutData),
                    backgroundColor: @json($doughnutPalette),
                    borderWidth: 2,
                    borderColor: 'rgba(255, 255, 255, 0.6)',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#a8a29e', usePointStyle: true, boxWidth: 8, padding: 16 } },
                },
            },
        });
    }
});
</script>
@endpush
