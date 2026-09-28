<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['customer', 'farmer'])],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'stall_name' => [Rule::requiredIf($request->role === 'farmer'), 'nullable', 'string', 'max:255'],
            'contact_person' => [Rule::requiredIf($request->role === 'farmer'), 'nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules, [
            'phone.regex' => 'The phone number must be 10-15 digits, numbers only (no spaces or symbols).',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['role'] === 'farmer' ? 'pending' : 'active',
            ]);

            if ($user->isFarmer()) {
                $user->farmer()->create([
                    'stall_name' => $validated['stall_name'],
                    'contact_person' => $validated['contact_person'] ?? $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

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
