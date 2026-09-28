<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerPickupSlotController extends Controller
{
    public function index(Request $request): View
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);

        $slots = $farmers->pickupSlots()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('farmer.pickup-slots.index', [
            'farmer' => $farmers,
            'slotsByDay' => $slots,
            'days' => [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);

        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        abort_if(
            $farmers->pickupSlots()->where('day_of_week', $data['day_of_week'])->where('start_time', $data['start_time'])->where('end_time', $data['end_time'])->exists(),
            422,
            'That pickup window already exists.'
        );

        $farmers->pickupSlots()->create($data + ['is_active' => true]);

        return back()->with('success', 'Pickup window added.');
    }

    public function destroy(Request $request, int $slot): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $farmers->pickupSlots()->where('id', $slot)->delete();

        return back()->with('success', 'Pickup window removed.');
    }

    public function toggle(Request $request, int $slot): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $pickupSlot = $farmers->pickupSlots()->where('id', $slot)->firstOrFail();
        $pickupSlot->update(['is_active' => !$pickupSlot->is_active]);

        return back()->with('success', 'Pickup window ' . ($pickupSlot->is_active ? 'enabled' : 'disabled') . '.');
    }
}
