<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeUrl(User $user, string $secret): string
    {
        $appName = 'MLUC Sentinel';
        $email = $user->email;

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );

        // Generate SVG QR code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        // Return as data URI
        return 'data:image/svg+xml;base64,'.base64_encode($qrCodeSvg);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        // Allow 2 windows before and after (2 minutes tolerance for time sync issues)
        return $this->google2fa->verifyKey($secret, $code, 2);
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10).'-'.Str::random(10);
        }

        return $codes;
    }

    public function enable2FA(User $user, string $secret, array $recoveryCodes): void
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disable2FA(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function getRecoveryCodes(User $user): array
    {
        if (! $user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true);
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $codes = $this->getRecoveryCodes($user);

        if (($key = array_search($code, $codes)) !== false) {
            unset($codes[$key]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
            ]);

            return true;
        }

        return false;
    }
}
