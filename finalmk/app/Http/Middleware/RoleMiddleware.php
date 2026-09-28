<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Ensure the authenticated user has one of the given roles.
     * Usage: ->middleware('role:admin') or ->middleware('role:farmer,customer')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles)) {
            abort(403, 'You are not authorized to access this page.');
        }

        // Admin area: only the single fixed admin account, never anyone else.
        if (in_array('admin', $roles, true) && ! $user->isAdmin()) {
            abort(403, 'You are not authorized to access this page.');
        }

        if ($user->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
