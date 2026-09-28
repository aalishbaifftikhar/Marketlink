<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => "We couldn't find an account with that email address."]);
        }

        if ($user->role === 'admin') {
            return back()->withErrors(['email' => 'Admin password cannot be reset from here.']);
        }

        $sent = Otp::send($user->email, Otp::PURPOSE_PASSWORD_RESET, $user->name);

        if (! $sent) {
            return back()->withErrors(['email' => "We couldn't send the code right now. Please try again in a moment."]);
        }

        $request->session()->put('otp', [
            'email' => $user->email,
            'purpose' => Otp::PURPOSE_PASSWORD_RESET,
            'name' => $user->name,
        ]);

        return redirect()->route('otp.show')
            ->with('status', 'We emailed a 6-digit code to ' . $user->email . '.');
    }
}
