<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $userType = auth()->user()->user_type;

        // Get the enum value (string) for comparison
        $userTypeValue = $userType->value;

        if (! in_array($userTypeValue, $allowedTypes)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
