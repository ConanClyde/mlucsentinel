<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements ValidationRule
{
    protected array $allowedMimes;
    protected int $maxSize;
    protected array $allowedExtensions;
    protected bool $scanForMalware;

    public function __construct(
        array $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/heic', 'image/heif'],
        int $maxSize = 5120, // 5MB in KB
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'heic', 'heif'],
        bool $scanForMalware = false
    ) {
        $this->allowedMimes = $allowedMimes;
        $this->maxSize = $maxSize;
        $this->allowedExtensions = $allowedExtensions;
        $this->scanForMalware = $scanForMalware;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file.');
            return;
        }

        // Check file size
        if ($value->getSize() > ($this->maxSize * 1024)) {
            $fail("The :attribute must not be larger than " . ($this->maxSize / 1024) . "MB.");
            return;
        }

        // Check MIME type
        if (!in_array($value->getMimeType(), $this->allowedMimes)) {
            $fail('The :attribute must be a file of type: ' . implode(', ', $this->allowedExtensions) . '.');
            return;
        }

        // Check file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            $fail('The :attribute must have one of the following extensions: ' . implode(', ', $this->allowedExtensions) . '.');
            return;
        }

        // Verify file content matches extension
        if (!$this->verifyFileContent($value, $extension)) {
            $fail('The :attribute file content does not match its extension.');
            return;
        }

        // Check for suspicious file names
        if ($this->hasSuspiciousFileName($value->getClientOriginalName())) {
            $fail('The :attribute has an invalid filename.');
            return;
        }

        // Basic malware scanning (if enabled)
        if ($this->scanForMalware && $this->containsSuspiciousContent($value)) {
            $fail('The :attribute contains suspicious content.');
            return;
        }

        // Check image dimensions for image files
        if (in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            $this->validateImageDimensions($value, $fail);
        }
    }

    /**
     * Verify file content matches its extension
     */
    protected function verifyFileContent(UploadedFile $file, string $extension): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 10);
        fclose($handle);

        // Check file signatures
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'heic' => ["\x00\x00\x00\x20\x66\x74\x79\x70\x68\x65\x69\x63"],
            'heif' => ["\x00\x00\x00\x20\x66\x74\x79\x70\x68\x65\x69\x66"],
        ];

        if (isset($signatures[$extension])) {
            foreach ($signatures[$extension] as $signature) {
                if (strpos($header, $signature) === 0) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Check for suspicious file names
     */
    protected function hasSuspiciousFileName(string $filename): bool
    {
        $suspiciousPatterns = [
            '/\.(exe|bat|cmd|com|scr|pif|vbs|js|jar|php|asp|aspx|jsp)$/i',
            '/\.(sh|bash|zsh|fish)$/i',
            '/\.(sql|db|sqlite)$/i',
            '/[<>:"|?*]/', // Invalid filename characters
            '/^(con|prn|aux|nul|com[1-9]|lpt[1-9])$/i', // Windows reserved names
            '/\.{2,}/', // Multiple consecutive dots
            '/^\./', // Hidden files
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Basic malware content scanning
     */
    protected function containsSuspiciousContent(UploadedFile $file): bool
    {
        $content = file_get_contents($file->getPathname());
        
        // Check for suspicious strings
        $suspiciousStrings = [
            'eval(',
            'base64_decode(',
            'exec(',
            'system(',
            'shell_exec(',
            'passthru(',
            'file_get_contents(',
            'fopen(',
            'fwrite(',
            '<?php',
            '<script',
            'javascript:',
            'vbscript:',
        ];

        foreach ($suspiciousStrings as $string) {
            if (stripos($content, $string) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate image dimensions
     */
    protected function validateImageDimensions(UploadedFile $file, Closure $fail): void
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            if (!$imageInfo) {
                $fail('The :attribute is not a valid image.');
                return;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Minimum dimensions
            if ($width < 100 || $height < 100) {
                $fail('The :attribute image must be at least 100x100 pixels.');
                return;
            }

            // Maximum dimensions
            if ($width > 4000 || $height > 4000) {
                $fail('The :attribute image must not exceed 4000x4000 pixels.');
                return;
            }

            // Aspect ratio check (prevent extremely wide/tall images)
            $aspectRatio = $width / $height;
            if ($aspectRatio > 10 || $aspectRatio < 0.1) {
                $fail('The :attribute image has an invalid aspect ratio.');
                return;
            }

        } catch (\Exception $e) {
            $fail('The :attribute could not be processed as an image.');
        }
    }
}