<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupFiles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:files {--name= : Custom backup name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a backup of application files';

    /**
     * Directories to backup
     */
    protected array $directoriesToBackup = [
        'storage/app/public',  // User uploads, stickers, receipts
        'public/storage',      // Symbolic link files
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting files backup...');

        try {
            // Check if ZipArchive is available
            if (! class_exists('ZipArchive')) {
                $this->error('ZipArchive extension is not installed. Please enable it in php.ini');

                return self::FAILURE;
            }

            // Create backup directory
            $backupPath = storage_path('app/backups/files');
            if (! file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Generate backup filename
            $filename = $this->option('name')
                ? $this->option('name').'.zip'
                : 'files-backup-'.date('Y-m-d_H-i-s').'.zip';

            $zipPath = $backupPath.'/'.$filename;

            // Create ZIP archive
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $this->error('Failed to create ZIP file');

                return self::FAILURE;
            }

            $totalFiles = 0;

            // Add directories to ZIP
            foreach ($this->directoriesToBackup as $directory) {
                $fullPath = base_path($directory);

                if (! file_exists($fullPath)) {
                    $this->warn("Directory not found: {$directory}");

                    continue;
                }

                $this->info("Backing up: {$directory}");
                $files = $this->addDirectoryToZip($zip, $fullPath, $directory);
                $totalFiles += $files;
            }

            $zip->close();

            // Get file size
            $fileSize = $this->formatBytes(filesize($zipPath));

            $this->info('✓ Files backup created successfully!');
            $this->info("  Location: {$zipPath}");
            $this->info("  Files: {$totalFiles}");
            $this->info("  Size: {$fileSize}");

            // Log successful backup
            Log::channel('security')->info('Files backup created', [
                'filename' => $filename,
                'files_count' => $totalFiles,
                'size' => filesize($zipPath),
                'path' => $zipPath,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: '.$e->getMessage());
            Log::channel('security')->error('Files backup exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Add directory contents to ZIP recursively
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $path, string $relativePath): int
    {
        $count = 0;

        if (! is_dir($path)) {
            return $count;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativeFilePath = $relativePath.'/'.substr($filePath, strlen($path) + 1);

                // Normalize path for ZIP (forward slashes)
                $relativeFilePath = str_replace('\\', '/', $relativeFilePath);

                $zip->addFile($filePath, $relativeFilePath);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Format bytes to human-readable size
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
