<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivilegeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $privilege): Response
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('login');
        }

        // Global administrators bypass all privilege checks
        if ($user->isGlobalAdministrator()) {
            return $next($request);
        }

        // Check if user is an administrator with the required privilege
        if ($user->user_type === UserType::Administrator && $user->administrator) {
            $role = $user->administrator->adminRole;

            // Check if role exists and is active
            if ($role && $role->is_active) {
                // Check if role has the required privilege
                if ($role->hasPrivilege($privilege)) {
                    return $next($request);
                }
            }
        }

        // User doesn't have the required privilege
        abort(403, 'You do not have the required privilege to access this resource.');
    }
}
