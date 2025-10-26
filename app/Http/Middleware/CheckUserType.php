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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userType = auth()->user()->user_type;
        
        if (!in_array($userType, $allowedTypes)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}