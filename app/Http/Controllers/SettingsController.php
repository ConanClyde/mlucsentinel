<?php

namespace App\Http\Controllers;

use App\Enums\StickerColor;
use App\Events\StickerPaletteUpdated;
use App\Events\StickerRulesUpdated;
use App\Models\StakeholderType;
use App\Models\StickerRule;
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
     * Get sticker configuration (Global Admin only)
     */
    public function getStickerConfig(): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $defaultPalette = collect(StickerColor::cases())->mapWithKeys(fn ($c) => [$c->value => $c->hex()])->toArray();

        $rules = StickerRule::getSingleton();
        $palette = $rules->palette && is_array($rules->palette) && ! empty($rules->palette)
            ? $rules->palette
            : $defaultPalette;

        // Build UI color options from current palette
        $colors = collect($palette)
            ->map(fn ($hex, $key) => [
                'value' => $key,
                'label' => ucwords(str_replace(['_', '-'], ' ', $key)),
                'hex' => $hex,
            ])->values();

        $stakeholderTypes = StakeholderType::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'config' => [
                'student_expiration_years' => $rules->student_expiration_years,
                'staff_expiration_years' => $rules->staff_expiration_years,
                'security_expiration_years' => $rules->security_expiration_years,
                'stakeholder_expiration_years' => $rules->stakeholder_expiration_years,
                'staff_color' => $rules->staff_color,
                'security_color' => $rules->security_color,
                'student_map' => $rules->student_map ?? [
                    '12' => 'blue', '34' => 'green', '56' => 'yellow', '78' => 'pink', '90' => 'orange', 'no_plate' => 'white',
                ],
                'stakeholder_map' => $rules->stakeholder_map ?? [
                    'Guardian' => 'white', 'Service Provider' => 'white', 'Visitor' => 'black',
                ],
                'palette' => $palette,
            ],
            'colors' => $colors,
            'stakeholder_types' => $stakeholderTypes,
        ]);
    }

    /**
     * Update sticker configuration (Global Admin only)
     */
    public function updateStickerConfig(Request $request): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $rules = StickerRule::getSingleton();
        $palette = $rules->palette ?? collect(StickerColor::cases())->mapWithKeys(fn ($c) => [$c->value => $c->hex()])->toArray();
        $paletteKeys = array_keys($palette);

        $validated = $request->validate([
            'student_expiration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'staff_expiration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'security_expiration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'stakeholder_expiration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'staff_color' => ['required'],
            'security_color' => ['required'],
            'student_map' => ['required', 'array'],
            'student_map.12' => ['required'],
            'student_map.34' => ['required'],
            'student_map.56' => ['required'],
            'student_map.78' => ['required'],
            'student_map.90' => ['required'],
            'student_map.no_plate' => ['required'],
            'stakeholder_map' => ['required', 'array'],
        ]);

        // Validate provided color keys exist in palette
        $colorKeysToValidate = [
            $validated['staff_color'],
            $validated['security_color'],
            $validated['student_map']['12'],
            $validated['student_map']['34'],
            $validated['student_map']['56'],
            $validated['student_map']['78'],
            $validated['student_map']['90'],
            $validated['student_map']['no_plate'],
        ];
        foreach ($colorKeysToValidate as $key) {
            if (! in_array($key, $paletteKeys, true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid color key '{$key}'. Please select a defined palette color.",
                ], 422);
            }
        }

        // Normalize stakeholder_map keys (ids) to names and validate color keys
        $map = [];
        foreach (($request->input('stakeholder_map') ?? []) as $typeId => $colorKey) {
            $type = StakeholderType::find((int) $typeId);
            if ($type && in_array($colorKey, $paletteKeys, true)) {
                $map[$type->name] = $colorKey;
            }
        }

        $rules->student_expiration_years = (int) $validated['student_expiration_years'];
        $rules->staff_expiration_years = (int) $validated['staff_expiration_years'];
        $rules->security_expiration_years = (int) $validated['security_expiration_years'];
        $rules->stakeholder_expiration_years = (int) $validated['stakeholder_expiration_years'];
        $rules->staff_color = $validated['staff_color'];
        $rules->security_color = $validated['security_color'];
        $rules->student_map = $validated['student_map'];
        $rules->stakeholder_map = $map;
        $rules->palette = $palette; // unchanged here
        $rules->save();

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StickerRulesUpdated('updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Sticker configuration updated',
            'config' => [
                'student_expiration_years' => $rules->student_expiration_years,
                'staff_expiration_years' => $rules->staff_expiration_years,
                'security_expiration_years' => $rules->security_expiration_years,
                'stakeholder_expiration_years' => $rules->stakeholder_expiration_years,
                'staff_color' => $rules->staff_color,
                'security_color' => $rules->security_color,
                'student_map' => $rules->student_map,
                'stakeholder_map' => $rules->stakeholder_map,
                'palette' => $rules->palette,
            ],
        ]);
    }

    /**
     * Get current sticker color palette
     */
    public function getStickerPalette(): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $rules = StickerRule::getSingleton();
        $palette = $rules->palette ?? collect(StickerColor::cases())->mapWithKeys(fn ($c) => [$c->value => $c->hex()])->toArray();

        $items = collect($palette)->map(fn ($hex, $key) => [
            'key' => $key,
            'name' => ucwords(str_replace(['_', '-'], ' ', $key)),
            'hex' => $hex,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Add a new color to sticker palette
     */
    public function addStickerColor(Request $request): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'hex' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $rules = StickerRule::getSingleton();
        $rules->palette = $rules->palette ?? [];

        $key = $this->normalizeColorKey($validated['name']);
        if (isset($rules->palette[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Color name already exists in palette',
            ], 422);
        }

        $p = $rules->palette;
        $p[$key] = strtoupper($validated['hex']);
        $rules->palette = $p;
        $rules->save();

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StickerPaletteUpdated('created', ['key' => $key, 'name' => $validated['name'], 'hex' => strtoupper($validated['hex'])], $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Color added',
            'data' => ['key' => $key, 'name' => $validated['name'], 'hex' => strtoupper($validated['hex'])],
        ]);
    }

    /**
     * Update an existing palette color (and references)
     */
    public function updateStickerColor(Request $request, string $key): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'hex' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $rules = StickerRule::getSingleton();
        $palette = $rules->palette ?? [];

        if (! isset($palette[$key])) {
            return response()->json(['success' => false, 'message' => 'Color not found'], 404);
        }

        $newKey = $this->normalizeColorKey($validated['name']);
        if ($newKey !== $key && isset($rules->palette[$newKey])) {
            return response()->json(['success' => false, 'message' => 'Another color with this name already exists'], 422);
        }

        // Update palette
        $p = $rules->palette;
        unset($p[$key]);
        $p[$newKey] = strtoupper($validated['hex']);

        // Update references in config
        if (($rules->staff_color ?? null) === $key) {
            $rules->staff_color = $newKey;
        }
        if (($rules->security_color ?? null) === $key) {
            $rules->security_color = $newKey;
        }
        $sm = $rules->student_map ?? [];
        foreach (['12', '34', '56', '78', '90', 'no_plate'] as $d) {
            if (($sm[$d] ?? null) === $key) {
                $sm[$d] = $newKey;
            }
        }
        $rules->student_map = $sm;
        $shm = $rules->stakeholder_map ?? [];
        foreach ($shm as $typeName => $c) {
            if ($c === $key) {
                $shm[$typeName] = $newKey;
            }
        }
        $rules->stakeholder_map = $shm;

        // Update existing vehicles that use the old color key
        \App\Models\Vehicle::where('color', $key)->update(['color' => $newKey]);

        $rules->palette = $p;
        $rules->save();

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StickerPaletteUpdated('updated', ['key' => $newKey, 'oldKey' => $key, 'name' => $validated['name'], 'hex' => strtoupper($validated['hex'])], $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Color updated',
            'data' => ['key' => $newKey, 'name' => $validated['name'], 'hex' => strtoupper($validated['hex'])],
        ]);
    }

    /**
     * Delete a palette color if not in use
     */
    public function deleteStickerColor(string $key): JsonResponse
    {
        if (! auth()->user()->isGlobalAdministrator()) {
            abort(403);
        }

        $rules = StickerRule::getSingleton();
        $palette = $rules->palette ?? [];
        if (! isset($palette[$key])) {
            return response()->json(['success' => false, 'message' => 'Color not found'], 404);
        }

        // Prevent deleting colors in active use
        if (($rules->staff_color ?? null) === $key || ($rules->security_color ?? null) === $key) {
            return response()->json(['success' => false, 'message' => 'Color is used by staff/security mapping'], 422);
        }
        $sm = $rules->student_map ?? [];
        foreach (['12', '34', '56', '78', '90', 'no_plate'] as $d) {
            if (($sm[$d] ?? null) === $key) {
                return response()->json(['success' => false, 'message' => 'Color is used by student mapping'], 422);
            }
        }
        $shm = $rules->stakeholder_map ?? [];
        if (in_array($key, $shm, true)) {
            return response()->json(['success' => false, 'message' => 'Color is used by stakeholder mapping'], 422);
        }

        // Prevent deleting if vehicles currently use it
        if (\App\Models\Vehicle::where('color', $key)->exists()) {
            return response()->json(['success' => false, 'message' => 'Color is used by existing vehicles'], 422);
        }

        unset($palette[$key]);
        $rules->palette = $palette;
        $rules->save();

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StickerPaletteUpdated('deleted', ['key' => $key], $editor))->toOthers();

        return response()->json(['success' => true, 'message' => 'Color deleted']);
    }

    private function normalizeColorKey(string $name): string
    {
        $key = strtolower(trim($name));
        $key = preg_replace('/[^a-z0-9\s_-]+/i', '', $key);
        $key = preg_replace('/[\s-]+/', '_', $key);

        return $key ?: 'color_'.substr(md5($name), 0, 6);
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
