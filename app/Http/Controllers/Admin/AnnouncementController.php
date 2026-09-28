<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::latest()->paginate(12);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'in:all,farmers,customers'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Announcement::create($data + [
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return back()->with('success', 'Announcement ' . ($data['is_published'] ?? false ? 'published' : 'saved as draft') . '.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'in:all,farmers,customers'],
        ]);
        $announcement->update($data);

        return back()->with('success', 'Announcement updated.');
    }

    public function toggle(Announcement $announcement): RedirectResponse
    {
        $publish = !$announcement->is_published;
        $announcement->update([
            'is_published' => $publish,
            'published_at' => $publish ? ($announcement->published_at ?? now()) : $announcement->published_at,
        ]);

        return back()->with('success', $publish ? 'Announcement published.' : 'Announcement unpublished.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
