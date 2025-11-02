<?php

namespace App\Services;

use App\Models\MapLocation;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class MapStickerGenerator
{
    /**
     * Generate a map location sticker with QR code
     */
    public function generateLocationSticker(MapLocation $location): string
    {
        try {
            return $this->generateSvgSticker($location);
        } catch (\Exception $e) {
            \Log::error('Map sticker generation failed: '.$e->getMessage());
            throw new \Exception('Failed to generate map location QR code: '.$e->getMessage());
        }
    }

    /**
     * Generate SVG sticker with QR code for map location
     */
    protected function generateSvgSticker(MapLocation $location): string
    {
        // Generate QR code URL pointing to security patrol check-in
        $base = rtrim((string) (config('app.url') ?? ''), '/');
        $qrData = ($base !== '' ? $base : '').'/security/patrol-checkin?location='.$location->id;

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
        $bg = $this->getHexColor($location->color);
        $textColor = $this->getContrastColor($location->color);
        $innerSize = $qrSize - 2 * $qrPadding;

        // Calculate scale
        $scale = $this->calculateQrScale($qrSvg, $innerSize);

        // Build SVG
        $svg = $this->buildSvgSticker(
            $width,
            $height,
            $qrSize,
            $qrPadding,
            $radius,
            $bg,
            $textColor,
            $scale,
            $qrSvg,
            $location->short_code,
            $location->color
        );

        // Generate filename and save
        $fileName = $this->generateFileName($location);

        // Store sticker
        Storage::disk('public')->put('map-stickers/'.$fileName, $svg);
        \Log::info("Map sticker saved successfully: {$fileName}");

        return '/storage/map-stickers/'.$fileName;
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
        string $shortCode,
        string $color
    ): string {
        // Truncate short code to maximum 5 characters
        $displayShortCode = mb_substr($shortCode, 0, 5);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
               "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">".
               "<rect width=\"100%\" height=\"100%\" fill=\"{$bg}\"/>".
               // QR Code Container with white background
               '<rect x="'.(($width - $qrSize) / 2)."\" y=\"60\" width=\"{$qrSize}\" height=\"{$qrSize}\" rx=\"{$radius}\" ry=\"{$radius}\" fill=\"#FFFFFF\" ".($color === 'white' ? 'stroke="#000" stroke-width="6"' : '').'/>'.
               // QR Code
               '<g transform="translate('.(($width - $qrSize) / 2 + $qrPadding).','.(60 + $qrPadding).") scale({$scale})\">".
                   preg_replace('/^.*?<svg[^>]*>|<\/svg>.*$/s', '', $qrSvg).
               '</g>'.
               // Title Text (smaller font)
               '<text x="50%" y="'.(60 + $qrSize + 80)."\" fill=\"{$textColor}\" font-size=\"42\" font-family=\"Arial, sans-serif\" font-weight=\"700\" text-anchor=\"middle\">MLUC CAMPUS MAP</text>".
               // Short Code (smaller font, max 5 chars)
               '<text x="50%" y="'.($height - 80)."\" fill=\"{$textColor}\" font-size=\"140\" font-family=\"Arial Black, Arial, sans-serif\" font-weight=\"900\" text-anchor=\"middle\">".htmlspecialchars($displayShortCode, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</text>'.
               '</svg>';
    }

    /**
     * Generate filename for sticker
     */
    protected function generateFileName(MapLocation $location): string
    {
        $sanitizedName = preg_replace('/[^A-Za-z0-9]+/', '_', strtolower($location->name));
        $sanitizedCode = preg_replace('/[^A-Za-z0-9]+/', '_', strtolower($location->short_code));
        $colorName = strtolower($location->color);

        return "map_{$colorName}_{$sanitizedCode}_{$sanitizedName}_{$location->id}.svg";
    }

    /**
     * Convert color to hex for background
     * Map locations always have hex colors from their type
     */
    protected function getHexColor(string $color): string
    {
        // Map locations use hex colors from their type's default_color
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }

        // Fallback to blue if invalid hex color provided
        return '#007BFF';
    }

    /**
     * Get contrasting text color based on background color
     * Always returns white for better visibility on all colored backgrounds
     */
    protected function getContrastColor(string $backgroundColor): string
    {
        // Always use white text for better visibility on all colored backgrounds
        return '#FFFFFF';
    }

    /**
     * Regenerate sticker for an existing location
     */
    public function regenerateSticker(MapLocation $location): string
    {
        // Delete old sticker if exists
        if ($location->sticker_path) {
            $oldPath = str_replace('/storage/', '', $location->sticker_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Generate new sticker
        $stickerPath = $this->generateLocationSticker($location);

        // Update the location with the new sticker path
        $location->update(['sticker_path' => $stickerPath]);

        return $stickerPath;
    }
}
