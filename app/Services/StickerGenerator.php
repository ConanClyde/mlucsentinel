<?php

namespace App\Services;

use App\Models\StickerCounter;
use App\Models\StickerRule;
use App\Models\Vehicle;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class StickerGenerator
{
    /**
     * Generate a vehicle sticker with QR code
     */
    public function generateVehicleSticker(
        string $stickerNo,
        string $vehicleType,
        ?string $plateNumber,
        string $color,
        int $vehicleId
    ): string {
        try {
            return $this->generateSvgSticker($stickerNo, $color, $vehicleId);
        } catch (\Exception $e) {
            \Log::error('Sticker generation failed: '.$e->getMessage());
            throw new \Exception('Failed to generate QR code: '.$e->getMessage());
        }
    }

    /**
     * Generate SVG sticker with QR code
     */
    protected function generateSvgSticker(string $stickerNo, string $color, int $vehicleId): string
    {
        // Generate QR code
        $base = rtrim((string) (config('app.url') ?? ''), '/');
        $qrData = ($base !== '' ? $base : '').'/report-user/'.$vehicleId;

        $svgOptions = new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 8,
            'imageBase64' => false,
            'svgDefs' => '',
        ]);
        $qrSvg = (new QRCode($svgOptions))->render($qrData);

        // Constants
        $width = 600;
        $height = 900;
        $qrSize = 520;
        $qrPadding = 30;
        $radius = 40;
        $bg = $this->getHexColor($color);
        $textColor = ($color === 'white') ? '#000000' : '#FFFFFF';
        $innerSize = $qrSize - 2 * $qrPadding;

        // Calculate scale
        $scale = $this->calculateQrScale($qrSvg, $innerSize);

        // Extract display number
        $displayNo = $this->extractDisplayNumber($stickerNo);

        // Build SVG
        $svg = $this->buildSvgSticker($width, $height, $qrSize, $qrPadding, $radius, $bg, $textColor, $scale, $qrSvg, $displayNo, $color);

        // Generate filename and save
        $fileName = $this->generateFileName($stickerNo, $color, $vehicleId);

        // Store sticker
        Storage::disk('public')->put('stickers/'.$fileName, $svg);
        \Log::info("Sticker saved successfully: {$fileName}");

        return '/storage/stickers/'.$fileName;
    }

    /**
     * Calculate QR code scale to fit in container
     */
    protected function calculateQrScale(string $qrSvg, int $innerSize): float
    {
        $baseSize = 0;
        if (preg_match('/viewBox="\s*0\s+0\s+(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)\s*"/i', $qrSvg, $m)) {
            $baseSize = (float) min($m[1], $m[2]);
        } elseif (preg_match('/width="(\d+(?:\.\d+)?)"[^>]*height="(\d+(?:\.\d+)?)"/i', $qrSvg, $m)) {
            $baseSize = (float) min($m[1], $m[2]);
        }
        if ($baseSize <= 0) {
            $baseSize = 256.0;
        }

        return $innerSize / $baseSize;
    }

    /**
     * Extract display number from sticker number
     */
    protected function extractDisplayNumber(string $stickerNo): string
    {
        if (strpos($stickerNo, '-') !== false) {
            $parts = explode('-', $stickerNo);

            return end($parts) ?: $stickerNo;
        }

        return $stickerNo;
    }

    /**
     * Build the SVG sticker markup
     */
    protected function buildSvgSticker(
        int $width,
        int $height,
        int $qrSize,
        int $qrPadding,
        int $radius,
        string $bg,
        string $textColor,
        float $scale,
        string $qrSvg,
        string $displayNo,
        string $color
    ): string {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
               "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">".
               "<rect width=\"100%\" height=\"100%\" fill=\"{$bg}\"/>".
               '<rect x="'.(($width - $qrSize) / 2)."\" y=\"60\" width=\"{$qrSize}\" height=\"{$qrSize}\" rx=\"{$radius}\" ry=\"{$radius}\" fill=\"#FFFFFF\" ".($color === 'white' ? 'stroke="#000" stroke-width="6"' : '').'/>'.
               '<g transform="translate('.(($width - $qrSize) / 2 + $qrPadding).','.(60 + $qrPadding).") scale({$scale})\">".
                   preg_replace('/^.*?<svg[^>]*>|<\/svg>.*$/s', '', $qrSvg).
               '</g>'.
               '<text x="50%" y="'.(60 + $qrSize + 90)."\" fill=\"{$textColor}\" font-size=\"64\" font-family=\"Arial, sans-serif\" font-weight=\"700\" text-anchor=\"middle\">MLUC SENTINEL</text>".
               '<text x="50%" y="'.($height - 60)."\" fill=\"{$textColor}\" font-size=\"190\" font-family=\"Arial Black, Arial, sans-serif\" text-anchor=\"middle\">".htmlspecialchars($displayNo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</text>'.
               '</svg>';
    }

    /**
     * Generate filename for sticker
     */
    protected function generateFileName(string $stickerNo, string $color, int $vehicleId): string
    {
        $sanitizedNo = preg_replace('/^(black|white|green|blue|yellow|orange|pink|maroon)[-_]?/i', '', $stickerNo);
        $vehicle = Vehicle::with('user')->find($vehicleId);

        $first = $vehicle && $vehicle->user ? preg_replace('/[^A-Za-z0-9]+/', '', strtolower($vehicle->user->first_name ?? '')) : '';
        $last = $vehicle && $vehicle->user ? preg_replace('/[^A-Za-z0-9]+/', '', strtolower($vehicle->user->last_name ?? '')) : '';

        return strtolower($color).'_'.$sanitizedNo.'_'.$first.'_'.$last.'.svg';
    }

    /**
     * Convert named color to hex for Intervention background
     */
    protected function getHexColor(string $colorName): string
    {
        $rules = StickerRule::getSingleton();
        $palette = is_array($rules->palette) ? $rules->palette : [];
        if (isset($palette[$colorName])) {
            return $palette[$colorName];
        }

        // Fallback strictly from DB palette: use the first available palette color
        $first = reset($palette);
        if ($first !== false) {
            return $first;
        }

        throw new \RuntimeException('Sticker palette is empty. Configure colors in Sticker Rules.');
    }

    /**
     * Determine sticker color based on user type and plate number
     */
    public function determineStickerColor(string $userType, ?string $stakeholderType = null, ?string $plateNumber = null): string
    {
        $rules = StickerRule::getSingleton();

        if ($userType === 'security') {
            return $rules->security_color ?? $this->fallbackColorKey();
        }
        if ($userType === 'staff') {
            return $rules->staff_color ?? $this->fallbackColorKey();
        }

        if ($userType === 'stakeholder') {
            $map = is_array($rules->stakeholder_map) ? $rules->stakeholder_map : [];
            if ($stakeholderType && isset($map[$stakeholderType])) {
                return (string) $map[$stakeholderType];
            }

            return $this->fallbackColorKey();
        }

        if ($userType === 'student') {
            $studentMap = is_array($rules->student_map) ? $rules->student_map : [];
            if (! $plateNumber) {
                return $studentMap['no_plate'] ?? $this->fallbackColorKey();
            }

            $lastDigit = substr($plateNumber, -1);
            if (in_array($lastDigit, ['1', '2'], true)) {
                return $studentMap['12'] ?? $this->fallbackColorKey();
            }
            if (in_array($lastDigit, ['3', '4'], true)) {
                return $studentMap['34'] ?? $this->fallbackColorKey();
            }
            if (in_array($lastDigit, ['5', '6'], true)) {
                return $studentMap['56'] ?? $this->fallbackColorKey();
            }
            if (in_array($lastDigit, ['7', '8'], true)) {
                return $studentMap['78'] ?? $this->fallbackColorKey();
            }
            if (in_array($lastDigit, ['9', '0'], true)) {
                return $studentMap['90'] ?? $this->fallbackColorKey();
            }

            return $this->fallbackColorKey();
        }

        return $this->fallbackColorKey();
    }

    protected function fallbackColorKey(): string
    {
        $rules = StickerRule::getSingleton();
        $palette = is_array($rules->palette) ? $rules->palette : [];
        $firstKey = array_key_first($palette);
        if ($firstKey === null) {
            throw new \RuntimeException('Sticker palette is empty. Configure colors in Sticker Rules.');
        }

        return $firstKey;
    }

    /**
     * Generate next sticker number for a given color
     */
    public function generateNextStickerNumber(string $color): string
    {
        // Get or create the counter for this color
        $counter = StickerCounter::firstOrCreate(
            ['color' => $color],
            ['count' => 0]
        );

        // Find the next available number by checking existing vehicles
        // This ensures we don't create duplicates when vehicles are deleted
        $existingNumbers = \App\Models\Vehicle::where('color', $color)
            ->pluck('number')
            ->map(function ($number) {
                return (int) $number;
            })
            ->toArray();

        // Start from the counter's current value
        $startNumber = $counter->count;

        // Find the next available number
        $nextNumber = $startNumber + 1;
        while (in_array($nextNumber, $existingNumbers)) {
            $nextNumber++;
        }

        // Update the counter to reflect the new number
        $counter->update(['count' => $nextNumber]);

        // Return padded number (e.g., "0001")
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
