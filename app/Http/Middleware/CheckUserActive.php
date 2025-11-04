<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // If user is inactive, logout and redirect
            if (! $user->is_active) {
                // Log the forced logout
                \Log::channel('security')->warning('Inactive user attempted to access system', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_type' => $user->user_type->value,
                    'ip_address' => $request->ip(),
                    'requested_url' => $request->fullUrl(),
                ]);

                // Logout the user
                Auth::logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with error message
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact an administrator.']);
            }
        }

        return $next($request);
    }
}
