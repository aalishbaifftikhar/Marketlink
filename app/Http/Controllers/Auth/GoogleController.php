<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * Existing accounts (matched by google_id or e-mail) are signed in directly.
     * New Google users are sent to a role-selection step to finish creating
     * their Customer or Farmer profile before entering the app.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::warning('Google OAuth callback failed', [
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user && $user->role === 'admin') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Admin must sign in with email and password.']);
        }

        if ($user) {
            // Link the Google account on first OAuth sign-in and refresh tokens.
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            $user->update([
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'avatar' => $user->avatar ?? $googleUser->getAvatar(),
            ]);

            if ($user->status === 'suspended') {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended($user->dashboardRoute())
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Brand new Google user — let them pick a role and finish registration.
        $request->session()->put('google_signup', [
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
            'token' => $googleUser->token,
            'refresh_token' => $googleUser->refreshToken,
        ]);

        return redirect()->route('google.complete');
    }

    /**
     * Show the "complete your account" form for first-time Google users.
     */
    public function showComplete(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('google_signup')) {
            return redirect()->route('login');
        }

        return view('auth.google-complete', ['google' => $request->session()->get('google_signup')]);
    }

    /**
     * Create the account for a first-time Google user with the chosen role.
     */
    public function complete(Request $request): RedirectResponse
    {
        $google = $request->session()->get('google_signup');

        if (! $google) {
            return redirect()->route('login');
        }

        if (strtolower((string) $google['email']) === User::ADMIN_EMAIL) {
            $request->session()->forget('google_signup');
            return redirect()->route('login')->withErrors(['email' => 'This email is reserved.']);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['customer', 'farmer'])],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'stall_name' => [Rule::requiredIf($request->role === 'farmer'), 'nullable', 'string', 'max:255'],
            'contact_person' => [Rule::requiredIf($request->role === 'farmer'), 'nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'phone.regex' => 'The phone number must be 10-15 digits, numbers only (no spaces or symbols).',
        ]);

        $user = DB::transaction(function () use ($google, $validated) {
            $user = User::create([
                'name' => $google['name'],
                'email' => $google['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : null,
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'avatar' => $google['avatar'] ?? null,
                'google_id' => $google['google_id'],
                'google_token' => $google['token'] ?? null,
                'google_refresh_token' => $google['refresh_token'] ?? null,
                'status' => $validated['role'] === 'farmer' ? 'pending' : 'active',
            ]);

            if ($user->isFarmer()) {
                $user->farmer()->create([
                    'stall_name' => $validated['stall_name'],
                    'contact_person' => $validated['contact_person'] ?? $google['name'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);
            }

            return $user;
        });

        $request->session()->forget('google_signup');

        $sent = Otp::send($user->email, Otp::PURPOSE_REGISTRATION, $user->name);

        $request->session()->put('otp', [
            'email' => $user->email,
            'purpose' => Otp::PURPOSE_REGISTRATION,
            'name' => $user->name,
        ]);

        return redirect()->route('otp.show')->with(
            $sent ? 'status' : 'error',
            $sent
                ? 'Almost there! Enter the 6-digit code we emailed to ' . $user->email . '.'
                : "Your account was created, but we couldn't send the verification email. Use the resend option to try again."
        );
    }
}
