<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('otp.email');
        $purpose = $request->session()->get('otp.purpose');

        if (! $email || ! $purpose) {
            return redirect()->route('login');
        }

        $otp = Otp::active($email, $purpose);

        return view('auth.verify-otp', [
            'email' => $email,
            'purpose' => $purpose,
            'resendIn' => $otp?->secondsUntilResend() ?? 0,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $email = $request->session()->get('otp.email');
        $purpose = $request->session()->get('otp.purpose');

        if (! $email || ! $purpose) {
            return redirect()->route('login');
        }

        $otp = Otp::active($email, $purpose);

        if (! $otp || $otp->isExpired()) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.']);
        }

        if ($otp->isLockedOut()) {
            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new code.']);
        }

        if (! $otp->matches($request->input('code'))) {
            $otp->registerFailedAttempt();
            $left = $otp->attemptsLeft();

            return back()->withErrors(['code' => $left > 0
                ? "That code isn't right. You have {$left} " . ($left === 1 ? 'attempt' : 'attempts') . ' left.'
                : 'Too many incorrect attempts. Please request a new code.']);
        }

        $otp->consume();

        return $purpose === Otp::PURPOSE_PASSWORD_RESET
            ? $this->completePasswordReset($request, $email)
            : $this->completeRegistration($request, $email);
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('otp.email');
        $purpose = $request->session()->get('otp.purpose');

        if (! $email || ! $purpose) {
            return redirect()->route('login');
        }

        $otp = Otp::active($email, $purpose);
        $wait = ($otp && ! $otp->isExpired()) ? $otp->secondsUntilResend() : 0;

        if ($wait > 0) {
            return back()->withErrors([
                'code' => "Please wait {$wait} seconds before requesting a new code.",
            ]);
        }

        $sent = Otp::send($email, $purpose, $request->session()->get('otp.name'));

        return back()->with(
            $sent ? 'status' : 'error',
            $sent
                ? "A new 6-digit code is on its way to {$email}."
                : "We couldn't send the email right now. Please try again in a moment."
        );
    }

    private function completeRegistration(Request $request, string $email): RedirectResponse
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('otp');

            return redirect()->route('register')
                ->withErrors(['email' => 'We could not find that account. Please create your account again.']);
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $request->session()->forget('otp');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($user->dashboardRoute())
            ->with('success', $user->isFarmer()
                ? 'Email verified. Your farmer account will be visible once an admin approves it.'
                : 'Welcome to MarketLink, ' . $user->name . '! Your account is ready.');
    }

    private function completePasswordReset(Request $request, string $email): RedirectResponse
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('otp');

            return redirect()->route('password.request')
                ->withErrors(['email' => "We couldn't find an account with that email address."]);
        }

        if ($user->role === 'admin') {
            $request->session()->forget('otp');
            return redirect()->route('login')->withErrors(['email' => 'Admin password cannot be reset from here.']);
        }

        $request->session()->forget('otp');

        $token = Password::broker()->createToken($user);

        return redirect()
            ->route('password.reset', ['token' => $token, 'email' => $email])
            ->with('status', 'Code verified. Choose your new password below.');
    }
}
