<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmerApproved
{
    /**
     * Farmers must be approved by an admin before they can list products or receive orders.
     * Pending farmers may only access the pending-approval notice, profile setup and logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isFarmer() && $user->status === 'pending') {
            $allowed = [
                'farmer.pending',
                'farmer.profile.edit',
                'farmer.profile.update',
                'logout',
            ];

            if (! in_array($request->route()?->getName(), $allowed)) {
                return redirect()->route('farmer.pending');
            }
        }

        return $next($request);
    }
}
