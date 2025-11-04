<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $fees = \App\Models\Fee::orderBy('display_name')->get();
        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        $colleges = \App\Models\College::orderBy('name')->get();
        $locationTypes = \App\Models\MapLocationType::orderBy('name')->get();
        $programs = \App\Models\Program::with('college')->orderBy('name')->get();

        return view('settings', [
            'pageTitle' => 'Settings',
            'twoFactorEnabled' => $user->two_factor_enabled,
            'fees' => $fees,
            'vehicleTypes' => $vehicleTypes,
            'colleges' => $colleges,
            'locationTypes' => $locationTypes,
            'programs' => $programs,
        ]);
    }

    /**
     * Get user's activity logs
     */
    public function getActivityLogs(): JsonResponse
    {
        $activities = ActivityLogService::getRecentActivity(Auth::id(), 20);

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    /**
     * Enable 2FA - Step 1: Generate QR Code
     */
    public function enable2FA(): JsonResponse
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => '2FA is already enabled',
            ], 400);
        }

        $secret = $this->twoFactorService->generateSecret();
        $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user, $secret);

        // Store secret in session temporarily until confirmed
        session(['2fa_secret' => $secret]);

        return response()->json([
            'success' => true,
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Enable 2FA - Step 2: Verify and Confirm
     */
    public function confirm2FA(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $secret = session('2fa_secret');

        if (! $secret) {
            return response()->json([
                'success' => false,
                'message' => 'No 2FA setup in progress',
            ], 400);
        }

        if (! $this->twoFactorService->verifyCode($secret, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
            ], 400);
        }

        // Generate recovery codes
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        // Enable 2FA
        $this->twoFactorService->enable2FA(Auth::user(), $secret, $recoveryCodes);

        // Clear session
        session()->forget('2fa_secret');

        // Log activity
        ActivityLogService::log(Auth::id(), '2fa_enabled');

        return response()->json([
            'success' => true,
            'message' => '2FA enabled successfully',
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable2FA(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 400);
        }

        $this->twoFactorService->disable2FA($user);

        // Log activity
        ActivityLogService::log(Auth::id(), '2fa_disabled');

        return response()->json([
            'success' => true,
            'message' => '2FA disabled successfully',
        ]);
    }

    /**
     * Get recovery codes
     */
    public function getRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 400);
        }

        $codes = $this->twoFactorService->getRecoveryCodes($user);

        return response()->json([
            'success' => true,
            'recoveryCodes' => $codes,
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 400);
        }

        $newCodes = $this->twoFactorService->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($newCodes)),
        ]);

        // Log activity
        ActivityLogService::log(Auth::id(), '2fa_recovery_codes_regenerated');

        return response()->json([
            'success' => true,
            'message' => 'Recovery codes regenerated successfully',
            'recoveryCodes' => $newCodes,
        ]);
    }
}
