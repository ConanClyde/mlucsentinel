<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileUploadSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request has file uploads
        if ($request->hasFile('evidence_image') || $request->hasFile('license_image')) {
            $this->validateFileUploads($request);
        }

        return $next($request);
    }

    /**
     * Validate file uploads for security
     */
    protected function validateFileUploads(Request $request): void
    {
        $files = $request->allFiles();

        foreach ($files as $fieldName => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile, $fieldName);
                }
            } else {
                $this->validateSingleFile($file, $fieldName);
            }
        }
    }

    /**
     * Validate a single file
     */
    protected function validateSingleFile($file, string $fieldName): void
    {
        if (! $file) {
            return;
        }

        // Check file size (5MB max)
        if ($file->getSize() > (5 * 1024 * 1024)) {
            abort(413, "File {$fieldName} is too large. Maximum size is 5MB.");
        }

        // Check for suspicious file names
        if ($this->hasSuspiciousFileName($file->getClientOriginalName())) {
            abort(400, "File {$fieldName} has an invalid filename.");
        }

        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/heic', 'image/heif'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            abort(400, "File {$fieldName} must be a JPEG, PNG, HEIC, or HEIF image.");
        }

        // Verify file content
        if (! $this->verifyFileContent($file)) {
            abort(400, "File {$fieldName} content is invalid.");
        }
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
     * Verify file content matches its extension
     */
    protected function verifyFileContent($file): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        if (! $handle) {
            return false;
        }

        $header = fread($handle, 10);
        fclose($handle);

        $mimeType = $file->getMimeType();

        // Check file signatures based on MIME type
        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            return strpos($header, "\xFF\xD8\xFF") === 0;
        }

        if ($mimeType === 'image/png') {
            return strpos($header, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") === 0;
        }

        if ($mimeType === 'image/heic') {
            return strpos($header, "\x00\x00\x00\x20\x66\x74\x79\x70\x68\x65\x69\x63") === 0;
        }

        if ($mimeType === 'image/heif') {
            return strpos($header, "\x00\x00\x00\x20\x66\x74\x79\x70\x68\x65\x69\x66") === 0;
        }

        return true;
    }
}
