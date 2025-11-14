<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show 2FA verification page
     */
    public function show()
    {
        if (! session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa:user:id');

        if (! $userId) {
            return back()->withErrors(['code' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);

        if (! $user || ! $user->two_factor_enabled) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid session.']);
        }

        // Check if user is active
        if (! $user->is_active) {
            // Log inactive user 2FA attempt
            \Log::channel('security')->warning('Inactive user attempted 2FA verification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Clear 2FA session
            $request->session()->forget(['2fa:user:id', '2fa:remember']);

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        $secret = decrypt($user->two_factor_secret);
        $code = $request->code;

        // Check if it's a recovery code
        if (strlen($code) > 6) {
            if ($this->twoFactorService->useRecoveryCode($user, $code)) {
                return $this->loginUser($user, $request);
            }

            return back()->withErrors(['code' => 'Invalid recovery code.']);
        }

        // Verify 2FA code
        if ($this->twoFactorService->verifyCode($secret, $code)) {
            return $this->loginUser($user, $request);
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    /**
     * Login the user after successful 2FA verification
     */
    protected function loginUser(User $user, Request $request)
    {
        Auth::login($user, session('2fa:remember', false));

        $request->session()->regenerate();
        $request->session()->forget(['2fa:user:id', '2fa:remember']);

        // Log successful login
        \Log::channel('security')->info('Successful login with 2FA', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $user->user_type->value,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Log activity
        ActivityLogService::log($user->id, 'login');

        return redirect()->route('home');
    }
}
