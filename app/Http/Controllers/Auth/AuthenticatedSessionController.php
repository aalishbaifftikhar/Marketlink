<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        if (! $user->email_verified_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $sent = Otp::send($user->email, Otp::PURPOSE_REGISTRATION, $user->name);

            $request->session()->put('otp', [
                'email' => $user->email,
                'purpose' => Otp::PURPOSE_REGISTRATION,
                'name' => $user->name,
            ]);

            return redirect()->route('otp.show')->with(
                $sent ? 'status' : 'error',
                $sent
                    ? 'Please verify your email first. We sent a fresh 6-digit code to ' . $user->email . '.'
                    : "Please verify your email first, but we couldn't send the code. Use the resend option to try again."
            );
        }

        return redirect()->intended($user->dashboardRoute())
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been signed out.');
    }
}
