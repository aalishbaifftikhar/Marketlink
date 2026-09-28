<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications', [
            'notifications' => $request->user()->notifications()->paginate(15),
        ]);
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $data = $notification->data;

        if (($data['order_id'] ?? null) && $request->user()->isCustomer()) {
            return redirect()->route('customer.orders.show', $data['order_id']);
        }

        if (($data['order_id'] ?? null) && $request->user()->isFarmer()) {
            return redirect()->route('farmer.orders.show', $data['order_id']);
        }

        if ($data['url'] ?? null) {
            return redirect($data['url']);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
