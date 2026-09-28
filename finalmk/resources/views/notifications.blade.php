@extends(dashboard_layout())

@section('title', 'Notifications')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">Notifications</h1>
                <p class="mt-1.5 text-stone-500 dark:text-stone-400">Order updates, approvals and announcements.</p>
            </div>
            @if($notifications->whereNull('read_at')->count())
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="btn-secondary btn-sm">Mark all as read</button>
                </form>
            @endif
        </div>

        @if($notifications->count())
            <div class="mt-8 space-y-3">
                @foreach($notifications as $notification)
                    @php($data = $notification->data ?? [])
                    <div class="card flex items-start gap-4 p-4 {{ $notification->read_at ? 'opacity-70' : '' }}">
                        <span class="mt-0.5 rounded-xl {{ $notification->read_at ? 'bg-stone-100 text-stone-400 dark:bg-leaf-800' : 'bg-leaf-100 text-leaf-700 dark:bg-leaf-900 dark:text-leaf-300' }} p-2.5">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-leaf-950 dark:text-cream-50">{{ $data['title'] ?? 'Notification' }}</p>
                            <p class="mt-0.5 text-sm text-stone-600 dark:text-stone-300">{{ $data['message'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="btn-ghost btn-sm">Mark read</button>
                            </form>
                        @endunless
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $notifications->links() }}</div>
        @else
            <div class="card mt-8 flex flex-col items-center px-6 py-16 text-center">
                <svg class="h-12 w-12 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">All caught up</h2>
                <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">You have no notifications right now.</p>
            </div>
        @endif
    </div>
@endsection
