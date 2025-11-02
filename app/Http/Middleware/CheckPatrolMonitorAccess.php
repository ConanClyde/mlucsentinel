<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPatrolMonitorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is Global Administrator
        if ($user->user_type === UserType::GlobalAdministrator) {
            return $next($request);
        }

        // Check if user is Administrator with Security role
        if ($user->user_type === UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
            if ($adminRole === 'Security') {
                return $next($request);
            }
        }

        // User doesn't have access
        abort(403, 'You do not have permission to access patrol monitoring.');
    }
}
