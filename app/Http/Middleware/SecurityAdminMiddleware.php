<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityAdminMiddleware
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

        // Global administrators can access everything
        if ($user->isGlobalAdministrator()) {
            return $next($request);
        }

        // Check if user is a Security Admin
        if ($user->user_type === \App\Enums\UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
            if ($adminRole === 'Security') {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Security Administrator access required.');
    }
}
