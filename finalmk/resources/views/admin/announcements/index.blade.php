@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
@php
    $audienceBadge = fn (?string $audience) => match ($audience) {
        'farmers' => 'badge-green',
        'customers' => 'badge-amber',
        default => 'badge-blue',
    };
    $audienceLabel = fn (?string $audience) => match ($audience) {
        'farmers' => 'Farmers',
        'customers' => 'Customers',
        default => 'Everyone',
    };
@endphp

<div class="flex flex-col gap-6">
    <div>
        <h2 class="font-display text-xl font-bold text-stone-900 dark:text-cream-50">Announcements</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Publish updates for everyone, or target farmers and customers specifically.</p>
    </div>

    <div class="card p-5">
        <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">New announcement</h3>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="mt-4 flex flex-col gap-4">
            @csrf
            <div>
                <label for="title" class="input-label">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="input" required maxlength="255">
                @error('title')
                    <p class="input-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="body" class="input-label">Message</label>
                <textarea id="body" name="body" rows="4" class="input" required>{{ old('body') }}</textarea>
                @error('body')
                    <p class="input-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="sm:w-64">
                    <label for="audience" class="input-label">Audience</label>
                    <select id="audience" name="audience" class="input">
                        <option value="all" @selected(old('audience') === 'all')>Everyone</option>
                        <option value="farmers" @selected(old('audience') === 'farmers')>Farmers</option>
                        <option value="customers" @selected(old('audience') === 'customers')>Customers</option>
                    </select>
                    @error('audience')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-sm font-medium text-stone-600 dark:text-stone-300">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
                    Publish immediately
                </label>
            </div>
            <button type="submit" class="btn btn-primary w-fit">Post Announcement</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-stone-100 px-5 py-4 dark:border-leaf-800/60">
            <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">All announcements</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 dark:divide-leaf-800/60">
                <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-400 dark:bg-leaf-900/40 dark:text-stone-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Title</th>
                        <th class="px-5 py-3 text-left">Audience</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Created</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                @forelse ($announcements as $announcement)
                    <tbody x-data="{ editing: false }" class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-stone-900 dark:text-cream-50">{{ $announcement->title }}</p>
                                <p class="mt-0.5 max-w-md truncate text-sm text-stone-500 dark:text-stone-400">{{ $announcement->body }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge {{ $audienceBadge($announcement->audience) }}">{{ $audienceLabel($announcement->audience) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($announcement->is_published)
                                    <span class="badge badge-green">Published</span>
                                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $announcement->published_at?->format('M j, Y') }}</p>
                                @else
                                    <span class="badge badge-stone">Draft</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $announcement->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editing = true" class="btn btn-ghost btn-sm">Edit</button>
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="editing" x-cloak style="display: none;">
                            <td colspan="5" class="bg-stone-50 px-5 py-4 dark:bg-leaf-900/40">
                                <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="flex flex-col gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="input-label">Title</label>
                                        <input type="text" name="title" value="{{ old('title', $announcement->title) }}" class="input" required maxlength="255">
                                    </div>
                                    <div>
                                        <label class="input-label">Message</label>
                                        <textarea name="body" rows="3" class="input" required>{{ old('body', $announcement->body) }}</textarea>
                                    </div>
                                    <div class="sm:w-64">
                                        <label class="input-label">Audience</label>
                                        <select name="audience" class="input">
                                            <option value="all" @selected(old('audience', $announcement->audience) === 'all')>Everyone</option>
                                            <option value="farmers" @selected(old('audience', $announcement->audience) === 'farmers')>Farmers</option>
                                            <option value="customers" @selected(old('audience', $announcement->audience) === 'customers')>Customers</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                        <button type="button" @click="editing = false" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-stone-400 dark:text-stone-500">No announcements yet.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
        @if ($announcements->hasPages())
            <div class="border-t border-stone-100 px-5 py-4 dark:border-leaf-800/60">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
