<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,suspended,pending'],
        ]);

        $customers = User::where('role', 'customer')
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where(fn($qq) => $qq->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))
            ->when(($filters['status'] ?? 'all') !== 'all', fn($q) => $q->where('status', $filters['status']))
            ->withCount('orders')
            ->latest()
            ->paginate(12)
            ->appends($filters);

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => ['q' => $filters['q'] ?? null, 'status' => $filters['status'] ?? 'all'],
            'counts' => [
                'all' => User::where('role', 'customer')->count(),
                'active' => User::where('role', 'customer')->where('status', 'active')->count(),
                'suspended' => User::where('role', 'customer')->where('status', 'suspended')->count(),
            ],
        ]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);
        $orders = $customer->orders()->with(['farmer', 'items'])->latest('placed_at')->paginate(10);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
            'stats' => [
                'orders' => $customer->orders()->count(),
                'completed' => $customer->orders()->where('status', 'completed')->count(),
                'spent' => $customer->orders()->where('status', 'completed')->sum('total_amount'),
                'favorites' => $customer->favorites()->count(),
                'reviews' => $customer->reviews()->count(),
            ],
        ]);
    }

    public function toggleStatus(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        $new = $customer->isActive() ? 'suspended' : 'active';
        $customer->update(['status' => $new]);

        return back()->with('success', $customer->name . ' is now ' . $new . '.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        $customer->delete();

        return back()->with('success', 'Customer account removed.');
    }
}
