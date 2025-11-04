<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the landing page.
     */
    public function landing()
    {
        // Redirect authenticated users to home
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.landing');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        // Redirect authenticated users to home
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (! $user->is_active) {
                // Log inactive user login attempt
                \Log::channel('security')->warning('Inactive user attempted login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_type' => $user->user_type->value,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Logout the user
                Auth::logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact an administrator.',
                ])->onlyInput('email');
            }

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID in session for 2FA verification
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:remember', $request->boolean('remember'));

                // Logout temporarily until 2FA is verified
                Auth::logout();

                // Redirect to 2FA verification page
                return redirect()->route('2fa.verify');
            }

            // Log successful login
            \Log::channel('security')->info('Successful login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log activity
            \App\Services\ActivityLogService::log($user->id, 'login');

            // Always redirect to home after login (ignore intended URL)
            return redirect()->route('home');
        }

        // Log failed login attempt
        \Log::channel('security')->warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log logout event
        if ($user) {
            \Log::channel('security')->info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'ip_address' => $request->ip(),
            ]);

            // Log activity
            \App\Services\ActivityLogService::log($user->id, 'logout');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('landing'))->with('success', 'You have been successfully logged out.');
    }
}
