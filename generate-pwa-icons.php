<?php

/**
 * Quick PWA Icon Generator Script
 * Run: php generate-pwa-icons.php
 */
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
$outputDir = __DIR__.'/public/images/icons/';

// Ensure directory exists
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "🎨 Generating PWA Icons...\n\n";

foreach ($sizes as $size) {
    $filename = "icon-{$size}x{$size}.png";
    $filepath = $outputDir.$filename;

    // Create image
    $image = imagecreatetruecolor($size, $size);

    // Background gradient (simulate with dark color)
    $bgColor = imagecolorallocate($image, 27, 27, 24); // #1b1b18
    imagefill($image, 0, 0, $bgColor);

    // Text color
    $textColor = imagecolorallocate($image, 237, 237, 236); // #EDEDEC

    // Draw "M" letter
    $fontSize = $size * 0.5;
    $fontFile = null;

    // Try to use system font
    $systemFonts = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/System/Library/Fonts/Helvetica.ttc',
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/arial.ttf',
    ];

    foreach ($systemFonts as $font) {
        if (file_exists($font)) {
            $fontFile = $font;
            break;
        }
    }

    if ($fontFile) {
        // Calculate text position for centering
        $bbox = imagettfbbox($fontSize, 0, $fontFile, 'M');
        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]);
        $x = ($size - $textWidth) / 2;
        $y = ($size + $textHeight) / 2;

        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, 'M');

        // Add "SENTINEL" for larger icons
        if ($size >= 192) {
            $subtitleSize = $size * 0.08;
            $subtitleColor = imagecolorallocate($image, 161, 160, 154); // #A1A09A
            $bbox = imagettfbbox($subtitleSize, 0, $fontFile, 'SENTINEL');
            $textWidth = abs($bbox[4] - $bbox[0]);
            $x = ($size - $textWidth) / 2;
            $y = $size * 0.85;
            imagettftext($image, $subtitleSize, 0, $x, $y, $subtitleColor, $fontFile, 'SENTINEL');
        }
    } else {
        // Fallback: use built-in font
        $text = 'M';
        $x = ($size - imagefontwidth(5) * strlen($text)) / 2;
        $y = ($size - imagefontheight(5)) / 2;
        imagestring($image, 5, $x, $y, $text, $textColor);
    }

    // Save image
    imagepng($image, $filepath);
    imagedestroy($image);

    echo "✅ Created: {$filename}\n";
}

echo "\n🎉 Done! All icons generated in: {$outputDir}\n";
echo "📱 Now test your PWA at: http://127.0.0.1:8000\n";
