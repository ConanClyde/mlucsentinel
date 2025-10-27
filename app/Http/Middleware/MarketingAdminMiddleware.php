<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarketingAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user is a Marketing admin
        if (! $user->isMarketingAdmin()) {
            abort(403, 'Access denied. Marketing admin access required.');
        }

        return $next($request);
    }
}
