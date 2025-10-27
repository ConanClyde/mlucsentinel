<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset code.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old codes for this email
        PasswordResetCode::where('email', $request->email)->delete();

        // Create new reset code
        PasswordResetCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        // Send email
        try {
            Mail::to($request->email)->send(new PasswordResetMail($code));
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error("Failed to send password reset email to {$request->email}: ".$e->getMessage());

            // For development/testing, you can also log the code
            \Log::info("Password reset code for {$request->email}: {$code}");
        }

        // Redirect to reset password form with email parameter
        return redirect()->route('password.reset', ['email' => $request->email])
            ->with('status', 'Password reset code has been sent to your email!');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email,
        ]);
    }

    /**
     * Validate reset code via AJAX.
     */
    public function validateResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        // Find the reset code
        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($resetCode) {
            return response()->json([
                'valid' => true,
                'message' => 'Code is valid!',
            ]);
        } else {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired reset code.',
            ]);
        }
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Find the reset code
        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetCode) {
            return back()->withErrors([
                'code' => 'Invalid or expired reset code. Please request a new code.',
            ])->withInput($request->only('email'));
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Mark code as used
        $resetCode->is_used = true;
        $resetCode->save();

        // Delete all reset codes for this email to prevent reuse
        PasswordResetCode::where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('status', 'Password has been reset successfully! You can now log in with your new password.');
    }
}
